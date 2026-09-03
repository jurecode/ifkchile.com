<?php
/**
 * Acciones del repositorio: estado, subir a GitHub y actualizar el servidor.
 * Incluido desde index.php (ya validó sesión y CSRF).
 */

$g       = $AJ['git'];
$consola = '';

/* --- Estado del repositorio --- */
if ($accion === 'git_estado') {
    if (!git_disponible()) {
        $aviso = ['tipo' => 'error', 'texto' => 'Git no está disponible en este servidor.'];
    } else {
        $lineas = [];
        if (!es_repo()) {
            $lineas[] = '· Esta carpeta todavía no es un repositorio git.';
        } else {
            [, $rama]   = correr(['git', 'rev-parse', '--abbrev-ref', 'HEAD']);
            [, $ultimo] = correr(['git', 'log', '-1', '--pretty=%h · %s · %cr']);
            [, $estado] = correr(['git', 'status', '--porcelain']);
            $pend = $estado === '' ? 0 : count(explode("\n", $estado));
            $lineas[] = 'Rama actual:      ' . ($rama ?: '—');
            $lineas[] = 'Último commit:    ' . ($ultimo ?: 'sin commits todavía');
            $lineas[] = 'Cambios sin subir: ' . $pend;
            if ($pend > 0) $lineas[] = "\n" . $estado;
        }
        $consola = implode("\n", $lineas);
        $aviso   = ['tipo' => 'ok', 'texto' => 'Estado del repositorio:', 'consola' => $consola];
    }
}

/* --- Guardar cambios y subir a GitHub --- */
if ($accion === 'git_subir') {
    $mensaje = trim((string)($_POST['mensaje'] ?? '')) ?: ('Actualización del sitio ' . date('d-m-Y H:i'));
    $salida  = [];
    $error   = null;

    if (!git_disponible())          $error = 'Git no está disponible en este servidor.';
    elseif (trim($g['repo']) === '') $error = 'Falta la dirección del repositorio.';
    elseif (trim($g['token']) === '')$error = 'Falta el token de GitHub.';

    if (!$error) {
        if (!es_repo()) {
            [$c, $o] = correr(['git', 'init', '-b', $g['rama'] ?: 'main']);
            $salida[] = "$ git init\n$o";
        }
        correr(['git', 'config', 'user.name',  $g['usuario'] ?: 'IFK Panel']);
        correr(['git', 'config', 'user.email', $SITE['email']]);

        [$c, $o] = correr(['git', 'add', '-A']);
        $salida[] = "$ git add -A\n" . ($o ?: 'ok');

        [$c, $o] = correr(['git', 'commit', '-m', $mensaje]);
        $salida[] = "$ git commit\n$o";
        $sinCambios = str_contains($o, 'nothing to commit');

        $url = git_url_push($g);
        $rama = $g['rama'] ?: 'main';
        [$c, $o] = correr(['git', 'push', $url, 'HEAD:refs/heads/' . $rama]);
        $salida[] = "$ git push\n" . ocultar_token($o, $g['token']);

        if ($c !== 0) {
            $error = 'GitHub rechazó la subida. Revisa el token, los permisos (scope repo) y la dirección del repositorio.';
        } else {
            $aviso = ['tipo' => 'ok',
                'texto' => $sinCambios ? 'No había cambios nuevos; el repositorio ya estaba al día.' : 'Cambios subidos a GitHub.',
                'consola' => implode("\n\n", $salida)];
            panel_log('github', 'push a ' . $g['repo'] . ' (' . $rama . ')');
        }
    }
    if ($error) {
        $aviso = ['tipo' => 'error', 'texto' => $error, 'consola' => implode("\n\n", $salida)];
        panel_log('github', 'error: ' . $error);
    }
}
