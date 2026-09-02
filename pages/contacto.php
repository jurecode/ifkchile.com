<?php
$META = [
    'title' => 'Contacto y cotizaciones — IFK · Inversiones Friomak',
    'desc'  => 'Solicita una cotización de refrigeración, climatización, ventilación o arriendo reefer. WhatsApp +56 9 8546 4643 · Puerto Montt.',
];
$NAV_SOLIDA = true;

/* Prefill desde el buscador del home o desde una página de área */
$pre = [
    'servicio'  => $FORM['datos']['servicio']  ?? ($_GET['servicio']  ?? ''),
    'ubicacion' => $FORM['datos']['ubicacion'] ?? ($_GET['ubicacion'] ?? ''),
    'mensaje'   => $FORM['datos']['mensaje']   ?? ($_GET['mensaje']   ?? ''),
    'nombre'    => $FORM['datos']['nombre']    ?? '',
    'empresa'   => $FORM['datos']['empresa']   ?? '',
    'rut'       => $FORM['datos']['rut']       ?? '',
    'telefono'  => $FORM['datos']['telefono']  ?? '',
    'email'     => $FORM['datos']['email']     ?? '',
];
if (!empty($_GET['tipo']) && $pre['mensaje'] === '') {
    $pre['mensaje'] = $_GET['tipo'] . '. ';
}
$err = $FORM['errores'];
require __DIR__ . '/../includes/header.php';
?>

<section class="phero">
  <div class="wrap phero__in">
    <nav class="migas" aria-label="Ruta"><a href="<?= url('home') ?>">Inicio</a> <span>/</span> Contacto</nav>
    <h1>Cuéntanos qué necesitas</h1>
    <p class="phero__lead">Respondemos cotizaciones por WhatsApp y correo. Si es una emergencia, llámanos directamente.</p>
  </div>
</section>

<section class="sec">
  <div class="wrap contacto">
    <div class="contacto__form">
      <?php if ($FORM['ok']): ?>
        <div class="aviso aviso--ok">
          <h2>Recibimos tu solicitud</h2>
          <p>Gracias por escribirnos. Un especialista del área revisará tu requerimiento y te contactará dentro del horario de atención.</p>
          <p>Si necesitas una respuesta inmediata, escríbenos por <a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>.</p>
        </div>
      <?php else: ?>
        <h2>Solicitud de cotización</h2>
        <p class="contacto__intro">Mientras más detalles nos dejes, más precisa será la propuesta.</p>

        <?php if ($err): ?>
          <div class="aviso aviso--error">Revisa los campos marcados para poder enviar tu solicitud.</div>
        <?php endif; ?>

        <form method="post" action="<?= url('contacto') ?>" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="form" value="cotizacion">
          <p class="hp"><label>No completar<input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>

          <div class="grid2">
            <div class="campo <?= isset($err['nombre']) ? 'has-error' : '' ?>">
              <label for="nombre">Nombre y apellido *</label>
              <input id="nombre" name="nombre" type="text" value="<?= e($pre['nombre']) ?>" required>
              <?php if (isset($err['nombre'])): ?><small><?= e($err['nombre']) ?></small><?php endif; ?>
            </div>
            <div class="campo">
              <label for="empresa">Empresa</label>
              <input id="empresa" name="empresa" type="text" value="<?= e($pre['empresa']) ?>">
            </div>
            <div class="campo">
              <label for="rut">RUT</label>
              <input id="rut" name="rut" type="text" value="<?= e($pre['rut']) ?>" placeholder="76.123.456-7">
            </div>
            <div class="campo <?= isset($err['telefono']) ? 'has-error' : '' ?>">
              <label for="telefono">Teléfono</label>
              <input id="telefono" name="telefono" type="tel" value="<?= e($pre['telefono']) ?>" placeholder="+56 9 ...">
              <?php if (isset($err['telefono'])): ?><small><?= e($err['telefono']) ?></small><?php endif; ?>
            </div>
            <div class="campo <?= isset($err['email']) ? 'has-error' : '' ?>">
              <label for="email">Correo electrónico</label>
              <input id="email" name="email" type="email" value="<?= e($pre['email']) ?>">
              <?php if (isset($err['email'])): ?><small><?= e($err['email']) ?></small><?php endif; ?>
            </div>
            <div class="campo">
              <label for="servicio">Tipo de servicio</label>
              <select id="servicio" name="servicio">
                <option value="">Selecciona un área</option>
                <?php foreach ($AREAS as $a):
                    $sel = ($pre['servicio'] === $a['nombre']) ? 'selected' : ''; ?>
                  <option value="<?= e($a['nombre']) ?>" <?= $sel ?>><?= e($a['nombre']) ?></option>
                <?php endforeach; ?>
                <option value="Otro requerimiento" <?= $pre['servicio'] === 'Otro requerimiento' ? 'selected' : '' ?>>Otro requerimiento</option>
              </select>
            </div>
            <div class="campo campo--full">
              <label for="ubicacion">Ubicación del servicio</label>
              <input id="ubicacion" name="ubicacion" type="text" value="<?= e($pre['ubicacion']) ?>" placeholder="Ciudad, comuna o dirección">
            </div>
            <div class="campo campo--full <?= isset($err['mensaje']) ? 'has-error' : '' ?>">
              <label for="mensaje">Descripción del requerimiento *</label>
              <textarea id="mensaje" name="mensaje" rows="5" required placeholder="Ej: necesitamos mantención preventiva para dos cámaras de frío y revisión de un equipo split."><?= e($pre['mensaje']) ?></textarea>
              <?php if (isset($err['mensaje'])): ?><small><?= e($err['mensaje']) ?></small><?php endif; ?>
            </div>
            <div class="campo campo--full">
              <label for="fotos">Fotografías (opcional)</label>
              <input id="fotos" name="fotos[]" type="file" accept="image/*" multiple>
              <small class="hint">Hasta 3 imágenes de 4 MB. Ayudan mucho a cotizar sin visita previa.</small>
            </div>
          </div>

          <button class="btn btn--primary btn--wide" type="submit">Enviar solicitud</button>
          <p class="legal">Al enviar aceptas que te contactemos para responder tu requerimiento.</p>
        </form>
      <?php endif; ?>
    </div>

    <aside class="contacto__datos">
      <div class="dato">
        <h3>WhatsApp</h3>
        <a class="dato__link" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><?= e($SITE['telefono']) ?></a>
        <p>La vía más rápida para consultas y emergencias.</p>
      </div>
      <div class="dato">
        <h3>Correo</h3>
        <a class="dato__link" href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a>
        <p>Cotizaciones, órdenes de compra y documentación técnica.</p>
      </div>
      <div class="dato">
        <h3>Bodega</h3>
        <p class="dato__txt"><?= e($SITE['direccion']) ?></p>
      </div>
      <div class="dato">
        <h3>Horario</h3>
        <p class="dato__txt"><?= e($SITE['horario']) ?></p>
        <p class="dato__tag"><?= e($SITE['emergencia']) ?></p>
      </div>
      <div class="dato">
        <h3>Cobertura</h3>
        <p class="dato__txt"><?= e($SITE['cobertura']) ?></p>
      </div>
    </aside>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
