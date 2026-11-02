<?php
/**
 * El acceso del cliente al panel de fotos (/fotos).
 *
 * Es una puerta aparte de la de /admin, a propósito: el cliente entra a
 * cambiar fotografías y no tiene por qué ver tokens, repositorios ni palabras
 * de consola. Tampoco comparten la clave, así que se le puede dar la suya sin
 * entregarle el panel técnico.
 *
 * La clave se guarda cifrada en datos/cliente.json, que no viaja a GitHub: es
 * de este servidor, igual que config.php. La pone quien instala el sitio,
 * desde /admin.
 */
declare(strict_types=1);

define('ARCHIVO_CLIENTE', RAIZ_SITIO . '/datos/cliente.json');

/** Lo guardado del cliente: la clave cifrada y cuándo entró por última vez. */
function cliente_datos(bool $recargar = false): array {
    static $cache = null;
    if ($cache !== null && !$recargar) return $cache;

    $d = is_file(ARCHIVO_CLIENTE)
       ? json_decode((string)@file_get_contents(ARCHIVO_CLIENTE), true)
       : null;
    return $cache = is_array($d) ? $d + ['clave' => '', 'visto' => ''] : ['clave' => '', 'visto' => ''];
}

/** ¿Ya tiene clave puesta? Sin clave, el panel de fotos no deja entrar a nadie. */
function cliente_tiene_clave(): bool {
    return cliente_datos()['clave'] !== '';
}

/** Deja una clave nueva. La guarda cifrada: ni siquiera nosotros la leemos. */
function cliente_clave_guardar(string $clave): array {
    $clave = trim($clave);
    if (mb_strlen($clave) < 6) return [false, 'La clave del cliente necesita al menos 6 letras.'];

    $carpeta = dirname(ARCHIVO_CLIENTE);
    if (!is_dir($carpeta) && !@mkdir($carpeta, 0775, true) && !is_dir($carpeta)) {
        return [false, 'No pude crear la carpeta datos/.'];
    }
    $d = cliente_datos();
    $d['clave'] = password_hash($clave, PASSWORD_DEFAULT);

    $json = json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (@file_put_contents(ARCHIVO_CLIENTE, $json, LOCK_EX) === false) {
        return [false, 'No pude escribir datos/cliente.json. Dale permiso de escritura a la carpeta.'];
    }
    @chmod(ARCHIVO_CLIENTE, 0640);
    cliente_datos(true);
    return [true, 'Clave del cliente guardada. Ya puede entrar a /fotos.'];
}

/** Le quita el acceso al cliente. */
function cliente_clave_quitar(): array {
    $d = cliente_datos();
    $d['clave'] = '';
    @file_put_contents(ARCHIVO_CLIENTE, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    cliente_datos(true);
    return [true, 'El cliente ya no puede entrar a /fotos.'];
}

/* ------------------------------------------------------------------ */
/* La sesión                                                           */
/* ------------------------------------------------------------------ */

/**
 * Abre la sesión del panel de fotos. Tiene nombre propio —no 'panel'— para
 * que la agencia pueda estar dentro de /admin y el cliente dentro de /fotos
 * en el mismo navegador sin pisarse.
 */
function cliente_sesion(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    if (headers_sent()) return;
    session_name('fotos');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure'   => !empty($_SERVER['HTTPS']),
    ]);
}

function cliente_dentro(): bool {
    cliente_sesion();
    return !empty($_SESSION['cliente']);
}

/** Revisa la clave y abre la sesión. Devuelve [entró, aviso]. */
function cliente_entrar(string $clave): array {
    if (!cliente_tiene_clave()) {
        return [false, 'Todavía no hay una clave puesta para este panel.'];
    }
    if (!password_verify($clave, (string)cliente_datos()['clave'])) {
        sleep(1);                                  // que probar claves al azar cueste
        return [false, 'Esa clave no es.'];
    }
    cliente_sesion();
    session_regenerate_id(true);
    $_SESSION['cliente'] = true;

    $d = cliente_datos();
    $d['visto'] = date('c');
    @file_put_contents(ARCHIVO_CLIENTE, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    cliente_datos(true);

    return [true, ''];
}

function cliente_salir(): void {
    cliente_sesion();
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
}
