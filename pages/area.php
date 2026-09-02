<?php
$slug = $_GET['a'] ?? 'refrigeracion';
if (!isset($AREAS[$slug])) { $slug = 'refrigeracion'; }
$A = $AREAS[$slug];

$META = [
    'title' => $A['nombre'] . ' — IFK · Inversiones Friomak',
    'desc'  => $A['bajada'],
];
$NAV_SOLIDA = true;
require __DIR__ . '/../includes/header.php';
?>

<section class="phero">
  <div class="wrap phero__in">
    <nav class="migas" aria-label="Ruta"><a href="<?= url('home') ?>">Inicio</a> <span>/</span> <?= e($A['nombre']) ?></nav>
    <h1><?= e($A['nombre']) ?></h1>
    <p class="phero__lead"><?= e($A['intro']) ?></p>
    <div class="phero__btns">
      <a class="btn btn--wa" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">Consultar por WhatsApp</a>
      <a class="btn btn--ghost" href="<?= url('contacto', ['servicio' => $A['nombre']]) ?>">Cotizar <?= e(mb_strtolower($A['nombre'])) ?></a>
    </div>
  </div>
</section>

<section class="sec">
  <div class="wrap split split--rev">
    <div class="split__media">
      <img src="<?= img_src($A['slot']) ?>" alt="<?= e($A['nombre']) ?>" width="720" height="560">
    </div>
    <div class="split__txt">
      <h2>Servicios del área</h2>
      <ul class="lista">
        <?php foreach ($A['servicios'] as $s): ?>
          <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><?= e($s) ?></li>
        <?php endforeach; ?>
      </ul>
      <h3 class="split__sub">Para quién trabajamos</h3>
      <div class="pills">
        <?php foreach ($A['para'] as $p): ?><span><?= e($p) ?></span><?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="sec sec--alt">
  <div class="wrap">
    <div class="sec__head">
      <h2>También podemos ayudarte en</h2>
    </div>
    <div class="cards cards--3">
      <?php foreach ($AREAS as $s2 => $a2): if ($s2 === $slug) continue; ?>
        <article class="card">
          <a class="card__img" href="<?= url('area', ['a' => $s2]) ?>">
            <img src="<?= img_src($a2['slot']) ?>" alt="<?= e($a2['nombre']) ?>" loading="lazy" width="640" height="420">
          </a>
          <div class="card__body">
            <h3><a href="<?= url('area', ['a' => $s2]) ?>"><?= e($a2['nombre']) ?></a></h3>
            <p><?= e($a2['bajada']) ?></p>
            <a class="card__link" href="<?= url('area', ['a' => $s2]) ?>">Ver el área
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m0 0-5-5m5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/cta.php'; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
