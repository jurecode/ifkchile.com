<?php
/** Proyectos realizados. */
$titulo = 'Proyectos — ' . $SITE['nombre_largo'];
$desc   = 'Cámaras de congelado, climatización de clínicas y proyectos integrales de frío, '
        . 'clima y extracción realizados por IFK en el sur de Chile.';
$aqui   = '/proyectos';
$og_foto = 'img/proyectos/proyecto-1.jpg';
require __DIR__ . '/cabeza.php';
?>

<section class="hero hero--corto">
  <img class="hero__foto" src="<?= asset('img/proyectos/proyecto-1.jpg') ?>" alt="" fetchpriority="high">
  <div class="hero__velo"></div>
  <div class="env">
    <div class="hero__caja">
      <nav class="miga" aria-label="Dónde estás"><a href="/">Inicio</a><span>/</span>Proyectos</nav>
      <h1>Proyectos</h1>
      <p>Instalaciones y puestas en marcha en hotelería, salud y gastronomía.
         Cada trabajo se muestra con la autorización de su cliente.</p>
    </div>
  </div>
</section>

<section class="seccion">
  <div class="env">
    <ul class="proyectos">
      <?php foreach ($PROYECTOS as $p): ?>
        <li class="tarjeta revelar">
          <div class="tarjeta__foto">
            <img src="<?= asset($p['foto']) ?>" alt="<?= e($p['titulo']) ?>" loading="lazy">
            <span class="tarjeta__marca"><?= e($p['area']) ?></span>
          </div>
          <div class="tarjeta__cuerpo">
            <h2 style="font-size:1.25rem;margin:0;font-weight:800;letter-spacing:-.02em"><?= e($p['titulo']) ?></h2>
            <p><?= e($p['detalle']) ?></p>
            <div class="proyecto__meta"><b><?= e($p['cliente']) ?></b> · <?= e($p['lugar']) ?></div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="seccion seccion--nieve">
  <div class="env">
    <div class="banda revelar">
      <div>
        <h2>¿Tienes un proyecto parecido?</h2>
        <p>Cuéntanos qué necesitas resolver y te preparamos una propuesta técnica con plazos y valores.</p>
      </div>
      <div class="banda__botones">
        <a class="btn btn--claro" href="/contacto">Solicitar cotización</a>
        <a class="btn btn--vidrio" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/pie.php'; ?>
