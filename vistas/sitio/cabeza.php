<?php
/**
 * Cabecera de las páginas del sitio: <head>, barra de navegación y apertura.
 * Antes de incluirla se definen $titulo, $desc, $aqui (ruta actual) y, si la
 * página tiene una foto propia, $og_foto.
 */
$titulo  = $titulo ?? ($SITE['nombre_largo'] . ' — ' . $SITE['claim']);
$desc    = $desc   ?? ($SITE['claim'] . ' para industria, comercio y hogar. '
                    . $SITE['anios'] . ' años de experiencia en el sur de Chile.');
$aqui    = $aqui ?? '/';
$og_foto = $og_foto ?? 'img/hero.jpg';
$canon   = rtrim($SITE['dominio'], '/') . ($aqui === '/' ? '/' : $aqui);
?>
<!doctype html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($titulo) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<?= meta_robots() ?>
<link rel="canonical" href="<?= e($canon) ?>">

<meta name="theme-color" content="#061a2e">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($SITE['nombre_largo']) ?>">
<meta property="og:title" content="<?= e($titulo) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<meta property="og:locale" content="es_CL">
<meta property="og:image" content="<?= e(rtrim($SITE['dominio'], '/') . '/assets/' . $og_foto) ?>">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/sitio.css') ?>">

<script type="application/ld+json">
<?= json_encode([
    '@context'      => 'https://schema.org',
    '@type'         => 'HVACBusiness',
    'name'          => $SITE['razon_social'],
    'alternateName' => $SITE['marca'],
    'description'   => $desc,
    'url'           => $SITE['dominio'],
    'image'         => rtrim($SITE['dominio'], '/') . '/assets/img/hero.jpg',
    'telephone'     => $SITE['telefono'],
    'email'         => $SITE['email'],
    'address'       => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => 'Camino a Pargua Km 9, Lote 23',
        'addressLocality' => 'Puerto Montt',
        'addressRegion'   => 'Los Lagos',
        'addressCountry'  => 'CL',
    ],
    'areaServed'    => ['Región de Los Lagos', 'Región de Los Ríos',
                        'Región de Aysén', 'Región de Magallanes'],
    'openingHours'  => 'Mo-Fr 09:00-18:00',
    'knowsAbout'    => array_column($AREAS, 'nombre'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
</head>
<body>

<a class="saltar" href="#contenido">Saltar al contenido</a>

<header class="nav" id="nav" data-abierto="no">
  <div class="env nav__in">

    <a class="nav__logo" href="/" aria-label="<?= e($SITE['nombre_largo']) ?>, inicio">
      <img src="<?= asset('img/ifk_logo.webp') ?>" width="393" height="179" alt="<?= e($SITE['marca']) ?>">
    </a>

    <nav class="nav__menu" id="menu" aria-label="Menú principal">
      <a href="/"<?= $aqui === '/' ? ' class="activo"' : '' ?>>Inicio</a>

      <span class="desplegable">
        <a href="/servicios"<?= str_starts_with($aqui, '/servicios') ? ' class="activo"' : '' ?>>Servicios</a>
        <span class="desplegable__caja">
          <?php foreach ($AREAS as $llave => $a): ?>
            <a href="/servicios/<?= e($llave) ?>">
              <?= e($a['nombre']) ?>
              <small><?= e($a['nota']) ?></small>
            </a>
          <?php endforeach; ?>
        </span>
      </span>

      <a href="/proyectos"<?= $aqui === '/proyectos' ? ' class="activo"' : '' ?>>Proyectos</a>
      <a href="/marcas"<?= $aqui === '/marcas' ? ' class="activo"' : '' ?>>Marcas</a>
      <a href="/nosotros"<?= $aqui === '/nosotros' ? ' class="activo"' : '' ?>>Nosotros</a>
      <a href="/contacto"<?= $aqui === '/contacto' ? ' class="activo"' : '' ?>>Contacto</a>
    </nav>

    <a class="btn btn--claro nav__cta" href="/contacto">Cotizar</a>

    <button class="nav__burger" id="burger" type="button"
            aria-expanded="false" aria-controls="menu" aria-label="Abrir el menú">
      <span></span>
    </button>

  </div>
</header>

<main id="contenido">
