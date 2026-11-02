<?php
/**
 * Prepara una fotografía para el sitio: la reduce, la guarda liviana y le
 * estampa el logo de IFK.
 *
 *   php herramientas/marcar-imagen.php entrada.jpg assets/img/salida.jpg [esquina] [ancho] [recorte]
 *
 *   esquina: sup-der (por defecto), sup-izq, inf-der, inf-izq, o "sin"
 *            cuando la foto ya muestra el logo por sí sola
 *   ancho:   ancho máximo de la imagen final, en píxeles (1600 por defecto)
 *   recorte: "x,y,ancho,alto" sobre la imagen original; sirve para cortar la
 *            franja donde otra empresa dejó su sello
 *
 * Se usa cada vez que llega una foto nueva del cliente, para que todas salgan
 * iguales: mismo tamaño, mismo peso y el logo en el mismo lugar. El estampado
 * en sí vive en app/sello.php, porque el panel lo usa también.
 */
declare(strict_types=1);

require_once __DIR__ . '/../app/sello.php';

$entrada = $argv[1] ?? '';
$salida  = $argv[2] ?? '';
$esquina = $argv[3] ?? 'sup-der';
$ancho   = (int)($argv[4] ?? 1600);
$recorte = $argv[5] ?? '';

if ($entrada === '' || $salida === '') {
    fwrite(STDERR, "Uso: php herramientas/marcar-imagen.php entrada.jpg salida.jpg [esquina] [ancho]\n");
    exit(1);
}

$foto = @imagecreatefromstring((string)@file_get_contents($entrada));
if (!$foto) { fwrite(STDERR, "No pude abrir $entrada\n"); exit(1); }

/* 1. Recortar, si se pidió. */
if ($recorte !== '') {
    [$rx, $ry, $rw, $rh] = array_map('intval', array_pad(explode(',', $recorte), 4, 0));
    $cortada = imagecrop($foto, ['x' => $rx, 'y' => $ry, 'width' => $rw, 'height' => $rh]);
    if ($cortada === false) { fwrite(STDERR, "Recorte inválido\n"); exit(1); }
    $foto = $cortada;
}

/* 2. Reducir, si hace falta. */
$w = imagesx($foto); $h = imagesy($foto);
if ($w > $ancho) {
    $nh = (int)round($h * $ancho / $w);
    $chico = imagecreatetruecolor($ancho, $nh);
    imagecopyresampled($chico, $foto, 0, 0, 0, 0, $ancho, $nh, $w, $h);
    $foto = $chico; $w = $ancho; $h = $nh;
}

/* 3. El sello. Salvo que se pida sin él: hay fotos donde la marca ya aparece
      —en la polera de un técnico, por ejemplo— y estamparlo otra vez sobra. */
$con_sello = $esquina !== 'sin';
if ($con_sello && !sello_poner($foto, $esquina)) {
    fwrite(STDERR, "No encontré assets/img/ifk_logo_corto.webp\n");
    exit(1);
}

/* 4. Guardar. */
$ok = str_ends_with(strtolower($salida), '.png')
    ? imagepng($foto, $salida, 8)
    : imagejpeg($foto, $salida, $con_sello ? 82 : 86);

if (!$ok) { fwrite(STDERR, "No pude escribir $salida\n"); exit(1); }
printf("%s  ·  %dx%d  ·  %d KB%s\n", $salida, $w, $h,
       (int)round(filesize($salida) / 1024), $con_sello ? '' : '  ·  sin logo estampado');
