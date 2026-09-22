<?php
/**
 * Prepara una fotografía para el sitio: la reduce, la guarda liviana y le
 * estampa el logo de IFK.
 *
 *   php herramientas/marcar-imagen.php entrada.jpg assets/img/salida.jpg [esquina] [ancho]
 *
 *   esquina: sup-der (por defecto), sup-izq, inf-der, inf-izq
 *   ancho:   ancho máximo de la imagen final, en píxeles (1600 por defecto)
 *
 * Se usa cada vez que llega una foto nueva del cliente, para que todas salgan
 * iguales: mismo tamaño, mismo peso y el logo en el mismo lugar.
 */
declare(strict_types=1);

$entrada = $argv[1] ?? '';
$salida  = $argv[2] ?? '';
$esquina = $argv[3] ?? 'sup-der';
$ancho   = (int)($argv[4] ?? 1600);

if ($entrada === '' || $salida === '') {
    fwrite(STDERR, "Uso: php herramientas/marcar-imagen.php entrada.jpg salida.jpg [esquina] [ancho]\n");
    exit(1);
}

$foto = @imagecreatefromstring((string)@file_get_contents($entrada));
if (!$foto) { fwrite(STDERR, "No pude abrir $entrada\n"); exit(1); }

/* 1. Reducir, si hace falta. */
$w = imagesx($foto); $h = imagesy($foto);
if ($w > $ancho) {
    $nh = (int)round($h * $ancho / $w);
    $chico = imagecreatetruecolor($ancho, $nh);
    imagecopyresampled($chico, $foto, 0, 0, 0, 0, $ancho, $nh, $w, $h);
    $foto = $chico; $w = $ancho; $h = $nh;
}

/* 2. El logo, al 11% del ancho de la foto. */
$logo = @imagecreatefromwebp(__DIR__ . '/../assets/img/ifk_logo.webp');
if (!$logo) { fwrite(STDERR, "No encontré assets/img/ifk_logo.webp\n"); exit(1); }
$lw = (int)round($w * 0.11);
$lh = (int)round(imagesy($logo) * $lw / imagesx($logo));

$marca = imagecreatetruecolor($lw, $lh);
imagealphablending($marca, false);
imagesavealpha($marca, true);
imagefill($marca, 0, 0, imagecolorallocatealpha($marca, 0, 0, 0, 127));
imagecopyresampled($marca, $logo, 0, 0, 0, 0, $lw, $lh, imagesx($logo), imagesy($logo));

/* 3. Dónde va. El margen es proporcional, para que se vea igual en toda foto. */
$m = (int)round($w * 0.025);
[$x, $y] = match ($esquina) {
    'sup-izq' => [$m, $m],
    'inf-der' => [$w - $lw - $m, $h - $lh - $m],
    'inf-izq' => [$m, $h - $lh - $m],
    default   => [$w - $lw - $m, $m],          // sup-der
};

/* 4. Estampar, con algo de transparencia para que no tape la foto. */
imagealphablending($foto, true);
imagecopymerge_alpha($foto, $marca, $x, $y, 78);

/* 5. Guardar. */
$ok = str_ends_with(strtolower($salida), '.png')
    ? imagepng($foto, $salida, 8)
    : imagejpeg($foto, $salida, 82);

if (!$ok) { fwrite(STDERR, "No pude escribir $salida\n"); exit(1); }
printf("%s  ·  %dx%d  ·  %d KB\n", $salida, $w, $h, (int)round(filesize($salida) / 1024));

/**
 * imagecopymerge() pierde la transparencia del PNG/WEBP; esta versión la
 * respeta: copia el logo sobre una capa y baja su opacidad canal por canal.
 */
function imagecopymerge_alpha($destino, $origen, int $x, int $y, int $opacidad): void {
    $w = imagesx($origen); $h = imagesy($origen);
    $capa = imagecreatetruecolor($w, $h);
    imagealphablending($capa, false);
    imagesavealpha($capa, true);
    imagecopy($capa, $origen, 0, 0, 0, 0, $w, $h);

    for ($i = 0; $i < $w; $i++) {
        for ($j = 0; $j < $h; $j++) {
            $c = imagecolorat($capa, $i, $j);
            $a = ($c >> 24) & 0x7F;
            $a = min(127, (int)round($a + (127 - $a) * (100 - $opacidad) / 100));
            imagesetpixel($capa, $i, $j, ($a << 24) | ($c & 0xFFFFFF));
        }
    }
    imagecopy($destino, $capa, $x, $y, 0, 0, $w, $h);
}
