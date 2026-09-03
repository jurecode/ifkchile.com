<?php
/**
 * Panel IFK — funciones base: sesión, seguridad, imágenes, git y despliegue.
 */
declare(strict_types=1);

require __DIR__ . '/../config.php';

const PANEL_LOG      = __DIR__ . '/../storage/panel.log';
const PANEL_INTENTOS = __DIR__ . '/../storage/intentos.json';
define('RAIZ', realpath(__DIR__ . '/..') ?: __DIR__ . '/..');

/* ---------------- Sesión y autenticación ---------------- */

function panel_sesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('ifk_panel');
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'cookie_secure'   => !empty($_SERVER['HTTPS']),
        ]);
    }
}

function panel_autenticado(): bool {
    return !empty($_SESSION['panel_ok']);
}

function panel_exigir_login(): void {
    if (!panel_autenticado()) {
        header('Location: index.php?v=login');
        exit;
    }
}

function panel_tiene_password(): bool {
    return ajustes()['password_hash'] !== '';
}

/* Bloqueo simple por intentos fallidos: 5 cada 10 minutos */
function panel_bloqueado(): int {
    $d = is_file(PANEL_INTENTOS) ? (json_decode((string)file_get_contents(PANEL_INTENTOS), true) ?: []) : [];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $reg = $d[$ip] ?? ['n' => 0, 't' => 0];
    if ($reg['n'] >= 5 && (time() - $reg['t']) < 600) {
        return 600 - (time() - $reg['t']);
    }
    return 0;
}

function panel_registrar_intento(bool $exito): void {
    $d = is_file(PANEL_INTENTOS) ? (json_decode((string)file_get_contents(PANEL_INTENTOS), true) ?: []) : [];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    if ($exito) { unset($d[$ip]); }
    else {
        $reg = $d[$ip] ?? ['n' => 0, 't' => 0];
        if ((time() - ($reg['t'] ?: time())) > 600) $reg['n'] = 0;
        $d[$ip] = ['n' => $reg['n'] + 1, 't' => time()];
    }
    @file_put_contents(PANEL_INTENTOS, json_encode($d), LOCK_EX);
}

/* ---------------- CSRF ---------------- */

function csrf(): string {
    panel_sesion();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_ok(): bool {
    panel_sesion();
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

/* ---------------- Registro de actividad ---------------- */

function panel_log(string $accion, string $detalle = ''): void {
    $linea = sprintf("[%s] %s | %s | %s\n", date('d-m-Y H:i:s'), $_SERVER['REMOTE_ADDR'] ?? '-', $accion, str_replace("\n", ' ', $detalle));
    @file_put_contents(PANEL_LOG, $linea, FILE_APPEND | LOCK_EX);
}

function panel_log_ultimas(int $n = 12): array {
    if (!is_file(PANEL_LOG)) return [];
    $l = array_filter(explode("\n", (string)file_get_contents(PANEL_LOG)));
    return array_slice(array_reverse($l), 0, $n);
}

/* ---------------- Imágenes ---------------- */

/**
 * Guarda la imagen subida en el slot indicado: valida, recorta al alto/ancho
 * del slot y respalda la versión anterior.
 */
function panel_guardar_imagen(string $clave, array $file): array {
    $slots = slots_imagenes();
    if (!isset($slots[$clave]))                    return [false, 'Imagen desconocida.'];
    if (($file['error'] ?? 1) !== UPLOAD_ERR_OK)   return [false, 'No se pudo recibir el archivo (¿supera el límite del servidor?).'];
    if ($file['size'] > 12 * 1024 * 1024)          return [false, 'El archivo supera los 12 MB.'];

    $mime  = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($tipos[$mime]))                     return [false, 'Formato no permitido. Usa JPG, PNG o WebP.'];

    $slot    = $slots[$clave];
    $destino = RAIZ . '/' . $slot['archivo'];
    $extDest = strtolower(pathinfo($slot['archivo'], PATHINFO_EXTENSION));

    /* Respaldo de la imagen actual */
    $dirBk = RAIZ . '/storage/respaldo-img';
    @mkdir($dirBk, 0775, true);
    $actual = RAIZ . '/' . img_ruta($clave);
    if (is_file($actual)) {
        @copy($actual, $dirBk . '/' . date('Ymd-His') . '-' . basename($actual));
    }

    /* Sin GD: se guarda tal cual si la extensión coincide */
    if (!function_exists('imagecreatefromstring')) {
        if ($tipos[$mime] !== $extDest) {
            return [false, 'El servidor no tiene la extensión GD; sube la imagen en formato ' . strtoupper($extDest) . '.'];
        }
        if (!@move_uploaded_file($file['tmp_name'], $destino)) return [false, 'No se pudo escribir el archivo.'];
        panel_log('imagen', $clave . ' (sin procesar)');
        return [true, 'Imagen actualizada.'];
    }

    $im = @imagecreatefromstring((string)file_get_contents($file['tmp_name']));
    if (!$im) return [false, 'La imagen no se pudo leer.'];

    if (!empty($slot['w']) && !empty($slot['h'])) {
        $im = panel_recortar($im, (int)$slot['w'], (int)$slot['h']);
    } else {
        $im = panel_limitar($im, 900, 500);   // logotipo: sólo se limita el tamaño
    }

    $rutaFinal = $slot['archivo'];
    $ok = false;
    if ($extDest === 'webp' && function_exists('imagewebp')) {
        imagepalettetotruecolor($im); imagealphablending($im, false); imagesavealpha($im, true);
        $ok = imagewebp($im, $destino, 88);
    } elseif ($extDest === 'jpg' || $extDest === 'jpeg') {
        $fondo = imagecreatetruecolor(imagesx($im), imagesy($im));
        imagefill($fondo, 0, 0, imagecolorallocate($fondo, 255, 255, 255));
        imagecopy($fondo, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
        $ok = imagejpeg($fondo, $destino, 82);
        imagedestroy($fondo);
    } elseif ($extDest === 'png') {
        imagealphablending($im, false); imagesavealpha($im, true);
        $ok = imagepng($im, $destino, 8);
    }

    /* Si no se pudo escribir en el formato original, se guarda como PNG */
    if (!$ok) {
        $rutaFinal = preg_replace('/\.[a-z]+$/i', '.png', $slot['archivo']);
        imagealphablending($im, false); imagesavealpha($im, true);
        $ok = imagepng($im, RAIZ . '/' . $rutaFinal, 8);
    }
    imagedestroy($im);
    if (!$ok) return [false, 'No se pudo guardar la imagen.'];

    $aj = ajustes();
    $aj['imagenes'][$clave] = $rutaFinal;
    guardar_ajustes(['imagenes' => $aj['imagenes']]);
    panel_log('imagen', $clave . ' → ' . $rutaFinal);
    return [true, 'Imagen actualizada.'];
}

function panel_recortar($im, int $W, int $H) {
    $w = imagesx($im); $h = imagesy($im);
    $tr = $W / $H; $r = $w / $h;
    if ($r > $tr) { $nw = (int)round($h * $tr); $nh = $h; $x = (int)(($w - $nw) / 2); $y = 0; }
    else          { $nw = $w; $nh = (int)round($w / $tr); $x = 0; $y = (int)(($h - $nh) * 0.35); }
    $out = imagecreatetruecolor($W, $H);
    imagealphablending($out, false); imagesavealpha($out, true);
    imagecopyresampled($out, $im, 0, 0, $x, $y, $W, $H, $nw, $nh);
    imagedestroy($im);
    return $out;
}

function panel_limitar($im, int $maxW, int $maxH) {
    $w = imagesx($im); $h = imagesy($im);
    if ($w <= $maxW && $h <= $maxH) return $im;
    $f = min($maxW / $w, $maxH / $h);
    $W = (int)round($w * $f); $H = (int)round($h * $f);
    $out = imagecreatetruecolor($W, $H);
    imagealphablending($out, false); imagesavealpha($out, true);
    imagecopyresampled($out, $im, 0, 0, 0, 0, $W, $H, $w, $h);
    imagedestroy($im);
    return $out;
}

/* ---------------- Ejecución de comandos ---------------- */

function shell_disponible(): bool {
    $off = array_map('trim', explode(',', (string)ini_get('disable_functions')));
    return function_exists('proc_open') && !in_array('proc_open', $off, true);
}

/**
 * Ejecuta un comando en la raíz del proyecto y devuelve [código, salida].
 * Lee salida y errores a la vez para que un comando hablador no se quede trabado.
 */
function correr(array $cmd, array $env = [], int $limite = 180): array {
    if (!shell_disponible()) return [127, 'El hosting no permite ejecutar comandos (proc_open deshabilitado).'];
    @set_time_limit($limite + 30);

    $desc    = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $entorno = array_merge($_ENV, [
        'HOME'                => sys_get_temp_dir(),
        'GIT_TERMINAL_PROMPT' => '0',
        'GIT_ASKPASS'         => 'echo',
        'GIT_CONFIG_NOSYSTEM' => '1',
    ], $env);

    $p = @proc_open($cmd, $desc, $tub, RAIZ, $entorno);
    if (!is_resource($p)) return [127, 'No se pudo iniciar el comando.'];

    stream_set_blocking($tub[1], false);
    stream_set_blocking($tub[2], false);

    $out    = '';
    $inicio = time();
    while (true) {
        $leer = [$tub[1], $tub[2]];
        $esc = $exc = null;
        if (@stream_select($leer, $esc, $exc, 1) > 0) {
            foreach ($leer as $h) $out .= (string)fread($h, 16384);
        }
        $estado = proc_get_status($p);
        if (!$estado['running']) break;
        if (time() - $inicio > $limite) {
            proc_terminate($p, 9);
            $out .= "\n[El comando superó los {$limite} segundos y se detuvo.]";
            break;
        }
    }
    foreach ([$tub[1], $tub[2]] as $h) {
        while (($b = fread($h, 16384)) !== '' && $b !== false) $out .= $b;
        fclose($h);
    }
    $cod = proc_close($p);
    if (isset($estado) && !$estado['running'] && $estado['exitcode'] >= 0) $cod = $estado['exitcode'];
    return [$cod, trim($out)];
}

function git_disponible(): bool {
    [$c] = correr(['git', '--version']);
    return $c === 0;
}

function es_repo(): bool {
    return is_dir(RAIZ . '/.git');
}

/** URL de push con el token (sólo se arma en memoria, nunca se guarda en .git) */
function git_url_push(array $g): string {
    $repo = trim($g['repo']);
    if ($repo === '') return '';
    $repo = preg_replace('#^https?://#', '', $repo);
    $repo = preg_replace('#^[^@]*@#', '', $repo);          // limpia credenciales previas
    $usuario = rawurlencode(trim($g['usuario'] ?: 'x-access-token'));
    $token   = rawurlencode(trim($g['token']));
    return "https://{$usuario}:{$token}@{$repo}";
}

function ocultar_token(string $texto, string $token): string {
    if ($token === '') return $texto;
    return str_replace([$token, rawurlencode($token)], '***', $texto);
}
