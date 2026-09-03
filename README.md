# IFK · Inversiones Friomak SpA — sitio web

Sitio en PHP puro (sin frameworks ni base de datos) con **panel de administración propio**.
Hoy está en modo **Próximamente**; el home oficial y las páginas internas ya están listos.

---

## 1. Panel de administración

```
https://ifkchile.com/panel/
```

La primera vez que entras, el panel te pide **crear una clave** (mínimo 8 caracteres). Desde ahí puedes:

| Sección | Qué permite |
|---|---|
| **Estado del sitio** | Cambiar entre **Publicado** y **Próximamente**, y la clave de previsualización. |
| **Imágenes** | Reemplazar cualquier foto del sitio (y el logotipo) **arrastrándola sobre la tarjeta** o con un clic. Se recorta y optimiza sola a la medida correcta, se ve al instante y la versión anterior queda respaldada. |
| **Repositorio** | Guardar la conexión con GitHub (repositorio, rama, usuario y token) y subir los cambios. |
| **Clave de acceso** | Cambiar la clave del panel. |

Todo queda registrado en `storage/panel.log` y visible en el panel.
Si pierdes la clave: borra la línea `password_hash` de `storage/settings.json` y el panel te pedirá crear una nueva.

### Previsualizar el sitio mientras está oculto

```
https://ifkchile.com/?preview=ifk2026
```

Deja una cookie por 8 horas. Para volver a la portada de construcción: `https://ifkchile.com/?salir=1`.
La clave se cambia desde el panel.

---

## 2. Subir cambios a GitHub

Configuración, una sola vez:

1. Crea el repositorio en GitHub (puede ser privado).
2. En GitHub → *Settings* → *Developer settings* → *Personal access tokens*, genera un token con permiso **repo**
   (o *Contents: read and write* si es de tipo *fine-grained*).
3. En el panel → **Repositorio**, pega la dirección (`github.com/usuario/repositorio.git`), la rama, el usuario y el
   token, y guarda.
4. Si esa carpeta todavía no está enlazada (por ejemplo, el sitio se subió por FTP), aparece el botón
   **Conectar con GitHub**: enlaza la carpeta sin tocar los archivos, sólo alineando el historial.

Después quedan tres botones:

- **Subir a GitHub** — `add` + `commit` + `push` de todo lo que cambió en la carpeta.
- **Traer cambios** — actualiza la carpeta con la última versión publicada (sólo si no hay cambios locales sin subir).
- **Ver estado** — rama, último commit y archivos pendientes.

Notas:

- El token nunca se guarda dentro del repositorio ni se muestra completo: vive en `storage/settings.json`,
  carpeta bloqueada por `.htaccess` y excluida del repositorio (`.gitignore`).
- El botón necesita `git` y `proc_open` en la máquina donde corre el panel. Si el hosting los bloquea, se sube
  desde tu computador con `git push`.
- `storage/` no viaja a GitHub, así que **el estado publicado/próximamente es propio de cada instalación**.
- La actualización del servidor se hace por FTP o `git pull`, según cómo tengas montado el hosting.

## 3. Estructura de archivos

```
index.php              Front controller (ruteo + modo Próximamente)
config.php             Datos de la empresa, ajustes y slots de imágenes
includes/
  data.php             Áreas, marcas, proyectos, valores y menú  ← contenido editable
  header.php           Navegación + SEO + datos estructurados
  footer.php           Pie + botón flotante de WhatsApp
  cta.php              Bloque de llamado a la acción
  form.php             Procesamiento del formulario de cotización
pages/
  coming-soon.php      Portada "Próximamente"
  home.php             Home oficial
  area.php             Plantilla de las 4 áreas
  nosotros.php         Empresa, valores, proyectos, marcas, cobertura
  contacto.php         Formulario de cotización
panel/                 Panel de administración (login, estado, imágenes, repositorio)
assets/css/style.css   Estilos del sitio
assets/css/soon.css    Estilos de la portada de construcción
assets/js/main.js      Menú, barra al hacer scroll, animaciones
assets/img/            Fotografías del sitio (reemplazables desde el panel)
assets/img/_svg/       Ilustraciones vectoriales de la primera versión (respaldo)
storage/               Ajustes, solicitudes recibidas, respaldos y registro
```

## 4. Dónde se edita cada cosa

- **Imágenes y estado del sitio** → panel.
- **Teléfono, WhatsApp, correo, dirección, horario** → `config.php` (arreglo `$SITE`).
- **Servicios de cada área, marcas, proyectos, valores, menú** → `includes/data.php`.
- **Textos del home** → `pages/home.php`.
- **Colores y estilo** → variables al inicio de `assets/css/style.css`.

## 5. Formulario de cotización

Campos: nombre, empresa, RUT, teléfono, correo, tipo de servicio, ubicación, descripción y hasta 3 fotografías.
Valida en el servidor, tiene honeypot antispam, guarda cada solicitud en `storage/leads.csv` y envía aviso al correo
definido en `$SITE['email']`. Si el hosting bloquea `mail()`, conviene cambiarlo por SMTP (PHPMailer).

## 6. Puesta en producción

1. Subir el contenido de la carpeta a `public_html` (o clonar el repositorio ahí).
2. Dar permiso de escritura a `storage/` (755 o 775) — el panel escribe ahí.
3. Verificar que se subió el `.htaccess` (bloquea `storage/` e `includes/`, comprime y cachea).
4. Entrar a `/panel/`, crear la clave y revisar el estado del sitio.
5. Opcional — URLs amigables (`/nosotros/`, `/servicios/refrigeracion/`): activar `URLS_AMIGABLES` en `config.php`
   (requiere Apache con `mod_rewrite`).

## 7. Sobre las imágenes actuales

Las fotografías son de **Unsplash** (licencia libre, uso comercial permitido, sin atribución obligatoria) y están
puestas como referencia hasta tener material propio. Se reemplazan desde el panel, una por una, sin tocar código.
El logotipo usado es el archivo `assets/img/ifk_logo.webp` entregado por el cliente.

## 8. Desarrollo local

```bash
php -S localhost:8787 -t .
```
