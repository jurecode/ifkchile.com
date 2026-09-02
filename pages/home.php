<?php
$META = [
    'title' => 'IFK · Inversiones Friomak — Refrigeración, climatización, ventilación y arriendo reefer',
    'desc'  => 'Especialistas en refrigeración industrial, climatización, ventilación y arriendo de contenedores reefer. Cobertura en las regiones X, XI, XII y XIV. Cotiza por WhatsApp.',
];
require __DIR__ . '/../includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="hero__bg" style="background-image:url('<?= img_src('hero') ?>')" role="img" aria-label="Instalación industrial de refrigeración y climatización"></div>
  <div class="hero__veil"></div>

  <div class="wrap hero__in">
    <h1 class="hero__title">Frío y clima<br>bajo control.</h1>
    <p class="hero__lead">
      IFK ejecuta proyectos de refrigeración, climatización, ventilación y arriendo reefer
      para industria, comercio y hogar. Servicio técnico propio, marcas líderes y respuesta
      de emergencia en las regiones X, XI, XII y XIV.
    </p>

    <form class="hero__search" method="get" action="/index.php">
      <input type="hidden" name="p" value="contacto">
      <label class="sr" for="q">¿Qué necesitas resolver?</label>
      <input id="q" name="mensaje" type="text" placeholder="¿Qué necesitas resolver? Ej: mantención de cámara de frío" autocomplete="off">
      <button type="submit" aria-label="Buscar">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h15m0 0-5.5-5.5M19 12l-5.5 5.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
    </form>

    <ul class="hero__chips">
      <li><?= e($SITE['emergencia']) ?></li>
      <li>Garantía por fabricante o contrato</li>
      <li>Personal técnico especializado</li>
    </ul>
  </div>

  <!-- Barra flotante de cotización rápida -->
  <form class="filtro" method="get" action="/index.php">
    <input type="hidden" name="p" value="contacto">
    <div class="filtro__f">
      <label for="f-area">Área</label>
      <select id="f-area" name="servicio">
        <?php foreach ($AREAS as $a): ?>
          <option value="<?= e($a['nombre']) ?>"><?= e($a['nombre']) ?></option>
        <?php endforeach; ?>
        <option value="Otro requerimiento">Otro requerimiento</option>
      </select>
    </div>
    <div class="filtro__f">
      <label for="f-tipo">Tipo de trabajo</label>
      <select id="f-tipo" name="tipo">
        <option>Instalación / proyecto</option>
        <option>Mantención preventiva</option>
        <option>Reparación / emergencia</option>
        <option>Venta de equipos o repuestos</option>
        <option>Arriendo reefer</option>
      </select>
    </div>
    <div class="filtro__f">
      <label for="f-ciudad">Ubicación</label>
      <input id="f-ciudad" name="ubicacion" type="text" placeholder="Puerto Montt" autocomplete="off">
    </div>
    <button class="filtro__btn" type="submit">
      <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.2" fill="none" stroke="currentColor" stroke-width="2"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      Cotizar
    </button>
  </form>
</section>

<!-- ============ ÁREAS ============ -->
<section class="sec" id="areas">
  <div class="wrap">
    <div class="sec__head">
      <h2>Cuatro áreas, un solo responsable</h2>
      <p class="sec__lead">
        Resolvemos el ciclo completo — proyecto, instalación, puesta en marcha y mantención —
        con un equipo técnico que conoce la operación de cada industria.
      </p>
    </div>

    <div class="cards">
      <?php foreach ($AREAS as $slug => $a): ?>
        <article class="card">
          <a class="card__img" href="<?= url('area', ['a' => $slug]) ?>">
            <img src="<?= img_src($a['slot']) ?>" alt="<?= e($a['nombre']) ?>" loading="lazy" width="640" height="420">
          </a>
          <div class="card__body">
            <div class="card__meta">
              <span><?= e($a['meta'][0]) ?></span>
              <span><?= e($a['meta'][1]) ?></span>
            </div>
            <h3><a href="<?= url('area', ['a' => $slug]) ?>"><?= e($a['nombre']) ?></a></h3>
            <p><?= e($a['bajada']) ?></p>
            <a class="card__link" href="<?= url('area', ['a' => $slug]) ?>">
              Ver el área
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m0 0-5-5m5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ MÉTODO ============ -->
<section class="sec sec--alt">
  <div class="wrap split">
    <div class="split__media">
      <img src="<?= img_src('metodo') ?>" alt="Técnicos de IFK en faena" loading="lazy" width="720" height="640">
      <div class="split__badge">
        <strong><?= (int)$SITE['anios'] ?></strong>
        <span>años resolviendo<br>proyectos de frío y clima</span>
      </div>
    </div>
    <div class="split__txt">
      <h2>La forma más simple de resolver tu proyecto</h2>
      <p class="sec__lead">Un proceso claro, sin intermediarios y con respaldo técnico desde la primera visita.</p>
      <ol class="pasos">
        <li><span>01</span><div><h3>Contacto y diagnóstico</h3><p>Nos escribes por WhatsApp o correo y evaluamos tu requerimiento con un especialista del área.</p></div></li>
        <li><span>02</span><div><h3>Visita y propuesta</h3><p>Levantamos las condiciones en terreno y entregamos una cotización con equipos, plazos y alcance.</p></div></li>
        <li><span>03</span><div><h3>Ejecución y puesta en marcha</h3><p>Instalamos con personal propio, certificamos el funcionamiento y entregamos la operación andando.</p></div></li>
        <li><span>04</span><div><h3>Mantención y soporte</h3><p>Programas preventivos, repuestos originales y servicio de emergencia cuando la operación no puede parar.</p></div></li>
      </ol>
      <a class="btn btn--primary" href="<?= url('contacto') ?>">Solicitar una cotización</a>
    </div>
  </div>
</section>

<!-- ============ RESPALDO ============ -->
<section class="sec sec--dark">
  <div class="wrap">
    <div class="sec__head sec__head--light">
      <h2>Experiencia técnica que se nota en terreno</h2>
    </div>
    <div class="stats">
      <?php foreach ($RESPALDO as $r): ?>
        <div class="stat">
          <strong><?= e($r['valor']) ?></strong>
          <span><?= e($r['label']) ?></span>
          <small><?= e($r['detalle']) ?></small>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="valores">
      <?php foreach ($VALORES as $v): ?>
        <div class="valor"><h3><?= e($v['t']) ?></h3><p><?= e($v['d']) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ PROYECTOS ============ -->
<section class="sec" id="proyectos">
  <div class="wrap">
    <div class="sec__head">
      <h2>Trabajos realizados</h2>
      <p class="sec__lead">Una muestra de proyectos ejecutados en la Región de Los Lagos.</p>
    </div>
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

<!-- ============ MARCAS ============ -->
<section class="sec sec--alt" id="marcas">
  <div class="wrap">
    <div class="sec__head">
      <h2>Trabajamos con fabricantes líderes</h2>
    </div>
    <div class="marcas">
      <?php foreach ($MARCAS as $cat => $lista): ?>
        <div class="marcas__g">
          <h3><?= e($cat) ?></h3>
          <ul><?php foreach ($lista as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ CTA ============ -->
<?php require __DIR__ . '/../includes/cta.php'; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
