<?php
/**
 * Panel de fotos del cliente (se entra por /fotos).
 *
 * Una sola cosa hace: cambiar las fotografías del sitio. Nada de GitHub,
 * tokens, palabras de consola ni logotipos de marcas; todo eso vive en
 * /admin y es trabajo de la agencia.
 *
 * El cliente entra con su propia clave, elige la página en el menú de la
 * izquierda, ve cada foto con su explicación —dónde sale y qué conviene que
 * muestre— y la reemplaza. La foto queda en el sitio al instante.
 *
 * Lo que el cliente no ve: después de cada cambio, el panel manda una copia
 * a GitHub por su cuenta. Si eso falla no se le dice nada, porque no es
 * asunto suyo y su foto ya quedó puesta; queda anotado en datos/respaldo.log
 * y el panel técnico lo verá como pendiente.
 */
declare(strict_types=1);

/* Subir una foto grande y respaldarla puede pasar de los 30 s de rigor. */
@set_time_limit(180);
@ini_set('max_execution_time', '180');

define('RAIZ', __DIR__);

require_once __DIR__ . '/app/sitio.php';
require_once __DIR__ . '/app/sello.php';
require_once __DIR__ . '/app/cliente.php';

cliente_sesion();
header('X-Robots-Tag: noindex, nofollow');

/* ------------------------------------------------------------------ */
/* Qué se pidió                                                        */
/* ------------------------------------------------------------------ */

$aviso = '';
$ok    = true;
$dentro = cliente_dentro();

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf_ok = $_SERVER['REQUEST_METHOD'] === 'POST'
        && hash_equals((string)$_SESSION['csrf'], (string)($_POST['csrf'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_ok) {

    if (!$dentro) {
        [$dentro, $aviso] = cliente_entrar((string)($_POST['clave'] ?? ''));
        $ok = $dentro;
        if ($dentro) { header('Location: /fotos'); exit; }

    } elseif (isset($_POST['salir'])) {
        cliente_salir();
        header('Location: /fotos');
        exit;

    } elseif (isset($_POST['cambiar'])) {
        [$ok, $aviso] = imagen_subir(
            $_FILES['foto'] ?? [],
            (string)$_POST['cambiar'],
            !empty($_POST['sello']),
            (string)($_POST['esquina'] ?? 'sup-der')
        );
        if ($ok) respaldo_silencioso('Foto cambiada desde el panel: ' . (string)$_POST['cambiar']);
        volver_a_la_pagina($ok, $aviso);

    } elseif (isset($_POST['restaurar'])) {
        [$ok, $aviso] = imagen_restaurar((string)$_POST['restaurar']);
        if ($ok) respaldo_silencioso('Foto restaurada desde el panel: ' . (string)$_POST['restaurar']);
        volver_a_la_pagina($ok, $aviso);
    }
}

/**
 * Después de guardar, se vuelve a la página con una dirección normal.
 *
 * Si no, el navegador se queda con el envío a cuestas: el cliente aprieta F5
 * o vuelve atrás y sube la misma foto otra vez sin querer. El aviso viaja en
 * la sesión y se muestra una sola vez.
 */
function volver_a_la_pagina(bool $ok, string $aviso): never {
    $_SESSION['recado'] = ['ok' => $ok, 'texto' => $aviso];
    $pag = (string)($_POST['pagina'] ?? '');
    header('Location: /fotos' . ($pag !== '' ? '?p=' . rawurlencode($pag) : ''));
    exit;
}

/**
 * Manda la copia a GitHub sin molestar al cliente.
 * Si no se puede, queda anotado y se sigue: la foto ya está en el sitio.
 */
function respaldo_silencioso(string $mensaje): void {
    try {
        require_once __DIR__ . '/app/github.php';
        [$bien, $motivo] = respaldar($mensaje);
    } catch (Throwable $e) {
        $bien = false; $motivo = 'error: ' . $e->getMessage();
    }
    @file_put_contents(RAIZ . '/datos/respaldo.log',
        date('d-m-Y H:i') . ' · ' . ($bien ? 'ok' : 'FALLÓ') . ' · ' . $motivo . ' · ' . $mensaje . "\n",
        FILE_APPEND | LOCK_EX);
}

/* ------------------------------------------------------------------ */
/* Qué se muestra                                                      */
/* ------------------------------------------------------------------ */

$csrf   = (string)$_SESSION['csrf'];
$GRUPOS = $dentro ? imagenes_catalogo() : [];

/* El aviso que dejó el guardado anterior, si lo hubo. */
if (isset($_SESSION['recado'])) {
    $ok    = (bool)$_SESSION['recado']['ok'];
    $aviso = (string)$_SESSION['recado']['texto'];
    unset($_SESSION['recado']);
}
$pag    = (string)($_GET['p'] ?? ($_POST['pagina'] ?? ''));
if ($pag !== '' && !isset($GRUPOS[$pag])) $pag = '';

/** Cuántas fotos de un grupo cambió el cliente. */
function grupo_cambiadas(array $grupo): int {
    $n = 0;
    foreach ($grupo['ranuras'] as $r) if (imagen_cambiada($r['id'])) $n++;
    return $n;
}
?>
<!doctype html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Fotos del sitio — IFK</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<style>
  :root{
    --navy:#0a2540; --navy-900:#061a2e; --azul:#2f6bb5; --azul-claro:#e8f0fb;
    --papel:#fff; --fondo:#f2f5f9; --linea:#e2e8f1; --tinta:#13203a; --suave:#5a6b85;
    --verde:#e8f5ec; --verde-linea:#bcdcc8; --verde-tinta:#1d6b36;
    --rojo:#fdecec; --rojo-linea:#f2c2c2; --rojo-tinta:#8a2020;
    --r:14px; --sombra:0 2px 14px -6px rgba(10,37,64,.28);
  }
  *{box-sizing:border-box}
  body{margin:0;background:var(--fondo);color:var(--tinta);
       font:15px/1.55 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;-webkit-font-smoothing:antialiased}
  a{color:var(--azul)}
  button{font:inherit;cursor:pointer;border:0}
  h1,h2,h3{margin:0;letter-spacing:-.01em}
  img{display:block}

  /* ---------------- Entrar ---------------- */
  .entrada{min-height:100dvh;display:grid;place-items:center;padding:24px;background:var(--navy-900)}
  .entrada__todo{width:100%;max-width:380px}
  /* El logotipo es blanco, así que va sobre el fondo oscuro, no sobre la tarjeta. */
  .entrada__todo > img{width:132px;margin:0 auto 26px}
  .entrada__caja{background:var(--papel);border-radius:20px;padding:30px 28px;
                 box-shadow:0 30px 70px -30px rgba(0,0,0,.6);text-align:center}
  .entrada__caja h1{font-size:19px;margin-bottom:4px}
  .entrada__caja p{margin:0 0 22px;color:var(--suave);font-size:13.5px}
  .entrada__caja input{width:100%;padding:13px 15px;font:inherit;border:1px solid #d3dbe6;
                       border-radius:11px;background:#fbfcfe;color:inherit;text-align:center}
  .entrada__caja input:focus{outline:2px solid var(--azul);outline-offset:-1px}
  .entrada__caja button{width:100%;margin-top:11px;padding:13px;border-radius:11px;
                        background:var(--navy);color:#fff;font-weight:600}

  /* ---------------- Marco ---------------- */
  .barra{position:sticky;top:0;z-index:10;display:flex;align-items:center;gap:16px;
         padding:0 clamp(16px,3vw,26px);height:62px;background:var(--navy);color:#fff}
  .barra__logo{height:26px;width:auto}
  .barra__t{font-size:14px;font-weight:600;letter-spacing:.01em}
  .barra__sep{flex:1}
  .barra a,.barra button{color:#fff;font-size:13.5px;text-decoration:none;padding:8px 13px;
                         border-radius:9px;background:rgba(255,255,255,.12)}
  .barra a:hover,.barra button:hover{background:rgba(255,255,255,.22)}

  .marco{display:grid;grid-template-columns:238px 1fr;gap:clamp(16px,2.4vw,28px);
         max-width:1180px;margin:0 auto;padding:clamp(16px,3vw,28px)}

  /* ---------------- Menú ---------------- */
  .menu{align-self:start;position:sticky;top:86px;background:var(--papel);border:1px solid var(--linea);
        border-radius:var(--r);padding:8px;box-shadow:var(--sombra)}
  .menu a{display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:10px;
          color:var(--tinta);text-decoration:none;font-size:13.5px}
  .menu a:hover{background:var(--fondo)}
  .menu a.aqui{background:var(--azul-claro);color:var(--navy);font-weight:600}
  .menu a span{margin-left:auto;font-size:11.5px;color:var(--suave);font-variant-numeric:tabular-nums}
  .menu a.aqui span{color:var(--azul)}
  .menu .punto{width:7px;height:7px;border-radius:50%;background:var(--azul);flex:none}
  .menu hr{border:0;border-top:1px solid var(--linea);margin:7px 10px}

  /* ---------------- Avisos ---------------- */
  .aviso{display:flex;gap:10px;align-items:flex-start;padding:13px 15px;border-radius:12px;
         background:var(--verde);border:1px solid var(--verde-linea);color:var(--verde-tinta);
         font-size:13.5px;margin-bottom:18px}
  .aviso.mal{background:var(--rojo);border-color:var(--rojo-linea);color:var(--rojo-tinta)}
  .aviso b{font-size:16px;line-height:1.2}

  /* ---------------- Inicio ---------------- */
  .intro{margin-bottom:22px}
  .intro h1{font-size:clamp(21px,3vw,27px)}
  .intro p{margin:7px 0 0;color:var(--suave);font-size:14px;max-width:62ch}
  .atajos{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px}
  .atajo{display:block;padding:17px;background:var(--papel);border:1px solid var(--linea);
         border-radius:var(--r);text-decoration:none;color:inherit;box-shadow:var(--sombra)}
  .atajo:hover{border-color:var(--azul)}
  .atajo b{display:block;font-size:15px;margin-bottom:3px}
  .atajo span{font-size:12.5px;color:var(--suave)}
  .atajo em{display:block;margin-top:7px;font-style:normal;font-size:12px;color:var(--azul);font-weight:600}

  /* ---------------- Tarjetas de foto ---------------- */
  .tarjeta{background:var(--papel);border:1px solid var(--linea);border-radius:var(--r);
           padding:16px;margin-bottom:14px;box-shadow:var(--sombra)}
  .tarjeta__arriba{display:grid;grid-template-columns:190px 1fr;gap:16px}
  .tarjeta__foto{position:relative;display:grid;place-items:center;height:132px;overflow:hidden;
                 border-radius:10px;background:var(--fondo)}
  .tarjeta__foto img{max-width:100%;max-height:132px}
  .tarjeta h3{font-size:15px;margin-bottom:7px}
  .tarjeta dl{margin:0;font-size:13px;color:var(--suave)}
  .tarjeta dt{float:left;clear:left;margin-right:6px;font-weight:600;color:var(--tinta)}
  .tarjeta dd{margin:0 0 5px}
  .ficha{margin:10px 0 0;font-size:11.5px;color:#8b95a6}
  .marbete{display:inline-block;margin-left:7px;padding:2px 9px;border-radius:20px;font-size:10.5px;
           font-weight:700;letter-spacing:.03em;background:var(--azul-claro);color:var(--azul);
           vertical-align:2px;text-transform:uppercase}

  /* ---------------- Cambiar la foto ---------------- */
  .cambiar{margin-top:15px;padding-top:15px;border-top:1px solid var(--linea)}
  .soltar{display:flex;flex-direction:column;align-items:center;gap:3px;padding:20px;text-align:center;
          border:2px dashed #cdd8e6;border-radius:12px;background:#fbfcfe;color:var(--suave);
          font-size:13.5px;cursor:pointer}
  .soltar:hover,.soltar.encima{border-color:var(--azul);background:var(--azul-claro);color:var(--navy)}
  .soltar b{color:var(--navy);font-size:14px}
  .soltar small{font-size:11.5px;color:#8b95a6}
  .js .soltar input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}

  .elegida{display:none;gap:14px;align-items:center;padding:13px;border:1px solid var(--linea);
           border-radius:12px;background:#fbfcfe}
  .elegida.ver{display:flex}
  .elegida img{width:104px;height:78px;object-fit:cover;border-radius:8px;flex:none;background:var(--fondo)}
  .elegida__d{min-width:0;flex:1}
  .elegida__n{font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .elegida__q{font-size:12px;color:var(--suave)}
  .js .elegida.ver ~ .soltar{display:none}

  .opcion{display:flex;align-items:center;gap:7px;margin-top:9px;font-size:12.5px;color:var(--suave)}
  .opcion input{accent-color:var(--azul)}
  .opcion select{padding:5px 8px;font-size:12px;border:1px solid #d3dbe6;border-radius:7px;background:#fff;color:inherit}
  .botones{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
  .btn{padding:10px 17px;border-radius:10px;font-size:13.5px;font-weight:600;
       background:var(--navy);color:#fff}
  .btn:hover{background:var(--navy-900)}
  .btn--suave{background:var(--fondo);color:var(--tinta);font-weight:500}
  .btn--suave:hover{background:#e6ebf2}
  .btn--volver{background:transparent;color:var(--azul);font-weight:500;padding-left:0}
  .btn--volver:hover{text-decoration:underline;background:transparent}

  .cargando{display:none;align-items:center;gap:8px;font-size:13px;color:var(--suave);margin-top:12px}
  .cargando.ver{display:flex}
  .rueda{width:15px;height:15px;border:2px solid #cdd8e6;border-top-color:var(--azul);
         border-radius:50%;animation:gira .7s linear infinite}
  @keyframes gira{to{transform:rotate(360deg)}}

  @media (max-width:820px){
    .marco{grid-template-columns:1fr}
    .menu{position:static;display:flex;gap:6px;overflow-x:auto;padding:7px;-webkit-overflow-scrolling:touch}
    .menu a{white-space:nowrap}
    .menu a span,.menu hr{display:none}
    .tarjeta__arriba{grid-template-columns:1fr}
    .tarjeta__foto{height:190px}
    .tarjeta__foto img{max-height:190px}
    .barra__t{display:none}
  }
  @media (prefers-reduced-motion:reduce){.rueda{animation:none}}
</style>
</head>
<body>

<?php if (!$dentro): ?>

  <div class="entrada">
    <div class="entrada__todo">
    <img src="<?= asset('img/ifk_logo.webp') ?>" alt="IFK">
    <div class="entrada__caja">
      <h1>Fotos del sitio</h1>
      <p>Para cambiar las fotografías de ifkchile.com</p>
      <form method="post">
        <input type="password" name="clave" placeholder="Tu clave" autofocus
               autocomplete="current-password" aria-label="Clave">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <button>Entrar</button>
      </form>
      <?php if ($aviso !== ''): ?>
        <div class="aviso mal" style="margin:18px 0 0;text-align:left"><b>!</b><span><?= e($aviso) ?></span></div>
      <?php endif; ?>
    </div>
    </div>
  </div>

<?php else: ?>

  <header class="barra">
    <img class="barra__logo" src="<?= asset('img/ifk_logo.webp') ?>" alt="IFK">
    <span class="barra__t">Fotos del sitio</span>
    <span class="barra__sep"></span>
    <a href="/?ver=sitio" target="_blank" rel="noopener">Ver el sitio</a>
    <form method="post" style="display:inline">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <button name="salir" value="1">Salir</button>
    </form>
  </header>

  <div class="marco">
    <nav class="menu">
      <a href="/fotos" class="<?= $pag === '' ? 'aqui' : '' ?>">Inicio</a>
      <hr>
      <?php foreach ($GRUPOS as $k => $g): $cam = grupo_cambiadas($g); ?>
        <a href="/fotos?p=<?= e($k) ?>" class="<?= $k === $pag ? 'aqui' : '' ?>">
          <?php if ($cam): ?><i class="punto" title="<?= $cam ?> cambiada(s)"></i><?php endif; ?>
          <?= e($g['titulo']) ?><span><?= count($g['ranuras']) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <main>
      <?php if ($aviso !== ''): ?>
        <div class="aviso <?= $ok ? '' : 'mal' ?>"><b><?= $ok ? '✓' : '!' ?></b><span><?= e($aviso) ?></span></div>
      <?php endif; ?>

      <?php if ($pag === ''):
        $total = $cambiadas = 0;
        foreach ($GRUPOS as $g) { $total += count($g['ranuras']); $cambiadas += grupo_cambiadas($g); }
      ?>
        <div class="intro">
          <h1>Hola 👋</h1>
          <p>Desde aquí puedes cambiar cualquiera de las <b><?= $total ?> fotografías</b> de ifkchile.com.
             Elige la página donde está la foto y reemplázala: el sitio se actualiza al instante.
             Si te arrepientes, cada foto tiene un botón para volver a la anterior.
             <?php if ($cambiadas): ?><br><b><?= $cambiadas ?></b> foto<?= $cambiadas === 1 ? '' : 's' ?>
             cambiada<?= $cambiadas === 1 ? '' : 's' ?> hasta ahora.<?php endif; ?></p>
        </div>
        <div class="atajos">
          <?php foreach ($GRUPOS as $k => $g): $cam = grupo_cambiadas($g); ?>
            <a class="atajo" href="/fotos?p=<?= e($k) ?>">
              <b><?= e($g['titulo']) ?></b>
              <span><?= count($g['ranuras']) ?> foto<?= count($g['ranuras']) === 1 ? '' : 's' ?></span>
              <?php if ($cam): ?><em><?= $cam ?> cambiada<?= $cam === 1 ? '' : 's' ?></em><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>

      <?php else: $g = $GRUPOS[$pag]; ?>
        <div class="intro">
          <h1><?= e($g['titulo']) ?></h1>
          <p>Estas <?= count($g['ranuras']) ?> fotos salen en
             <a href="<?= e($g['pagina']) ?>?ver=sitio" target="_blank" rel="noopener"><?= e($g['pagina']) ?></a>.
             Debajo de cada una dice dónde aparece y qué conviene que muestre.</p>
        </div>

        <?php foreach ($g['ranuras'] as $r):
          $ruta     = imagen($r['id']);
          $archivo  = RAIZ . '/assets/' . $ruta;
          $medida   = @getimagesize($archivo);
          $cambiada = imagen_cambiada($r['id']);
        ?>
          <article class="tarjeta">
            <div class="tarjeta__arriba">
              <div class="tarjeta__foto">
                <?php if ($medida): ?>
                  <img src="/assets/<?= e($ruta) ?>?v=<?= (int)@filemtime($archivo) ?>" alt="" loading="lazy">
                <?php else: ?><span style="color:#8b95a6;font-size:12px">falta el archivo</span><?php endif; ?>
              </div>
              <div>
                <h3><?= e($r['titulo']) ?><?php if ($cambiada): ?><span class="marbete">cambiada</span><?php endif; ?></h3>
                <dl>
                  <dt>Dónde se ve:</dt><dd><?= e($r['donde']) ?></dd>
                  <dt>Qué poner:</dt><dd><?= e($r['que']) ?></dd>
                </dl>
                <p class="ficha"><?= e($r['forma']) ?><?php if ($medida): ?>
                  · mide <?= (int)$medida[0] ?>×<?= (int)$medida[1] ?> px y pesa
                  <?= (int)round((int)@filesize($archivo) / 1024) ?> KB<?php endif; ?></p>
              </div>
            </div>

            <div class="cambiar">
              <form method="post" enctype="multipart/form-data" class="forma">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="pagina" value="<?= e($pag) ?>">

                <div class="elegida">
                  <img alt="" class="elegida__v">
                  <div class="elegida__d">
                    <div class="elegida__n"></div>
                    <div class="elegida__q">Lista para reemplazar la foto de arriba.</div>
                    <div class="opcion">
                      <label><input type="checkbox" name="sello" value="1"> Ponerle el logo de IFK encima</label>
                      <select name="esquina" aria-label="Esquina del logo">
                        <?php foreach (sello_esquinas() as $clave => $texto): ?>
                          <option value="<?= e($clave) ?>" <?= $clave === $r['esquina'] ? 'selected' : '' ?>><?= e($texto) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>

                <label class="soltar">
                  <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                  <b>Arrastra aquí la foto nueva</b>
                  <span>o haz clic para buscarla en tu computador o celular</span>
                  <small>JPG, PNG o WEBP · hasta 14 MB</small>
                </label>

                <div class="botones">
                  <button class="btn" name="cambiar" value="<?= e($r['id']) ?>">Guardar esta foto</button>
                  <button type="button" class="btn btn--suave cancelar">Cancelar</button>
                </div>
                <div class="cargando"><span class="rueda"></span>Subiendo la foto, no cierres esta página…</div>
              </form>

              <?php if ($cambiada): ?>
                <form method="post" onsubmit="return confirm('¿Volver a la foto que estaba antes?')">
                  <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                  <input type="hidden" name="pagina" value="<?= e($pag) ?>">
                  <button class="btn btn--volver" name="restaurar" value="<?= e($r['id']) ?>">↺ Volver a la foto anterior</button>
                </form>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </main>
  </div>

  <script>
  /* Sin JavaScript el panel igual funciona: se ve el botón de archivo de
     siempre y el de guardar. Con JavaScript se esconden hasta que haya una
     foto elegida, se puede arrastrar, y se ve antes de subirla. */
  document.documentElement.className = 'js';

  document.querySelectorAll('.forma').forEach(function (forma) {
    var entrada  = forma.querySelector('input[type=file]');
    var soltar   = forma.querySelector('.soltar');
    var elegida  = forma.querySelector('.elegida');
    var vista    = forma.querySelector('.elegida__v');
    var nombre   = forma.querySelector('.elegida__n');
    var botones  = forma.querySelector('.botones');
    var cargando = forma.querySelector('.cargando');

    botones.style.display = 'none';

    function mostrar(archivo) {
      if (!archivo) return;
      nombre.textContent = archivo.name + ' · ' + Math.round(archivo.size / 1024) + ' KB';
      vista.src = URL.createObjectURL(archivo);
      elegida.classList.add('ver');
      soltar.style.display = 'none';
      botones.style.display = 'flex';
    }

    function limpiar() {
      entrada.value = '';
      elegida.classList.remove('ver');
      soltar.style.display = '';
      botones.style.display = 'none';
    }

    entrada.addEventListener('change', function () { mostrar(entrada.files[0]); });
    forma.querySelector('.cancelar').addEventListener('click', limpiar);

    ['dragenter', 'dragover'].forEach(function (ev) {
      soltar.addEventListener(ev, function (e) { e.preventDefault(); soltar.classList.add('encima'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      soltar.addEventListener(ev, function () { soltar.classList.remove('encima'); });
    });
    soltar.addEventListener('drop', function (e) {
      e.preventDefault();
      var f = e.dataTransfer.files[0];
      if (!f) return;
      var caja = new DataTransfer();
      caja.items.add(f);
      entrada.files = caja.files;
      mostrar(f);
    });

    /* Subir una foto de celular tarda; sin esto el cliente vuelve a apretar. */
    forma.addEventListener('submit', function () {
      botones.style.display = 'none';
      cargando.classList.add('ver');
    });
  });
  </script>

<?php endif; ?>
</body>
</html>
