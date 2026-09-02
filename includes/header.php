<?php
/** Cabecera común. Las páginas definen $META y $NAV_SOLIDA antes de incluir. */
$META  = $META ?? [];
$title = $META['title'] ?? 'IFK · Inversiones Friomak — Refrigeración y climatización';
$desc  = $META['desc']  ?? 'Refrigeración, climatización, ventilación y arriendo reefer para industria, comercio y hogar. 10 años de experiencia en el sur de Chile.';
$solida = !empty($NAV_SOLIDA);
?>
<!DOCTYPE html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="theme-color" content="#0a2540">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($SITE['nombre_largo']) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:locale" content="es_CL">
<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'HVACBusiness',
    'name'     => $SITE['razon_social'],
    'alternateName' => $SITE['marca'],
    'description'   => $desc,
    'url'      => $SITE['dominio'],
    'telephone'=> $SITE['telefono'],
    'email'    => $SITE['email'],
    'address'  => ['@type' => 'PostalAddress', 'streetAddress' => $SITE['direccion'], 'addressLocality' => 'Puerto Montt', 'addressCountry' => 'CL'],
    'areaServed' => ['Región de Los Lagos', 'Región de Los Ríos', 'Región de Aysén', 'Región de Magallanes'],
    'openingHours' => 'Mo-Fr 09:00-18:00',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
</head>
<body class="<?= $solida ? 'nav-solida' : '' ?>">

<a class="skip" href="#contenido">Saltar al contenido</a>

<div class="nav__fondo" aria-hidden="true"></div>

<header class="nav" id="nav">
  <div class="wrap nav__in">
    <a class="logo" href="<?= url('home') ?>" aria-label="IFK, inicio">
      <img src="<?= img_src('logo') ?>" alt="IFK · Inversiones Friomak" width="393" height="179">
    </a>

    <nav class="menu" id="menu" aria-label="Menú principal">
      <?php foreach ($MENU as $m):
          $href   = isset($m['a']) ? url('area', ['a' => $m['a']]) : url($m['p']);
          $activo = ($PAGE === $m['p']) && (!isset($m['a']) || ($_GET['a'] ?? '') === $m['a']);
      ?>
        <a href="<?= $href ?>" class="<?= $activo ? 'is-active' : '' ?>"><?= e($m['label']) ?></a>
      <?php endforeach; ?>
      <a class="menu__cta" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
    </nav>

    <a class="btn btn--ghost nav__cta" href="<?= url('contacto') ?>">Cotizar</a>

    <button class="burger" id="burger" aria-label="Abrir menú" aria-expanded="false" aria-controls="menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<main id="contenido">
