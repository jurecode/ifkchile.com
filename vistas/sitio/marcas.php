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
    <?php foreach (marcas_por_rubro() as $rubro => $lista): ?>
      <div class="revelar" style="margin-bottom:clamp(30px,4vw,52px)">
        <h2 style="margin:0 0 16px;font-size:1.15rem;font-weight:800;letter-spacing:-.02em"><?= e($rubro) ?></h2>
        <ul class="logos">
          <?php foreach ($lista as $m): ?>
            <li>
              <?php if ($m['archivo'] !== '' && is_file(CARPETA_LOGOS . '/' . $m['archivo'])): ?>
                <span class="marca">
                  <img src="<?= asset('img/marcas/' . $m['archivo']) ?>"
                       alt="<?= e($m['nombre']) ?>" loading="lazy">
                </span>
              <?php else: ?>
                <span class="marca marca--texto"><?= e($m['nombre']) ?></span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>

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
