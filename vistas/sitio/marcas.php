<?php
/** Marcas con las que trabaja IFK. */
$titulo = 'Marcas — ' . $SITE['nombre_largo'];
$desc   = 'Hispania, Danfoss, Bitzer, Dorin, Midea, Hisense, LG, Samsung, Trane, Sodeca y '
        . 'Soler & Palau: las marcas con las que IFK proyecta, instala y mantiene.';
$aqui   = '/marcas';
$og_foto = 'img/climatizacion.jpg';
require __DIR__ . '/cabeza.php';
?>

<section class="hero hero--corto">
  <img class="hero__foto" src="<?= asset('img/climatizacion.jpg') ?>" alt="" fetchpriority="high">
  <div class="hero__velo"></div>
  <div class="env">
    <div class="hero__caja">
      <nav class="miga" aria-label="Dónde estás"><a href="/">Inicio</a><span>/</span>Marcas</nav>
      <h1>Marcas</h1>
      <p>Equipos, repuestos y respaldo técnico de los fabricantes que la industria del frío
         y del clima ya conoce.</p>
    </div>
  </div>
</section>

<section class="seccion">
  <div class="env">
    <ul class="marcas">
      <?php foreach ($MARCAS as $rubro => $lista): ?>
        <li class="revelar">
          <h2 style="margin:0 0 14px;font-size:1.05rem;font-weight:800;letter-spacing:-.02em"><?= e($rubro) ?></h2>
          <ul>
            <?php foreach ($lista as $m): ?><li><?= e($m) ?></li><?php endforeach; ?>
          </ul>
        </li>
      <?php endforeach; ?>
    </ul>

    <p class="sub revelar" style="margin-top:clamp(26px,3vw,40px)">
      Vendemos e instalamos equipos nuevos y trabajamos con repuestos originales.
      Si tu equipo es de otra marca, igual lo revisamos: escríbenos y te decimos si podemos atenderlo.
    </p>
  </div>
</section>

<section class="seccion seccion--nieve">
  <div class="env">
    <div class="banda revelar">
      <div>
        <h2>¿Buscas un repuesto o un equipo?</h2>
        <p>Dinos la marca y el modelo y te confirmamos disponibilidad y valor.</p>
      </div>
      <div class="banda__botones">
        <a class="btn btn--claro" href="/contacto">Consultar</a>
        <a class="btn btn--vidrio" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/pie.php'; ?>
