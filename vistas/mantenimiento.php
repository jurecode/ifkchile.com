<?php
/** Pantalla de mantenimiento. Se sirve con 503 para que los buscadores esperen. */
$titulo = $SITE['nombre_largo'] . ' — Estamos mejorando';
$desc   = 'Estamos realizando mejoras en el sitio. Volvemos pronto.';
require __DIR__ . '/cabecera.php';
?>

<div class="marco">

  <header class="cima sube" style="--d:.05s">
    <span class="logo">
      <img src="<?= asset('img/ifk_logo.webp') ?>" width="393" height="179" alt="<?= e($SITE['marca']) ?>">
    </span>
    <a class="btn btn--vidrio" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a>
  </header>

  <main class="centro">
    <p class="eti sube" style="--d:.15s">Mantenimiento</p>
    <h1 class="sube" style="--d:.22s">
      Estamos realizando mejoras
      <em>Volvemos pronto.</em>
    </h1>
    <p class="bajada sube" style="--d:.3s">
      El sitio estará disponible en unos momentos. Si necesitas atención ahora,
      escríbenos por WhatsApp o al correo y te respondemos igual.
    </p>
    <div class="acciones sube" style="--d:.38s">
      <a class="btn btn--claro" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">Contáctanos</a>
      <a class="btn btn--vidrio" href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a>
    </div>
  </main>

  <footer class="pie sube" style="--d:.5s">
    <ul class="datos">
      <li><i>Teléfono</i><a href="tel:<?= e(str_replace(' ', '', $SITE['telefono'])) ?>"><?= e($SITE['telefono']) ?></a></li>
      <li><i>Horario</i><?= e($SITE['horario']) ?></li>
    </ul>
    <p class="firma">© <?= date('Y') ?> <?= e($SITE['razon_social']) ?></p>
  </footer>

</div>

<?php require __DIR__ . '/pie-html.php'; ?>
