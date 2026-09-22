<?php
/**
 * Las marcas con las que trabaja IFK y sus logotipos.
 *
 * La lista vive en contenido/marcas.json y los archivos en assets/img/marcas/.
 * Las dos cosas viajan a GitHub, así que lo que se sube desde el panel queda
 * versionado y llega a cualquier otra instalación con "traer".
 *
 * Si todavía no hay logotipo para una marca, el sitio muestra su nombre en
 * una pastilla: la sección nunca se ve rota a medio llenar.
 */
declare(strict_types=1);

define('ARCHIVO_MARCAS', RAIZ_SITIO . '/contenido/marcas.json');
define('CARPETA_LOGOS',  RAIZ_SITIO . '/assets/img/marcas');

const LOGO_ANCHO_MAX = 420;          // píxeles
const LOGO_ALTO_MAX  = 170;
const LOGO_PESO_MAX  = 3 * 1024 * 1024;
const LOGO_TIPOS     = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];

/** Un nombre convertido en algo que sirva de archivo: "Soler & Palau" → "soler-palau". */
function marca_id(string $nombre): string {
    $s = mb_strtolower(trim($nombre), 'UTF-8');
    $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u','&'=>'y']);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
    return trim($s, '-') ?: 'marca';
}

/**
 * Todas las marcas, en orden.
 * La primera vez arma la lista con los nombres que ya estaban en el sitio.
 * @return array<int,array{id:string,nombre:string,rubro:string,archivo:string}>
 */
function marcas_listar(bool $recargar = false): array {
    static $cache = null;
    if ($cache !== null && !$recargar) return $cache;

    if (is_file(ARCHIVO_MARCAS)) {
        $d = json_decode((string)@file_get_contents(ARCHIVO_MARCAS), true);
        if (isset($d['marcas']) && is_array($d['marcas'])) return $cache = $d['marcas'];
    }
    return $cache = marcas_semilla();
}

/** La lista de partida: los nombres que el sitio ya mostraba, sin logotipo. */
function marcas_semilla(): array {
    $por_rubro = [
        'Refrigeración'            => ['Hispania', 'Danfoss', 'Bitzer', 'Dorin'],
        'Climatización'            => ['Midea', 'Hisense', 'LG', 'Samsung', 'Trane'],
        'Ventilación y extracción' => ['Sodeca', 'Soler & Palau'],
        'Contenedores refrigerados'=> ['Carrier', 'Thermo King'],
    ];
    $lista = [];
    foreach ($por_rubro as $rubro => $nombres) {
        foreach ($nombres as $n) {
            $lista[] = ['id' => marca_id($n), 'nombre' => $n, 'rubro' => $rubro, 'archivo' => ''];
        }
    }
    return $lista;
}

function marcas_rubros(): array {
    return ['Refrigeración', 'Climatización', 'Ventilación y extracción', 'Contenedores refrigerados', 'General'];
}

/** Las marcas agrupadas por rubro, sólo los rubros que tengan algo. */
function marcas_por_rubro(): array {
    $out = [];
    foreach (marcas_listar() as $m) {
        $out[$m['rubro']][] = $m;
    }
    return $out;
}

/** Sólo las que ya tienen logotipo cargado. */
function marcas_con_logo(): array {
    return array_values(array_filter(marcas_listar(), fn(array $m): bool =>
        $m['archivo'] !== '' && is_file(CARPETA_LOGOS . '/' . $m['archivo'])));
}

/** Busca una marca por su nombre. Devuelve null si no está en la lista. */
function marca_por_nombre(string $nombre): ?array {
    $id = marca_id($nombre);
    foreach (marcas_listar() as $m) {
        if ($m['id'] === $id) return $m;
    }
    return null;
}

/** ¿Esta marca tiene un logotipo cargado y en su sitio? */
function marca_tiene_logo(?array $m): bool {
    return $m !== null && $m['archivo'] !== '' && is_file(CARPETA_LOGOS . '/' . $m['archivo']);
}

function marcas_guardar(array $marcas): bool {
    if (!is_dir(dirname(ARCHIVO_MARCAS)) && !@mkdir(dirname(ARCHIVO_MARCAS), 0775, true)) return false;
    $json = json_encode(['marcas' => array_values($marcas), 'actualizado' => date('c')],
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (@file_put_contents(ARCHIVO_MARCAS, $json, LOCK_EX) === false) return false;
    marcas_listar(true);
    return true;
}

/**
 * Agrega una marca o le cambia el logotipo. El archivo es opcional: una marca
 * puede existir sólo con su nombre, y recibir el logotipo más adelante.
 *
 * @param array $subido un elemento de $_FILES (puede venir vacío)
 * @return array{0:bool,1:string} [salió bien, aviso]
 */
function marcas_subir_logo(array $subido, string $nombre, string $rubro): array {
    $sin_archivo = ($subido['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE;

    /* Sólo el nombre: la marca entra a la lista y queda esperando su logotipo. */
    if ($sin_archivo) {
        $nombre = trim($nombre);
        if ($nombre === '') return [false, 'Escribe el nombre de la marca, o elige un archivo.'];
        return marcas_anotar($nombre, $rubro, null);
    }

    if (($subido['error'] ?? 1) !== UPLOAD_ERR_OK) {
        return [false, 'No se pudo recibir el archivo. Puede que pese demasiado para este servidor.'];
    }
    if ((int)($subido['size'] ?? 0) > LOGO_PESO_MAX) {
        return [false, 'El logotipo pesa más de 3 MB.'];
    }
    $tmp = (string)($subido['tmp_name'] ?? '');
    if (!is_uploaded_file($tmp)) return [false, 'No se pudo leer el archivo.'];

    $info = @getimagesize($tmp);
    $mime = is_array($info) ? (string)($info['mime'] ?? '') : '';
    if (!isset(LOGO_TIPOS[$mime])) {
        return [false, 'El logotipo tiene que ser PNG, JPG o WEBP. (El PNG con fondo transparente es el que mejor queda.)'];
    }

    $nombre = trim($nombre) !== '' ? trim($nombre)
            : ucwords(str_replace('-', ' ', pathinfo((string)$subido['name'], PATHINFO_FILENAME)));
    $rubro  = in_array($rubro, marcas_rubros(), true) ? $rubro : 'General';
    $id     = marca_id($nombre);

    if (!is_dir(CARPETA_LOGOS) && !@mkdir(CARPETA_LOGOS, 0775, true) && !is_dir(CARPETA_LOGOS)) {
        return [false, 'No pude crear assets/img/marcas/. Dale permiso de escritura a la carpeta.'];
    }

    $destino = CARPETA_LOGOS . '/' . $id . '.png';
    if (!logo_normalizar($tmp, $destino)) {
        return [false, 'No pude procesar la imagen.'];
    }

    return marcas_anotar($nombre, $rubro, $id . '.png');
}

/**
 * Deja la marca en la lista: si ya estaba, la actualiza; si no, la agrega.
 * $archivo null significa "no toques el logotipo que ya tenga".
 */
function marcas_anotar(string $nombre, string $rubro, ?string $archivo): array {
    $id     = marca_id($nombre);
    $rubro  = in_array($rubro, marcas_rubros(), true) ? $rubro : 'General';
    $marcas = marcas_listar();

    foreach ($marcas as &$m) {
        if ($m['id'] !== $id) continue;
        $m['nombre'] = $nombre;
        $m['rubro']  = $rubro;
        if ($archivo !== null) $m['archivo'] = $archivo;
        unset($m);
        return marcas_guardar($marcas)
            ? [true, ($archivo !== null ? 'Logotipo de ' : 'Marca ') . $nombre . ' guardado.']
            : [false, 'No pude guardar contenido/marcas.json.'];
    }
    unset($m);

    $marcas[] = ['id' => $id, 'nombre' => $nombre, 'rubro' => $rubro, 'archivo' => $archivo ?? ''];
    return marcas_guardar($marcas)
        ? [true, 'Marca ' . $nombre . ' agregada.']
        : [false, 'No pude guardar contenido/marcas.json.'];
}

/**
 * Guarda los cambios de nombre y rubro hechos en la tabla del panel.
 * Si cambia el nombre, cambia la llave y el archivo del logotipo se renombra
 * con ella, para que las dos cosas no se separen nunca.
 *
 * @param array<string,array{nombre?:string,rubro?:string}> $filas  llave => campos
 */
function marcas_actualizar(array $filas): array {
    $marcas  = marcas_listar();
    $ids     = array_column($marcas, 'id');
    $cambios = 0;
    $avisos  = [];

    foreach ($marcas as $i => $m) {
        $fila = $filas[$m['id']] ?? null;
        if (!is_array($fila)) continue;

        $nombre = trim((string)($fila['nombre'] ?? ''));
        $rubro  = (string)($fila['rubro'] ?? $m['rubro']);
        if ($nombre === '') { $avisos[] = 'A ' . $m['nombre'] . ' le faltó el nombre: no se cambió.'; continue; }
        if (!in_array($rubro, marcas_rubros(), true)) $rubro = $m['rubro'];

        $nuevo_id = marca_id($nombre);

        /* Dos marcas no pueden terminar con la misma llave. */
        if ($nuevo_id !== $m['id'] && in_array($nuevo_id, $ids, true)) {
            $avisos[] = 'Ya hay otra marca que se llama ' . $nombre . ': ese cambio se dejó como estaba.';
            continue;
        }

        if ($nombre === $m['nombre'] && $rubro === $m['rubro']) continue;

        /* El logotipo acompaña al nombre nuevo. */
        if ($nuevo_id !== $m['id'] && $m['archivo'] !== '') {
            $viejo = CARPETA_LOGOS . '/' . $m['archivo'];
            $nuevo = CARPETA_LOGOS . '/' . $nuevo_id . '.png';
            if (is_file($viejo) && @rename($viejo, $nuevo)) {
                $marcas[$i]['archivo'] = $nuevo_id . '.png';
            }
        }

        $ids[$i] = $nuevo_id;
        $marcas[$i]['id']     = $nuevo_id;
        $marcas[$i]['nombre'] = $nombre;
        $marcas[$i]['rubro']  = $rubro;
        $cambios++;
    }

    if ($cambios === 0 && !$avisos) return [true, 'No había nada que cambiar.'];
    if (!marcas_guardar($marcas)) return [false, 'No pude guardar contenido/marcas.json.'];

    $aviso = $cambios === 1 ? 'Se guardó 1 cambio.' : 'Se guardaron ' . $cambios . ' cambios.';
    return [empty($avisos), $aviso . ($avisos ? ' ' . implode(' ', $avisos) : '')];
}

/** Saca la marca de la lista, con su logotipo. */
function marcas_borrar(string $id): array {
    $marcas = marcas_listar();
    foreach ($marcas as $i => $m) {
        if ($m['id'] !== $id) continue;
        if ($m['archivo'] !== '' && is_file(CARPETA_LOGOS . '/' . $m['archivo'])) {
            @unlink(CARPETA_LOGOS . '/' . $m['archivo']);
        }
        unset($marcas[$i]);
        return marcas_guardar($marcas)
            ? [true, 'Marca ' . $m['nombre'] . ' borrada.']
            : [false, 'No pude guardar contenido/marcas.json.'];
    }
    return [false, 'No encontré esa marca.'];
}

/**
 * Deja el logotipo listo para el sitio: tamaño parejo, fondo transparente y
 * PNG, que es el formato que respeta la transparencia de cualquier logo.
 */
function logo_normalizar(string $origen, string $destino): bool {
    $im = @imagecreatefromstring((string)@file_get_contents($origen));
    if (!$im) return false;

    $im = logo_sin_margen($im);
    $w = imagesx($im); $h = imagesy($im);
    $escala = min(LOGO_ANCHO_MAX / $w, LOGO_ALTO_MAX / $h, 1);
    $nw = max(1, (int)round($w * $escala));
    $nh = max(1, (int)round($h * $escala));

    $salida = imagecreatetruecolor($nw, $nh);
    imagealphablending($salida, false);
    imagesavealpha($salida, true);
    imagefill($salida, 0, 0, imagecolorallocatealpha($salida, 0, 0, 0, 127));
    imagecopyresampled($salida, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);

    $ok = imagepng($salida, $destino, 8);
    if ($ok) @chmod($destino, 0644);
    return $ok;
}

/**
 * Recorta el marco vacío que rodea al logotipo.
 *
 * Los archivos llegan con márgenes muy distintos: unos pegados al borde y
 * otros con un cinturón blanco enorme. Sin esto, dos logos del mismo tamaño
 * se ven de tamaños distintos en la misma fila. Se mira el color de las
 * esquinas: lo que sea igual a ese color —o transparente— es marco y se va.
 */
function logo_sin_margen($im) {
    $w = imagesx($im); $h = imagesy($im);
    if ($w < 12 || $h < 12) return $im;

    /* Las cuatro esquinas tienen que coincidir; si no, no hay marco que sacar. */
    $esquinas = [imagecolorat($im, 0, 0), imagecolorat($im, $w - 1, 0),
                 imagecolorat($im, 0, $h - 1), imagecolorat($im, $w - 1, $h - 1)];
    $fondo = $esquinas[0];
    foreach ($esquinas as $c) {
        if (!logo_mismo_color($c, $fondo)) return $im;
    }

    $es_marco = function (int $x0, int $y0, int $x1, int $y1) use ($im, $fondo): bool {
        for ($x = $x0; $x <= $x1; $x += max(1, (int)(($x1 - $x0) / 60))) {
            for ($y = $y0; $y <= $y1; $y += max(1, (int)(($y1 - $y0) / 60))) {
                if (!logo_mismo_color(imagecolorat($im, $x, $y), $fondo)) return false;
            }
        }
        return true;
    };

    $arriba = 0;  while ($arriba < $h - 2 && $es_marco(0, $arriba, $w - 1, $arriba)) $arriba++;
    $abajo  = $h - 1; while ($abajo > $arriba + 2 && $es_marco(0, $abajo, $w - 1, $abajo)) $abajo--;
    $izq    = 0;  while ($izq < $w - 2 && $es_marco($izq, $arriba, $izq, $abajo)) $izq++;
    $der    = $w - 1; while ($der > $izq + 2 && $es_marco($der, $arriba, $der, $abajo)) $der--;

    /* Un respiro de 2 px, para no comerse el filo de una letra. */
    $izq = max(0, $izq - 2); $arriba = max(0, $arriba - 2);
    $der = min($w - 1, $der + 2); $abajo = min($h - 1, $abajo + 2);

    $nw = $der - $izq + 1; $nh = $abajo - $arriba + 1;
    if ($nw < 8 || $nh < 8 || ($nw === $w && $nh === $h)) return $im;

    $cortado = imagecrop($im, ['x' => $izq, 'y' => $arriba, 'width' => $nw, 'height' => $nh]);
    return $cortado ?: $im;
}

/** Dos colores son "el mismo" si se parecen, o si ambos son transparentes. */
function logo_mismo_color(int $a, int $b): bool {
    $aa = ($a >> 24) & 0x7F; $ab = ($b >> 24) & 0x7F;
    if ($aa > 100 && $ab > 100) return true;          // los dos transparentes
    if (abs($aa - $ab) > 40) return false;
    foreach ([16, 8, 0] as $desp) {
        if (abs((($a >> $desp) & 0xFF) - (($b >> $desp) & 0xFF)) > 14) return false;
    }
    return true;
}

/** Saca el logotipo (la marca se queda en la lista, como nombre). */
function marcas_quitar_logo(string $id): array {
    $marcas = marcas_listar();
    foreach ($marcas as &$m) {
        if ($m['id'] !== $id) continue;
        if ($m['archivo'] !== '' && is_file(CARPETA_LOGOS . '/' . $m['archivo'])) {
            @unlink(CARPETA_LOGOS . '/' . $m['archivo']);
        }
        $m['archivo'] = '';
        unset($m);
        return marcas_guardar($marcas)
            ? [true, 'Logotipo quitado. La marca sigue en la lista, como nombre.']
            : [false, 'No pude guardar el cambio.'];
    }
    return [false, 'No encontré esa marca.'];
}
