<?php if (!function_exists('panel_sesion')) { http_response_code(403); exit; } ?>
<!DOCTYPE html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Panel IFK · Crear clave</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/panel/assets/panel.css?v=<?= @filemtime(__DIR__ . '/../assets/panel.css') ?>">
</head>
<body class="acceso">
  <form class="acceso__caja" method="post" action="index.php">
    <img class="acceso__logo" src="<?= img_src('logo') ?>" alt="IFK">
    <h1>Crea tu clave</h1>
    <p class="acceso__txt">Es la primera vez que entras al panel. Define una clave de al menos 8 caracteres; sólo tú la conocerás.</p>
    <?php if ($aviso): ?><div class="aviso aviso--err"><p><?= e($aviso['texto']) ?></p></div><?php endif; ?>
    <input type="hidden" name="accion" value="crear_clave">
    <label for="c1">Clave</label>
    <input id="c1" name="clave" type="password" autocomplete="new-password" required autofocus>
    <label for="c2">Repite la clave</label>
    <input id="c2" name="clave2" type="password" autocomplete="new-password" required>
    <button class="btn" type="submit">Crear clave y entrar</button>
  </form>
</body>
</html>
