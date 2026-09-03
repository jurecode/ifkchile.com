<?php
/**
 * Panel de administración IFK
 *  · Estado del sitio (publicado / próximamente)
 *  · Imágenes del sitio
 *  · Repositorio y publicación (GitHub + servidor)
 *  · Clave de acceso
 */
declare(strict_types=1);

require __DIR__ . '/lib.php';
@set_time_limit(300);   // las operaciones con GitHub pueden demorar
panel_sesion();

$vista  = $_GET['v'] ?? 'estado';
$aviso  = null;   // ['tipo' => ok|error, 'texto' => '', 'consola' => '']
$AJ     = ajustes();

/* ---------- Primer uso: crear la clave ---------- */
if (!panel_tiene_password()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear_clave') {
        $c1 = (string)($_POST['clave'] ?? '');
        $c2 = (string)($_POST['clave2'] ?? '');
        if (strlen($c1) < 8)      $aviso = ['tipo' => 'error', 'texto' => 'La clave debe tener al menos 8 caracteres.'];
        elseif ($c1 !== $c2)      $aviso = ['tipo' => 'error', 'texto' => 'Las claves no coinciden.'];
        else {
            guardar_ajustes(['password_hash' => password_hash($c1, PASSWORD_DEFAULT)]);
            panel_log('clave', 'clave inicial creada');
            $_SESSION['panel_ok'] = true;
            header('Location: index.php?v=estado'); exit;
        }
    }
    require __DIR__ . '/vista/instalar.php';
    exit;
}

/* ---------- Login / salida ---------- */
if (isset($_GET['salir'])) {
    panel_log('sesión', 'cierre de sesión');
    $_SESSION = []; session_destroy();
    header('Location: index.php?v=login'); exit;
}

if (!panel_autenticado()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'login') {
        $espera = panel_bloqueado();
        if ($espera > 0) {
            $aviso = ['tipo' => 'error', 'texto' => 'Demasiados intentos. Espera ' . ceil($espera / 60) . ' minutos.'];
        } elseif (password_verify((string)($_POST['clave'] ?? ''), $AJ['password_hash'])) {
            panel_registrar_intento(true);
            session_regenerate_id(true);
            $_SESSION['panel_ok'] = true;
            panel_log('sesión', 'ingreso correcto');
            header('Location: index.php?v=estado'); exit;
        } else {
            panel_registrar_intento(false);
            panel_log('sesión', 'clave incorrecta');
            $aviso = ['tipo' => 'error', 'texto' => 'Clave incorrecta.'];
        }
    }
    require __DIR__ . '/vista/login.php';
    exit;
}

/* ---------- Acciones (todas requieren POST + CSRF) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $esAjax = ($_POST['ajax'] ?? '') === '1';
    if (!csrf_ok()) {
        if ($esAjax) responder_json(false, 'La sesión expiró. Recarga la página.');
        $aviso = ['tipo' => 'error', 'texto' => 'La sesión expiró. Vuelve a intentarlo.'];
    } else {
        $accion = $_POST['accion'] ?? '';

        /* --- Estado del sitio --- */
        if ($accion === 'estado') {
            $coming = ($_POST['modo'] ?? 'soon') === 'soon';
            $clave  = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($_POST['preview_key'] ?? '')) ?: 'ifk2026';
            guardar_ajustes(['coming_soon' => $coming, 'preview_key' => $clave]);
            panel_log('estado', $coming ? 'sitio en PRÓXIMAMENTE' : 'sitio PUBLICADO');
            $aviso = ['tipo' => 'ok', 'texto' => $coming
                ? 'El sitio público muestra la portada "Próximamente".'
                : 'El sitio quedó publicado y visible para todos.'];
            $AJ = ajustes(true);
        }

        /* --- Imágenes --- */
        if ($accion === 'imagen') {
            $clave = (string)($_POST['clave'] ?? '');
            [$ok, $msg] = panel_guardar_imagen($clave, $_FILES['archivo'] ?? []);
            $AJ = ajustes(true);
            if ($esAjax) responder_json($ok, $msg, $ok ? $clave : '');
            $aviso = ['tipo' => $ok ? 'ok' : 'error', 'texto' => $msg];
        }

        /* --- Datos del repositorio --- */
        if ($accion === 'git_guardar') {
            $g = $AJ['git'];
            $g['repo']         = trim((string)($_POST['repo'] ?? ''));
            $g['rama']         = trim((string)($_POST['rama'] ?? 'main')) ?: 'main';
            $g['usuario']      = trim((string)($_POST['usuario'] ?? ''));
            $tokenNuevo        = trim((string)($_POST['token'] ?? ''));
            if ($tokenNuevo !== '') $g['token'] = $tokenNuevo;
            if (!empty($_POST['borrar_token'])) $g['token'] = '';
            guardar_ajustes(['git' => $g]);
            panel_log('repositorio', 'datos guardados' . ($tokenNuevo !== '' ? ' (token actualizado)' : ''));
            $aviso = ['tipo' => 'ok', 'texto' => 'Datos del repositorio guardados.'];
            $AJ = ajustes(true);
        }

        /* --- Acciones de repositorio --- */
        if (in_array($accion, ['git_estado', 'git_subir', 'git_conectar', 'git_traer', 'git_forzar'], true)) {
            require __DIR__ . '/acciones-git.php';
        }

        /* --- Cambio de clave --- */
        if ($accion === 'clave') {
            $actual = (string)($_POST['actual'] ?? '');
            $n1     = (string)($_POST['nueva'] ?? '');
            $n2     = (string)($_POST['nueva2'] ?? '');
            if (!password_verify($actual, $AJ['password_hash'])) $aviso = ['tipo' => 'error', 'texto' => 'La clave actual no es correcta.'];
            elseif (strlen($n1) < 8)                             $aviso = ['tipo' => 'error', 'texto' => 'La nueva clave debe tener al menos 8 caracteres.'];
            elseif ($n1 !== $n2)                                 $aviso = ['tipo' => 'error', 'texto' => 'Las claves nuevas no coinciden.'];
            else {
                guardar_ajustes(['password_hash' => password_hash($n1, PASSWORD_DEFAULT)]);
                panel_log('clave', 'clave cambiada');
                $aviso = ['tipo' => 'ok', 'texto' => 'Clave actualizada.'];
                $AJ = ajustes(true);
            }
        }
    }
}

$vistas = ['estado', 'imagenes', 'repositorio', 'clave'];
if (!in_array($vista, $vistas, true)) $vista = 'estado';

require __DIR__ . '/vista/layout.php';
