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
        $lineas[] = 'Carpeta: ' . RAIZ;
        if (!es_repo()) {
            $lineas[] = '';
            $lineas[] = 'Esta carpeta todavía no está conectada con GitHub.';
            $lineas[] = 'Completa los datos de arriba y usa el botón "Conectar con GitHub".';
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

    elseif (!es_repo())              $error = 'Esta carpeta no está conectada con GitHub. Usa primero el botón "Conectar con GitHub".';

    /* Si GitHub tiene commits que aquí no están, el push se rechazaría y quedarían
       dos historias distintas: mejor detenerse antes de crear el commit. */
    if (!$error) {
        [$c, $o] = correr(['git', 'fetch', git_url_push($g), $g['rama'] ?: 'main']);
        if ($c === 0) {
            [, $atras] = correr(['git', 'rev-list', '--count', 'HEAD..FETCH_HEAD']);
            if ((int)trim($atras) > 0) {
                $error = 'GitHub tiene ' . (int)trim($atras) . ' cambio(s) más nuevos que esta carpeta. '
                       . 'Usa primero "Traer cambios" y vuelve a intentarlo.';
            }
        }
    }

    if (!$error) {
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

/* --- Conectar esta carpeta con el repositorio de GitHub --- */
if ($accion === 'git_conectar') {
    $salida = [];
    $error  = null;
    $rama   = $g['rama'] ?: 'main';

    if (!git_disponible())            $error = 'Git no está disponible en este servidor.';
    elseif (trim($g['repo']) === '')  $error = 'Falta la dirección del repositorio.';
    elseif (trim($g['token']) === '') $error = 'Falta el token de GitHub.';

    if (!$error) {
        if (!es_repo()) {
            [$c, $o] = correr(['git', 'init', '-b', $rama]);
            $salida[] = "$ git init\n" . ($o ?: 'ok');
            if ($c !== 0) $error = 'No se pudo iniciar el repositorio local.';
        }
    }

    if (!$error) {
        /* El remoto se guarda sin credenciales; el token se usa sólo al momento de conectar o subir */
        $limpia = 'https://' . preg_replace('#^[^@]*@#', '', preg_replace('#^https?://#', '', trim($g['repo'])));
        correr(['git', 'remote', 'remove', 'origin']);
        [$c, $o] = correr(['git', 'remote', 'add', 'origin', $limpia]);
        $salida[] = "$ git remote add origin " . $limpia . "\n" . ($o ?: 'ok');

        [$c, $o] = correr(['git', 'fetch', git_url_push($g), $rama]);
        $salida[] = "$ git fetch origin " . $rama . "\n" . ocultar_token($o, $g['token']);
        if ($c !== 0) {
            $error = 'No se pudo leer el repositorio. Revisa la dirección, la rama y el token.';
        } else {
            /* --mixed: deja los archivos intactos y sólo alinea el historial */
            [$c, $o] = correr(['git', 'reset', '--mixed', 'FETCH_HEAD']);
            $salida[] = "$ git reset --mixed FETCH_HEAD\n" . ($o ?: 'ok');

            [, $estado] = correr(['git', 'status', '--porcelain']);
            $pend = $estado === '' ? 0 : count(explode("\n", $estado));
            $salida[] = 'Carpeta conectada. Diferencias con GitHub: ' . $pend;

            $aviso = ['tipo' => 'ok',
                'texto'   => 'Carpeta conectada con GitHub.' . ($pend ? ' Hay ' . $pend . ' archivo(s) con diferencias listos para subir.' : ' No hay diferencias.'),
                'consola' => implode("\n\n", $salida)];
            panel_log('github', 'carpeta conectada a ' . $g['repo']);
        }
    }

    if ($error) {
        $aviso = ['tipo' => 'error', 'texto' => $error, 'consola' => implode("\n\n", $salida)];
        panel_log('github', 'error al conectar: ' . $error);
    }
}

/* --- Traer los cambios publicados en GitHub --- */
if ($accion === 'git_traer') {
    $salida = [];
    $error  = null;
    $rama   = $g['rama'] ?: 'main';

    if (!git_disponible())            $error = 'Git no está disponible en este servidor.';
    elseif (!es_repo())               $error = 'Esta carpeta no está conectada con GitHub. Usa primero "Conectar con GitHub".';
    elseif (trim($g['repo']) === '')  $error = 'Falta la dirección del repositorio.';
    elseif (trim($g['token']) === '') $error = 'Falta el token de GitHub.';

    /* Nunca se pisan cambios hechos aquí: primero hay que subirlos */
    if (!$error) {
        [, $sucio] = correr(['git', 'status', '--porcelain']);
        if ($sucio !== '') {
            $error = 'Esta carpeta tiene cambios sin subir. Usa primero "Subir a GitHub" para no perderlos.';
            $salida[] = $sucio;
        }
    }

    if (!$error) {
        [$c, $o] = correr(['git', 'fetch', git_url_push($g), $rama]);
        $salida[] = "$ git fetch origin " . $rama . "\n" . (ocultar_token($o, $g['token']) ?: 'ok');
        if ($c !== 0) {
            $error = 'No se pudo leer el repositorio. Revisa la dirección, la rama y el token.';
        } else {
            [, $pendientes] = correr(['git', 'rev-list', '--count', 'HEAD..FETCH_HEAD']);
            $nuevos = (int)trim($pendientes);

            if ($nuevos === 0) {
                $aviso = ['tipo' => 'ok', 'texto' => 'El sitio ya estaba al día con GitHub.', 'consola' => implode("\n\n", $salida)];
                panel_log('github', 'pull sin novedades');
            } else {
                [$c, $o] = correr(['git', 'merge', '--ff-only', 'FETCH_HEAD']);
                $salida[] = "$ git merge --ff-only\n" . ($o ?: 'ok');
                if ($c !== 0) {
                    $error = 'El historial de esta carpeta se separó del de GitHub. '
                           . 'Puedes dejarla igual a GitHub con el botón "Usar la versión de GitHub" (se guarda un respaldo antes).';
                } else {
                    [, $head] = correr(['git', 'log', '-1', '--pretty=%h · %s']);
                    $salida[] = 'Versión actual: ' . $head;
                    $aviso = ['tipo' => 'ok',
                        'texto'   => 'Sitio actualizado con ' . $nuevos . ' cambio(s) desde GitHub.',
                        'consola' => implode("\n\n", $salida)];
                    panel_log('github', 'pull de ' . $nuevos . ' commit(s)');
                }
            }
        }
    }

    if ($error) {
        $aviso = ['tipo' => 'error', 'texto' => $error, 'consola' => implode("\n\n", $salida)];
        panel_log('github', 'error al traer: ' . $error);
    }
}

/* --- Dejar la carpeta igual a GitHub, guardando antes un respaldo --- */
if ($accion === 'git_forzar') {
    $salida = [];
    $error  = null;
    $rama   = $g['rama'] ?: 'main';

    if (!git_disponible())            $error = 'Git no está disponible en este servidor.';
    elseif (!es_repo())               $error = 'Esta carpeta no está conectada con GitHub.';
    elseif (trim($g['token']) === '') $error = 'Falta el token de GitHub.';

    if (!$error) {
        [$c, $o] = correr(['git', 'fetch', git_url_push($g), $rama]);
        $salida[] = "$ git fetch origin " . $rama . "\n" . (ocultar_token($o, $g['token']) ?: 'ok');
        if ($c !== 0) $error = 'No se pudo leer el repositorio. Revisa la dirección, la rama y el token.';
    }

    if (!$error) {
        /* Respaldo completo: primero se comitea lo que esté suelto (fotos subidas
           desde el panel, por ejemplo) y recién después se guarda la rama. */
        [, $sucio] = correr(['git', 'status', '--porcelain']);
        if ($sucio !== '') {
            correr(['git', 'config', 'user.name',  $g['usuario'] ?: 'IFK Panel']);
            correr(['git', 'config', 'user.email', $SITE['email']]);
            correr(['git', 'add', '-A']);
            [$c, $o] = correr(['git', 'commit', '-m', 'Respaldo automático antes de alinear con GitHub']);
            $salida[] = "$ git commit (respaldo de los cambios sueltos)\n" . $o;
        }

        $respaldo = 'respaldo-' . date('Ymd-Hi');
        [$c, $o] = correr(['git', 'branch', '-f', $respaldo, 'HEAD']);
        $salida[] = '$ git branch ' . $respaldo . "\n" . ($o ?: 'ok');

        [$c, $o] = correr(['git', 'reset', '--hard', 'FETCH_HEAD']);
        $salida[] = "$ git reset --hard FETCH_HEAD\n" . ($o ?: 'ok');
        if ($c !== 0) {
            $error = 'No se pudo alinear la carpeta con GitHub.';
        } else {
            [, $head] = correr(['git', 'log', '-1', '--pretty=%h · %s']);
            $salida[] = 'Versión actual: ' . $head;
            $aviso = ['tipo' => 'ok',
                'texto'   => 'La carpeta quedó igual a GitHub. Lo que había antes se guardó en la rama local "' . $respaldo . '".',
                'consola' => implode("\n\n", $salida)];
            panel_log('github', 'reset duro a GitHub · respaldo en ' . $respaldo);
        }
    }

    if ($error) {
        $aviso = ['tipo' => 'error', 'texto' => $error, 'consola' => implode("\n\n", $salida)];
        panel_log('github', 'error al forzar: ' . $error);
    }
}
