<?php
/**
 * IFK — Inversiones Friomak SPA
 * Configuración general del sitio.
 */

/* ---------------------------------------------------------------
 |  ARCHIVO DE AJUSTES
 |  El panel (/panel/) escribe storage/settings.json y desde ahí se
 |  controla si el sitio está publicado o en modo "Próximamente",
 |  la clave de previsualización y las imágenes reemplazadas.
 |  Los valores de abajo son sólo el punto de partida.
 * --------------------------------------------------------------- */
define('RUTA_AJUSTES', __DIR__ . '/storage/settings.json');

function ajustes_defecto(): array {
    return [
        'coming_soon'   => true,
        'preview_key'   => 'ifk2026',
        'password_hash' => '',
        'imagenes'      => [],   // clave => ruta relativa (si se cambió la extensión)
        'git'           => ['repo' => '', 'rama' => 'main', 'usuario' => '', 'token' => ''],
        'actualizado'   => '',
    ];
}

function ajustes(bool $recargar = false): array {
    static $cache = null;
    if ($cache !== null && !$recargar) return $cache;
    $datos = [];
    if (is_file(RUTA_AJUSTES)) {
        $datos = json_decode((string)file_get_contents(RUTA_AJUSTES), true) ?: [];
    }
    $cache = array_replace_recursive(ajustes_defecto(), $datos);
    return $cache;
}

function guardar_ajustes(array $nuevos): bool {
    if (!is_dir(dirname(RUTA_AJUSTES))) @mkdir(dirname(RUTA_AJUSTES), 0775, true);
    $datos = array_replace_recursive(ajustes(), $nuevos);
    $datos['actualizado'] = date('c');
    $ok = @file_put_contents(RUTA_AJUSTES, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) !== false;
    if ($ok) { @chmod(RUTA_AJUSTES, 0640); ajustes(true); }
    return $ok;
}

$AJ = ajustes();
define('COMING_SOON', (bool)$AJ['coming_soon']);
define('PREVIEW_KEY', (string)$AJ['preview_key']);

/* URLs amigables (/nosotros/ en vez de /index.php?p=nosotros).
   Requiere Apache con mod_rewrite y el .htaccess incluido. Activar sólo
   después de comprobar que el hosting las resuelve bien. */
define('URLS_AMIGABLES', false);

/* Datos de la empresa (fuente única de verdad para todo el sitio) */
$SITE = [
    'marca'        => 'IFK',
    'razon_social' => 'Inversiones Friomak SpA',
    'nombre_largo' => 'IFK · Inversiones Friomak',
    'claim'        => 'Refrigeración, climatización, ventilación y arriendo reefer',
    'anios'        => 10,
    'dominio'      => 'https://ifkchile.com',
    'email'        => 'contacto@ifkchile.com',
    'telefono'     => '+56 9 8546 4643',
    'whatsapp'     => '56985464643',           // solo dígitos, formato internacional
    'whatsapp_msg' => '',                      // el cliente pidió sin mensaje automático
    'direccion'    => 'Camino a Pargua Km 9, Lote 23, Puerto Montt',
    'horario'      => 'Lunes a viernes, 09:00 a 18:00 hrs',
    'emergencia'   => 'Servicio de emergencia disponible',
    'cobertura'    => 'Regiones X, XI, XII y XIV — proyectos a nivel nacional',
    'redes'        => [
        // 'instagram' => 'https://instagram.com/...',
        // 'linkedin'  => 'https://linkedin.com/company/...',
        // 'facebook'  => 'https://facebook.com/...',
    ],
];

/* Helpers */
function e(?string $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function url(string $page = 'home', array $params = []): string {
    if (!URLS_AMIGABLES) {
        return '/index.php?' . http_build_query(array_merge(['p' => $page], $params));
    }
    $ruta = match ($page) {
        'home'     => '/',
        'area'     => '/servicios/' . ($params['a'] ?? '') . '/',
        default    => '/' . $page . '/',
    };
    unset($params['a']);
    return $ruta . ($params ? '?' . http_build_query($params) : '');
}

function wa_link(string $texto = ''): string {
    global $SITE;
    $base = 'https://wa.me/' . $SITE['whatsapp'];
    $txt  = $texto !== '' ? $texto : $SITE['whatsapp_msg'];
    return $txt !== '' ? $base . '?text=' . rawurlencode($txt) : $base;
}

function asset(string $path): string {
    $full = __DIR__ . '/assets/' . ltrim($path, '/');
    $ver  = is_file($full) ? filemtime($full) : time();
    return '/assets/' . ltrim($path, '/') . '?v=' . $ver;
}

function is_active(string $page): bool {
    return ($GLOBALS['PAGE'] ?? 'home') === $page;
}

/* ---------------------------------------------------------------
 |  IMÁGENES DEL SITIO
 |  Cada "slot" tiene un archivo por defecto; el panel puede
 |  reemplazarlo (y guardar otra ruta si cambia la extensión).
 * --------------------------------------------------------------- */
function slots_imagenes(): array {
    return [
        'logo'          => ['archivo' => 'assets/img/ifk_logo.webp',            'titulo' => 'Logotipo',                 'medidas' => 'PNG o WebP con fondo transparente', 'w' => 0,    'h' => 0],
        'hero'          => ['archivo' => 'assets/img/hero.jpg',                 'titulo' => 'Portada principal',        'medidas' => '1920 × 1080 px',                    'w' => 1920, 'h' => 1080],
        'refrigeracion' => ['archivo' => 'assets/img/refrigeracion.jpg',        'titulo' => 'Área · Refrigeración',     'medidas' => '1000 × 688 px',                     'w' => 1000, 'h' => 688],
        'climatizacion' => ['archivo' => 'assets/img/climatizacion.jpg',        'titulo' => 'Área · Climatización',     'medidas' => '1000 × 688 px',                     'w' => 1000, 'h' => 688],
        'ventilacion'   => ['archivo' => 'assets/img/ventilacion.jpg',          'titulo' => 'Área · Ventilación',       'medidas' => '1000 × 688 px',                     'w' => 1000, 'h' => 688],
        'reefer'        => ['archivo' => 'assets/img/reefer.jpg',               'titulo' => 'Área · Arriendo Reefer',   'medidas' => '1000 × 688 px',                     'w' => 1000, 'h' => 688],
        'metodo'        => ['archivo' => 'assets/img/metodo.jpg',               'titulo' => 'Sección "Cómo trabajamos"','medidas' => '1200 × 1040 px',                    'w' => 1200, 'h' => 1040],
        'nosotros'      => ['archivo' => 'assets/img/nosotros.jpg',             'titulo' => 'Sección "Nosotros"',       'medidas' => '1200 × 1040 px',                    'w' => 1200, 'h' => 1040],
        'proyecto-1'    => ['archivo' => 'assets/img/proyectos/proyecto-1.jpg', 'titulo' => 'Proyecto 1',               'medidas' => '1000 × 625 px',                     'w' => 1000, 'h' => 625],
        'proyecto-2'    => ['archivo' => 'assets/img/proyectos/proyecto-2.jpg', 'titulo' => 'Proyecto 2',               'medidas' => '1000 × 625 px',                     'w' => 1000, 'h' => 625],
        'proyecto-3'    => ['archivo' => 'assets/img/proyectos/proyecto-3.jpg', 'titulo' => 'Proyecto 3',               'medidas' => '1000 × 625 px',                     'w' => 1000, 'h' => 625],
    ];
}

function img_ruta(string $clave): string {
    $slots = slots_imagenes();
    $aj    = ajustes();
    $rel   = $aj['imagenes'][$clave] ?? ($slots[$clave]['archivo'] ?? '');
    return $rel;
}

function img_src(string $clave): string {
    $rel  = img_ruta($clave);
    if ($rel === '') return '';
    $full = __DIR__ . '/' . $rel;
    $ver  = is_file($full) ? filemtime($full) : time();
    return '/' . $rel . '?v=' . $ver;
}
