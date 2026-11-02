<?php
/**
 * El sello de IFK sobre una fotografía.
 *
 * Lo usan dos cosas distintas y por eso vive aparte:
 *   · herramientas/marcar-imagen.php, cuando preparamos fotos a mano
 *   · el panel, cuando el cliente sube una foto y pide ponerle el logo
 *
 * Dos decisiones que vale la pena recordar:
 *
 *   Se estampa la versión corta del logotipo —el copo y el IFK, sin la
 *   bajada de texto—. Esa línea fina, al tamaño de un sello, no se lee en
 *   ninguna foto: queda como una mancha y hace ver borroso todo el logo.
 *
 *   El logotipo es blanco. Sobre una foto oscura se lee solo, pero sobre un
 *   diagrama de fondo blanco desaparece. Así que antes de estampar se mira
 *   qué hay debajo: si la zona es clara, el logo va en el azul de la marca
 *   con un halo blanco; si es oscura, va blanco sobre una sombra blanda.
 */
declare(strict_types=1);

const SELLO_ARCHIVO = '/assets/img/ifk_logo_corto.webp';
const SELLO_ANCHO   = 0.12;     // del ancho de la foto
const SELLO_MARGEN  = 0.025;    // separación del borde, también proporcional
const SELLO_AZUL    = [10, 37, 64];

/** Las cuatro esquinas, con el nombre que se ve en el panel. */
function sello_esquinas(): array {
    return [
        'sup-der' => 'Arriba a la derecha',
        'sup-izq' => 'Arriba a la izquierda',
        'inf-der' => 'Abajo a la derecha',
        'inf-izq' => 'Abajo a la izquierda',
    ];
}

/**
 * Estampa el logo sobre la imagen, en la esquina pedida.
 * Devuelve false sólo si no encuentra el archivo del logotipo.
 */
function sello_poner($foto, string $esquina = 'sup-der'): bool {
    $logo = @imagecreatefromwebp(dirname(__DIR__) . SELLO_ARCHIVO);
    if (!$logo) return false;

    $w = imagesx($foto); $h = imagesy($foto);
    $lw = (int)round($w * SELLO_ANCHO);
    $lh = (int)round(imagesy($logo) * $lw / imagesx($logo));
    if ($lw < 24 || $lh < 8) return false;          // foto diminuta: no vale la pena

    $marca = imagecreatetruecolor($lw, $lh);
    imagealphablending($marca, false);
    imagesavealpha($marca, true);
    imagefill($marca, 0, 0, imagecolorallocatealpha($marca, 0, 0, 0, 127));
    imagecopyresampled($marca, $logo, 0, 0, 0, 0, $lw, $lh, imagesx($logo), imagesy($logo));

    $m = (int)round($w * SELLO_MARGEN);
    [$x, $y] = match ($esquina) {
        'sup-izq' => [$m, $m],
        'inf-der' => [$w - $lw - $m, $h - $lh - $m],
        'inf-izq' => [$m, $h - $lh - $m],
        default   => [$w - $lw - $m, $m],           // sup-der
    };

    imagealphablending($foto, true);
    $claridad = sello_claridad($foto, $x, $y, $lw, $lh);

    if ($claridad > 150) {
        /* Fondo claro: logo azul marino y un halo blanco que lo despega. */
        $halo = sello_silueta($marca, 255, 255, 255);
        foreach ([[0,2],[0,-2],[2,0],[-2,0],[2,2],[-2,2],[2,-2],[-2,-2]] as [$dx, $dy]) {
            sello_pegar($foto, $halo, $x + $dx, $y + $dy, 55);
        }
        sello_pegar($foto, sello_silueta($marca, ...SELLO_AZUL), $x, $y, 100);
    } else {
        /* Fondo oscuro o medio: logo blanco sobre una sombra blanda. */
        $fuerza = (int)round(14 + 26 * max(0, min(1, ($claridad - 90) / 150)));
        $sombra = sello_silueta($marca, 0, 0, 0);
        foreach ([[1,2],[-1,2],[0,3],[2,1],[-2,1],[0,1],[1,1],[-1,1]] as [$dx, $dy]) {
            sello_pegar($foto, $sombra, $x + $dx, $y + $dy, $fuerza);
        }
        sello_pegar($foto, $marca, $x, $y, 90);
    }
    return true;
}

/** Brillo medio (0-255) del trozo de foto donde va a caer el logotipo. */
function sello_claridad($foto, int $x, int $y, int $w, int $h): float {
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
function sello_silueta($logo, int $r, int $v, int $a) {
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
function sello_pegar($destino, $origen, int $x, int $y, int $opacidad): void {
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
