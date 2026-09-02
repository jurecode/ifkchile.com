<?php
/**
 * Actualiza el sitio del servidor con la última versión publicada en GitHub.
 * Lo llama el panel (Repositorio → "Actualizar servidor").
 *
 * CONFIGURACIÓN EN EL SERVIDOR (una sola vez, por FTP o consola):
 *   Crear el archivo  storage/deploy.json  con este contenido:
 *
 *     {"clave":"una-clave-larga-e-inventada","rama":"main",
 *      "repo":"","usuario":"","token":""}
 *
 *   · clave  → la misma que se escribe en el panel.
 *   · repo/usuario/token → sólo si el repositorio es privado.
 *
 * Ese archivo vive en storage/, que no viaja en el repositorio: así la
 * configuración del servidor sobrevive a cada actualización.
 */
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$cfg = [];
if (is_file(__DIR__ . '/storage/deploy.json')) {
    $cfg = json_decode((string)file_get_contents(__DIR__ . '/storage/deploy.json'), true) ?: [];
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

$dir = __DIR__;
if (!is_dir($dir . '/.git')) {
    http_response_code(500);
    exit("Esta carpeta no es un clon del repositorio.\n");
}

function ejecutar(array $cmd, string $dir): array {
    if (!function_exists('proc_open')) return [127, 'El hosting no permite ejecutar comandos.'];
    $p = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $t, $dir,
        array_merge($_ENV, ['HOME' => sys_get_temp_dir(), 'GIT_TERMINAL_PROMPT' => '0']));
    if (!is_resource($p)) return [127, 'No se pudo iniciar git.'];
    $salida = stream_get_contents($t[1]) . stream_get_contents($t[2]);
    fclose($t[1]); fclose($t[2]);
    return [proc_close($p), trim($salida)];
}

$origen = ($REPO !== '' && $TOKEN !== '')
    ? 'https://' . rawurlencode($USUARIO ?: 'x-access-token') . ':' . rawurlencode($TOKEN) . '@' . preg_replace('#^https?://#', '', $REPO)
    : 'origin';

$log = [];
foreach ([
    ['git', 'fetch', $origen, $RAMA],
    ['git', 'reset', '--hard', 'FETCH_HEAD'],
    ['git', 'clean', '-fd', ':!storage'],
] as $cmd) {
    [$cod, $out] = ejecutar($cmd, $dir);
    if ($TOKEN !== '') $out = str_replace([$TOKEN, rawurlencode($TOKEN)], '***', $out);
    $log[] = '$ ' . implode(' ', array_map(fn($a) => $a === $origen ? '<origen>' : $a, $cmd)) . "\n" . $out;
    if ($cod !== 0) {
        http_response_code(500);
        exit(implode("\n\n", $log) . "\n\nFalló la actualización.\n");
    }
}

[, $head] = ejecutar(['git', 'log', '-1', '--pretty=%h · %s · %cr'], $dir);
echo implode("\n\n", $log) . "\n\nSitio actualizado.\nVersión actual: " . $head . "\n";
