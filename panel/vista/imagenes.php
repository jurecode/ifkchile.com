<?php if (!function_exists('panel_sesion')) { http_response_code(403); exit; } ?>
<div class="tarjeta tarjeta--intro">
  <div>
    <h2>Fotografías del sitio</h2>
    <p class="intro">
      Arrastra una imagen sobre la que quieras cambiar, o haz clic para elegirla desde tu computador.
      Se recorta y optimiza sola a la medida indicada, y la versión anterior queda respaldada.
    </p>
  </div>
  <p class="formatos">JPG · PNG · WebP · hasta 12 MB</p>
</div>

<div class="galeria">
  <?php foreach (slots_imagenes() as $clave => $slot):
      $src  = img_src($clave);
      $ruta = RAIZ . '/' . img_ruta($clave);
      $peso = is_file($ruta) ? round(filesize($ruta) / 1024) . ' KB' : 'sin archivo';
  ?>
    <article class="img-card" data-clave="<?= e($clave) ?>">
      <form class="img-card__form" method="post" action="index.php?v=imagenes" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <input type="hidden" name="accion" value="imagen">
        <input type="hidden" name="clave" value="<?= e($clave) ?>">

        <div class="zona <?= $clave === 'logo' ? 'es-logo' : '' ?>" tabindex="0" role="button"
             aria-label="Reemplazar imagen: <?= e($slot['titulo']) ?>">
          <?php if ($src): ?><img class="zona__img" src="<?= e($src) ?>" alt="<?= e($slot['titulo']) ?>" loading="lazy"><?php endif; ?>
          <div class="zona__capa">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <span>Arrastra una imagen<br>o haz clic para elegir</span>
          </div>
          <div class="zona__barra"><i></i></div>
        </div>

        <div class="img-card__body">
          <h3><?= e($slot['titulo']) ?></h3>
          <p class="img-card__meta"><span class="medidas"><?= e($slot['medidas']) ?></span> · <span class="peso"><?= e($peso) ?></span></p>
          <p class="img-card__estado" role="status"></p>
        </div>

        <div class="img-card__manual">
          <input type="file" name="archivo" accept="image/jpeg,image/png,image/webp" required>
          <button class="btn btn--sm" type="submit">Reemplazar</button>
        </div>
      </form>
    </article>
  <?php endforeach; ?>
</div>
