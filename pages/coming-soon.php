<?php
/** Portada pública mientras el sitio está en construcción. Versión simple. */
http_response_code(200);
header('X-Robots-Tag: noindex');
?>
<!DOCTYPE html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>IFK · Inversiones Friomak — Sitio en construcción</title>
<meta name="description" content="Refrigeración, climatización, ventilación y arriendo reefer. Nuestro nuevo sitio está en construcción. Escríbenos por WhatsApp o correo.">
<meta name="robots" content="noindex, follow">
<meta name="theme-color" content="#0a2540">
<meta property="og:title" content="IFK · Inversiones Friomak">
<meta property="og:description" content="Refrigeración, climatización, ventilación y arriendo reefer. Muy pronto, nuevo sitio.">
<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/soon.css') ?>">
</head>
<body>
<main class="soon">
  <div class="soon__caja">
    <img class="soon__logo" src="<?= img_src('logo') ?>" alt="IFK · <?= e($SITE['razon_social']) ?>" width="393" height="179">

    <p class="soon__eti">Sitio en construcción</p>
    <h1>Estamos preparando nuestro nuevo sitio.</h1>
    <p class="soon__lead">
      <?= e($SITE['razon_social']) ?> sigue operando con normalidad en refrigeración, climatización,
      ventilación y arriendo reefer. Escríbenos y te respondemos como siempre.
    </p>

    <div class="soon__btns">
      <a class="b b--wa" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">Escribir por WhatsApp</a>
      <a class="b b--ghost" href="mailto:<?= e($SITE['email']) ?>">Enviar un correo</a>
    </div>

    <ul class="soon__datos">
      <li><span>Teléfono</span><a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><?= e($SITE['telefono']) ?></a></li>
      <li><span>Correo</span><a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a></li>
      <li><span>Horario</span><?= e($SITE['horario']) ?></li>
      <li><span>Bodega</span><?= e($SITE['direccion']) ?></li>
    </ul>
  </div>

  <p class="soon__pie">© <?= date('Y') ?> <?= e($SITE['razon_social']) ?> · <?= e($SITE['cobertura']) ?></p>
</main>
</body>
</html>
