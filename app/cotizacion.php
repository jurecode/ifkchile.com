<?php
/**
 * Solicitudes de cotización del formulario de contacto.
 *
 * Lo que llega se revisa, se guarda en datos/cotizaciones/ (por si el correo
 * falla, que en hosting compartido pasa) y recién después se manda por correo
 * a la casilla de la empresa, con las fotos adjuntas.
 */
declare(strict_types=1);

define('CARPETA_COTIZA', RAIZ_SITIO . '/datos/cotizaciones');

const COTIZA_MAX_FOTOS = 3;
const COTIZA_MAX_PESO  = 6 * 1024 * 1024;          // 6 MB por foto
const COTIZA_TIPOS     = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

/**
 * Revisa lo enviado y, si está bien, lo guarda y lo manda por correo.
 *
 * @return array{ok:bool, correo:bool, aviso:string, errores:array<string,string>, datos:array<string,string>}
 */
function cotizacion_recibir(array $AREAS, array $SITE): array {
    $datos = [];
    foreach (['nombre', 'empresa', 'rut', 'telefono', 'email', 'servicio', 'ubicacion', 'mensaje'] as $campo) {
        $datos[$campo] = trim((string)($_POST[$campo] ?? ''));
    }

    /* Trampa para robots: el campo está escondido, una persona nunca lo llena. */
    if (trim((string)($_POST['sitio_web'] ?? '')) !== '') {
        return ['ok' => true, 'correo' => true, 'aviso' => 'Gracias, recibimos tu mensaje.',
                'errores' => [], 'datos' => []];
    }

    $errores = [];
    if ($datos['nombre'] === '')                                   $errores['nombre']   = 'Escribe tu nombre.';
    if ($datos['telefono'] === '')                                 $errores['telefono'] = 'Necesitamos un teléfono para responderte.';
    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL))       $errores['email']    = 'Revisa el correo.';
    if (!isset($AREAS[$datos['servicio']]) && $datos['servicio'] !== 'otro') {
        $errores['servicio'] = 'Elige el servicio que necesitas.';
    }
    if (mb_strlen($datos['mensaje']) < 10)                         $errores['mensaje']  = 'Cuéntanos un poco más del trabajo.';

    /* Las fotos son opcionales, pero si vienen tienen que ser fotos. */
    [$fotos, $error_foto] = cotizacion_fotos();
    if ($error_foto !== '') $errores['fotos'] = $error_foto;

    if ($errores) {
        return ['ok' => false, 'correo' => false, 'aviso' => 'Falta algo en el formulario. Revisa lo marcado.',
                'errores' => $errores, 'datos' => $datos];
    }

    $servicio = $datos['servicio'] === 'otro' ? 'Otro / no lo sé' : $AREAS[$datos['servicio']]['nombre'];

    $guardadas = cotizacion_guardar($datos, $servicio, $fotos);
    $enviado   = cotizacion_enviar($datos, $servicio, $guardadas, $SITE);

    $aviso = $enviado
        ? 'Gracias, recibimos tu solicitud. Te respondemos dentro del horario de atención.'
        : 'Gracias, dejamos registrada tu solicitud. Si es urgente, escríbenos por WhatsApp.';

    return ['ok' => true, 'correo' => $enviado, 'aviso' => $aviso, 'errores' => [], 'datos' => []];
}

/**
 * Revisa las fotos que vengan en el formulario.
 * @return array{0:array<int,array{nombre:string,tipo:string,tmp:string}>, 1:string}
 */
function cotizacion_fotos(): array {
    $subidas = $_FILES['fotos'] ?? null;
    if (!is_array($subidas) || !isset($subidas['error'])) return [[], ''];

    $lista = [];
    $total = is_array($subidas['error']) ? count($subidas['error']) : 0;

    for ($i = 0; $i < $total; $i++) {
        $cod = (int)$subidas['error'][$i];
        if ($cod === UPLOAD_ERR_NO_FILE) continue;
        if ($cod === UPLOAD_ERR_INI_SIZE || $cod === UPLOAD_ERR_FORM_SIZE) {
            return [[], 'Alguna foto pesa demasiado. El máximo es 6 MB por imagen.'];
        }
        if ($cod !== UPLOAD_ERR_OK) return [[], 'No pudimos leer una de las fotos. Inténtalo de nuevo.'];

        if (count($lista) >= COTIZA_MAX_FOTOS) return [[], 'Puedes adjuntar hasta 3 fotos.'];
        if ((int)$subidas['size'][$i] > COTIZA_MAX_PESO) return [[], 'Alguna foto pesa más de 6 MB.'];

        $tmp  = (string)$subidas['tmp_name'][$i];
        if (!is_uploaded_file($tmp)) return [[], 'No pudimos leer una de las fotos.'];

        $info = @getimagesize($tmp);
        $mime = is_array($info) ? (string)($info['mime'] ?? '') : '';
        if (!isset(COTIZA_TIPOS[$mime])) return [[], 'Las fotos tienen que ser JPG, PNG o WEBP.'];

        $lista[] = ['nombre' => basename((string)$subidas['name'][$i]), 'tipo' => $mime, 'tmp' => $tmp];
    }
    return [$lista, ''];
}

/**
 * Deja la solicitud escrita en el servidor. Devuelve las fotos ya guardadas.
 * @return array<int,array{ruta:string,tipo:string,nombre:string}>
 */
function cotizacion_guardar(array $datos, string $servicio, array $fotos): array {
    $sello   = date('Y-m-d_His') . '-' . bin2hex(random_bytes(3));
    $carpeta = CARPETA_COTIZA;
    if (!is_dir($carpeta) && !@mkdir($carpeta, 0775, true) && !is_dir($carpeta)) return [];

    $guardadas = [];
    foreach ($fotos as $n => $f) {
        $destino = $carpeta . '/' . $sello . '-' . ($n + 1) . '.' . COTIZA_TIPOS[$f['tipo']];
        if (@move_uploaded_file($f['tmp'], $destino)) {
            @chmod($destino, 0640);
            $guardadas[] = ['ruta' => $destino, 'tipo' => $f['tipo'], 'nombre' => basename($destino)];
        }
    }

    $registro = $datos + [
        'servicio_nombre' => $servicio,
        'fecha'           => date('c'),
        'ip'              => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
        'fotos'           => array_column($guardadas, 'nombre'),
    ];
    @file_put_contents(
        $carpeta . '/' . $sello . '.json',
        json_encode($registro, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    );

    return $guardadas;
}

/** Manda la solicitud al correo de la empresa, con las fotos adjuntas. */
function cotizacion_enviar(array $datos, string $servicio, array $fotos, array $SITE): bool {
    if (!function_exists('mail')) return false;

    $texto = implode("\n", [
        'Nueva solicitud de cotización desde el sitio.',
        '',
        'Nombre:      ' . $datos['nombre'],
        'Empresa:     ' . ($datos['empresa'] !== '' ? $datos['empresa'] : '—'),
        'RUT:         ' . ($datos['rut'] !== '' ? $datos['rut'] : '—'),
        'Teléfono:    ' . $datos['telefono'],
        'Correo:      ' . $datos['email'],
        'Servicio:    ' . $servicio,
        'Ubicación:   ' . ($datos['ubicacion'] !== '' ? $datos['ubicacion'] : '—'),
        'Fotos:       ' . (count($fotos) ?: 'sin fotos'),
        '',
        'Mensaje:',
        $datos['mensaje'],
        '',
        '— Enviado el ' . date('d-m-Y H:i') . ' desde ' . $SITE['dominio'],
    ]);

    $asunto = '=?UTF-8?B?' . base64_encode('Cotización web · ' . $servicio . ' · ' . $datos['nombre']) . '?=';
    $de     = 'IFK web <contacto@' . preg_replace('#^https?://(www\.)?#', '', $SITE['dominio']) . '>';

    $cab = [
        'From: ' . $de,
        'Reply-To: ' . $datos['nombre'] . ' <' . $datos['email'] . '>',
        'MIME-Version: 1.0',
    ];

    if (!$fotos) {
        $cab[] = 'Content-Type: text/plain; charset=UTF-8';
        return @mail($SITE['email'], $asunto, $texto, implode("\r\n", $cab));
    }

    $borde = '=_' . bin2hex(random_bytes(12));
    $cab[] = 'Content-Type: multipart/mixed; boundary="' . $borde . '"';

    $cuerpo = "--$borde\r\nContent-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n" . $texto . "\r\n";

    foreach ($fotos as $f) {
        $contenido = @file_get_contents($f['ruta']);
        if ($contenido === false) continue;
        $cuerpo .= "--$borde\r\nContent-Type: " . $f['tipo'] . '; name="' . $f['nombre'] . "\"\r\n"
                 . "Content-Transfer-Encoding: base64\r\n"
                 . 'Content-Disposition: attachment; filename="' . $f['nombre'] . "\"\r\n\r\n"
                 . chunk_split(base64_encode($contenido)) . "\r\n";
    }
    $cuerpo .= "--$borde--\r\n";

    return @mail($SITE['email'], $asunto, $cuerpo, implode("\r\n", $cab));
}
