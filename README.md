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
| **Imágenes** | Reemplazar cualquier foto del sitio (y el logotipo). Se recorta y optimiza sola a la medida correcta; la versión anterior queda respaldada. |
| **Repositorio** | Guardar el token de GitHub y publicar: *local → GitHub* y *GitHub → servidor*. |
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

## 2. Publicar cambios (local → GitHub → servidor)

Configuración, una sola vez:

1. Crea el repositorio en GitHub (puede ser privado).
2. En GitHub → *Settings* → *Developer settings* → *Personal access tokens*, genera un token con permiso **repo**
   (o *Contents: read and write* si es de tipo *fine-grained*).
3. En el panel → **Repositorio**, pega la dirección (`github.com/usuario/repositorio.git`), el usuario y el token, y guarda.
4. En el servidor, crea el archivo `storage/deploy.json`:

   ```json
   {"clave":"una-clave-larga-e-inventada","repo":"jurecode/ifkchile.com","rama":"main","usuario":"jurecode","token":"ghp_…"}
   ```

   `usuario` y `token` sólo hacen falta si el repositorio es privado. Escribe esa misma clave y la URL
   `https://ifkchile.com/deploy.php` en el panel. Va en `storage/` a propósito: esa carpeta no viaja en el
   repositorio, así la configuración del servidor sobrevive a cada actualización.

5. En el servidor la carpeta puede ser un clon de git **o** una copia subida por FTP: `deploy.php` detecta
   cuál es y usa el método que corresponda (ver abajo).
Después, cada actualización son dos botones:

- **Subir a GitHub** — hace `add` + `commit` + `push` de todo lo que cambió en esta carpeta.
- **Actualizar servidor** — le pide al servidor que descargue la última versión.

Notas:

- El token nunca se guarda dentro del repositorio ni se muestra completo: vive en `storage/settings.json`,
  carpeta bloqueada por `.htaccess` y excluida del repositorio (`.gitignore`).
- `storage/` no viaja a GitHub, así que **el estado publicado/próximamente es propio de cada instalación**:
  para publicar el sitio real se cambia desde el panel del servidor (`ifkchile.com/panel/`).
- **`deploy.php` funciona de dos formas.** Si la carpeta del servidor es un clon de git y el hosting permite ejecutar
  comandos, hace `fetch` + `reset --hard`. Si no —hosting compartido, sitio subido por FTP— descarga el ZIP del
  repositorio desde GitHub y reemplaza los archivos, sin necesitar git. En ese segundo modo, los archivos que se
  **eliminen** del repositorio hay que borrarlos a mano en el servidor.
- El botón *Subir a GitHub* del panel sí necesita `git` y `proc_open` en la máquina donde corre el panel. Si el hosting
  los bloquea, se sube desde tu computador y el servidor se actualiza igual con el botón *Actualizar servidor*.

---

## 3. Estructura de archivos

```
index.php              Front controller (ruteo + modo Próximamente)
config.php             Datos de la empresa, ajustes y slots de imágenes
deploy.php             Se sube al servidor: actualiza el sitio desde GitHub
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
