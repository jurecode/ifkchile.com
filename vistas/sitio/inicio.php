<?php
/** Portada del sitio. */
$titulo = $SITE['nombre_largo'] . ' — ' . $SITE['claim'] . ' en el sur de Chile';
$desc   = 'Refrigeración industrial, climatización, ventilación y arriendo de contenedores refrigerados. '
        . 'Proyectos, servicio técnico y repuestos en las regiones X, XI, XII y XIV.';
$aqui   = '/';
$og_foto = 'img/portada.jpg';     // la que se ve al compartir el enlace
require __DIR__ . '/cabeza.php';
?>

<section class="hero hero--clara">
  <img class="hero__foto" src="<?= asset('img/portada.jpg') ?>" alt="" fetchpriority="high">
  <div class="hero__velo"></div>
  <div class="env">
    <div class="hero__caja">
      <h1>Frío, clima y aire para la industria del sur de Chile
        <em>Refrigeración y HVAC</em></h1>
      <p><?= e($SITE['claim']) ?>. Proyectos, servicio técnico y repuestos con
         respaldo de las marcas líderes del rubro.</p>
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

<section class="seccion seccion--cinta seccion--nieve">
  <div class="env">
    <p class="cinta-titulo revelar">Trabajamos con los fabricantes del rubro
       <a href="/marcas">Ver todas las marcas</a></p>
  <div class="desfile revelar">
    <div class="desfile__pista">
      <?php /* la fila va dos veces: así el desfile vuelve a empezar sin saltos */ ?>
      <?php for ($vuelta = 0; $vuelta < 2; $vuelta++): ?>
        <div class="desfile__grupo"<?= $vuelta ? ' aria-hidden="true"' : '' ?>>
          <?php foreach (marcas_listar() as $m): ?>
            <?php if ($m['archivo'] !== '' && is_file(CARPETA_LOGOS . '/' . $m['archivo'])): ?>
              <span class="marca">
                <img src="<?= asset('img/marcas/' . $m['archivo']) ?>"
                     alt="<?= e($m['nombre']) ?>" loading="lazy">
              </span>
            <?php else: ?>
              <span class="marca marca--texto"><?= e($m['nombre']) ?></span>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endfor; ?>
    </div>
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
                <?php foreach (array_slice(array_filter($a['servicios'], 'is_string'), 0, 3) as $s): ?>
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
        <?php if (MOSTRAR_PROYECTOS): ?>
          <a class="btn btn--vidrio" href="/proyectos">Ver proyectos</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="dos__foto revelar">
      <img src="<?= asset('img/nosotros-terreno.jpg') ?>"
           alt="Técnico de IFK trabajando en un equipo de climatización en terreno" loading="lazy">
    </div>
  </div>
</section>

<?php if (MOSTRAR_PROYECTOS): ?>
<section class="seccion">
  <div class="env">
    <div class="cabeza-seccion revelar">
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
            <div class="proyecto__meta"><b><?= e($p['lugar']) ?></b></div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<section class="seccion seccion--nieve">
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
