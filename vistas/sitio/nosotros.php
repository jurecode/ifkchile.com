<?php
/** Nosotros. */
$titulo = 'Nosotros — ' . $SITE['nombre_largo'];
$desc   = $SITE['razon_social'] . ': ' . $SITE['anios'] . ' años de experiencia en refrigeración, '
        . 'climatización, ventilación y arriendo reefer en el sur de Chile.';
$aqui   = '/nosotros';
$og_foto = 'img/nosotros.jpg';
require __DIR__ . '/cabeza.php';
?>

<section class="hero hero--corto">
  <img class="hero__foto" src="<?= asset('img/nosotros.jpg') ?>" alt="" fetchpriority="high">
  <div class="hero__velo"></div>
  <div class="env">
    <div class="hero__caja">
      <nav class="miga" aria-label="Dónde estás"><a href="/">Inicio</a><span>/</span>Nosotros</nav>
      <h1>Somos IFK</h1>
      <p><?= e($SITE['razon_social']) ?>. Una década resolviendo temperatura y calidad de aire
         para la industria, el comercio y el hogar del sur de Chile.</p>
    </div>
  </div>
</section>

<section class="seccion">
  <div class="env dos">
    <div class="revelar">
      <p class="eti">La empresa</p>
      <h2 class="tit">Técnicos, no intermediarios</h2>
      <p class="sub">Trabajamos desde Puerto Montt con personal propio: proyectamos, instalamos,
         ponemos en marcha y mantenemos. Eso nos permite responder rápido cuando algo falla y
         hacernos cargo del equipo durante toda su vida útil.</p>
      <p class="sub" style="margin-top:14px">Atendemos las regiones X, XI, XII y XIV, y desarrollamos
         proyectos a nivel nacional para clientes que ya nos conocen.</p>
      <ul class="lista-check" style="margin-top:22px">
        <li>Servicio técnico correctivo y preventivo en las cuatro áreas</li>
        <li>Certificaciones técnicas vigentes y procedimientos de seguridad</li>
        <li>Garantía por fabricante o por contrato</li>
        <li>Servicio de emergencia ante fallas críticas</li>
      </ul>
    </div>
    <div class="dos__foto revelar">
      <img src="<?= asset('img/metodo.jpg') ?>" alt="Equipo técnico de IFK trabajando" loading="lazy">
    </div>
  </div>
</section>

<section class="seccion seccion--oscuro">
  <div class="env">
    <div class="cabeza-seccion revelar">
      <p class="eti">Lo que nos mueve</p>
      <h2 class="tit">Nuestros valores</h2>
    </div>
    <ul class="valores revelar">
      <?php foreach ($VALORES as $v): ?>
        <li>
          <h3><?= e($v['t']) ?></h3>
          <p><?= e($v['d']) ?></p>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="cabeza-seccion revelar" style="margin-top:clamp(40px,5vw,64px)">
      <p class="eti">Clientes</p>
      <h2 class="tit">A quiénes atendemos</h2>
    </div>
    <ul class="nube revelar">
      <?php foreach ($CLIENTES as $c): ?><li><?= e($c) ?></li><?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="seccion">
  <div class="env">
    <ul class="tira revelar">
      <?php foreach ($RESPALDO as $r): ?>
        <li><b><?= e($r['valor']) ?></b><i><?= e($r['label']) ?></i><small><?= e($r['detalle']) ?></small></li>
      <?php endforeach; ?>
    </ul>

    <div class="banda revelar" style="margin-top:clamp(34px,4vw,56px)">
      <div>
        <h2>Conversemos tu proyecto</h2>
        <p>Visitamos, medimos y proponemos. Sin compromiso.</p>
      </div>
      <div class="banda__botones">
        <a class="btn btn--claro" href="/contacto">Solicitar cotización</a>
        <a class="btn btn--vidrio" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/pie.php'; ?>
