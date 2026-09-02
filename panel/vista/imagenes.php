<?php if (!function_exists('panel_sesion')) { http_response_code(403); exit; } ?>
<div class="tarjeta">
  <p class="intro">
    Reemplaza cualquier imagen del sitio. La foto se recorta y optimiza automáticamente a la medida
    indicada, y la versión anterior queda respaldada en <code>storage/respaldo-img/</code>.
    Formatos aceptados: JPG, PNG o WebP (hasta 12 MB).
  </p>
</div>

<div class="galeria">
  <?php foreach (slots_imagenes() as $clave => $slot):
      $src = img_src($clave);
      $ruta = RAIZ . '/' . img_ruta($clave);
      $peso = is_file($ruta) ? round(filesize($ruta) / 1024) . ' KB' : 'sin archivo';
  ?>
    <article class="img-card">
      <div class="img-card__vista <?= $clave === 'logo' ? 'es-logo' : '' ?>">
        <?php if ($src): ?><img src="<?= e($src) ?>" alt="<?= e($slot['titulo']) ?>" loading="lazy"><?php endif; ?>
      </div>
      <div class="img-card__body">
        <h3><?= e($slot['titulo']) ?></h3>
        <p><?= e($slot['medidas']) ?> · <?= e($peso) ?></p>
        <form method="post" action="index.php?v=imagenes" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
          <input type="hidden" name="accion" value="imagen">
          <input type="hidden" name="clave" value="<?= e($clave) ?>">
          <input type="file" name="archivo" accept="image/jpeg,image/png,image/webp" required
                 onchange="this.form.querySelector('button').disabled=false">
          <button class="btn btn--sm" type="submit" disabled>Reemplazar</button>
        </form>
      </div>
    </article>
  <?php endforeach; ?>
</div>
