<?php
/**
 * Las fotografías del sitio, cambiables desde el panel.
 *
 * Cada foto del sitio tiene una "ranura": un nombre fijo (portada,
 * nosotros-metodo, area-refrigeracion-foto-1…) con su descripción, dónde
 * aparece y qué conviene que muestre. El catálogo de ranuras está más abajo
 * y es lo que el panel dibuja en pestañas.
 *
 * Mientras nadie suba nada, cada ranura muestra la foto original del sitio.
 * Cuando el cliente sube una, el archivo nuevo queda en
 * assets/img/subidas/ y la ranura se anota en contenido/imagenes.json.
 * La original no se toca nunca: por eso siempre se puede volver a ella con
 * un botón, sin tener que ir a buscar el archivo a ninguna parte.
 *
 * Las dos cosas —el archivo subido y el json— viajan a GitHub con "subir",
 * igual que los logotipos de las marcas.
 */
declare(strict_types=1);

define('ARCHIVO_IMAGENES', RAIZ_SITIO . '/contenido/imagenes.json');
define('CARPETA_SUBIDAS',  RAIZ_SITIO . '/assets/img/subidas');

const IMAGEN_PESO_MAX = 14 * 1024 * 1024;
const IMAGEN_TIPOS    = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'jpg'];

/* ------------------------------------------------------------------ */
/* Qué foto se muestra                                                 */
/* ------------------------------------------------------------------ */

/** Lo que hay subido: ranura => ['archivo'=>…, 'subido'=>…, 'sello'=>bool]. */
function imagenes_cambios(bool $recargar = false): array {
    static $cache = null;
    if ($cache !== null && !$recargar) return $cache;

    $d = is_file(ARCHIVO_IMAGENES)
       ? json_decode((string)@file_get_contents(ARCHIVO_IMAGENES), true)
       : null;
    $lista = is_array($d['imagenes'] ?? null) ? $d['imagenes'] : [];

    /* Si el archivo subido ya no está, la ranura vuelve sola a su original:
       vale más una foto vieja que un hueco. */
    foreach ($lista as $id => $c) {
        if (!is_array($c) || !is_file(CARPETA_SUBIDAS . '/' . ($c['archivo'] ?? ''))) {
            unset($lista[$id]);
        }
    }
    return $cache = $lista;
}

/**
 * La ruta de la foto de una ranura, relativa a assets/.
 * Si nadie subió nada, devuelve la original del sitio.
 */
function imagen(string $id, string $por_defecto = ''): string {
    $c = imagenes_cambios()[$id] ?? null;
    if ($c) return 'img/subidas/' . $c['archivo'];
    if ($por_defecto !== '') return $por_defecto;
    return imagenes_ranura($id)['defecto'] ?? '';
}

/** ¿Esta ranura tiene una foto subida por el cliente? */
function imagen_cambiada(string $id): bool {
    return isset(imagenes_cambios()[$id]);
}

/**
 * Cambia las fotos de las áreas por las que se hayan subido.
 * Se llama una sola vez, desde marca.php, con $AREAS recién armado: así las
 * vistas de área no tienen que saber nada de todo esto.
 */
function imagenes_aplicar(array &$AREAS): void {
    imagenes_catalogo($AREAS);                 // el catálogo, con las rutas originales

    /* Sin referencias a propósito: escribir por índice es más largo pero no
       se presta a sorpresas con los arreglos de adentro. */
    foreach ($AREAS as $clave => $A) {
        $AREAS[$clave]['foto'] = imagen("area-$clave-hero", $A['foto']);

        if (isset($A['esquema']['archivo'])) {
            $AREAS[$clave]['esquema']['archivo'] =
                imagen("area-$clave-esquema", $A['esquema']['archivo']);
        }
        foreach (($A['galeria']['fotos'] ?? []) as $i => $f) {
            $AREAS[$clave]['galeria']['fotos'][$i]['archivo'] =
                imagen("area-$clave-foto-" . ($i + 1), $f['archivo']);
        }
    }
}

/* ------------------------------------------------------------------ */
/* El catálogo de ranuras                                              */
/* ------------------------------------------------------------------ */

/**
 * Todas las ranuras, agrupadas en las pestañas del panel.
 *
 * Se arma una sola vez, antes de cambiar nada, para que "defecto" siga
 * siendo siempre la foto original del sitio aunque haya otra subida encima.
 *
 * @param array|null $AREAS sólo la primera vez, desde imagenes_aplicar()
 */
function imagenes_catalogo(?array $AREAS = null): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    if ($AREAS === null) return [];            // todavía no se ha cargado marca.php

    $g = [
        'portada' => [
            'titulo'  => 'Portada',
            'pagina'  => '/',
            'ranuras' => [
                [
                    'id'      => 'portada',
                    'titulo'  => 'Foto grande de la portada',
                    'donde'   => 'Lo primero que se ve al entrar a ifkchile.com, detrás del título.',
                    'que'     => 'Una instalación real de IFK, de lejos, que se entienda aunque esté '
                               . 'oscurecida: encima van el título y los botones. Evitar fotos con '
                               . 'mucho detalle al centro o con texto.',
                    'forma'   => 'Horizontal y bien ancha (16:9). Se recorta para llenar la pantalla.',
                    'ancho'   => 1800,
                    'esquina' => 'sup-der',
                    'defecto' => 'img/portada.jpg',
                ],
                [
                    'id'      => 'inicio-terreno',
                    'titulo'  => 'Foto de la franja oscura',
                    'donde'   => 'Sección "Diez años resolviendo frío y clima en el sur", más abajo en la portada.',
                    'que'     => 'Gente de IFK trabajando en terreno. Es la foto que le pone cara a la empresa.',
                    'forma'   => 'Horizontal (4:3). Se recorta un poco por los lados.',
                    'ancho'   => 1400,
                    'esquina' => 'sup-der',
                    'defecto' => 'img/nosotros-terreno.jpg',
                ],
            ],
        ],
        'nosotros' => [
            'titulo'  => 'Nosotros',
            'pagina'  => '/nosotros',
            'ranuras' => [
                [
                    'id'      => 'nosotros-hero',
                    'titulo'  => 'Foto de arriba de Nosotros',
                    'donde'   => 'Encabezado de la página Nosotros, detrás del título.',
                    'que'     => 'Equipo o faena de IFK. Igual que la portada: va oscurecida y con texto encima.',
                    'forma'   => 'Horizontal y ancha (16:9).',
                    'ancho'   => 1800,
                    'esquina' => 'sup-der',
                    'defecto' => 'img/nosotros.jpg',
                ],
                [
                    'id'      => 'nosotros-metodo',
                    'titulo'  => 'Foto de "Técnicos, no intermediarios"',
                    'donde'   => 'Mitad de la página Nosotros, al lado del texto.',
                    'que'     => 'Un técnico de IFK con las manos en un equipo. Hoy hay una foto de banco '
                               . '(una mano sobre un actuador) que conviene reemplazar por una propia.',
                    'forma'   => 'Horizontal (4:3). Se recorta por los lados.',
                    'ancho'   => 1400,
                    'esquina' => 'sup-der',
                    'defecto' => 'img/metodo.jpg',
                ],
            ],
        ],
        'contacto' => [
            'titulo'  => 'Contacto y otras páginas',
            'pagina'  => '/contacto',
            'ranuras' => [
                [
                    'id'      => 'contacto-hero',
                    'titulo'  => 'Foto de arriba de Contacto',
                    'donde'   => 'Encabezado de la página Contacto, sobre el formulario.',
                    'que'     => 'Algo neutro y reconocible de IFK. Hoy hay una foto de banco de ductos.',
                    'forma'   => 'Horizontal y ancha (16:9).',
                    'ancho'   => 1800,
                    'esquina' => 'sup-der',
                    'defecto' => 'img/ventilacion.jpg',
                ],
                [
                    'id'      => 'servicios-hero',
                    'titulo'  => 'Foto de arriba de Servicios',
                    'donde'   => 'Encabezado de /servicios, la página que lista las cuatro áreas.',
                    'que'     => 'Una faena que represente a las cuatro áreas juntas.',
                    'forma'   => 'Horizontal y ancha (16:9).',
                    'ancho'   => 1800,
                    'esquina' => 'sup-der',
                    'defecto' => 'img/areas/contenedores-1.jpg',
                ],
                [
                    'id'      => 'marcas-hero',
                    'titulo'  => 'Foto de arriba de Marcas',
                    'donde'   => 'Encabezado de /marcas, donde salen todos los logotipos.',
                    'que'     => 'Equipos de marca a la vista, o un técnico instalando.',
                    'forma'   => 'Horizontal y ancha (16:9).',
                    'ancho'   => 1800,
                    'esquina' => 'sup-der',
                    'defecto' => 'img/climatizacion.jpg',
                ],
                [
                    'id'      => 'compartir',
                    'titulo'  => 'Foto para compartir el enlace',
                    'donde'   => 'La imagen que aparece cuando se pega el enlace del sitio en WhatsApp, '
                               . 'Facebook o LinkedIn. También se usa en la página de "no encontrada".',
                    'que'     => 'La foto más representativa de IFK. Se ve chica: que se entienda de un vistazo.',
                    'forma'   => 'Horizontal (16:9), mínimo 1200 px de ancho.',
                    'ancho'   => 1600,
                    'esquina' => 'sup-der',
                    'defecto' => 'img/hero.jpg',
                ],
            ],
        ],
    ];

    /* Las cuatro áreas: su foto de encabezado, su esquema y su galería. */
    foreach ($AREAS as $clave => $A) {
        $ranuras = [[
            'id'      => "area-$clave-hero",
            'titulo'  => 'Foto de arriba de ' . $A['nombre'],
            'donde'   => 'Encabezado de /servicios/' . $clave . ', y la tarjeta de esta área en la portada.',
            'que'     => 'Una faena de ' . mb_strtolower($A['nombre']) . '. Va oscurecida y con el título encima.',
            'forma'   => 'Horizontal y ancha (16:9).',
            'ancho'   => 1800,
            'esquina' => 'sup-der',
            'defecto' => $A['foto'],
        ]];

        if (isset($A['esquema']['archivo'])) {
            $ranuras[] = [
                'id'      => "area-$clave-esquema",
                'titulo'  => 'Esquema: ' . $A['esquema']['titulo'],
                'donde'   => 'Recuadro blanco con el dibujo explicativo, dentro de /servicios/' . $clave . '.',
                'que'     => 'Un dibujo o plano, no una fotografía. Se muestra completo y sobre fondo blanco.',
                'forma'   => 'Horizontal. Se muestra entera, no se recorta.',
                'ancho'   => 1400,
                'esquina' => 'inf-izq',
                'defecto' => $A['esquema']['archivo'],
            ];
        }

        $tipo = $A['galeria']['tipo'] ?? 'fotos';
        foreach (($A['galeria']['fotos'] ?? []) as $i => $f) {
            $n = $i + 1;
            $ranuras[] = [
                'id'      => "area-$clave-foto-$n",
                'titulo'  => 'Galería ' . $n . ': ' . rtrim($f['pie'], '.'),
                'donde'   => '"' . $A['galeria']['titulo'] . '", en /servicios/' . $clave
                           . '. Es la ' . $n . '.ª tarjeta de la fila.',
                'que'     => $tipo === 'equipos'
                           ? 'Foto del equipo sobre fondo blanco, como en un catálogo.'
                           : ($tipo === 'esquemas'
                             ? 'Un esquema o dibujo técnico, no una fotografía.'
                             : 'Un trabajo terminado de IFK. El pie que la acompaña dice: «'
                               . $f['pie'] . '» — si cambias la foto, avísanos para cambiar el pie.'),
                'forma'   => 'Se muestra completa, con su propia forma: no hace falta recortarla.',
                'ancho'   => 1200,
                'esquina' => 'inf-der',
                'defecto' => $f['archivo'],
            ];
        }

        $g['area-' . $clave] = [
            'titulo'  => $A['menu'],
            'pagina'  => '/servicios/' . $clave,
            'ranuras' => $ranuras,
        ];
    }

    return $cache = $g;
}

/** Una ranura suelta, por su nombre. */
function imagenes_ranura(string $id): ?array {
    foreach (imagenes_catalogo() as $grupo) {
        foreach ($grupo['ranuras'] as $r) {
            if ($r['id'] === $id) return $r;
        }
    }
    return null;
}

/* ------------------------------------------------------------------ */
/* Subir y volver atrás                                                */
/* ------------------------------------------------------------------ */

function imagenes_guardar(array $lista): bool {
    if (!is_dir(dirname(ARCHIVO_IMAGENES)) && !@mkdir(dirname(ARCHIVO_IMAGENES), 0775, true)) return false;
    $json = json_encode(['imagenes' => $lista, 'actualizado' => date('c')],
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (@file_put_contents(ARCHIVO_IMAGENES, $json, LOCK_EX) === false) return false;
    imagenes_cambios(true);
    return true;
}

/**
 * Recibe la foto que subió el cliente y la deja lista para el sitio:
 * la endereza, la achica al ancho que corresponda y —si lo pidió— le estampa
 * el sello de IFK.
 *
 * @return array{0:bool,1:string} [salió bien, aviso]
 */
function imagen_subir(array $subido, string $id, bool $sello, string $esquina): array {
    $ranura = imagenes_ranura($id);
    if (!$ranura) return [false, 'No conozco esa foto del sitio.'];

    $error = (int)($subido['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return [false, 'Elige primero un archivo de imagen.'];
    }
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        return [false, 'La foto pesa más de lo que acepta este servidor. Mándala más liviana.'];
    }
    if ($error !== UPLOAD_ERR_OK) {
        return [false, 'No se pudo recibir el archivo. Inténtalo otra vez.'];
    }
    if ((int)($subido['size'] ?? 0) > IMAGEN_PESO_MAX) {
        return [false, 'La foto pesa más de 14 MB. Mándala más liviana.'];
    }

    $tmp = (string)($subido['tmp_name'] ?? '');
    if (!is_uploaded_file($tmp)) return [false, 'No se pudo leer el archivo.'];

    $info = @getimagesize($tmp);
    $mime = is_array($info) ? (string)($info['mime'] ?? '') : '';
    if (!isset(IMAGEN_TIPOS[$mime])) {
        return [false, 'La foto tiene que ser JPG, PNG o WEBP. Lo que sale de un celular sirve.'];
    }

    $im = @imagecreatefromstring((string)@file_get_contents($tmp));
    if (!$im) return [false, 'No pude abrir esa imagen.'];

    $im = imagen_enderezar($im, $tmp, $mime);

    /* Achicar, nunca agrandar: estirar una foto chica sólo la vuelve borrosa. */
    $w = imagesx($im); $h = imagesy($im);
    $tope = (int)$ranura['ancho'];
    if ($w > $tope) {
        $nh = max(1, (int)round($h * $tope / $w));
        $chica = imagecreatetruecolor($tope, $nh);
        imagealphablending($chica, false);
        imagesavealpha($chica, true);
        imagecopyresampled($chica, $im, 0, 0, 0, 0, $tope, $nh, $w, $h);
        $im = $chica; $w = $tope; $h = $nh;
    }

    if ($sello) {
        require_once __DIR__ . '/sello.php';
        $esquina = isset(sello_esquinas()[$esquina]) ? $esquina : 'sup-der';
        if (!sello_poner($im, $esquina)) {
            return [false, 'No encontré el archivo del logotipo para estampar. Sube la foto sin marca de agua.'];
        }
    }

    if (!is_dir(CARPETA_SUBIDAS) && !@mkdir(CARPETA_SUBIDAS, 0775, true) && !is_dir(CARPETA_SUBIDAS)) {
        return [false, 'No pude crear assets/img/subidas/. Dale permiso de escritura a la carpeta.'];
    }

    $ext     = IMAGEN_TIPOS[$mime];
    $archivo = $id . '-' . date('Ymd-His') . '.' . $ext;
    $destino = CARPETA_SUBIDAS . '/' . $archivo;

    $guardado = $ext === 'png' ? imagepng($im, $destino, 8) : imagejpeg($im, $destino, $sello ? 82 : 86);
    if (!$guardado) return [false, 'No pude guardar la foto en assets/img/subidas/.'];
    @chmod($destino, 0644);

    /* La anterior de esta misma ranura ya no le sirve a nadie. */
    $lista   = imagenes_cambios();
    $anterior = $lista[$id]['archivo'] ?? '';
    $lista[$id] = ['archivo' => $archivo, 'subido' => date('c'), 'sello' => $sello];

    if (!imagenes_guardar($lista)) {
        @unlink($destino);
        return [false, 'No pude guardar contenido/imagenes.json.'];
    }
    if ($anterior !== '' && $anterior !== $archivo) @unlink(CARPETA_SUBIDAS . '/' . $anterior);

    $peso = (int)round(filesize($destino) / 1024);
    return [true, '«' . $ranura['titulo'] . '» cambiada. Quedó en ' . $w . '×' . $h . ' px y ' . $peso . ' KB'
                . ($sello ? ', con el sello de IFK.' : ', sin marca de agua.')
                . ' Escribe "subir" para que quede guardada en GitHub.'];
}

/** Vuelve a la foto original del sitio y borra la que estaba subida. */
function imagen_restaurar(string $id): array {
    $ranura = imagenes_ranura($id);
    if (!$ranura) return [false, 'No conozco esa foto del sitio.'];

    $lista = imagenes_cambios();
    if (!isset($lista[$id])) return [true, 'Esa foto ya era la original.'];

    $archivo = $lista[$id]['archivo'];
    unset($lista[$id]);
    if (!imagenes_guardar($lista)) return [false, 'No pude guardar contenido/imagenes.json.'];
    @unlink(CARPETA_SUBIDAS . '/' . $archivo);

    return [true, '«' . $ranura['titulo'] . '» volvió a la foto original.'];
}

/**
 * Endereza la foto si viene de un celular.
 *
 * Las cámaras no giran la imagen: la guardan acostada y anotan aparte cómo
 * había que darla vuelta. Si no se hace esto, una foto vertical aparece
 * tumbada en el sitio.
 */
function imagen_enderezar($im, string $archivo, string $mime) {
    if ($mime !== 'image/jpeg' || !function_exists('exif_read_data')) return $im;

    $exif = @exif_read_data($archivo);
    $giro = (int)($exif['Orientation'] ?? 1);

    $girada = match ($giro) {
        3, 4 => imagerotate($im, 180, 0),
        5, 6 => imagerotate($im, -90, 0),
        7, 8 => imagerotate($im, 90, 0),
        default => $im,
    };
    if (!$girada) return $im;

    /* 2, 4, 5 y 7 vienen además reflejadas como en un espejo. */
    if (in_array($giro, [2, 4, 5, 7], true)) @imageflip($girada, IMG_FLIP_HORIZONTAL);

    return $girada;
}
