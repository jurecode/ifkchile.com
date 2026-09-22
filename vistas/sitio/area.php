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

/* Las secciones alternan fondo blanco y fondo nieve, sea cual sea el área. */
$n_seccion = 0;
$fondo = function () use (&$n_seccion): string { return (++$n_seccion % 2 === 0) ? ' seccion--nieve' : ''; };
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

<section class="seccion<?= $fondo() ?>">
  <div class="env dos">
    <div class="revelar">
      <h2 class="tit">Servicios del área</h2>
      <ul class="lista-check" style="margin-top:20px">
        <?php foreach ($A['servicios'] as $s): ?>
          <?php if (is_array($s)): /* un grupo: separa lo que es venta de lo que es servicio */ ?>
            <li class="grupo"><?= e($s['grupo']) ?></li>
          <?php else: ?>
            <li><?= e($s) ?></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="revelar">
      <div class="marcas" style="grid-template-columns:1fr">
        <div style="padding:26px;border-radius:var(--r);background:#fff;border:1px solid var(--linea-clara);box-shadow:var(--sombra)">
          <h3 style="margin:0 0 14px;font-size:1.05rem;font-weight:800;letter-spacing:-.02em">Marcas con las que trabajamos</h3>
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

<?php if (!empty($A['esquema'])): ?>
<section class="seccion<?= $fondo() ?>">
  <div class="env">
    <div class="cabeza-seccion revelar">
      <h2 class="tit"><?= e($A['esquema']['titulo']) ?></h2>
      <p class="sub"><?= e($A['esquema']['bajada']) ?></p>
    </div>
    <figure class="esquema revelar">
      <img src="<?= asset($A['esquema']['archivo']) ?>" alt="<?= e($A['esquema']['titulo']) ?>" loading="lazy">
    </figure>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($A['galeria'])): $g = $A['galeria']; ?>
<section class="seccion<?= $fondo() ?>">
  <div class="env">
    <div class="cabeza-seccion revelar">
      <h2 class="tit"><?= e($g['titulo']) ?></h2>
      <p class="sub"><?= e($g['bajada']) ?></p>
    </div>
    <ul class="galeria galeria--<?= e($g['tipo']) ?>">
      <?php foreach ($g['fotos'] as $f): ?>
        <li class="revelar">
          <figure>
            <img src="<?= asset($f['archivo']) ?>" alt="<?= e($f['pie']) ?>" loading="lazy">
            <figcaption><?= e($f['pie']) ?></figcaption>
          </figure>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($suyos): ?>
<section class="seccion<?= $fondo() ?>">
  <div class="env">
    <div class="cabeza-seccion revelar">
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
            <div class="proyecto__meta"><b><?= e($p['lugar']) ?></b></div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<section class="seccion<?= $fondo() ?>">
  <div class="env">
    <div class="banda revelar">
      <div>
        <h2>Cotiza <?= e(mb_strtolower($A['nombre'])) ?></h2>
        <p>Te respondemos con una propuesta técnica y, si es urgente, coordinamos visita.
           También armamos programas de mantención preventiva.</p>
      </div>
      <div class="banda__botones">
        <a class="btn btn--claro" href="/contacto">Solicitar cotización</a>
        <a class="btn btn--vidrio" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/pie.php'; ?>
