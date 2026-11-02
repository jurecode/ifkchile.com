<?php
/**
 * La conexión con GitHub: ejecutar git, hablar con la API y respaldar.
 *
 * Vivía dentro de admin.php, pero ahora hay dos paneles que lo necesitan:
 * el técnico (/admin), donde se escriben las palabras subir y traer, y el de
 * fotos del cliente (/fotos), que respalda solo y en silencio lo que el
 * cliente cambia. Mejor una sola copia de esto que dos que se desincronicen.
 */
declare(strict_types=1);

if (!defined('RAIZ')) define('RAIZ', dirname(__DIR__));

/** Lee config.php. Devuelve null si todavía no existe. */
function cargar_config(): ?array {
    if (!is_file(RAIZ . '/config.php')) return null;
    $c = require RAIZ . '/config.php';
    if (!is_array($c)) return null;
    /* Sólo el token y la clave son obligatorios; el resto tiene valor por defecto. */
    $c += ['token' => '', 'repo' => '', 'rama' => 'main', 'clave' => ''];
    if (trim((string)$c['rama']) === '') $c['rama'] = 'main';
    return $c;
}



/**
 * Ejecuta un programa y devuelve [código de salida, salida completa].
 * Lee salida y errores a la vez —si no, un comando hablador puede quedarse
 * trabado llenando su tubo— y corta solo si algo se cuelga.
 */
function correr(array $args, int $tope = 240): array {
    $tubos = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $env   = ['GIT_TERMINAL_PROMPT' => '0', 'HOME' => RAIZ, 'PATH' => getenv('PATH') ?: '/usr/bin:/bin'];

    $proc = @proc_open($args, $tubos, $t, RAIZ, $env);
    if (!is_resource($proc)) return [127, 'No se pudo ejecutar: ' . $args[0]];

    stream_set_blocking($t[1], false);
    stream_set_blocking($t[2], false);

    $salida = '';
    $inicio = time();
    $estado = ['running' => true, 'exitcode' => 1];
    while (true) {
        $leer = [$t[1], $t[2]];
        $escribir = $excepto = null;
        if (@stream_select($leer, $escribir, $excepto, 1) > 0) {
            foreach ($leer as $tubo) $salida .= (string)fread($tubo, 16384);
        }
        $estado = proc_get_status($proc);
        if (!$estado['running']) break;
        if (time() - $inicio > $tope) {
            proc_terminate($proc, 9);
            $salida .= "\n[El comando pasó de " . $tope . " segundos y se detuvo.]";
            break;
        }
    }
    foreach ([$t[1], $t[2]] as $tubo) {
        while (($resto = fread($tubo, 16384)) !== '' && $resto !== false) $salida .= $resto;
        fclose($tubo);
    }
    $cod = proc_close($proc);
    if (($estado['exitcode'] ?? -1) >= 0) $cod = $estado['exitcode'];

    return [$cod, trim($salida)];
}

/** Lo mismo, pero para git y sin que se queje del dueño de la carpeta. */
function git(array $args): array {
    return correr(array_merge(['git', '-c', 'safe.directory=' . RAIZ], $args));
}

function hay_git(): bool { return git(['--version'])[0] === 0; }
function es_repo(): bool { return is_dir(RAIZ . '/.git'); }



/** Le pregunta algo a GitHub. Devuelve [código HTTP, respuesta]. */
function api(array $c, string $metodo, string $ruta, ?array $datos = null): array {
    $url    = 'https://api.github.com' . $ruta;
    $cuerpo = $datos === null ? null : json_encode($datos, JSON_UNESCAPED_SLASHES);
    $cab    = [
        'Accept: application/vnd.github+json',
        'Authorization: Bearer ' . trim((string)$c['token']),
        'User-Agent: panel-web',
        'X-GitHub-Api-Version: 2022-11-28',
        'Content-Type: application/json',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $metodo,
            CURLOPT_HTTPHEADER     => $cab,
            CURLOPT_TIMEOUT        => 20,
        ]);
        if ($cuerpo !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $cuerpo);
        $resp = (string)curl_exec($ch);
        $cod  = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if (PHP_VERSION_ID < 80000) curl_close($ch);   // en PHP 8 ya no hace falta
    } else {
        $ctx  = stream_context_create(['http' => [
            'method'         => $metodo,
            'header'         => implode("\r\n", $cab),
            'content'        => $cuerpo,
            'ignore_errors'  => true,
            'timeout'        => 20,
        ]]);
        $resp = (string)@file_get_contents($url, false, $ctx);
        $cod  = 0;
        foreach ($http_response_header ?? [] as $h) {
            if (preg_match('#^HTTP/\S+ (\d+)#', $h, $m)) $cod = (int)$m[1];
        }
    }

    return [$cod, json_decode($resp, true) ?: []];
}

/** Quién es el dueño del token. */
function github_usuario(array $c): string {
    static $u = null;
    if ($u !== null) return $u;
    [$cod, $d] = api($c, 'GET', '/user');
    return $u = ($cod === 200 && !empty($d['login'])) ? (string)$d['login'] : '';
}

/** "usuario/proyecto", sacado de config.php o, si va vacío, del propio dominio. */
function repo_nombre(array $c): string {
    static $n = null;
    if ($n !== null) return $n;

    $r = trim((string)($c['repo'] ?? ''));
    if ($r === '') {
        $r = preg_replace('/:\d+$/', '', strtolower((string)($_SERVER['HTTP_HOST'] ?? '')));
        $r = preg_replace('/^www\./', '', (string)$r);
    }
    $r = preg_replace('#^(https?://)?([^@/]*@)?github\.com/#i', '', $r);
    $r = preg_replace('#\.git$#i', '', rtrim((string)$r, '/'));
    if (!str_contains((string)$r, '/')) {
        $duenio = github_usuario($c);
        $r = ($duenio !== '' ? $duenio . '/' : '') . $r;
    }
    return $n = (string)$r;
}

/** Dirección del repositorio sin usuario ni contraseña. */
function url_limpia(array $c): string {
    return 'https://github.com/' . repo_nombre($c) . '.git';
}

/** La misma dirección, pero con el token para poder subir o bajar. */
function url_token(array $c): string {
    return 'https://x-access-token:' . rawurlencode(trim((string)$c['token']))
         . '@github.com/' . repo_nombre($c) . '.git';
}

/** El token nunca se muestra en pantalla. */
function ocultar(string $texto, string $token): string {
    $token = trim($token);
    return $token === '' ? $texto : str_replace([$token, rawurlencode($token)], '••••••', $texto);
}

/** Si la carpeta todavía no es un repositorio, la prepara. */
function preparar(array $c): void {
    if (!es_repo()) git(['init', '-b', $c['rama']]);

    [, $actual] = git(['remote', 'get-url', 'origin']);
    if (trim($actual) !== url_limpia($c)) {
        git(['remote', 'remove', 'origin']);
        git(['remote', 'add', 'origin', url_limpia($c)]);
    }

    /* config.php lleva el token: pase lo que pase, nunca viaja a GitHub. */
    $gi  = RAIZ . '/.gitignore';
    $txt = is_file($gi) ? (string)file_get_contents($gi) : '';
    if (!preg_match('#^\s*/?config\.php\s*$#m', $txt)) {
        @file_put_contents($gi, ($txt === '' ? '' : rtrim($txt) . "\n") . "config.php\n");
    }
    if (git(['ls-files', '--error-unmatch', 'config.php'])[0] === 0) {
        git(['rm', '--cached', '-q', 'config.php']);
    }
}
/* ------------------------------------------------------------------ */
/* Respaldo silencioso                                                 */
/* ------------------------------------------------------------------ */

/**
 * Guarda en GitHub lo que el cliente cambió desde el panel de fotos.
 *
 * Se llama sola, sin que nadie escriba nada, y pase lo que pase no debe
 * estorbar: la foto ya quedó en el servidor y el sitio ya la está mostrando.
 * Si GitHub no contesta, el cambio sigue en pie y el panel técnico lo verá
 * como pendiente la próxima vez que alguien escriba "estado".
 *
 * @return array{0:bool,1:string} [salió bien, motivo corto para el registro]
 */
function respaldar(string $mensaje): array {
    $c = cargar_config();
    if (!$c || trim((string)$c['token']) === '') return [false, 'sin configuración de GitHub'];
    if (!hay_git())                              return [false, 'este servidor no tiene git'];

    preparar($c);

    /* Si GitHub va más adelante no se fuerza nada: eso lo resuelve una
       persona desde /admin, no un respaldo automático. */
    if (git(['fetch', url_token($c), $c['rama']])[0] !== 0) return [false, 'no se pudo leer GitHub'];
    if (git(['rev-parse', '--verify', 'HEAD'])[0] === 0
     && git(['rev-parse', '--verify', 'FETCH_HEAD'])[0] === 0) {
        [, $atras] = git(['rev-list', '--count', 'HEAD..FETCH_HEAD']);
        if ((int)trim($atras) > 0) return [false, 'GitHub tiene cambios más nuevos'];
    }

    git(['add', '-A', 'assets/img/subidas', 'contenido/imagenes.json']);
    [, $sal] = git(['-c', 'user.name=Panel de fotos', '-c', 'user.email=panel@localhost',
                    'commit', '-m', $mensaje]);
    if (str_contains($sal, 'nothing to commit')) return [true, 'no había nada que guardar'];

    [$cod, $sal] = git(['push', url_token($c), 'HEAD:refs/heads/' . $c['rama']]);
    return $cod === 0 ? [true, 'guardado en GitHub'] : [false, 'GitHub no aceptó el respaldo'];
}
