<?php
/**
 * Procesa la solicitud de cotización.
 * Guarda el lead en /storage/leads.csv y lo envía por correo (si el hosting lo permite).
 */

$campos = ['nombre', 'empresa', 'rut', 'telefono', 'email', 'servicio', 'ubicacion', 'mensaje'];
$d = [];
foreach ($campos as $c) {
    $d[$c] = trim((string)($_POST[$c] ?? ''));
}
$FORM['datos']   = $d;
$FORM['enviado'] = true;

/* Honeypot antispam: campo oculto que sólo completan los bots */
if (trim((string)($_POST['website'] ?? '')) !== '') {
    $FORM['ok'] = true;   // respuesta silenciosa
    return;
}

if ($d['nombre'] === '')                                  $FORM['errores']['nombre']   = 'Indícanos tu nombre.';
if ($d['telefono'] === '' && $d['email'] === '')          $FORM['errores']['telefono'] = 'Déjanos un teléfono o un correo.';
if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
    $FORM['errores']['email'] = 'El correo no parece válido.';
}
if (mb_strlen($d['mensaje']) < 10)                        $FORM['errores']['mensaje']  = 'Cuéntanos brevemente qué necesitas (mínimo 10 caracteres).';

if ($FORM['errores']) {
    return;
}

/* Fotografías adjuntas (opcional, máximo 3 imágenes de 4 MB) */
$adjuntos = [];
$updir = __DIR__ . '/../storage/uploads';
if (!empty($_FILES['fotos']['name'][0]) && is_dir(dirname($updir))) {
    @mkdir($updir, 0775, true);
    $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic'];
    $total = min(3, count($_FILES['fotos']['name']));
    for ($i = 0; $i < $total; $i++) {
        if (($_FILES['fotos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
        if ($_FILES['fotos']['size'][$i] > 4 * 1024 * 1024) continue;
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['fotos']['tmp_name'][$i]);
        if (!isset($permitidos[$mime])) continue;
        $nombre = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $permitidos[$mime];
        if (@move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $updir . '/' . $nombre)) {
            $adjuntos[] = $nombre;
        }
    }
}

/* Registro local del lead */
$fila = array_merge([date('c'), $_SERVER['REMOTE_ADDR'] ?? ''], array_values($d), [implode(' | ', $adjuntos)]);
$dir  = __DIR__ . '/../storage';
if (!is_dir($dir)) @mkdir($dir, 0775, true);
if (is_dir($dir) && is_writable($dir)) {
    if ($fh = @fopen($dir . '/leads.csv', 'a')) {
        fputcsv($fh, $fila, ',', '"', '\\');
        fclose($fh);
    }
}

/* Aviso por correo */
$asunto = 'Nueva cotización desde ifkchile.com — ' . ($d['servicio'] ?: 'General');
$cuerpo = "Nueva solicitud de cotización\n\n"
    . "Nombre:      {$d['nombre']}\n"
    . "Empresa:     {$d['empresa']}\n"
    . "RUT:         {$d['rut']}\n"
    . "Teléfono:    {$d['telefono']}\n"
    . "Correo:      {$d['email']}\n"
    . "Servicio:    {$d['servicio']}\n"
    . "Ubicación:   {$d['ubicacion']}\n\n"
    . "Mensaje:\n{$d['mensaje']}\n\n"
    . ($adjuntos ? "Fotografías adjuntas: " . implode(', ', $adjuntos) . "\n\n" : '')
    . "Enviado el " . date('d-m-Y H:i') . " hrs\n";

$headers = "From: Sitio IFK <no-reply@ifkchile.com>\r\n"
    . ($d['email'] !== '' ? "Reply-To: {$d['email']}\r\n" : '')
    . "Content-Type: text/plain; charset=UTF-8\r\n";

@mail($SITE['email'], $asunto, $cuerpo, $headers);

$FORM['ok'] = true;
