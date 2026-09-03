<?php
/**
 * Panel — una sola página y una sola caja.
 * Escribes una palabra y se ejecuta:
 *
 *   estado   → cómo está la carpeta y qué falta por subir
 *   subir    → guarda todo y lo manda a GitHub  (subir arreglé el logo)
 *   traer    → baja de GitHub los cambios nuevos
 *   ayuda    → la lista de palabras
 *   salir    → cierra la sesión
 *
 * Los datos (token, dirección, rama y clave) están en config.php.
 */
declare(strict_types=1);

define('RAIZ', __DIR__);

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

$CFG = cargar_config();

session_name('panel');
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure'   => !empty($_SERVER['HTTPS']),
]);

/* ------------------------------------------------------------------ */
/* Herramientas                                                        */
/* ------------------------------------------------------------------ */

/** Ejecuta un programa y devuelve [código de salida, salida completa]. */
function correr(array $args): array {
    $tubos = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $env   = ['GIT_TERMINAL_PROMPT' => '0', 'HOME' => RAIZ, 'PATH' => getenv('PATH') ?: '/usr/bin:/bin'];

    $proc = @proc_open($args, $tubos, $t, RAIZ, $env);
    if (!is_resource($proc)) return [127, 'No se pudo ejecutar: ' . $args[0]];

    $salida = stream_get_contents($t[1]) . stream_get_contents($t[2]);
    fclose($t[1]); fclose($t[2]);

    return [proc_close($proc), trim($salida)];
}

/** Lo mismo, pero para git y sin que se queje del dueño de la carpeta. */
function git(array $args): array {
    return correr(array_merge(['git', '-c', 'safe.directory=' . RAIZ], $args));
}

function hay_git(): bool { return git(['--version'])[0] === 0; }
function es_repo(): bool { return is_dir(RAIZ . '/.git'); }

/* ---------------- Primera vez ---------------- */

/** Deja escrito config.php en esta misma carpeta. */
function guardar_config(string $token, string $clave, string $repo): bool {
    $php = "<?php\n"
         . "/* Escrito por el panel el " . date('d-m-Y H:i') . ". No se sube a GitHub. */\n\n"
         . "return [\n"
         . "    'token' => " . var_export($token, true) . ",\n"
         . "    'clave' => " . var_export($clave, true) . ",\n"
         . "    'repo'  => " . var_export($repo, true) . ",\n"
         . "];\n";

    if (@file_put_contents(RAIZ . '/config.php', $php, LOCK_EX) === false) return false;
    @chmod(RAIZ . '/config.php', 0600);
    return true;
}

/** Que el navegador no pueda abrir config.php aunque PHP se caiga. */
function proteger_config(): void {
    $f   = RAIZ . '/.htaccess';
    $txt = is_file($f) ? (string)file_get_contents($f) : '';
    if (str_contains($txt, 'config.php')) return;

    $bloque = "\n# El archivo con el token no se sirve nunca por el navegador\n"
            . "<FilesMatch \"^config(-ejemplo)?\\.php$\">\n"
            . "  <IfModule mod_authz_core.c>\n    Require all denied\n  </IfModule>\n"
            . "  <IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n  </IfModule>\n"
            . "</FilesMatch>\n";

    @file_put_contents($f, ($txt === '' ? '' : rtrim($txt) . "\n") . $bloque, LOCK_EX);
}

/* ---------------- GitHub ---------------- */

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
/* Las palabras                                                        */
/* ------------------------------------------------------------------ */

/** @return array{0:bool,1:string,2:string} [salió bien, aviso, consola] */
function palabra_estado(array $c): array {
    preparar($c);
    $l   = [];
    $l[] = 'Carpeta:     ' . RAIZ;
    $l[] = 'Repositorio: ' . url_limpia($c);

    $guardado = git(['rev-parse', '--verify', 'HEAD'])[0] === 0;   // ¿ya hay algo guardado?
    [, $rama]  = git(['symbolic-ref', '--short', 'HEAD']);
    [, $sucio] = git(['status', '--porcelain']);
    $pend = $sucio === '' ? 0 : count(explode("\n", $sucio));

    $l[] = 'Rama:        ' . ($rama ?: $c['rama']);
    $l[] = 'Último cambio: ' . ($guardado ? git(['log', '-1', '--pretty=%h · %s · %cr'])[1] : 'todavía no hay ninguno');
    $l[] = 'Sin guardar: ' . $pend . ($pend ? ' archivo(s)' : '');

    [$cod, $sal] = git(['fetch', url_token($c), $c['rama']]);
    if ($cod !== 0) {
        $l[] = '';
        $l[] = 'No se pudo leer GitHub. Revisa el token, la dirección y la rama.';
        $l[] = ocultar($sal, $c['token']);
    } elseif (!$guardado) {
        $l[] = '';
        $l[] = 'Esta carpeta está vacía para git: escribe "traer" para bajar lo que hay en GitHub.';
    } else {
        [, $atras]    = git(['rev-list', '--count', 'HEAD..FETCH_HEAD']);
        [, $adelante] = git(['rev-list', '--count', 'FETCH_HEAD..HEAD']);
        $l[] = '';
        $l[] = 'En GitHub y no aquí: ' . (int)trim($atras) . '  →  escribe "traer"';
        $l[] = 'Aquí y no en GitHub: ' . (int)trim($adelante) . '  →  escribe "subir"';
    }

    if ($pend) { $l[] = ''; $l[] = $sucio; }

    return [true, 'Así está la carpeta:', implode("\n", $l)];
}

function palabra_subir(array $c, string $mensaje): array {
    preparar($c);
    $mensaje = $mensaje !== '' ? $mensaje : 'Cambios del sitio ' . date('d-m-Y H:i');
    $log     = [];

    /* Si GitHub va más adelante, primero hay que traer: si no, quedan dos historias. */
    [$cod, $sal] = git(['fetch', url_token($c), $c['rama']]);
    $log[] = "$ git fetch\n" . (ocultar($sal, $c['token']) ?: 'ok');
    if ($cod !== 0) {
        return [false, 'No se pudo leer GitHub. Revisa el token, la dirección y la rama.', implode("\n\n", $log)];
    }
    $guardado = git(['rev-parse', '--verify', 'HEAD'])[0] === 0;
    $enGitHub = git(['rev-parse', '--verify', 'FETCH_HEAD'])[0] === 0;

    if (!$guardado && $enGitHub) {
        return [false, 'Esta carpeta todavía no tiene lo que hay en GitHub. Escribe primero "traer".', implode("\n\n", $log)];
    }
    if ($guardado && $enGitHub) {
        [, $atras] = git(['rev-list', '--count', 'HEAD..FETCH_HEAD']);
        if ((int)trim($atras) > 0) {
            return [false, 'En GitHub hay ' . (int)trim($atras) . ' cambio(s) más nuevos. Escribe primero "traer".', implode("\n\n", $log)];
        }
    }

    [, $sal] = git(['add', '-A']);
    $log[] = "$ git add -A\n" . ($sal ?: 'ok');

    [, $sal] = git(['-c', 'user.name=Panel', '-c', 'user.email=panel@localhost', 'commit', '-m', $mensaje]);
    $log[]  = "$ git commit -m \"$mensaje\"\n" . $sal;
    $nada   = str_contains($sal, 'nothing to commit');

    [$cod, $sal] = git(['push', url_token($c), 'HEAD:refs/heads/' . $c['rama']]);
    $log[] = "$ git push\n" . (ocultar($sal, $c['token']) ?: 'ok');

    if ($cod !== 0) {
        $motivo = (str_contains($sal, 'non-fast-forward') || str_contains($sal, 'rejected'))
            ? 'GitHub tiene cambios más nuevos. Escribe "traer" y vuelve a intentarlo.'
            : 'GitHub no aceptó la subida. Revisa el token y sus permisos.';
        return [false, $motivo, implode("\n\n", $log)];
    }
    return [true, $nada ? 'No había nada nuevo: GitHub ya estaba al día.' : 'Listo, subido a GitHub.', implode("\n\n", $log)];
}

function palabra_traer(array $c, string $modo = ''): array {
    preparar($c);
    $log    = [];
    $manda  = strtolower(strtr(trim($modo), 'áéíóú', 'aeiou')) === 'github';   // desempate

    $guardado = git(['rev-parse', '--verify', 'HEAD'])[0] === 0;
    [, $sucio] = git(['status', '--porcelain']);

    /* Si aquí hay cambios sueltos, primero se guardan: así nada se pierde al
       juntar, y no queda uno atrapado entre "subir" y "traer".
       (Recién instalado no hay nada que guardar todavía.) */
    if ($guardado && $sucio !== '') {
        git(['add', '-A']);
        [, $sal] = git(['-c', 'user.name=Panel', '-c', 'user.email=panel@localhost',
                        'commit', '-m', 'Cambios guardados antes de traer ' . date('d-m-Y H:i')]);
        $log[] = "$ git commit\n" . $sal;
    }

    [$cod, $sal] = git(['fetch', url_token($c), $c['rama']]);
    $log[] = "$ git fetch\n" . (ocultar($sal, $c['token']) ?: 'ok');
    if ($cod !== 0) {
        return [false, 'No se pudo leer GitHub. Revisa el token, la dirección y la rama.', implode("\n\n", $log)];
    }

    /* Desempate: mandan las de GitHub. Lo que había aquí no se pierde, queda
       guardado en una rama aparte por si hay que ir a buscarlo. */
    if ($manda) {
        if ($guardado) {
            $respaldo = 'respaldo-' . date('Ymd-His');
            git(['branch', $respaldo]);
            $log[] = '$ git branch ' . $respaldo . "\nok";
        }
        [$cod, $sal] = git(['reset', '--hard', 'FETCH_HEAD']);
        $log[] = "$ git reset --hard\n" . ($sal ?: 'ok');
        if ($cod !== 0) {
            return [false, 'No se pudo dejar la carpeta igual que GitHub. Mira el detalle.', implode("\n\n", $log)];
        }
        return [true, 'Listo, esta carpeta quedó igual que GitHub'
            . (isset($respaldo) ? '. Lo que había aquí quedó guardado en "' . $respaldo . '".' : '.'),
            implode("\n\n", $log)];
    }

    /* Camino normal: GitHub va adelante y aquí no hay nada propio que juntar.
       Si algún archivo suelto fuera a ser pisado, git se detiene solo. */
    [$cod, $sal] = git(['merge', '--ff-only', 'FETCH_HEAD']);

    if ($cod !== 0 && str_contains($sal, 'would be overwritten')) {
        $log[] = "$ git merge\n" . $sal;
        return [false, 'Hay archivos en esta carpeta que GitHub también tiene. Bórralos o renómbralos y vuelve a escribir "traer".', implode("\n\n", $log)];
    }

    /* Las dos partes cambiaron cosas: se juntan de verdad. */
    if ($cod !== 0) {
        [$cod, $sal] = git(['-c', 'user.name=Panel', '-c', 'user.email=panel@localhost',
                            'merge', '--no-edit', 'FETCH_HEAD']);
        if ($cod !== 0) {
            git(['merge', '--abort']);
            $log[] = "$ git merge\n" . $sal;
            $motivo = str_contains($sal, 'unrelated histories')
                ? 'Esta carpeta y GitHub empezaron por separado, así que no puedo juntarlas desde aquí.'
                : 'GitHub y esta carpeta tocaron lo mismo. Quedó todo como estaba: si quieres que manden las de GitHub, escribe "traer github" (lo de aquí se guarda aparte).';
            return [false, $motivo, implode("\n\n", $log)];
        }
    }

    $log[] = "$ git merge\n" . ($sal ?: 'ok');

    [, $adelante] = git(['rev-list', '--count', 'FETCH_HEAD..HEAD']);
    $aviso = (int)trim($adelante) > 0
        ? 'Listo, se juntó con GitHub. Ahora escribe "subir" para dejarlo todo igual allá.'
        : 'Listo, esta carpeta quedó igual que GitHub.';

    return [true, $aviso, implode("\n\n", $log)];
}

function palabra_instalar(array $c): array {
    $log = [];

    $duenio = github_usuario($c);
    if ($duenio === '') {
        return [false, 'GitHub no contestó quién eres: revisa el token, o que este servidor tenga salida a internet.', ''];
    }
    $nombre = repo_nombre($c);
    if (!str_contains($nombre, '/') || str_ends_with($nombre, '/')) {
        return [false, 'No sé cómo se llama el repositorio. Escríbelo en config.php, en "repo".', ''];
    }
    $log[] = 'Cuenta:      ' . $duenio . "\nRepositorio: " . $nombre;

    /* ¿Existe ya en GitHub? Si no, se crea. */
    [$cod] = api($c, 'GET', '/repos/' . $nombre);
    $vacio = false;

    if ($cod === 404) {
        [$owner, $corto] = array_pad(explode('/', $nombre, 2), 2, '');
        if ($owner !== $duenio) {
            return [false, 'El repositorio ' . $nombre . ' no existe, y no lo puedo crear porque no es tu cuenta.', implode("\n\n", $log)];
        }
        [$cod2, $d] = api($c, 'POST', '/user/repos', [
            'name' => $corto, 'private' => true, 'description' => 'Sitio ' . $corto,
        ]);
        if ($cod2 !== 201) {
            $m = $d['errors'][0]['message'] ?? $d['message'] ?? ('GitHub contestó ' . $cod2);
            return [false, 'No pude crear el repositorio: ' . $m, implode("\n\n", $log)];
        }
        $log[] = 'Repositorio creado en GitHub, privado.';
        $vacio = true;
    } elseif ($cod !== 200) {
        return [false, 'GitHub contestó ' . $cod . ' al buscar ' . $nombre . '. Revisa el token y sus permisos.', implode("\n\n", $log)];
    } else {
        [$c2, $ramas] = api($c, 'GET', '/repos/' . $nombre . '/branches');
        $vacio = ($c2 === 200 && $ramas === []);
    }

    preparar($c);

    /* Repositorio nuevo o vacío: se sube el sitio tal como está y queda listo. */
    if ($vacio) {
        git(['add', '-A']);
        [, $sal] = git(['-c', 'user.name=Panel', '-c', 'user.email=panel@localhost',
                        'commit', '-m', 'Primera subida del sitio ' . date('d-m-Y H:i')]);
        $log[] = "$ git commit\n" . $sal;

        [$cod, $sal] = git(['push', url_token($c), 'HEAD:refs/heads/' . $c['rama']]);
        $log[] = "$ git push\n" . (ocultar($sal, $c['token']) ?: 'ok');
        if ($cod !== 0) {
            return [false, 'No se pudo subir el sitio. Revisa que el token tenga permiso de escritura.', implode("\n\n", $log)];
        }
        return [true, 'Listo: el sitio quedó guardado en GitHub, en ' . $nombre . '.', implode("\n\n", $log)];
    }

    /* El repositorio ya tiene cosas: se enlaza sin pisar nada de lo que hay aquí. */
    [$cod, $sal] = git(['fetch', url_token($c), $c['rama']]);
    $log[] = "$ git fetch\n" . (ocultar($sal, $c['token']) ?: 'ok');
    if ($cod !== 0) {
        return [false, 'No se pudo leer GitHub. Revisa el token, la dirección y la rama.', implode("\n\n", $log)];
    }

    [$cod, $sal] = git(['reset', 'FETCH_HEAD']);
    $log[] = "$ git reset\n" . ($sal ?: 'ok');
    if ($cod !== 0) {
        return [false, 'No se pudo enlazar la carpeta con GitHub. Mira el detalle.', implode("\n\n", $log)];
    }

    /* Lo que está en GitHub y falta aquí sí se baja: no pisa nada, sólo completa. */
    [, $faltan] = git(['ls-files', '--deleted']);
    $faltan = array_values(array_filter(explode("\n", trim($faltan)), function (string $f): bool {
        /* Nunca bajar una portada que tape la que el sitio ya tiene. */
        if (!preg_match('#^index\.(php|html?)$#i', $f)) return true;
        foreach (['index.php', 'index.html', 'index.htm'] as $otra) {
            if (is_file(RAIZ . '/' . $otra)) return false;
        }
        return true;
    }));
    if ($faltan) {
        git(array_merge(['checkout', '--'], $faltan));
        $log[] = 'Se bajaron los que faltaban aquí: ' . implode(', ', $faltan);
    }

    [, $sucio] = git(['status', '--porcelain']);
    $n = $sucio === '' ? 0 : count(explode("\n", $sucio));
    $log[] = 'Ojo: al escribir "subir", GitHub queda igual que esta carpeta; lo que borres aquí, se borra allá.';
    if ($sucio !== '') $log[] = $sucio;

    return [true, 'Listo: la carpeta quedó enlazada con ' . $nombre . ' y no se pisó ningún archivo tuyo. '
        . ($n ? 'Hay ' . $n . ' diferencia(s); revísalas abajo y escribe "subir".' : 'Está todo igual que GitHub.'),
        implode("\n\n", $log)];
}

function palabra_ayuda(): array {
    return [true, 'Palabras que entiende el panel:', implode("\n", [
        'estado            cómo está la carpeta y qué falta',
        'subir             guarda todo y lo manda a GitHub',
        'subir cambié el logo   igual, pero deja escrito qué hiciste',
        'traer             guarda lo tuyo y baja lo nuevo de GitHub',
        'traer github      deja esta carpeta igual que GitHub (guarda lo de aquí aparte)',
        'instalar          enlaza con GitHub un sitio que ya está en el servidor, sin tocar nada',
        'ayuda             esta lista',
        'salir             cierra la sesión',
    ])];
}

/* ------------------------------------------------------------------ */
/* Qué hacer con lo que se escribió                                    */
/* ------------------------------------------------------------------ */

$aviso = $consola = '';
$ok    = true;
$dentro = !empty($_SESSION['ok']);

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf_ok = $_SERVER['REQUEST_METHOD'] === 'POST'
        && hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''));

/* Primera vez: se escribe config.php y, en el mismo golpe, se clona el sitio. */
if (!$CFG && $_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_ok) {
    $token = trim((string)($_POST['token'] ?? ''));
    $clave = trim((string)($_POST['clave'] ?? ''));
    $repo  = trim((string)($_POST['repo']  ?? ''));

    if ($token === '' || $clave === '') {
        $ok = false; $aviso = 'Faltan el token o la clave.';
    } elseif (mb_strlen($clave) < 6) {
        $ok = false; $aviso = 'Ponle a la clave al menos 6 letras.';
    } elseif (github_usuario(['token' => $token]) === '') {
        $ok = false; $aviso = 'GitHub no reconoció ese token. Revisa que esté completo y que no haya vencido.';
    } elseif (!guardar_config($token, $clave, $repo)) {
        $ok = false; $aviso = 'No pude escribir config.php aquí. Dale permiso de escritura a esta carpeta y vuelve a intentarlo.';
    } else {
        proteger_config();
        $CFG = cargar_config();
        session_regenerate_id(true);
        $_SESSION['ok'] = true;
        $dentro = true;

        if (!hay_git()) {
            $ok = false;
            $aviso = 'Guardé la configuración, pero este servidor no deja ejecutar git desde PHP. Pídele al soporte que habilite proc_open.';
        } else {
            [$ok, $aviso, $consola] = palabra_instalar($CFG);
        }
    }
}

if ($CFG && $_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_ok && $aviso === '') {

    /* Entrar */
    if (!$dentro) {
        if (hash_equals((string)$CFG['clave'], (string)($_POST['clave'] ?? ''))) {
            session_regenerate_id(true);
            $_SESSION['ok'] = true;
            $dentro = true;
        } else {
            sleep(1);
            $ok = false; $aviso = 'Esa no es la clave.';
        }

    /* Ya dentro: la palabra */
    } else {
        $texto   = trim((string)($_POST['orden'] ?? ''));
        $partes  = preg_split('/\s+/', $texto, 2);
        $palabra = strtolower(strtr($partes[0] ?? '', 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU'));
        $resto   = trim($partes[1] ?? '');

        if (!hay_git() && $palabra !== '' && $palabra !== 'salir') {
            $ok = false; $aviso = 'Este servidor no tiene git instalado.';
        } else {
            switch ($palabra) {
                case '':        break;
                case 'estado':  [$ok, $aviso, $consola] = palabra_estado($CFG); break;
                case 'subir':   [$ok, $aviso, $consola] = palabra_subir($CFG, $resto); break;
                case 'traer':   [$ok, $aviso, $consola] = palabra_traer($CFG, $resto); break;
                case 'instalar':[$ok, $aviso, $consola] = palabra_instalar($CFG); break;
                case 'ayuda':   [$ok, $aviso, $consola] = palabra_ayuda(); break;
                case 'salir':
                    session_destroy();
                    header('Location: panel.php');
                    exit;
                default:
                    $ok = false;
                    $aviso = 'No conozco la palabra "' . $palabra . '". Escribe "ayuda".';
            }
        }
    }
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$csrf = $_SESSION['csrf'];
?>
<!doctype html>
<html lang="es">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Panel</title>
<style>
  :root { color-scheme: light dark; }
  * { box-sizing: border-box; }
  body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
         font: 15px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif;
         background: #f4f5f7; color: #16181d; }
  main { width: 100%; max-width: 620px; }
  h1 { margin: 0 0 4px; font-size: 20px; }
  p.sub { margin: 0 0 20px; color: #6b7280; }
  form { display: flex; gap: 8px; }
  input { flex: 1; padding: 12px 14px; font: inherit; border: 1px solid #d0d3d9;
          border-radius: 8px; background: #fff; color: inherit; }
  input:focus { outline: 2px solid #2563eb; outline-offset: -1px; border-color: #2563eb; }
  button { padding: 12px 18px; font: inherit; border: 0; border-radius: 8px;
           background: #16181d; color: #fff; cursor: pointer; }
  form.alta { flex-direction: column; gap: 16px; }
  form.alta label { display: flex; flex-direction: column; gap: 6px; font-size: 13.5px; color: #6b7280; }
  form.alta small { color: #8b919b; font-size: 12px; }
  form.alta code { font-size: 12px; background: rgba(127,127,127,.14); padding: 1px 4px; border-radius: 3px; }
  form.alta button { align-self: flex-start; }
  .palabras { margin: 10px 2px 0; color: #6b7280; font-size: 13px; }
  .aviso { margin-top: 18px; padding: 12px 14px; border-radius: 8px;
           background: #e8f3ec; border: 1px solid #bcdcc8; }
  .aviso.mal { background: #fdecec; border-color: #f2c2c2; }
  pre { margin: 10px 0 0; padding: 12px 14px; border-radius: 8px; background: #16181d;
        color: #e6e8ec; font-size: 12.5px; overflow-x: auto; white-space: pre-wrap;
        word-break: break-word; }
  @media (prefers-color-scheme: dark) {
    body { background: #14161a; color: #e6e8ec; }
    input { background: #1c1f25; border-color: #333842; }
    button { background: #e6e8ec; color: #14161a; }
    .aviso { background: #16261c; border-color: #2c4634; }
    .aviso.mal { background: #2a1818; border-color: #4d2a2a; }
  }
</style>
<main>

<?php if (!$CFG): ?>
  <h1>Panel</h1>
  <p class="sub">Primera vez en esta carpeta. Con estos tres datos se deja listo solo:
     escribe el <code>config.php</code>, lo protege, y clona el sitio.</p>
  <form method="post" class="alta">
    <label>Token de GitHub
      <input type="password" name="token" autofocus autocomplete="off" spellcheck="false"
             placeholder="ghp_...">
      <small>Con permiso <code>repo</code>. Se guarda sólo aquí, nunca viaja a GitHub.</small>
    </label>
    <label>Clave para entrar después
      <input type="password" name="clave" autocomplete="new-password" placeholder="mínimo 6 letras">
    </label>
    <label>Repositorio
      <input name="repo" autocomplete="off" spellcheck="false"
             placeholder="<?= e(preg_replace('/^www\./', '', explode(':', (string)($_SERVER['HTTP_HOST'] ?? 'midominio.cl'))[0])) ?>">
      <small>Si lo dejas vacío usa el nombre de este dominio. Si no existe en GitHub, lo crea privado.</small>
    </label>
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <button>Guardar y clonar</button>
  </form>
  <?php if ($aviso): ?>
    <div class="aviso <?= $ok ? '' : 'mal' ?>"><?= e($aviso) ?></div>
  <?php endif; ?>
  <?php if ($consola !== ''): ?><pre><?= e($consola) ?></pre><?php endif; ?>

<?php elseif (!$dentro): ?>
  <h1>Panel</h1>
  <p class="sub">Escribe la clave para entrar.</p>
  <form method="post">
    <input type="password" name="clave" placeholder="Clave" autofocus autocomplete="current-password">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <button>Entrar</button>
  </form>
  <?php if ($aviso): ?><div class="aviso mal"><?= e($aviso) ?></div><?php endif; ?>

<?php else: ?>
  <h1>Panel</h1>
  <p class="sub">Escribe una palabra y presiona Enter.</p>
  <form method="post">
    <input name="orden" placeholder="estado" autofocus autocomplete="off" spellcheck="false">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <button>Hacer</button>
  </form>
  <p class="palabras">estado · subir · traer · instalar · ayuda · salir</p>

  <?php if ($aviso): ?>
    <div class="aviso <?= $ok ? '' : 'mal' ?>"><?= e($aviso) ?></div>
  <?php endif; ?>
  <?php if ($consola !== ''): ?>
    <pre><?= e($consola) ?></pre>
  <?php endif; ?>
<?php endif; ?>

</main>
