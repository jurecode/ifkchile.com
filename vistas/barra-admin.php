<?php
/**
 * Barra flotante que sólo ve el administrador con sesión abierta.
 * Le dice qué está mirando y le deja cambiar entre la fachada y el sitio real.
 */
if (!admin_dentro()) return;

$viendo_sitio = modo_vista() === 'sitio';
$estado_txt   = estados_posibles()[estado_sitio()];
?>
<div class="barra">
  <span>Administrador · <strong><?= e($viendo_sitio ? 'viendo el sitio real' : 'viendo la fachada') ?></strong> · <?= e($estado_txt) ?></span>
  <?php if ($viendo_sitio): ?>
    <a href="/?ver=fachada">Ver la fachada</a>
  <?php else: ?>
    <a href="/?ver=sitio">Previsualizar sitio</a>
  <?php endif; ?>
  <a href="/admin">Panel</a>
</div>
