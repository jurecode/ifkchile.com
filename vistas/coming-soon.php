<?php
/**
 * La fachada: lo único que ve el público mientras el sitio está en Coming Soon.
 * El texto y los datos salen de app/marca.php.
 */
$titulo = $SITE['nombre_largo'] . ' — Próximamente';
$desc   = 'Nuevo sitio en preparación. ' . $SITE['claim'] . ' en el sur de Chile. Escríbenos por WhatsApp o correo.';
require __DIR__ . '/cabecera.php';
?>

<div class="marco">

  <header class="cima sube" style="--d:.05s">
    <a class="logo" href="/" aria-label="<?= e($SITE['nombre_largo']) ?>">
      <img src="<?= asset('img/ifk_logo.webp') ?>" width="393" height="179"
           alt="<?= e($SITE['marca']) ?>">
    </a>
    <a class="btn btn--vidrio" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
           stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M21 11.6a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.4A8.4 8.4 0 1 1 21 11.6Z"/>
      </svg>
      WhatsApp
    </a>
  </header>

  <main class="centro">

    <p class="eti sube" style="--d:.15s">
      <?= e($SITE['razon_social']) ?> · <?= (int)$SITE['anios'] ?> años en el sur de Chile
    </p>

    <h1 class="sube" style="--d:.22s">
      Próximamente
      <em>Estamos preparando una nueva experiencia <?= e($SITE['marca']) ?>.</em>
    </h1>

    <p class="bajada sube" style="--d:.3s">
      <?= e($SITE['claim']) ?>. Mientras terminamos el nuevo sitio seguimos
      atendiendo como siempre: escríbenos y te respondemos.
    </p>

    <div class="acciones sube" style="--d:.38s">
      <a class="btn btn--claro" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M21 11.6a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.4A8.4 8.4 0 1 1 21 11.6Z"/>
        </svg>
        Contáctanos
      </a>
      <a class="btn btn--vidrio" href="mailto:<?= e($SITE['email']) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="2.5" y="4.5" width="19" height="15" rx="2.5"/><path d="m3.2 6.2 8.8 6.4 8.8-6.4"/>
        </svg>
        <?= e($SITE['email']) ?>
      </a>
    </div>

    <ul class="areas sube" style="--d:.46s">
      <?php foreach ($AREAS as $a): ?>
        <li>
          <b><?= e($a['nombre']) ?></b>
          <span><?= e($a['nota']) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>

  </main>

  <footer class="pie sube" style="--d:.56s">
    <ul class="datos">
      <li><i>Teléfono</i><a href="tel:<?= e(str_replace(' ', '', $SITE['telefono'])) ?>"><?= e($SITE['telefono']) ?></a></li>
      <li><i>Dirección</i><?= e($SITE['direccion']) ?></li>
      <li><i>Horario</i><?= e($SITE['horario']) ?></li>
    </ul>

    <div class="redes">
      <?php foreach ($SITE['redes'] as $red => $url): ?>
        <a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($red)) ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
               stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <?php if ($red === 'instagram'): ?>
              <rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/>
              <circle cx="17.2" cy="6.8" r="1.05" fill="currentColor" stroke="none"/>
            <?php elseif ($red === 'linkedin'): ?>
              <rect x="3" y="3" width="18" height="18" rx="4"/>
              <path d="M7.6 10.6V17M7.6 7.6v.1M11.2 17v-3.5a2.3 2.3 0 0 1 4.6 0V17"/>
            <?php else: ?>
              <rect x="3" y="3" width="18" height="18" rx="5"/>
              <path d="M14.7 8.6h-1.3c-.9 0-1.5.6-1.5 1.5V12h2.6l-.4 2.4h-2.2V20"/>
            <?php endif; ?>
          </svg>
        </a>
      <?php endforeach; ?>
    </div>

    <p class="firma">© <?= date('Y') ?> <?= e($SITE['razon_social']) ?> · <?= e($SITE['emergencia']) ?> · <?= e($SITE['cobertura']) ?></p>
  </footer>

</div>

<?php require __DIR__ . '/pie-html.php'; ?>
