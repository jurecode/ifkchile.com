<?php
/** Portada pública mientras el sitio está en construcción. */
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
<meta name="theme-color" content="#061a2e">
<meta property="og:title" content="IFK · Inversiones Friomak">
<meta property="og:description" content="Refrigeración, climatización, ventilación y arriendo reefer. Muy pronto, nuevo sitio.">
<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/soon.css') ?>">
</head>
<body>
<div class="soon" style="--portada:url('<?= img_src('hero') ?>')">
  <div class="soon__bg" aria-hidden="true"><span></span><span></span><span></span></div>

  <header class="soon__top">
    <div class="soon__logo">
      <img src="<?= img_src('logo') ?>" alt="IFK · <?= e($SITE['razon_social']) ?>" width="393" height="179">
    </div>
    <a class="soon__wa" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12.04 2C6.54 2 2.08 6.46 2.08 11.96c0 1.76.46 3.45 1.34 4.95L2 22l5.2-1.36a9.9 9.9 0 0 0 4.84 1.25c5.5 0 9.96-4.46 9.96-9.96S17.54 2 12.04 2m0 1.83a8.13 8.13 0 0 1 8.13 8.13c0 4.49-3.64 8.13-8.13 8.13a8.1 8.1 0 0 1-4.14-1.13l-.3-.18-3.08.81.82-3.01-.19-.31a8.06 8.06 0 0 1-1.24-4.32c0-4.49 3.65-8.13 8.13-8.13"/></svg>
      WhatsApp
    </a>
  </header>

  <main class="soon__main">
    <p class="soon__kicker"><span></span> Sitio en construcción</p>
    <h1>Estamos preparando<br>nuestro nuevo sitio.</h1>
    <p class="soon__lead">
      <strong>IFK · <?= e($SITE['razon_social']) ?></strong> sigue operando con normalidad.
      <?= (int)$SITE['anios'] ?> años de experiencia en refrigeración, climatización, ventilación
      y arriendo reefer en las regiones X, XI, XII y XIV.
    </p>

    <ul class="soon__areas">
      <?php foreach ($AREAS as $a): ?>
        <li><?= e($a['nombre']) ?></li>
      <?php endforeach; ?>
    </ul>

    <div class="soon__count" id="cuenta" data-fecha="<?= e($SITE['lanzamiento']) ?>" aria-label="Cuenta regresiva para el lanzamiento">
      <div><strong data-u="d">--</strong><span>días</span></div>
      <div><strong data-u="h">--</strong><span>horas</span></div>
      <div><strong data-u="m">--</strong><span>min</span></div>
      <div><strong data-u="s">--</strong><span>seg</span></div>
    </div>

    <div class="soon__btns">
      <a class="b b--wa" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">Cotizar por WhatsApp</a>
      <a class="b b--ghost" href="mailto:<?= e($SITE['email']) ?>">Escribir un correo</a>
    </div>
  </main>

  <footer class="soon__foot">
    <div>
      <strong>Contacto</strong>
      <a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><?= e($SITE['telefono']) ?></a>
      <a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a>
    </div>
    <div>
      <strong>Bodega</strong>
      <span><?= e($SITE['direccion']) ?></span>
      <span><?= e($SITE['horario']) ?></span>
    </div>
    <div>
      <strong>Cobertura</strong>
      <span><?= e($SITE['cobertura']) ?></span>
      <span><?= e($SITE['emergencia']) ?></span>
    </div>
  </footer>
</div>

<script>
(function () {
  var box = document.getElementById('cuenta');
  if (!box) return;
  var fin = new Date(box.dataset.fecha.replace(' ', 'T')).getTime();
  if (isNaN(fin)) { box.style.display = 'none'; return; }
  var u = {};
  box.querySelectorAll('[data-u]').forEach(function (el) { u[el.dataset.u] = el; });
  function dos(n) { return String(n).padStart(2, '0'); }
  function tick() {
    var t = fin - Date.now();
    if (t <= 0) { box.classList.add('is-fin'); t = 0; }
    var s = Math.floor(t / 1000);
    u.d.textContent = dos(Math.floor(s / 86400));
    u.h.textContent = dos(Math.floor(s / 3600) % 24);
    u.m.textContent = dos(Math.floor(s / 60) % 60);
    u.s.textContent = dos(s % 60);
  }
  tick();
  setInterval(tick, 1000);
})();
</script>
</body>
</html>
