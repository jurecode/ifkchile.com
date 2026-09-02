<?php if (!function_exists('panel_sesion')) { http_response_code(403); exit; } ?>
<div class="tarjeta">
  <form method="post" action="index.php?v=clave">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <input type="hidden" name="accion" value="clave">
    <div class="campos">
      <div class="campo campo--full">
        <label for="actual">Clave actual</label>
        <input id="actual" name="actual" type="password" autocomplete="current-password" required>
      </div>
      <div class="campo">
        <label for="nueva">Clave nueva</label>
        <input id="nueva" name="nueva" type="password" autocomplete="new-password" required>
      </div>
      <div class="campo">
        <label for="nueva2">Repite la clave nueva</label>
        <input id="nueva2" name="nueva2" type="password" autocomplete="new-password" required>
      </div>
    </div>
    <button class="btn" type="submit">Cambiar clave</button>
  </form>
</div>

<div class="tarjeta">
  <h2>Recomendaciones</h2>
  <ul class="lista-chk">
    <li>Usa el panel siempre sobre <strong>https://</strong>.</li>
    <li>El token de GitHub se guarda en <code>storage/settings.json</code>, fuera del alcance del navegador.</li>
    <li>Si pierdes la clave: borra la línea <code>password_hash</code> de ese archivo y el panel te pedirá crear una nueva.</li>
  </ul>
</div>
