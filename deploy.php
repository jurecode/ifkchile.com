<?php
/**
 * Actualiza el sitio del servidor con la última versión publicada en GitHub.
 * Lo llama el panel (Repositorio → "Actualizar servidor").
 *
 * Funciona de dos formas, en este orden:
 *   1. Si la carpeta es un clon de git y el hosting deja ejecutar comandos → git fetch + reset.
 *   2. Si no → descarga el ZIP del repositorio desde GitHub y reemplaza los archivos.
 *      No necesita git ni permisos especiales: sirve en hosting compartido.
 *
 * CONFIGURACIÓN EN EL SERVIDOR (una sola vez, por FTP o el administrador de archivos):
 *   Crear el archivo  storage/deploy.json  con este contenido:
 *
 *     {"clave":"una-clave-larga-e-inventada",
 *      "repo":"jurecode/ifkchile.com",
 *      "rama":"main",
 *      "usuario":"",
 *      "token":""}
 *
 *   · clave → la misma que se escribe en el panel.
 *   · repo  → usuario/repositorio de GitHub.
 *   · usuario y token → sólo si el repositorio es privado.
 *
 * storage/ no viaja en el repositorio, así que esta configuración
 * sobrevive a todas las actualizaciones.
 */
declare(strict_types=1);

@set_time_limit(300);
header('Content-Type: text/plain; charset=utf-8');

$RAIZ = __DIR__;
$cfg  = [];
if (is_file($RAIZ . '/storage/deploy.json')) {
    $cfg = json_decode((string)file_get_contents($RAIZ . '/storage/deploy.json'), true) ?: [];
}

$CLAVE   = (string)($cfg['clave']   ?? '');
$RAMA    = (string)($cfg['rama']    ?? 'main') ?: 'main';
$REPO    = (string)($cfg['repo']    ?? '');
$USUARIO = (string)($cfg['usuario'] ?? '');
$TOKEN   = (string)($cfg['token']   ?? '');

if ($CLAVE === '') {
    http_response_code(500);
    exit("Falta storage/deploy.json con la clave de despliegue.\n");
}
if (!hash_equals($CLAVE, (string)($_GET['clave'] ?? ''))) {
    http_response_code(403);
    exit("Clave incorrecta.\n");
}

/* Normaliza github.com/usuario/repo.git → usuario/repo */
function repo_corto(string $repo): string {
    $r = preg_replace('#^https?://#', '', trim($repo));
    $r = preg_replace('#^[^@]*@#', '', (string)$r);
    $r = preg_replace('#^(www\.)?github\.com/#', '', (string)$r);
    return trim(preg_replace('#\.git$#', '', (string)$r), '/');
}

function ocultar(string $texto, string $token): string {
    return $token === '' ? $texto : str_replace([$token, rawurlencode($token)], '***', $texto);
}

$log = [];

/* =====================================================================
 |  MÉTODO 1 — git (cuando la carpeta es un clon y se pueden correr comandos)
 * ===================================================================== */
function ejecutar(array $cmd, string $dir): array {
    if (!function_exists('proc_open')) return [127, 'proc_open deshabilitado'];
    $p = @proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $t, $dir,
        array_merge($_ENV, ['HOME' => sys_get_temp_dir(), 'GIT_TERMINAL_PROMPT' => '0']));
    if (!is_resource($p)) return [127, 'no se pudo iniciar el comando'];
    $salida = stream_get_contents($t[1]) . stream_get_contents($t[2]);
    fclose($t[1]); fclose($t[2]);
    return [proc_close($p), trim($salida)];
}

if (is_dir($RAIZ . '/.git') && ejecutar(['git', '--version'], $RAIZ)[0] === 0) {
    $origen = ($REPO !== '' && $TOKEN !== '')
        ? 'https://' . rawurlencode($USUARIO ?: 'x-access-token') . ':' . rawurlencode($TOKEN) . '@github.com/' . repo_corto($REPO) . '.git'
        : 'origin';

    foreach ([
        ['git', 'fetch', $origen, $RAMA],
        ['git', 'reset', '--hard', 'FETCH_HEAD'],
    ] as $cmd) {
        [$cod, $out] = ejecutar($cmd, $RAIZ);
        $log[] = '$ ' . implode(' ', array_map(fn($a) => $a === $origen ? '<origen>' : $a, $cmd)) . "\n" . ocultar($out, $TOKEN);
        if ($cod !== 0) {
            http_response_code(500);
            exit(implode("\n\n", $log) . "\n\nFalló la actualización por git.\n");
        }
    }
    [, $head] = ejecutar(['git', 'log', '-1', '--pretty=%h · %s · %cr'], $RAIZ);
    exit(implode("\n\n", $log) . "\n\nSitio actualizado por git.\nVersión actual: " . $head . "\n");
}

/* =====================================================================
 |  MÉTODO 2 — descarga directa del ZIP publicado en GitHub
 * ===================================================================== */
$repo = repo_corto($REPO);
if ($repo === '' || !str_contains($repo, '/')) {
    http_response_code(500);
    exit("Esta carpeta no es un clon de git, así que hay que actualizar por descarga.\n" .
         "Agrega \"repo\":\"usuario/repositorio\" en storage/deploy.json.\n");
}

$tmp = $RAIZ . '/storage/tmp-deploy';
if (!is_dir($tmp) && !@mkdir($tmp, 0775, true)) {
    http_response_code(500);
    exit("No se pudo crear storage/tmp-deploy (¿permisos de escritura en storage/?).\n");
}
foreach (glob($tmp . '/*') ?: [] as $viejo) { borrar_ruta($viejo); }

$zip = $tmp . '/repo.zip';
$url = $TOKEN !== ''
    ? "https://api.github.com/repos/$repo/zipball/$RAMA"
    : "https://codeload.github.com/$repo/zip/refs/heads/$RAMA";

$detalle = null;
$bytes   = descargar($url, $zip, $TOKEN, $detalle);
if ($bytes <= 0) {
    http_response_code(500);
    exit("No se pudo descargar el repositorio desde GitHub.\n" .
         'Repositorio: ' . $repo . ' · rama: ' . $RAMA . "\n" .
         'Respuesta:   ' . ($detalle ?? 'sin detalle') . "\n");
}
$log[] = 'Descargado ' . round($bytes / 1024) . ' KB desde GitHub (' . $repo . ' · ' . $RAMA . ')';

$destino = $tmp . '/extraido';
@mkdir($destino, 0775, true);
if (!extraer($zip, $destino)) {
    http_response_code(500);
    exit(implode("\n", $log) . "\n\nNo se pudo descomprimir el archivo (falta la extensión zip o phar).\n");
}

/* GitHub empaqueta todo dentro de una carpeta: repo-main/ */
$carpetas = array_values(array_filter(glob($destino . '/*') ?: [], 'is_dir'));
$origenArchivos = count($carpetas) === 1 ? $carpetas[0] : $destino;

if (!is_file($origenArchivos . '/index.php') || !is_file($origenArchivos . '/config.php')) {
    http_response_code(500);
    exit(implode("\n", $log) . "\n\nEl contenido descargado no parece el sitio (no se encontró index.php).\n");
}

$copiados = copiar_sitio($origenArchivos, $RAIZ, ['storage', '.git', '.github']);
borrar_ruta($tmp);

$log[] = "Archivos actualizados: $copiados";
echo implode("\n", $log) . "\n\nSitio actualizado por descarga.\n" .
     "Nota: este método copia y reemplaza archivos; los que se eliminen del repositorio hay que borrarlos a mano.\n";

/* ---------------- utilidades ---------------- */

function descargar(string $url, string $destino, string $token, ?string &$detalle = null): int {
    $cabeceras = ['User-Agent: IFK-Deploy', 'Accept: application/vnd.github+json'];
    if ($token !== '') $cabeceras[] = 'Authorization: Bearer ' . $token;

    if (function_exists('curl_init')) {
        $fh = @fopen($destino, 'w');
        if (!$fh) { $detalle = 'no se pudo escribir en storage/tmp-deploy'; return 0; }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fh,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 180,
            CURLOPT_HTTPHEADER     => $cabeceras,
            CURLOPT_FAILONERROR    => true,
        ]);
        curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        fclose($fh);
        $detalle = 'HTTP ' . $http . ($err !== '' ? ' · ' . $err : '');
        if ($http === 404) {
            $detalle .= $token === ''
                ? ' · el repositorio es privado o no existe: agrega "token" en storage/deploy.json'
                : ' · revisa el nombre del repositorio, la rama y los permisos del token';
        }
        if ($http === 401 || $http === 403) $detalle .= ' · el token no tiene permiso sobre este repositorio';
    } else {
        $ctx = stream_context_create(['http' => ['header' => implode("\r\n", $cabeceras), 'timeout' => 180, 'ignore_errors' => true]]);
        $datos   = @file_get_contents($url, false, $ctx);
        $cab     = function_exists('http_get_last_response_headers') ? http_get_last_response_headers() : [];
        $detalle = $cab[0] ?? ($datos === false ? 'sin respuesta' : 'descarga completada');
        if ($datos === false) return 0;
        @file_put_contents($destino, $datos);
    }
    return is_file($destino) ? (int)filesize($destino) : 0;
}

function extraer(string $zip, string $destino): bool {
    if (class_exists('ZipArchive')) {
        $z = new ZipArchive();
        if ($z->open($zip) === true) { $z->extractTo($destino); $z->close(); return true; }
    }
    if (class_exists('PharData')) {
        try { (new PharData($zip))->extractTo($destino, null, true); return true; }
        catch (Throwable $e) { return false; }
    }
    return false;
}

function copiar_sitio(string $desde, string $hacia, array $excluir): int {
    $n = 0;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($desde, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($items as $item) {
        $rel = ltrim(str_replace($desde, '', $item->getPathname()), '/\\');
        $primer = explode(DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $rel))[0];
        if (in_array($primer, $excluir, true)) continue;
        $destino = $hacia . '/' . $rel;
        if ($item->isDir()) {
            if (!is_dir($destino)) @mkdir($destino, 0775, true);
        } elseif (@copy($item->getPathname(), $destino)) {
            $n++;
        }
    }
    return $n;
}

function borrar_ruta(string $ruta): void {
    if (is_file($ruta) || is_link($ruta)) { @unlink($ruta); return; }
    if (!is_dir($ruta)) return;
    foreach (scandir($ruta) ?: [] as $i) {
        if ($i === '.' || $i === '..') continue;
        borrar_ruta($ruta . '/' . $i);
    }
    @rmdir($ruta);
}
