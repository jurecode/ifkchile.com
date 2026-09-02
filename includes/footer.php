</main>

<footer class="foot">
  <div class="wrap">
    <div class="foot__top">
      <div class="foot__brand">
        <a class="logo logo--foot" href="<?= url('home') ?>">
          <img src="<?= img_src('logo') ?>" alt="IFK · <?= e($SITE['razon_social']) ?>" width="393" height="179">
        </a>
        <p><?= e($SITE['claim']) ?> para industria, comercio y hogar. <?= (int)$SITE['anios'] ?> años de experiencia en el sur de Chile.</p>
        <?php if ($SITE['redes']): ?>
          <div class="foot__redes">
            <?php foreach ($SITE['redes'] as $red => $link): ?>
              <a href="<?= e($link) ?>" target="_blank" rel="noopener"><?= e(ucfirst($red)) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="foot__col">
        <h4>Áreas</h4>
        <?php foreach ($AREAS as $slug => $a): ?>
          <a href="<?= url('area', ['a' => $slug]) ?>"><?= e($a['nombre']) ?></a>
        <?php endforeach; ?>
      </div>

      <div class="foot__col">
        <h4>Empresa</h4>
        <a href="<?= url('nosotros') ?>">Nosotros</a>
        <a href="<?= url('nosotros') ?>#proyectos">Proyectos</a>
        <a href="<?= url('nosotros') ?>#marcas">Marcas</a>
        <a href="<?= url('contacto') ?>">Contacto</a>
      </div>

      <div class="foot__col">
        <h4>Contacto</h4>
        <a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><?= e($SITE['telefono']) ?> · WhatsApp</a>
        <a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a>
        <span><?= e($SITE['direccion']) ?></span>
        <span><?= e($SITE['horario']) ?></span>
        <span class="foot__tag"><?= e($SITE['emergencia']) ?></span>
      </div>
    </div>

    <div class="foot__bar">
      <span>© <?= date('Y') ?> <?= e($SITE['razon_social']) ?>. Todos los derechos reservados.</span>
      <span><?= e($SITE['cobertura']) ?></span>
    </div>
  </div>
</footer>

<a class="wa" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener" aria-label="Escribir por WhatsApp">
  <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12.04 2c-5.5 0-9.96 4.46-9.96 9.96 0 1.76.46 3.45 1.34 4.95L2 22l5.2-1.36a9.9 9.9 0 0 0 4.84 1.25h.01c5.5 0 9.96-4.46 9.96-9.96A9.9 9.9 0 0 0 19.1 4.9 9.9 9.9 0 0 0 12.04 2m0 1.83c2.17 0 4.21.85 5.75 2.38a8.1 8.1 0 0 1 2.38 5.76c0 4.49-3.65 8.13-8.14 8.13a8.1 8.1 0 0 1-4.14-1.13l-.3-.18-3.08.81.82-3.01-.19-.31a8.06 8.06 0 0 1-1.24-4.32c0-4.49 3.65-8.13 8.14-8.13m-3.7 4.2c-.18 0-.46.06-.7.32-.24.26-.92.9-.92 2.2s.94 2.55 1.07 2.73c.13.17 1.84 2.94 4.53 4 .63.28 1.13.44 1.51.56.64.2 1.22.17 1.68.11.51-.08 1.58-.65 1.8-1.27.22-.62.22-1.16.16-1.27-.07-.11-.24-.17-.5-.3-.26-.13-1.58-.78-1.82-.87-.24-.09-.42-.13-.6.13-.17.26-.68.87-.83 1.05-.15.17-.31.2-.57.07-.26-.13-1.12-.41-2.13-1.32-.79-.7-1.32-1.57-1.47-1.83-.16-.26-.02-.4.11-.53.12-.12.26-.31.39-.46.13-.15.17-.26.26-.44.09-.17.04-.33-.02-.46-.07-.13-.58-1.45-.81-1.98-.21-.51-.43-.44-.59-.45z"/></svg>
</a>

<?php if (COMING_SOON && ($PREVIEW ?? false)): ?>
  <div class="preview-bar">
    Modo previsualización — el sitio público muestra <strong>Próximamente</strong>.
    <a href="/index.php?salir=1">Salir</a>
  </div>
<?php endif; ?>

<script src="<?= asset('js/main.js') ?>" defer></script>
</body>
</html>
