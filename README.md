# IFK · Inversiones Friomak

Sitio de IFK en PHP simple, sin framework ni base de datos. Se administra desde
`/admin`: ahí se decide qué ve el público y ahí está el enlace con GitHub.

## Las tres caras del dominio

El sitio tiene un **estado**, y el estado decide lo que ve una persona que entra
al dominio. Se cambia en el panel, en *Configuración · Sitio*, sin tocar código:

| Estado | Qué ve el público |
|---|---|
| **Coming Soon** | sólo la fachada "Próximamente"; cualquier otra dirección vuelve a `/` |
| **Publicado** | el sitio completo |
| **Mantenimiento** | "Estamos realizando mejoras" (con código 503, para que Google espere) |

El administrador con sesión abierta puede ver el sitio real aunque el público
siga viendo la fachada: botón **Previsualizar sitio**. Una barra abajo le
recuerda qué está mirando y le deja volver.

Mientras el sitio no esté publicado, las páginas salen con `noindex, nofollow`
y `robots.txt` dice que no se indexe nada. Al publicar, eso se revierte solo.

## Los archivos

```
index.php           la puerta: decide fachada, mantenimiento o sitio
admin.php           el panel  (se entra por /admin)
app/marca.php       la empresa: teléfono, correo, dirección y las 4 áreas
app/contenido.php   respaldo, valores, método, proyectos, marcas, clientes
app/cotizacion.php  el formulario de contacto: revisa, guarda y envía
app/marcas.php      las marcas y sus logotipos
app/imagenes.php    las fotos del sitio, cambiables desde el panel
app/sello.php       el logo de IFK estampado sobre una foto
contenido/          lo que se edita desde el panel (marcas, imágenes)
app/sitio.php       el estado del sitio, la sesión y las ayudas comunes
vistas/             fachada y mantenimiento
vistas/sitio/       las páginas del sitio y sus partes comunes
assets/             imágenes, CSS y el JS del sitio
datos/              estado y cotizaciones  (de cada servidor, no viaja a GitHub)
config.php          token de GitHub y clave del panel  (tampoco viaja)
```

Para cambiar un teléfono, un correo o el texto de un área: `app/marca.php`.
Para proyectos, marcas, valores o el método de trabajo: `app/contenido.php`.

## Las páginas

| Dirección | Qué es |
|---|---|
| `/` | portada |
| `/servicios` | las cuatro áreas |
| `/servicios/refrigeracion` `…/climatizacion` `…/ventilacion` `…/reefer` | una página por área |
| `/proyectos` | trabajos realizados |
| `/marcas` | marcas por rubro |
| `/nosotros` | la empresa, sus valores y sus clientes |
| `/contacto` | datos de contacto y solicitud de cotización |
| `/robots.txt` `/sitemap.xml` | se arman solos según el estado del sitio |

## Las marcas y sus logotipos

La lista vive en `contenido/marcas.json` y los archivos en `assets/img/marcas/`.
Las dos cosas viajan a GitHub, así que lo que se sube desde el panel queda
versionado y llega a cualquier otra instalación con `traer`.

En el panel, la caja **Marcas · logotipos** recibe un PNG, JPG o WEBP —el PNG
con fondo transparente es el que mejor queda—, lo deja a un tamaño parejo y lo
asocia a la marca. Si el nombre ya estaba en la lista, le pega el logotipo; si
no, agrega la marca.

En el sitio se ven en tres lugares: una cinta que avanza sola en la portada, la
rejilla por rubro de la página Marcas, y la tarjeta de cada área. Siempre en
blanco y negro, y a color cuando les pasan el mouse. **Una marca sin logotipo
muestra su nombre**, así que la sección nunca se ve a medio llenar.

## Las fotos del sitio

Las 30 fotografías del sitio se cambian desde el panel, sin tocar código.

Cada una tiene una **ranura**: un nombre fijo (`portada`, `nosotros-metodo`,
`area-refrigeracion-foto-1`…) con su descripción, dónde aparece, qué conviene
que muestre y a qué ancho se guarda. El catálogo está en `app/imagenes.php` y
es lo que el panel dibuja en pestañas, una por página.

Mientras nadie suba nada, cada ranura muestra la foto original del repositorio.
Al subir una, el archivo nuevo queda en `assets/img/subidas/` y la ranura se
anota en `contenido/imagenes.json`. **La original no se toca nunca**, por eso
siempre hay un botón para volver a ella.

Las vistas no saben nada de todo esto: las fotos sueltas se piden con
`imagen('ranura')`, y las de las áreas las cambia `imagenes_aplicar()` dentro
de `app/marca.php`, antes de que las vistas lleguen a mirarlas.

Al recibir una foto, el panel la endereza si viene de un celular (las cámaras
guardan la imagen acostada y anotan aparte cómo girarla), la achica al ancho de
la ranura —nunca la agranda— y, **sólo si se marca la casilla**, le estampa el
logo de IFK. Por omisión las fotos van **sin marca de agua**.

El estampado vive aparte, en `app/sello.php`, porque lo usan dos cosas: el
panel y `herramientas/marcar-imagen.php`. Se estampa la versión corta del
logotipo —el copo y el IFK, sin la bajada de texto, que a ese tamaño no se lee
y hace ver borroso todo el logo— y se mira el fondo: sobre una zona clara el
logo va en el azul de la marca con halo blanco; sobre una oscura, blanco con
sombra blanda.

Tanto `contenido/imagenes.json` como `assets/img/subidas/` viajan a GitHub, así
que después de cambiar fotos hay que escribir `subir` en el panel.

## El formulario de cotización

Pide nombre, empresa, RUT, teléfono, correo, área, ubicación, descripción y hasta
tres fotos. Cada solicitud se guarda primero en `datos/cotizaciones/` —un `.json`
con los datos y las fotos al lado— y recién después se manda por correo a la
casilla de la empresa, con las fotos adjuntas. Así, si el hosting no deja enviar
correo, la solicitud igual queda registrada en el servidor.

Trae un campo trampa escondido para los robots de spam, y responde con una
redirección para que al recargar no se mande dos veces.

## El panel

Se abre en `midominio.cl/admin`. La primera vez pide tres cosas —token de
GitHub, clave para entrar después y nombre del repositorio— y con eso escribe
`config.php`, lo protege y enlaza la carpeta con GitHub.

Después, todo se maneja escribiendo una palabra:

| Palabra | Qué hace |
|---|---|
| `estado` | cómo está la carpeta y qué falta por subir |
| `subir` | guarda todo y lo manda a GitHub |
| `subir cambié el logo` | igual, pero deja escrito qué hiciste |
| `traer` | guarda lo tuyo y baja lo nuevo de GitHub |
| `traer github` | deja la carpeta igual que GitHub; lo de aquí queda guardado aparte |
| `instalar` | enlaza esta carpeta con su repositorio; lo crea si no existe |
| `ayuda` | la lista de palabras |
| `salir` | cierra la sesión |

Debajo de la caja de la palabra hay tres secciones que se usan con el mouse:
**Sitio** (coming soon / publicado / mantenimiento), **Marcas · logotipos** y
**Imágenes del sitio**, esta última en pestañas, una por página.

El token necesita permiso `repo` (o, si es *fine-grained*, **Contents: Read and
write** y **Administration: Read and write** para poder crear el repositorio).

`config.php` y `datos/` se quedan en cada servidor: nunca viajan a GitHub. Por
eso un sitio de prueba puede estar publicado y el de verdad en Coming Soon.

## Probar en el computador

```bash
php -S localhost:8788 -t . index.php
```

El último `index.php` es el enrutador: sin él, `/admin` y `/servicios` no
existen. Para entrar al panel en local hace falta un `config.php` con una clave.

## Lo que falta

Están listos la fachada, el panel, el control de estado, el sitio completo, las
marcas y las fotos editables desde el panel. Queda editar **textos** desde el
panel, revisar ahí mismo las cotizaciones que llegan, y la publicación final.
