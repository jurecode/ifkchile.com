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
 * Guarda el logotipo que llega del panel.
 * @param array $subido un elemento de $_FILES
 * @return array{0:bool,1:string} [salió bien, aviso]
 */
function marcas_subir_logo(array $subido, string $nombre, string $rubro): array {
    if (($subido['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [false, 'Elige el archivo del logotipo.'];
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

    /* Si la marca ya estaba en la lista, se le pega el logotipo; si no, entra. */
    $marcas = marcas_listar();
    $encontrada = false;
    foreach ($marcas as &$m) {
        if ($m['id'] === $id) {
            $m['nombre']  = $nombre;
            $m['rubro']   = $rubro;
            $m['archivo'] = $id . '.png';
            $encontrada = true;
            break;
        }
    }
    unset($m);
    if (!$encontrada) {
        $marcas[] = ['id' => $id, 'nombre' => $nombre, 'rubro' => $rubro, 'archivo' => $id . '.png'];
    }

    if (!marcas_guardar($marcas)) return [false, 'No pude guardar contenido/marcas.json.'];
    return [true, 'Logotipo de ' . $nombre . ' guardado.'];
}

/**
 * Deja el logotipo listo para el sitio: tamaño parejo, fondo transparente y
 * PNG, que es el formato que respeta la transparencia de cualquier logo.
 */
function logo_normalizar(string $origen, string $destino): bool {
    $im = @imagecreatefromstring((string)@file_get_contents($origen));
    if (!$im) return false;

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
