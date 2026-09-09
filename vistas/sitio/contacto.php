<?php
/**
 * Contacto y solicitud de cotización.
 * El enrutador define $envio (resultado de cotizacion_recibir) cuando llega un POST.
 */
$titulo = 'Contacto — ' . $SITE['nombre_largo'];
$desc   = 'Solicita una cotización de refrigeración, climatización, ventilación o arriendo reefer. '
        . 'WhatsApp ' . $SITE['telefono'] . ' · ' . $SITE['email'];
$aqui   = '/contacto';
$og_foto = 'img/ventilacion.jpg';

$envio   = $envio ?? ['ok' => null, 'aviso' => '', 'errores' => [], 'datos' => []];
$errores = $envio['errores'];
$viejo   = $envio['datos'];
$v = fn(string $c): string => e((string)($viejo[$c] ?? ''));

require __DIR__ . '/cabeza.php';
?>

<section class="hero hero--corto">
  <img class="hero__foto" src="<?= asset('img/ventilacion.jpg') ?>" alt="" fetchpriority="high">
  <div class="hero__velo"></div>
  <div class="env">
    <div class="hero__caja">
      <nav class="miga" aria-label="Dónde estás"><a href="/">Inicio</a><span>/</span>Contacto</nav>
      <h1>Hablemos de tu proyecto</h1>
      <p>Cuéntanos qué necesitas resolver. Respondemos dentro del horario de atención
         y atendemos emergencias.</p>
    </div>
  </div>
</section>

<section class="seccion">
  <div class="env contacto">

    <div class="revelar">
      <p class="eti">Contacto directo</p>
      <h2 class="tit">Escríbenos</h2>
      <p class="sub">Si prefieres, escríbenos por WhatsApp o al correo y seguimos por ahí.</p>

      <ul class="datos" style="margin-top:24px">
        <li><i>WhatsApp</i><a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><?= e($SITE['telefono']) ?></a></li>
        <li><i>Correo</i><a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a></li>
        <li><i>Dirección</i><?= e($SITE['direccion']) ?></li>
        <li><i>Horario</i><?= e($SITE['horario']) ?> · <?= e($SITE['emergencia']) ?></li>
        <li><i>Cobertura</i><?= e($SITE['cobertura']) ?> · proyectos a nivel nacional</li>
      </ul>

      <a class="btn btn--azul" style="margin-top:24px" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M21 11.6a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.4A8.4 8.4 0 1 1 21 11.6Z"/>
        </svg>
        Abrir WhatsApp
      </a>
    </div>

    <div class="formulario revelar">
      <?php if ($envio['aviso'] !== ''): ?>
        <p class="recado <?= $envio['ok'] ? '' : 'recado--mal' ?>"><?= e($envio['aviso']) ?></p>
      <?php endif; ?>

      <h2 class="tit" style="font-size:1.35rem;margin-bottom:6px">Solicitar cotización</h2>
      <p class="sub" style="font-size:.92rem;margin-bottom:22px">
        Los campos con <span style="color:var(--azul-hondo)">*</span> son los necesarios para responderte.
      </p>

      <form method="post" action="/contacto" enctype="multipart/form-data" novalidate>
        <div class="campos">

          <div class="campo campo--ancho<?= isset($errores['nombre']) ? ' campo--error' : '' ?>">
            <label for="nombre">Nombre <span>*</span></label>
            <input id="nombre" name="nombre" value="<?= $v('nombre') ?>" required autocomplete="name">
            <?php if (isset($errores['nombre'])): ?><em><?= e($errores['nombre']) ?></em><?php endif; ?>
          </div>

          <div class="campo">
            <label for="empresa">Empresa</label>
            <input id="empresa" name="empresa" value="<?= $v('empresa') ?>" autocomplete="organization">
          </div>

          <div class="campo">
            <label for="rut">RUT</label>
            <input id="rut" name="rut" value="<?= $v('rut') ?>" placeholder="76.123.456-7">
          </div>

          <div class="campo<?= isset($errores['telefono']) ? ' campo--error' : '' ?>">
            <label for="telefono">Teléfono <span>*</span></label>
            <input id="telefono" name="telefono" value="<?= $v('telefono') ?>" required
                   inputmode="tel" autocomplete="tel" placeholder="+56 9 …">
            <?php if (isset($errores['telefono'])): ?><em><?= e($errores['telefono']) ?></em><?php endif; ?>
          </div>

          <div class="campo<?= isset($errores['email']) ? ' campo--error' : '' ?>">
            <label for="email">Correo <span>*</span></label>
            <input id="email" name="email" type="email" value="<?= $v('email') ?>" required autocomplete="email">
            <?php if (isset($errores['email'])): ?><em><?= e($errores['email']) ?></em><?php endif; ?>
          </div>

          <div class="campo<?= isset($errores['servicio']) ? ' campo--error' : '' ?>">
            <label for="servicio">Servicio <span>*</span></label>
            <select id="servicio" name="servicio" required>
              <option value="">Elige un área…</option>
              <?php foreach ($AREAS as $llave => $a): ?>
                <option value="<?= e($llave) ?>" <?= ($viejo['servicio'] ?? '') === $llave ? 'selected' : '' ?>>
                  <?= e($a['nombre']) ?>
                </option>
              <?php endforeach; ?>
              <option value="otro" <?= ($viejo['servicio'] ?? '') === 'otro' ? 'selected' : '' ?>>Otro / no lo sé</option>
            </select>
            <?php if (isset($errores['servicio'])): ?><em><?= e($errores['servicio']) ?></em><?php endif; ?>
          </div>

          <div class="campo">
            <label for="ubicacion">Ubicación</label>
            <input id="ubicacion" name="ubicacion" value="<?= $v('ubicacion') ?>" placeholder="Ciudad o comuna">
          </div>

          <div class="campo campo--ancho<?= isset($errores['mensaje']) ? ' campo--error' : '' ?>">
            <label for="mensaje">Descripción del trabajo o de la falla <span>*</span></label>
            <textarea id="mensaje" name="mensaje" required
                      placeholder="Qué equipo o recinto es, qué está pasando y hace cuánto…"><?= $v('mensaje') ?></textarea>
            <?php if (isset($errores['mensaje'])): ?><em><?= e($errores['mensaje']) ?></em><?php endif; ?>
          </div>

          <div class="campo campo--ancho<?= isset($errores['fotos']) ? ' campo--error' : '' ?>">
            <label for="fotos">Fotografías</label>
            <input id="fotos" name="fotos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
            <small>Opcional. Hasta 3 fotos (JPG, PNG o WEBP) de 6 MB cada una. Ayudan mucho a cotizar.</small>
            <?php if (isset($errores['fotos'])): ?><em><?= e($errores['fotos']) ?></em><?php endif; ?>
          </div>

        </div>

        <!-- Campo trampa: escondido para las personas, tentador para los robots. -->
        <div class="trampa" aria-hidden="true">
          <label for="sitio_web">No llenar</label>
          <input id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off">
        </div>

        <button class="btn btn--azul" type="submit">Enviar solicitud</button>
      </form>
    </div>

  </div>
</section>

<?php require __DIR__ . '/pie.php'; ?>
