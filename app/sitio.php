<?php
/**
 * Estado del sitio y ayudas comunes.
 *
 * El sitio tiene tres estados y se cambian desde el panel, sin tocar código:
 *
 *   coming_soon    el visitante sólo ve la fachada "Próximamente"
 *   publicado      el sitio completo queda visible para todos
 *   mantenimiento  pantalla de "estamos realizando mejoras"
 *
 * El estado vive en datos/sitio.json, en el propio servidor (igual que
 * config.php: no viaja a GitHub, así el sitio de prueba y el de verdad
 * pueden estar en estados distintos).
 */
declare(strict_types=1);

/* Las horas que se muestran son las de Chile, no las del servidor. */
date_default_timezone_set('America/Santiago');

define('RAIZ_SITIO', dirname(__DIR__));
define('ARCHIVO_ESTADO', RAIZ_SITIO . '/datos/sitio.json');

require_once __DIR__ . '/marca.php';

/* ------------------------------------------------------------------ */
/* Estado del sitio                                                    */
/* ------------------------------------------------------------------ */

/** Los tres estados, con el nombre que se ve en el panel. */
function estados_posibles(): array {
    return [
        'coming_soon'   => 'Coming Soon (sólo la fachada)',
        'publicado'     => 'Publicado (sitio completo visible)',
        'mantenimiento' => 'Mantenimiento (estamos mejorando)',
    ];
}

/** Todo lo guardado. Si el archivo no existe todavía, valores de partida. */
function ajustes(bool $recargar = false): array {
    static $cache = null;
    if ($cache !== null && !$recargar) return $cache;

    $base = ['estado' => 'coming_soon', 'actualizado' => '', 'por' => ''];
    $datos = [];
    if (is_file(ARCHIVO_ESTADO)) {
        $datos = json_decode((string)@file_get_contents(ARCHIVO_ESTADO), true) ?: [];
    }
    return $cache = array_replace($base, is_array($datos) ? $datos : []);
}

/** El estado de ahora. Ante cualquier duda, la fachada: nunca se publica solo. */
function estado_sitio(): string {
    $e = (string)ajustes()['estado'];
    return isset(estados_posibles()[$e]) ? $e : 'coming_soon';
}

/** Guarda el estado nuevo. Devuelve [salió bien, aviso]. */
function guardar_estado(string $estado): array {
    if (!isset(estados_posibles()[$estado])) {
        return [false, 'Ese estado no existe.'];
    }
    $carpeta = dirname(ARCHIVO_ESTADO);
    if (!is_dir($carpeta) && !@mkdir($carpeta, 0775, true) && !is_dir($carpeta)) {
        return [false, 'No pude crear la carpeta datos/. Dale permiso de escritura.'];
    }
    $datos = ['estado' => $estado, 'actualizado' => date('c'), 'por' => 'panel'];
    $json  = json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (@file_put_contents(ARCHIVO_ESTADO, $json, LOCK_EX) === false) {
        return [false, 'No pude escribir datos/sitio.json. Dale permiso de escritura a la carpeta.'];
    }
    @chmod(ARCHIVO_ESTADO, 0640);
    ajustes(true);
    return [true, 'Estado del sitio: ' . estados_posibles()[$estado] . '.'];
}

/* ------------------------------------------------------------------ */
/* Quién está mirando                                                  */
/* ------------------------------------------------------------------ */

/** Abre la sesión del panel sólo si hace falta (el visitante normal no la toca). */
function sesion_panel(bool $forzar = false): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    if (!$forzar && empty($_COOKIE['panel'])) return;   // visitante sin sesión: ni la creamos
    if (headers_sent()) return;                         // demasiado tarde: no vale la pena avisar
    session_name('panel');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure'   => !empty($_SERVER['HTTPS']),
    ]);
}

/** ¿Hay un administrador con la sesión abierta? */
function admin_dentro(): bool {
    sesion_panel();
    return session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['ok']);
}

/**
 * Qué está mirando el administrador: 'sitio' (el frontend real) o 'fachada'
 * (lo mismo que ve el público). Se recuerda mientras dure la sesión.
 */
function modo_vista(): string {
    if (!admin_dentro()) return 'fachada';
    return ($_SESSION['ver'] ?? 'fachada') === 'sitio' ? 'sitio' : 'fachada';
}

function guardar_modo_vista(string $modo): void {
    if (!admin_dentro()) return;
    $_SESSION['ver'] = $modo === 'sitio' ? 'sitio' : 'fachada';
}

/** ¿Este visitante puede ver el sitio completo aunque esté oculto al público? */
function puede_ver_sitio(): bool {
    return estado_sitio() === 'publicado' || (admin_dentro() && modo_vista() === 'sitio');
}

/* ------------------------------------------------------------------ */
/* Ayudas de plantilla                                                 */
/* ------------------------------------------------------------------ */

if (!function_exists('e')) {
    function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

/** Ruta a un archivo de assets con la fecha pegada, para que el navegador no guarde uno viejo. */
function asset(string $ruta): string {
    $ruta = '/assets/' . ltrim($ruta, '/');
    $f = RAIZ_SITIO . $ruta;
    return $ruta . (is_file($f) ? '?v=' . filemtime($f) : '');
}

/** Enlace de WhatsApp de la empresa. */
function wa_link(): string {
    global $SITE;
    $u = 'https://wa.me/' . $SITE['whatsapp'];
    return $SITE['whatsapp_msg'] !== '' ? $u . '?text=' . rawurlencode($SITE['whatsapp_msg']) : $u;
}

/** Manda al visitante a otra dirección y corta. */
function llevar_a(string $destino, int $codigo = 302): never {
    header('Location: ' . $destino, true, $codigo);
    exit;
}

/**
 * Mientras el sitio no esté publicado le pedimos a Google que no lo indexe;
 * al publicar, la etiqueta desaparece sola.
 */
function cabeceras_buscadores(): void {
    if (estado_sitio() !== 'publicado') {
        header('X-Robots-Tag: noindex, nofollow', true);
    }
}

/** La etiqueta <meta robots> equivalente, para el <head> de las vistas. */
function meta_robots(): string {
    return estado_sitio() === 'publicado'
        ? '<meta name="robots" content="index, follow">'
        : '<meta name="robots" content="noindex, nofollow">';
}
