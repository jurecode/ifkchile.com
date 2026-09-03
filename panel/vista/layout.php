<?php if (!function_exists('panel_sesion')) { http_response_code(403); exit; } ?>
<?php $secciones = [
  'estado'      => ['Estado del sitio', 'Publicado o "Próximamente"'],
  'imagenes'    => ['Imágenes',         'Fotos del sitio'],
  'repositorio' => ['Repositorio',      'Conexión con GitHub'],
  'clave'       => ['Clave de acceso',  'Seguridad del panel'],
]; ?>
<!DOCTYPE html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Panel IFK · <?= e($secciones[$vista][0]) ?></title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/panel/assets/panel.css?v=<?= @filemtime(__DIR__ . '/../assets/panel.css') ?>">
</head>
<body>
<div class="app">

  <aside class="lado">
    <a class="lado__logo" href="/" target="_blank" rel="noopener">
      <img src="<?= img_src('logo') ?>" alt="IFK">
    </a>
    <nav class="lado__nav">
      <?php foreach ($secciones as $k => $s): ?>
        <a href="index.php?v=<?= $k ?>" class="<?= $vista === $k ? 'is-active' : '' ?>">
          <strong><?= e($s[0]) ?></strong><span><?= e($s[1]) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="lado__pie">
      <p class="estado-chip <?= COMING_SOON ? 'es-soon' : 'es-live' ?>">
        <?= COMING_SOON ? 'Sitio en Próximamente' : 'Sitio publicado' ?>
      </p>
      <a class="lado__ver" href="/<?= COMING_SOON ? '?preview=' . rawurlencode(PREVIEW_KEY) : '' ?>" target="_blank" rel="noopener">Ver el sitio ↗</a>
      <a class="lado__salir" href="index.php?salir=1">Cerrar sesión</a>
    </div>
  </aside>

  <main class="cont">
    <header class="cont__head">
      <div>
        <h1><?= e($secciones[$vista][0]) ?></h1>
        <p><?= e($secciones[$vista][1]) ?></p>
      </div>
    </header>

    <?php if ($aviso): ?>
      <div class="aviso aviso--<?= $aviso['tipo'] === 'ok' ? 'ok' : 'err' ?>">
        <p><?= e($aviso['texto']) ?></p>
        <?php if (!empty($aviso['consola'])): ?><pre><?= e($aviso['consola']) ?></pre><?php endif; ?>
      </div>
    <?php endif; ?>

    <?php require __DIR__ . '/' . $vista . '.php'; ?>
  </main>
</div>
<script src="/panel/assets/panel.js?v=<?= @filemtime(__DIR__ . '/../assets/panel.js') ?>" defer></script>
</body>
</html>
