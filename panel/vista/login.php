<?php if (!function_exists('panel_sesion')) { http_response_code(403); exit; } ?>
<!DOCTYPE html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Panel IFK · Ingreso</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/panel/assets/panel.css?v=<?= @filemtime(__DIR__ . '/../assets/panel.css') ?>">
</head>
<body class="acceso">
  <form class="acceso__caja" method="post" action="index.php?v=login">
    <img class="acceso__logo" src="<?= img_src('logo') ?>" alt="IFK">
    <h1>Panel de administración</h1>
    <p class="acceso__txt">Ingresa la clave para administrar el sitio.</p>
    <?php if ($aviso): ?><div class="aviso aviso--err"><p><?= e($aviso['texto']) ?></p></div><?php endif; ?>
    <input type="hidden" name="accion" value="login">
    <label for="clave">Clave</label>
    <input id="clave" name="clave" type="password" autocomplete="current-password" required autofocus>
    <button class="btn" type="submit">Entrar</button>
  </form>
</body>
</html>
