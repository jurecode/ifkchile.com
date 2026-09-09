<?php
/**
 * Página de un área. Antes de incluirla, el enrutador define:
 *   $llave_area  la llave del área ("refrigeracion", …)
 *   $A           sus datos, sacados de $AREAS
 */
$titulo = $A['nombre'] . ' — ' . $SITE['nombre_largo'];
$desc   = $A['intro'];
$aqui   = '/servicios/' . $llave_area;
$og_foto = $A['foto'];
require __DIR__ . '/cabeza.php';

/* Proyectos donde participó esta área. */
$suyos = array_values(array_filter($PROYECTOS, fn(array $p): bool => str_contains($p['area'], explode(' ', $A['nombre'])[0])));
?>

<section class="hero hero--corto">
  <img class="hero__foto" src="<?= asset($A['foto']) ?>" alt="" fetchpriority="high">
  <div class="hero__velo"></div>
  <div class="env">
    <div class="hero__caja">
      <nav class="miga" aria-label="Dónde estás">
        <a href="/">Inicio</a><span>/</span><a href="/servicios">Servicios</a><span>/</span><?= e($A['nombre']) ?>
      </nav>
      <h1><?= e($A['nombre']) ?></h1>
      <p><?= e($A['intro']) ?></p>
      <div class="hero__botones">
        <a class="btn btn--claro" href="/contacto">Cotizar <?= e(mb_strtolower($A['nombre'])) ?></a>
        <a class="btn btn--vidrio" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<section class="seccion">
  <div class="env dos">
    <div class="revelar">
      <p class="eti">Qué hacemos</p>
      <h2 class="tit">Servicios del área</h2>
      <ul class="lista-check" style="margin-top:20px">
        <?php foreach ($A['servicios'] as $s): ?>
          <li><?= e($s) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="revelar">
      <div class="marcas" style="grid-template-columns:1fr">
        <div style="padding:26px;border-radius:var(--r);background:#fff;border:1px solid var(--linea-clara);box-shadow:var(--sombra)">
          <h3 style="margin:0 0 14px;font-size:1.05rem;font-weight:800;letter-spacing:-.02em">Para quién trabajamos</h3>
          <ul class="tarjeta__lista">
            <?php foreach ($A['para'] as $p): ?><li><?= e($p) ?></li><?php endforeach; ?>
          </ul>

          <h3 style="margin:24px 0 14px;font-size:1.05rem;font-weight:800;letter-spacing:-.02em">Marcas con las que trabajamos</h3>
          <ul class="tarjeta__lista">
            <?php foreach ($A['marcas'] as $m): ?><li><?= e($m) ?></li><?php endforeach; ?>
          </ul>

          <p style="margin:22px 0 0;font-size:.9rem;color:var(--tinta-suave)">
            <?= e($SITE['emergencia']) ?> · <?= e($SITE['cobertura']) ?>
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($suyos): ?>
<section class="seccion seccion--nieve">
  <div class="env">
    <div class="cabeza-seccion revelar">
      <p class="eti">Proyectos</p>
      <h2 class="tit">Trabajos de esta área</h2>
    </div>
    <ul class="proyectos">
      <?php foreach ($suyos as $p): ?>
        <li class="tarjeta revelar">
          <div class="tarjeta__foto">
            <img src="<?= asset($p['foto']) ?>" alt="<?= e($p['titulo']) ?>" loading="lazy">
            <span class="tarjeta__marca"><?= e($p['area']) ?></span>
          </div>
          <div class="tarjeta__cuerpo">
            <h3><?= e($p['titulo']) ?></h3>
            <p><?= e($p['detalle']) ?></p>
            <div class="proyecto__meta"><b><?= e($p['cliente']) ?></b> · <?= e($p['lugar']) ?></div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<section class="seccion">
  <div class="env">
    <div class="banda revelar">
      <div>
        <h2>Cotiza <?= e(mb_strtolower($A['nombre'])) ?></h2>
        <p>Cuéntanos el recinto, el equipo o la falla. Te respondemos con una propuesta técnica
           y, si es urgente, coordinamos visita.</p>
      </div>
      <div class="banda__botones">
        <a class="btn btn--claro" href="/contacto">Solicitar cotización</a>
        <a class="btn btn--vidrio" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/pie.php'; ?>
