<?php
/**
 * Front controller — IFK / Inversiones Friomak SpA
 */
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/includes/data.php';

/* --- Acceso de previsualización mientras el sitio está en Coming Soon --- */
if (isset($_GET['preview']) && $_GET['preview'] === PREVIEW_KEY) {
    setcookie('ifk_preview', PREVIEW_KEY, [
        'expires'  => time() + 60 * 60 * 8,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    header('Location: ' . url('home'));
    exit;
}
if (isset($_GET['salir'])) {
    setcookie('ifk_preview', '', ['expires' => time() - 3600, 'path' => '/']);
    header('Location: /');
    exit;
}

$PREVIEW = (($_COOKIE['ifk_preview'] ?? '') === PREVIEW_KEY);

/* --- Ruteo (acepta URLs amigables y también ?p=) --- */
$rutas = ['home', 'area', 'nosotros', 'contacto'];
$seg   = array_values(array_filter(explode('/', (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH))));
if ($seg && $seg[0] !== 'index.php') {
    if ($seg[0] === 'servicios') {
        $_GET['p'] = 'area';
        $_GET['a'] = $seg[1] ?? 'refrigeracion';
    } elseif (in_array($seg[0], $rutas, true)) {
        $_GET['p'] = $seg[0];
    }
}
$PAGE  = $_GET['p'] ?? 'home';
if (!in_array($PAGE, $rutas, true)) {
    $PAGE = 'home';
}

/* Con el sitio en Coming Soon, sólo el preview ve el sitio real */
if (COMING_SOON && !$PREVIEW) {
    require __DIR__ . '/pages/coming-soon.php';
    exit;
}

/* --- Procesamiento del formulario de cotización --- */
$FORM = ['ok' => false, 'errores' => [], 'datos' => [], 'enviado' => false];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'cotizacion') {
    require __DIR__ . '/includes/form.php';
}

require __DIR__ . '/pages/' . $PAGE . '.php';
