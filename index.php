<?php
/**
 * Puerta de entrada del sitio. Todo lo público pasa por aquí.
 *
 *   Sitio en Coming Soon    →  la fachada, y cualquier otra dirección vuelve a "/"
 *   Sitio en Mantenimiento  →  pantalla de mejoras (con 503, para que Google espere)
 *   Sitio publicado         →  el sitio completo
 *
 * El administrador con sesión abierta puede ver el sitio real aunque el
 * público siga viendo la fachada (botón "Previsualizar sitio" del panel).
 */
declare(strict_types=1);

/* Con el servidor de prueba de PHP, los archivos que existen los sirve él. */
if (PHP_SAPI === 'cli-server') {
    $pedido = urldecode((string)parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (preg_match('#^/(app|datos|vistas)/#', $pedido)) { http_response_code(403); exit; }
    if ($pedido !== '/' && is_file(__DIR__ . $pedido)) return false;
}

require __DIR__ . '/app/sitio.php';

/* Si quien entra trae la sesión del panel, se abre ahora: más adelante ya
   habría salido HTML y PHP no deja abrirla después. */
sesion_panel();

$ruta = trim((string)parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

/* ---------------- El panel ---------------- */

if ($ruta === 'admin' || $ruta === 'panel' || $ruta === 'panel.php') {
    require __DIR__ . '/admin.php';
    exit;
}

/* ---------------- robots.txt ---------------- */

if ($ruta === 'robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    if (estado_sitio() === 'publicado') {
        echo "User-agent: *\nAllow: /\nDisallow: /admin\n\nSitemap: " . $SITE['dominio'] . "/sitemap.xml\n";
    } else {
        /* Sitio a medias: mejor que ningún buscador lo guarde todavía. */
        echo "User-agent: *\nDisallow: /\n";
    }
    exit;
}

/* ---------------- sitemap.xml ---------------- */

/* Sólo tiene sentido con el sitio publicado; mientras tanto, no existe. */
if ($ruta === 'sitemap.xml') {
    if (estado_sitio() !== 'publicado') { http_response_code(404); exit; }

    $base  = rtrim($SITE['dominio'], '/');
    $rutas = ['/', '/servicios', '/proyectos', '/marcas', '/nosotros', '/contacto'];
    foreach (array_keys($AREAS) as $llave) $rutas[] = '/servicios/' . $llave;

    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($rutas as $r) {
        echo '  <url><loc>' . e($base . $r) . '</loc>'
           . '<priority>' . ($r === '/' ? '1.0' : '0.8') . "</priority></url>\n";
    }
    echo "</urlset>\n";
    exit;
}

/* ---------------- Servicios internos ---------------- */

/* Lo que cuelgue de /api/ nunca se manda a la fachada: la fachada es para
   personas, no para los servicios que el sitio use por dentro. */
if (str_starts_with($ruta, 'api/')) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(404);
    echo json_encode(['error' => 'no existe'], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------------- Previsualizar / volver a la fachada ---------------- */

if (isset($_GET['ver']) && admin_dentro()) {
    guardar_modo_vista((string)$_GET['ver']);
    llevar_a(strtok((string)$_SERVER['REQUEST_URI'], '?') ?: '/');
}

cabeceras_buscadores();

/* ---------------- El sitio todavía no es para el público ---------------- */

if (!puede_ver_sitio()) {

    if (estado_sitio() === 'mantenimiento') {
        http_response_code(503);
        header('Retry-After: 3600');
        require __DIR__ . '/vistas/mantenimiento.php';
        exit;
    }

    /* Coming Soon: sólo existe la portada; el resto vuelve a ella. */
    if ($ruta !== '') llevar_a('/', 302);
    require __DIR__ . '/vistas/coming-soon.php';
    exit;
}

/* ---------------- Sitio visible ---------------- */

require __DIR__ . '/app/contenido.php';

/* Las páginas con dirección propia. */
$paginas = [
    ''          => 'inicio',
    'nosotros'  => 'nosotros',
    'servicios' => 'servicios',
    'proyectos' => 'proyectos',
    'marcas'    => 'marcas',
    'contacto'  => 'contacto',
];

/* Cada área tiene su página: /servicios/refrigeracion, /servicios/reefer… */
if (str_starts_with($ruta, 'servicios/')) {
    $llave_area = substr($ruta, strlen('servicios/'));
    if (isset($AREAS[$llave_area])) {
        $A = $AREAS[$llave_area];
        require __DIR__ . '/vistas/sitio/area.php';
        exit;
    }
}

/* La solicitud de cotización. Si sale bien se responde con una redirección,
   para que al recargar la página no se mande dos veces. */
if ($ruta === 'contacto' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/app/cotizacion.php';
    $envio = cotizacion_recibir($AREAS, $SITE);
    if ($envio['ok']) llevar_a('/contacto?enviado=' . ($envio['correo'] ? '1' : '2'), 303);
}

if ($ruta === 'contacto' && isset($_GET['enviado'])) {
    $envio = [
        'ok'      => true,
        'correo'  => $_GET['enviado'] === '1',
        'aviso'   => $_GET['enviado'] === '1'
            ? 'Gracias, recibimos tu solicitud. Te respondemos dentro del horario de atención.'
            : 'Gracias, dejamos registrada tu solicitud. Si es urgente, escríbenos por WhatsApp.',
        'errores' => [],
        'datos'   => [],
    ];
}

if (isset($paginas[$ruta])) {
    require __DIR__ . '/vistas/sitio/' . $paginas[$ruta] . '.php';
    exit;
}

http_response_code(404);
require __DIR__ . '/vistas/sitio/no-encontrada.php';
