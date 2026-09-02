<?php if (!function_exists('panel_sesion')) { http_response_code(403); exit; } ?>
<div class="tarjeta">
  <form method="post" action="index.php?v=estado">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <input type="hidden" name="accion" value="estado">

    <div class="opciones">
      <label class="opcion <?= COMING_SOON ? 'is-sel' : '' ?>">
        <input type="radio" name="modo" value="soon" <?= COMING_SOON ? 'checked' : '' ?>>
        <span class="opcion__t">Próximamente</span>
        <span class="opcion__d">Las visitas ven la portada de construcción con la cuenta regresiva, el WhatsApp y el correo. El sitio completo queda oculto.</span>
      </label>
      <label class="opcion <?= COMING_SOON ? '' : 'is-sel' ?>">
        <input type="radio" name="modo" value="live" <?= COMING_SOON ? '' : 'checked' ?>>
        <span class="opcion__t">Publicado</span>
        <span class="opcion__d">El sitio queda visible para todos: home, las cuatro áreas, nosotros y contacto.</span>
      </label>
    </div>

    <div class="campos">
      <div class="campo">
        <label for="lanzamiento">Fecha de lanzamiento <small>(cuenta regresiva de la portada)</small></label>
        <input id="lanzamiento" name="lanzamiento" type="datetime-local"
               value="<?= e(date('Y-m-d\TH:i', strtotime($AJ['lanzamiento']) ?: time())) ?>">
      </div>
      <div class="campo">
        <label for="preview_key">Clave de previsualización</label>
        <input id="preview_key" name="preview_key" type="text" value="<?= e($AJ['preview_key']) ?>">
        <small class="hint">Para revisar el sitio real mientras está oculto:
          <code><?= e($SITE['dominio']) ?>/?preview=<?= e($AJ['preview_key']) ?></code></small>
      </div>
    </div>

    <button class="btn" type="submit">Guardar estado</button>
  </form>
</div>

<div class="tarjeta">
  <h2>Últimos movimientos</h2>
  <?php $log = panel_log_ultimas(8); ?>
  <?php if (!$log): ?>
    <p class="vacio">Todavía no hay actividad registrada.</p>
  <?php else: ?>
    <ul class="log">
      <?php foreach ($log as $l): ?><li><?= e($l) ?></li><?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
