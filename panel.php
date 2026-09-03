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

$CFG = is_file(RAIZ . '/config.php') ? require RAIZ . '/config.php' : null;

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

/** Dirección del repositorio sin usuario ni contraseña. */
function url_limpia(array $c): string {
    return 'https://' . preg_replace('#^(https?://)?([^@/]*@)?#', '', trim((string)$c['repo']));
}

/** La misma dirección, pero con el token para poder subir o bajar. */
function url_token(array $c): string {
    return 'https://x-access-token:' . rawurlencode(trim((string)$c['token']))
         . '@' . preg_replace('#^(https?://)?([^@/]*@)?#', '', trim((string)$c['repo']));
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

function palabra_ayuda(): array {
    return [true, 'Palabras que entiende el panel:', implode("\n", [
        'estado            cómo está la carpeta y qué falta',
        'subir             guarda todo y lo manda a GitHub',
        'subir cambié el logo   igual, pero deja escrito qué hiciste',
        'traer             guarda lo tuyo y baja lo nuevo de GitHub',
        'traer github      deja esta carpeta igual que GitHub (guarda lo de aquí aparte)',
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

if ($CFG && $_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_ok) {

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
  <h1>Falta la configuración</h1>
  <p class="sub">Copia el archivo <code>config-ejemplo.php</code> como <code>config.php</code>
     y escribe adentro tu token, la dirección del repositorio y una clave.</p>

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
  <p class="palabras">estado · subir · traer · ayuda · salir</p>

  <?php if ($aviso): ?>
    <div class="aviso <?= $ok ? '' : 'mal' ?>"><?= e($aviso) ?></div>
  <?php endif; ?>
  <?php if ($consola !== ''): ?>
    <pre><?= e($consola) ?></pre>
  <?php endif; ?>
<?php endif; ?>

</main>
