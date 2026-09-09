<?php
/**
 * Cabecera de las pantallas públicas (fachada y mantenimiento).
 * Antes de incluirla se definen: $titulo, $desc y —si se quiere otra foto— $foto.
 */
$titulo = $titulo ?? ($SITE['nombre_largo'] . ' — Próximamente');
$desc   = $desc   ?? $SITE['claim'];
$foto   = $foto   ?? 'img/hero.jpg';
?>
<!doctype html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($titulo) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<?= meta_robots() ?>

<meta name="theme-color" content="#061a2e">
<meta name="color-scheme" content="dark">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($SITE['nombre_largo']) ?>">
<meta property="og:title" content="<?= e($titulo) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:locale" content="es_CL">
<meta property="og:image" content="<?= e($SITE['dominio'] . '/assets/' . $foto) ?>">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="image" href="<?= asset($foto) ?>" fetchpriority="high">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/fachada.css') ?>">
</head>
<body>

<div class="escena" aria-hidden="true">
  <img class="escena__foto" src="<?= asset($foto) ?>" alt="" fetchpriority="high" decoding="async">
  <div class="escena__velo"></div>
  <div class="escena__luz"></div>
  <div class="escena__grano"></div>
</div>
