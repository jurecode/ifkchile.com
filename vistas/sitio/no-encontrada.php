<?php
/** Página que no existe. */
$titulo = 'Página no encontrada — ' . $SITE['nombre_largo'];
$desc   = 'La dirección que buscas no existe en el sitio de IFK.';
$aqui   = '/404';
require __DIR__ . '/cabeza.php';
?>

<section class="hero hero--corto">
  <img class="hero__foto" src="<?= asset('img/hero.jpg') ?>" alt="">
  <div class="hero__velo"></div>
  <div class="env">
    <div class="hero__caja">
      <p class="eti">Error 404</p>
      <h1>Esta página no existe</h1>
      <p>Puede que la dirección haya cambiado. Desde el inicio llegas a todo,
         o escríbenos y te ayudamos.</p>
      <div class="hero__botones">
        <a class="btn btn--claro" href="/">Ir al inicio</a>
        <a class="btn btn--vidrio" href="/contacto">Contacto</a>
      </div>
    </div>
  </div>
</section>

<section class="seccion">
  <div class="env">
    <div class="cabeza-seccion">
      <p class="eti">Servicios</p>
      <h2 class="tit">Nuestras cuatro áreas</h2>
    </div>
    <ul class="hero__areas" style="margin:0">
      <?php foreach ($AREAS as $llave => $a): ?>
        <li style="list-style:none">
          <a href="/servicios/<?= e($llave) ?>" style="display:block;background:var(--nieve);border-color:var(--linea-clara);color:var(--tinta)">
            <b><?= e($a['nombre']) ?></b>
            <span style="color:var(--tinta-suave)"><?= e($a['nota']) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php require __DIR__ . '/pie.php'; ?>
