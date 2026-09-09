<?php
/** Cierre de las páginas del sitio: pie, WhatsApp flotante y scripts. */
?>
</main>

<footer class="pie">
  <div class="env">
    <div class="pie__grid">

      <div>
        <span class="pie__logo">
          <img src="<?= asset('img/ifk_logo.webp') ?>" width="393" height="179" alt="<?= e($SITE['marca']) ?>">
        </span>
        <p class="pie__intro"><?= e($SITE['razon_social']) ?>. <?= e($SITE['claim']) ?>
           para industria, comercio y hogar en el sur de Chile.</p>
        <?php if ($SITE['redes']): ?>
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
        <?php endif; ?>
      </div>

      <div>
        <h3>Servicios</h3>
        <ul>
          <?php foreach ($AREAS as $llave => $a): ?>
            <li><a href="/servicios/<?= e($llave) ?>"><?= e($a['nombre']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div>
        <h3>Empresa</h3>
        <ul>
          <li><a href="/nosotros">Nosotros</a></li>
          <li><a href="/proyectos">Proyectos</a></li>
          <li><a href="/marcas">Marcas</a></li>
          <li><a href="/contacto">Contacto</a></li>
        </ul>
      </div>

      <div>
        <h3>Contacto</h3>
        <ul>
          <li><a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp <?= e($SITE['telefono']) ?></a></li>
          <li><a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a></li>
          <li><?= e($SITE['direccion']) ?></li>
          <li><?= e($SITE['horario']) ?></li>
          <li><?= e($SITE['emergencia']) ?></li>
        </ul>
      </div>

    </div>

    <div class="pie__abajo">
      <span>© <?= date('Y') ?> <?= e($SITE['razon_social']) ?>. Todos los derechos reservados.</span>
      <span><?= e($SITE['cobertura']) ?> · Proyectos a nivel nacional</span>
    </div>
  </div>
</footer>

<a class="wa-flota" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
       stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="M21 11.6a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.4A8.4 8.4 0 1 1 21 11.6Z"/>
  </svg>
  <span>WhatsApp</span>
</a>

<?php require dirname(__DIR__) . '/barra-admin.php'; ?>
<script src="<?= asset('js/sitio.js') ?>" defer></script>
</body>
</html>
