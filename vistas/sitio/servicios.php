<?php
/** Índice de servicios: las cuatro áreas. */
$titulo = 'Servicios — ' . $SITE['nombre_largo'];
$desc   = 'Refrigeración industrial, climatización, ventilación y extracción, y arriendo de '
        . 'contenedores reefer. Proyectos, servicio técnico, repuestos y venta de equipos.';
$aqui   = '/servicios';
require __DIR__ . '/cabeza.php';
?>

<section class="hero hero--corto">
  <img class="hero__foto" src="<?= asset('img/refrigeracion.jpg') ?>" alt="" fetchpriority="high">
  <div class="hero__velo"></div>
  <div class="env">
    <div class="hero__caja">
      <nav class="miga" aria-label="Dónde estás">
        <a href="/">Inicio</a><span>/</span>Servicios
      </nav>
      <h1>Servicios</h1>
      <p>Cuatro áreas que resuelven temperatura y calidad de aire para industria,
         comercio y hogar, con un mismo equipo técnico detrás.</p>
    </div>
  </div>
</section>

<section class="seccion">
  <div class="env">
    <ul class="areas">
      <?php foreach ($AREAS as $llave => $a): ?>
        <li class="tarjeta revelar">
          <a href="/servicios/<?= e($llave) ?>">
            <div class="tarjeta__foto">
              <img src="<?= asset($a['foto']) ?>" alt="<?= e($a['nombre']) ?>" loading="lazy">
              <span class="tarjeta__marca"><?= e($a['nota']) ?></span>
            </div>
            <div class="tarjeta__cuerpo">
              <h2><?= e($a['nombre']) ?></h2>
              <p><?= e($a['intro']) ?></p>
              <ul class="tarjeta__lista">
                <?php foreach (array_slice($a['servicios'], 0, 3) as $s): ?>
                  <li><?= e($s) ?></li>
                <?php endforeach; ?>
              </ul>
              <span class="tarjeta__ver">Ver el área</span>
            </div>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="seccion seccion--nieve">
  <div class="env">
    <div class="banda revelar">
      <div>
        <h2>¿No sabes qué área necesitas?</h2>
        <p>Cuéntanos el problema —una cámara que no enfría, una oficina que no climatiza,
           una cocina sin extracción— y nosotros lo derivamos al equipo correcto.</p>
      </div>
      <div class="banda__botones">
        <a class="btn btn--claro" href="/contacto">Solicitar cotización</a>
        <a class="btn btn--vidrio" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/pie.php'; ?>
