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
 * iguales: mismo tamaño, mismo peso y el logo en el mismo lugar.
 */
declare(strict_types=1);

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

/* 3. El logo, al 12% del ancho de la foto. Salvo que se pida sin él: hay
      fotos donde la marca ya aparece —en la polera de un técnico, por
      ejemplo— y estamparlo otra vez sobra. */
if ($esquina === 'sin') {
    $ok = str_ends_with(strtolower($salida), '.png')
        ? imagepng($foto, $salida, 8)
        : imagejpeg($foto, $salida, 86);
    if (!$ok) { fwrite(STDERR, "No pude escribir $salida\n"); exit(1); }
    printf("%s  ·  %dx%d  ·  %d KB  ·  sin logo estampado\n", $salida, $w, $h, (int)round(filesize($salida) / 1024));
    exit(0);
}

/* Se estampa la versión corta —el copo y el IFK, sin la bajada de texto—.
   Esa línea fina, al tamaño de un estampado, no se lee en ninguna foto:
   queda como una mancha y parece que el logotipo estuviera borroso. */
$logo = @imagecreatefromwebp(__DIR__ . '/../assets/img/ifk_logo_corto.webp');
if (!$logo) { fwrite(STDERR, "No encontré assets/img/ifk_logo_corto.webp\n"); exit(1); }
$lw = (int)round($w * 0.12);
$lh = (int)round(imagesy($logo) * $lw / imagesx($logo));

$marca = imagecreatetruecolor($lw, $lh);
imagealphablending($marca, false);
imagesavealpha($marca, true);
imagefill($marca, 0, 0, imagecolorallocatealpha($marca, 0, 0, 0, 127));
imagecopyresampled($marca, $logo, 0, 0, 0, 0, $lw, $lh, imagesx($logo), imagesy($logo));

/* 4. Dónde va. El margen es proporcional, para que se vea igual en toda foto. */
$m = (int)round($w * 0.025);
[$x, $y] = match ($esquina) {
    'sup-izq' => [$m, $m],
    'inf-der' => [$w - $lw - $m, $h - $lh - $m],
    'inf-izq' => [$m, $h - $lh - $m],
    default   => [$w - $lw - $m, $m],          // sup-der
};

/* 5. Estampar. El logotipo del sitio es blanco: sobre una foto oscura se lee
      solo, pero sobre un diagrama de fondo blanco se vuelve un manchón gris
      —eso es lo que se veía "borroso"—. Así que miramos qué hay debajo y,
      si la zona es clara, el logo se estampa en el azul de la marca. */
imagealphablending($foto, true);

$claridad = zona_claridad($foto, $x, $y, $lw, $lh);

if ($claridad > 150) {
    /* Fondo claro: logo azul marino y un halo blanco que lo despega de las
       líneas del dibujo. */
    $halo = logo_silueta($marca, 255, 255, 255);
    foreach ([[0, 2], [0, -2], [2, 0], [-2, 0], [2, 2], [-2, 2], [2, -2], [-2, -2]] as [$dx, $dy]) {
        imagecopymerge_alpha($foto, $halo, $x + $dx, $y + $dy, 55);
    }
    imagecopymerge_alpha($foto, logo_silueta($marca, 10, 37, 64), $x, $y, 100);
} else {
    /* Fondo oscuro o medio: logo blanco sobre una sombra blanda. */
    $fuerza = (int)round(14 + 26 * max(0, min(1, ($claridad - 90) / 150)));
    $sombra = logo_silueta($marca, 0, 0, 0);
    foreach ([[1, 2], [-1, 2], [0, 3], [2, 1], [-2, 1], [0, 1], [1, 1], [-1, 1]] as [$dx, $dy]) {
        imagecopymerge_alpha($foto, $sombra, $x + $dx, $y + $dy, $fuerza);
    }
    imagecopymerge_alpha($foto, $marca, $x, $y, 90);
}

/* 6. Guardar. */
$ok = str_ends_with(strtolower($salida), '.png')
    ? imagepng($foto, $salida, 8)
    : imagejpeg($foto, $salida, 82);

if (!$ok) { fwrite(STDERR, "No pude escribir $salida\n"); exit(1); }
printf("%s  ·  %dx%d  ·  %d KB\n", $salida, $w, $h, (int)round(filesize($salida) / 1024));

/** Brillo medio (0-255) del trozo de foto donde va a caer el logotipo. */
function zona_claridad($foto, int $x, int $y, int $w, int $h): float {
    $suma = 0; $n = 0;
    $x1 = min(imagesx($foto) - 1, $x + $w);
    $y1 = min(imagesy($foto) - 1, $y + $h);
    for ($i = max(0, $x); $i < $x1; $i += 3) {
        for ($j = max(0, $y); $j < $y1; $j += 3) {
            $c = imagecolorat($foto, $i, $j);
            $suma += 0.2126 * (($c >> 16) & 0xFF) + 0.7152 * (($c >> 8) & 0xFF) + 0.0722 * ($c & 0xFF);
            $n++;
        }
    }
    return $n ? $suma / $n : 128;
}

/**
 * La silueta del logotipo en un color plano, conservando su transparencia.
 * Sirve para dos cosas: teñirlo de azul cuando el fondo es claro, y —puesta
 * varias veces, desplazada y con poca opacidad— hacerle una sombra o un halo
 * blando, que es lo que lo despega del fondo sin dibujarle una caja encima.
 */
function logo_silueta($logo, int $r, int $v, int $a) {
    $w = imagesx($logo); $h = imagesy($logo);
    $s = imagecreatetruecolor($w, $h);
    imagealphablending($s, false);
    imagesavealpha($s, true);
    $rgb = ($r << 16) | ($v << 8) | $a;
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            $t = (imagecolorat($logo, $x, $y) >> 24) & 0x7F;
            imagesetpixel($s, $x, $y, ($t << 24) | $rgb);
        }
    }
    return $s;
}

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
