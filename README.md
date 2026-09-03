# Base web

Base para cualquier dominio del hosting. Un solo archivo de panel: escribes una
palabra y el sitio sube o baja de GitHub, sin FTP y sin consola.

## Un sitio que ya existe, sin nada de git

Es el caso más común y son tres pasos:

1. Sube `panel.php` y `config-ejemplo.php` a la carpeta del dominio
   (`public_html`, o la del subdominio). Renombra la copia a `config.php`.
2. Abre `config.php` y pon dos cosas: el **token** de GitHub y una **clave** tuya.
3. Entra a `midominio.cl/panel.php`, escribe la clave, y luego `instalar`.

Eso crea el repositorio en GitHub —privado y con el nombre del dominio— y sube
el sitio tal como está. No hay que crear nada a mano en GitHub.

Si el repositorio ya existía y tenía cosas, `instalar` enlaza la carpeta sin
pisar ningún archivo tuyo y te deja listo para `subir`.

## Un dominio nuevo, partiendo de esta plantilla

1. Botón **Use this template** → repositorio nuevo con el nombre del dominio.
2. Sube `panel.php` y `config.php` a la carpeta del dominio.
3. En el panel, `traer`: el resto del sitio baja solo.

## config.php

```php
<?php
return [
    'token' => 'el-token-de-github',
    'clave' => 'la-clave-del-panel',
];
```

El resto es opcional: `repo` vacío usa el nombre del dominio (también acepta
`midominio.cl` o la dirección completa), y `rama` es `main` si no dices otra cosa.

## Palabras

| Palabra | Qué hace |
|---|---|
| `estado` | cómo está la carpeta y qué falta por subir |
| `instalar` | deja este sitio guardado en GitHub; si el repositorio no existe, lo crea |
| `subir` | guarda todo y lo manda a GitHub |
| `subir cambié el logo` | igual, pero deja escrito qué hiciste |
| `traer` | guarda lo tuyo y baja lo nuevo de GitHub |
| `traer github` | deja la carpeta igual que GitHub; lo de aquí queda guardado aparte |
| `ayuda` | la lista de palabras |
| `salir` | cierra la sesión |

## El token

Sirve un token clásico con permiso `repo`. Si usas uno *fine-grained*, necesita
**Contents: Read and write** y, para que el panel pueda crear repositorios solo,
**Administration: Read and write**.

## Lo que el panel cuida solo

- El token nunca aparece en pantalla ni viaja a GitHub: vive únicamente en el
  `config.php` de cada servidor, y el panel se asegura de que quede ignorado.
- Si GitHub va más adelante, no deja subir encima: primero manda a `traer`.
- Si las dos partes cambiaron lo mismo, se detiene sin romper nada y ofrece
  `traer github`, que guarda lo del servidor en una rama `respaldo-fecha`.
- Al instalar, no baja una portada que tape la que el sitio ya tiene.

## Un repositorio por dominio

Cada sitio necesita el suyo: si dos dominios apuntaran al mismo, el `subir` de
uno pisaría el sitio del otro. Esta plantilla es sólo el punto de partida.
