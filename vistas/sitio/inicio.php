<?php
/** Portada del sitio. */
$titulo = $SITE['nombre_largo'] . ' — ' . $SITE['claim'] . ' en el sur de Chile';
$desc   = 'Refrigeración industrial, climatización, ventilación y arriendo de contenedores reefer. '
        . 'Proyectos, servicio técnico y repuestos en las regiones X, XI, XII y XIV.';
$aqui   = '/';
require __DIR__ . '/cabeza.php';
?>

<section class="hero">
  <img class="hero__foto" src="<?= asset('img/hero.jpg') ?>" alt="" fetchpriority="high">
  <div class="hero__velo"></div>
  <div class="env">
    <div class="hero__caja">
      <p class="eti"><?= e($SITE['razon_social']) ?> · <?= (int)$SITE['anios'] ?> años</p>
      <h1>Frío, clima y aire para la industria del sur de Chile</h1>
      <p><?= e($SITE['claim']) ?>. Proyectos, servicio técnico y repuestos con
         personal propio y respaldo de las marcas líderes del rubro.</p>
      <div class="hero__botones">
        <a class="btn btn--claro" href="/contacto">Solicitar cotización</a>
        <a class="btn btn--vidrio" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
               stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 11.6a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.4A8.4 8.4 0 1 1 21 11.6Z"/>
          </svg>
          Escríbenos por WhatsApp
        </a>
      </div>
    </div>

    <div class="hero__areas">
      <?php foreach ($AREAS as $llave => $a): ?>
        <a href="/servicios/<?= e($llave) ?>">
          <b><?= e($a['nombre']) ?></b>
          <span><?= e($a['nota']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="seccion">
  <div class="env">
    <ul class="tira revelar">
      <?php foreach ($RESPALDO as $r): ?>
        <li>
          <b><?= e($r['valor']) ?></b>
          <i><?= e($r['label']) ?></i>
          <small><?= e($r['detalle']) ?></small>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="seccion seccion--nieve">
  <div class="env">
    <div class="cabeza-seccion revelar">
      <p class="eti">Nuestros servicios</p>
      <h2 class="tit">Cuatro áreas, un solo equipo técnico</h2>
      <p class="sub">Resolvemos temperatura y calidad de aire de principio a fin: el proyecto,
         el montaje, la puesta en marcha, la mantención y los repuestos.</p>
    </div>

    <ul class="areas">
      <?php foreach ($AREAS as $llave => $a): ?>
        <li class="tarjeta revelar">
          <a href="/servicios/<?= e($llave) ?>">
            <div class="tarjeta__foto">
              <img src="<?= asset($a['foto']) ?>" alt="<?= e($a['nombre']) ?>" loading="lazy">
              <span class="tarjeta__marca"><?= e($a['nota']) ?></span>
            </div>
            <div class="tarjeta__cuerpo">
              <h3><?= e($a['nombre']) ?></h3>
              <p><?= e($a['bajada']) ?></p>
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

<section class="seccion">
  <div class="env">
    <div class="cabeza-seccion revelar">
      <p class="eti">Cómo trabajamos</p>
      <h2 class="tit">De la visita a terreno a la mantención</h2>
      <p class="sub">El mismo procedimiento en un proyecto industrial y en una casa:
         entender el problema, proponer con números claros y hacernos cargo después.</p>
    </div>
    <ol class="metodo revelar">
      <?php foreach ($METODO as $m): ?>
        <li>
          <b><?= e($m['n']) ?></b>
          <h3><?= e($m['t']) ?></h3>
          <p><?= e($m['d']) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="seccion seccion--oscuro">
  <div class="env dos">
    <div class="revelar">
      <p class="eti">Nosotros</p>
      <h2 class="tit">Diez años resolviendo frío y clima en el sur</h2>
      <p class="sub">Inversiones Friomak SpA trabaja desde Puerto Montt para la industria
         acuícola, el comercio, la salud y el hogar de las regiones X, XI, XII y XIV,
         con proyectos a nivel nacional.</p>
      <ul class="lista-check" style="margin-top:22px">
        <li>Personal técnico especializado y certificaciones vigentes</li>
        <li>Garantía por fabricante o por contrato en cada trabajo</li>
        <li>Servicio de emergencia ante fallas críticas</li>
        <li>Repuestos originales y marcas líderes del rubro</li>
      </ul>
      <div class="hero__botones">
        <a class="btn btn--claro" href="/nosotros">Conoce IFK</a>
        <a class="btn btn--vidrio" href="/proyectos">Ver proyectos</a>
      </div>
    </div>
    <div class="dos__foto revelar">
      <img src="<?= asset('img/nosotros.jpg') ?>" alt="Técnico de IFK en terreno" loading="lazy">
    </div>
  </div>
</section>

<section class="seccion">
  <div class="env">
    <div class="cabeza-seccion revelar">
      <p class="eti">Proyectos</p>
      <h2 class="tit">Trabajos realizados</h2>
      <p class="sub">Instalaciones y puestas en marcha en hotelería, salud y gastronomía del sur de Chile.</p>
    </div>
    <ul class="proyectos">
      <?php foreach ($PROYECTOS as $p): ?>
        <li class="tarjeta revelar">
          <div class="tarjeta__foto">
            <img src="<?= asset($p['foto']) ?>" alt="<?= e($p['titulo']) ?>" loading="lazy">
            <span class="tarjeta__marca"><?= e($p['area']) ?></span>
          </div>
          <div class="tarjeta__cuerpo">
            <h3><?= e($p['titulo']) ?></h3>
            <p><?= e($p['detalle']) ?></p>
            <div class="proyecto__meta">
              <b><?= e($p['cliente']) ?></b> · <?= e($p['lugar']) ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="seccion seccion--nieve">
  <div class="env">
    <div class="cabeza-seccion revelar">
      <p class="eti">Marcas</p>
      <h2 class="tit">Trabajamos con los fabricantes del rubro</h2>
      <p class="sub">Equipos, repuestos y respaldo técnico de las marcas que la industria ya conoce.</p>
    </div>
    <ul class="marcas revelar">
      <?php foreach ($MARCAS as $rubro => $lista): ?>
        <li>
          <h3><?= e($rubro) ?></h3>
          <ul>
            <?php foreach ($lista as $m): ?><li><?= e($m) ?></li><?php endforeach; ?>
          </ul>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="seccion">
  <div class="env">
    <div class="banda revelar">
      <div>
        <h2>¿Necesitas una cotización?</h2>
        <p>Cuéntanos qué equipo o recinto tienes que resolver y te respondemos con una
           propuesta técnica. También atendemos emergencias.</p>
      </div>
      <div class="banda__botones">
        <a class="btn btn--claro" href="/contacto">Solicitar cotización</a>
        <a class="btn btn--vidrio" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/pie.php'; ?>
