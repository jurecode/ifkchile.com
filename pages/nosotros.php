<?php
$META = [
    'title' => 'Nosotros — IFK · Inversiones Friomak SpA',
    'desc'  => 'Diez años ejecutando proyectos de refrigeración, climatización y ventilación en el sur de Chile, con personal técnico especializado y marcas líderes.',
];
$NAV_SOLIDA = true;
require __DIR__ . '/../includes/header.php';
?>

<section class="phero">
  <div class="wrap phero__in">
    <nav class="migas" aria-label="Ruta"><a href="<?= url('home') ?>">Inicio</a> <span>/</span> Nosotros</nav>
    <h1>Especialistas en frío y clima, desde Puerto Montt al sur de Chile</h1>
    <p class="phero__lead">
      <?= e($SITE['razon_social']) ?> es una empresa de servicios técnicos con <?= (int)$SITE['anios'] ?> años de trayectoria.
      Ejecutamos proyectos de refrigeración, climatización, ventilación y arriendo reefer para clientes
      particulares, empresas e industria.
    </p>
  </div>
</section>

<section class="sec">
  <div class="wrap split">
    <div class="split__media">
      <img src="<?= img_src('nosotros') ?>" alt="Equipo técnico de IFK" width="720" height="620">
    </div>
    <div class="split__txt">
      <h2>Profesionalismo y experiencia técnica</h2>
      <p>Nacimos para resolver un problema concreto: mantener la temperatura bajo control en operaciones donde una falla cuesta producción. Hoy trabajamos con salmoneras, clínicas, hoteles, restaurantes, supermercados, constructoras, instituciones públicas y hogares.</p>
      <p>Contamos con personal especializado, certificaciones técnicas y respaldo de fabricantes líderes. Cada trabajo se entrega con garantía por fabricante o por contrato, y con la posibilidad de continuar bajo un programa de mantención preventiva.</p>
      <div class="stats stats--claro">
        <?php foreach ($RESPALDO as $r): ?>
          <div class="stat"><strong><?= e($r['valor']) ?></strong><span><?= e($r['label']) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="sec sec--alt">
  <div class="wrap">
    <div class="sec__head"><h2>Lo que sostiene cada proyecto</h2></div>
    <div class="valores valores--claro">
      <?php foreach ($VALORES as $v): ?>
        <div class="valor"><h3><?= e($v['t']) ?></h3><p><?= e($v['d']) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec" id="proyectos">
  <div class="wrap">
    <div class="sec__head"><h2>Proyectos realizados</h2></div>
    <div class="proys">
      <?php foreach ($PROYECTOS as $p): ?>
        <article class="proy">
          <div class="proy__img"><img src="<?= img_src($p['slot']) ?>" alt="<?= e($p['titulo']) ?>" loading="lazy" width="600" height="420"></div>
          <div class="proy__body">
            <span class="tag"><?= e($p['area']) ?></span>
            <h3><?= e($p['titulo']) ?></h3>
            <p><?= e($p['detalle']) ?></p>
            <p class="proy__pie"><?= e($p['cliente']) ?> · <?= e($p['lugar']) ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec sec--alt" id="marcas">
  <div class="wrap">
    <div class="sec__head"><h2>Fabricantes con los que trabajamos</h2></div>
    <div class="marcas">
      <?php foreach ($MARCAS as $cat => $lista): ?>
        <div class="marcas__g"><h3><?= e($cat) ?></h3><ul><?php foreach ($lista as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec sec--dark">
  <div class="wrap cobertura">
    <div>
      <h2>Dónde trabajamos</h2>
      <p><?= e($SITE['cobertura']) ?>. Atendemos proyectos fuera de la ciudad y coordinamos faenas en terreno según la envergadura del trabajo.</p>
    </div>
    <ul class="cobertura__list">
      <li><strong>Bodega</strong><?= e($SITE['direccion']) ?></li>
      <li><strong>Horario</strong><?= e($SITE['horario']) ?></li>
      <li><strong>Emergencias</strong><?= e($SITE['emergencia']) ?></li>
      <li><strong>Contacto</strong><?= e($SITE['telefono']) ?> · <?= e($SITE['email']) ?></li>
    </ul>
  </div>
</section>

<?php require __DIR__ . '/../includes/cta.php'; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
