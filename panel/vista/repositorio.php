<?php if (!function_exists('panel_sesion')) { http_response_code(403); exit; } ?>
<?php
$g       = $AJ['git'];
$hayGit  = git_disponible();
$repoOk  = es_repo();
$token   = trim($g['token']);
$colaTok = $token !== '' ? '•••• ' . substr($token, -4) : '';
?>

<div class="tarjeta">
  <h2>Conexión con GitHub</h2>
  <p class="intro">
    Guarda aquí los datos del repositorio. El token queda almacenado en el servidor y nunca se
    muestra completo: para cambiarlo, escribe uno nuevo.
  </p>

  <form method="post" action="index.php?v=repositorio">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <input type="hidden" name="accion" value="git_guardar">
    <div class="campos">
      <div class="campo campo--full">
        <label for="repo">Repositorio</label>
        <input id="repo" name="repo" type="text" value="<?= e($g['repo']) ?>" placeholder="github.com/usuario/ifkchile.git">
        <small class="hint">Sin <code>https://</code> ni usuario: sólo <code>github.com/usuario/repositorio.git</code></small>
      </div>
      <div class="campo">
        <label for="rama">Rama</label>
        <input id="rama" name="rama" type="text" value="<?= e($g['rama']) ?>" placeholder="main">
      </div>
      <div class="campo">
        <label for="usuario">Usuario de GitHub</label>
        <input id="usuario" name="usuario" type="text" value="<?= e($g['usuario']) ?>" placeholder="mi-usuario">
      </div>
      <div class="campo campo--full">
        <label for="token">Token de acceso <?= $colaTok ? '<small>(guardado: ' . e($colaTok) . ')</small>' : '' ?></label>
        <input id="token" name="token" type="password" autocomplete="off" placeholder="<?= $colaTok ? 'Escribe uno nuevo para reemplazarlo' : 'ghp_… o github_pat_…' ?>">
        <small class="hint">
          GitHub → Settings → Developer settings → Personal access tokens. Necesita permiso <strong>repo</strong>
          (o <em>Contents: read and write</em> si es de tipo <em>fine-grained</em>).
          <?php if ($colaTok): ?><label class="chk"><input type="checkbox" name="borrar_token" value="1"> Borrar el token guardado</label><?php endif; ?>
        </small>
      </div>

      <div class="campo campo--full sep">
        <label for="deploy_url">Dirección de <code>deploy.php</code> en el servidor</label>
        <input id="deploy_url" name="deploy_url" type="url" value="<?= e($g['deploy_url']) ?>" placeholder="https://ifkchile.com/deploy.php">
      </div>
      <div class="campo campo--full">
        <label for="deploy_clave">Clave de deploy</label>
        <input id="deploy_clave" name="deploy_clave" type="text" value="<?= e($g['deploy_clave']) ?>" placeholder="una clave larga e inventada">
        <small class="hint">La misma que anotaste en <code>storage/deploy.json</code> del servidor.</small>
      </div>
    </div>
    <button class="btn" type="submit">Guardar datos</button>
  </form>
</div>

<div class="tarjeta">
  <h2>Publicar cambios</h2>

  <?php if (!$hayGit): ?>
    <div class="aviso aviso--err"><p>Este servidor no tiene <code>git</code> disponible o bloquea la ejecución de comandos, así que la subida automática no funcionará desde aquí. Puedes usar el panel igualmente para el resto, y subir por FTP o desde tu computador.</p></div>
  <?php endif; ?>

  <div class="pasos-deploy">
    <div class="paso">
      <span>1</span>
      <div>
        <h3>Local → GitHub</h3>
        <p>Guarda todos los cambios de esta carpeta en un commit y los sube al repositorio<?= $repoOk ? '' : ' (la primera vez también crea el repositorio local)' ?>.</p>
        <form method="post" action="index.php?v=repositorio" onsubmit="return confirm('¿Subir todos los cambios actuales a GitHub?')">
          <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
          <input type="hidden" name="accion" value="git_subir">
          <input type="text" name="mensaje" placeholder="Descripción del cambio (opcional)">
          <button class="btn" type="submit" <?= $hayGit ? '' : 'disabled' ?>>Subir a GitHub</button>
        </form>
      </div>
    </div>

    <div class="paso">
      <span>2</span>
      <div>
        <h3>GitHub → servidor</h3>
        <p>Le pide al servidor que descargue la última versión publicada en GitHub.</p>
        <form method="post" action="index.php?v=repositorio" onsubmit="return confirm('¿Actualizar el sitio del servidor con la última versión de GitHub?')">
          <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
          <input type="hidden" name="accion" value="git_desplegar">
          <button class="btn btn--osc" type="submit">Actualizar servidor</button>
        </form>
      </div>
    </div>

    <div class="paso">
      <span>3</span>
      <div>
        <h3>Revisar</h3>
        <p>Muestra la rama, el último commit y los archivos pendientes de subir.</p>
        <form method="post" action="index.php?v=repositorio">
          <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
          <input type="hidden" name="accion" value="git_estado">
          <button class="btn btn--claro" type="submit" <?= $hayGit ? '' : 'disabled' ?>>Ver estado</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="tarjeta">
  <h2>Cómo dejarlo funcionando</h2>
  <ol class="lista-num">
    <li>Crea el repositorio en GitHub (puede ser privado) y pega su dirección arriba.</li>
    <li>Genera un token con permiso <strong>repo</strong> y guárdalo en este panel.</li>
    <li>Sube el sitio al servidor (por FTP o <code>git clone</code>, da lo mismo).</li>
    <li>En el servidor, crea <code>storage/deploy.json</code> con la clave de arriba, el repositorio y —si es privado— un token:
      <code>{"clave":"…","repo":"usuario/repositorio","rama":"main","usuario":"…","token":"…"}</code></li>
    <li>Listo: cada cambio se publica con los botones 1 y 2.</li>
  </ol>
</div>
