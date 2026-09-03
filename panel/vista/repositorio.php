<?php if (!function_exists('panel_sesion')) { http_response_code(403); exit; } ?>
<?php
$g      = $AJ['git'];
$hayGit = git_disponible();
$repoOk = es_repo();
$token  = trim($g['token']);
$colaTok = $token !== '' ? '•••• ' . substr($token, -4) : '';
?>

<div class="tarjeta">
  <h2>Conexión con GitHub</h2>
  <p class="intro">
    El token queda guardado en el servidor y nunca se muestra completo: para cambiarlo, escribe uno nuevo.
  </p>

  <form method="post" action="index.php?v=repositorio">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <input type="hidden" name="accion" value="git_guardar">
    <div class="campos">
      <div class="campo campo--full">
        <label for="repo">Repositorio</label>
        <input id="repo" name="repo" type="text" value="<?= e($g['repo']) ?>" placeholder="github.com/usuario/repositorio.git">
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
    </div>
    <button class="btn" type="submit">Guardar datos</button>
  </form>
</div>

<?php if (!$repoOk): ?>
<div class="tarjeta">
  <h2>Conectar la carpeta</h2>
  <p class="intro">
    Esta carpeta todavía no está enlazada con el repositorio. Al conectarla se enlaza con GitHub
    <strong>sin tocar tus archivos</strong>: sólo queda claro qué está subido y qué no.
  </p>
  <form method="post" action="index.php?v=repositorio">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <input type="hidden" name="accion" value="git_conectar">
    <button class="btn btn--osc" type="submit" <?= $hayGit ? '' : 'disabled' ?>>Conectar con GitHub</button>
  </form>
</div>
<?php endif; ?>

<div class="tarjeta">
  <h2>Subir cambios</h2>

  <?php if (!$hayGit): ?>
    <div class="aviso aviso--err"><p>Este servidor no tiene <code>git</code> disponible o bloquea la ejecución de comandos, así que la subida automática no funcionará desde aquí.</p></div>
  <?php endif; ?>

  <p class="intro">
    Guarda todos los cambios de esta carpeta en un commit y los sube al repositorio.
    <?= $repoOk ? '' : ' Primero hay que conectar la carpeta.' ?>
  </p>

  <form method="post" action="index.php?v=repositorio" onsubmit="return confirm('¿Subir todos los cambios actuales a GitHub?')">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <input type="hidden" name="accion" value="git_subir">
    <div class="campos">
      <div class="campo campo--full">
        <label for="mensaje">Descripción del cambio <small>(opcional)</small></label>
        <input id="mensaje" name="mensaje" type="text" placeholder="Ej: fotos nuevas de proyectos">
      </div>
    </div>
    <button class="btn" type="submit" <?= ($hayGit && $repoOk) ? '' : 'disabled' ?>>Subir a GitHub</button>
  </form>
</div>

<div class="tarjeta">
  <h2>Traer cambios de GitHub</h2>
  <p class="intro">
    Actualiza esta carpeta con la última versión publicada. Sólo avanza si aquí no hay cambios sin subir,
    así nunca se pierde nada de lo que hayas hecho en este servidor.
  </p>
  <form method="post" action="index.php?v=repositorio" onsubmit="return confirm('¿Actualizar esta carpeta con la última versión de GitHub?')">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <input type="hidden" name="accion" value="git_traer">
    <button class="btn btn--osc" type="submit" <?= ($hayGit && $repoOk) ? '' : 'disabled' ?>>Traer cambios</button>
  </form>

  <details class="rescate">
    <summary>El historial se separó y no puede actualizarse solo</summary>
    <p>
      Pasa cuando esta carpeta y GitHub siguieron caminos distintos (por ejemplo, si se subieron archivos por FTP
      y además se hizo un commit aquí). Este botón deja la carpeta <strong>exactamente igual a GitHub</strong>.
      Antes de hacerlo guarda el estado actual en una rama local, así nada se pierde de forma definitiva.
    </p>
    <form method="post" action="index.php?v=repositorio" onsubmit="return confirm('La carpeta quedará igual a GitHub y se descartarán los cambios locales (se guarda un respaldo). ¿Continuar?')">
      <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
      <input type="hidden" name="accion" value="git_forzar">
      <button class="btn btn--alerta" type="submit" <?= ($hayGit && $repoOk) ? '' : 'disabled' ?>>Usar la versión de GitHub</button>
    </form>
  </details>
</div>

<div class="tarjeta">
  <h2>Estado del repositorio</h2>
  <p class="intro">Muestra la rama actual, el último commit y los archivos pendientes de subir.</p>
  <form method="post" action="index.php?v=repositorio">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <input type="hidden" name="accion" value="git_estado">
    <button class="btn btn--claro" type="submit" <?= $hayGit ? '' : 'disabled' ?>>Ver estado</button>
  </form>
</div>
