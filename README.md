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

Están listos la fachada, el panel, el control de estado y el sitio completo.
Queda el CMS —editar textos, proyectos y fotos desde el panel, y revisar ahí
mismo las cotizaciones que llegan— y la publicación final.
