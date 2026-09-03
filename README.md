# Base web

Un archivo, `panel.php`. Lo subes a la carpeta de un dominio, lo abres en el
navegador, y él solo escribe su configuración, crea el repositorio en GitHub y
guarda el sitio. Después, todo se maneja escribiendo una palabra.

## Instalar en un dominio

1. Sube `panel.php` a la carpeta del dominio (`public_html`, o la del subdominio).
2. Abre `midominio.cl/panel.php`.
3. Te pide tres cosas: el **token** de GitHub, una **clave** para entrar después,
   y el **nombre del repositorio** (vacío = el nombre del dominio).
4. Botón *Guardar y clonar*.

Eso escribe `config.php` con permisos cerrados, agrega al `.htaccess` la regla
que impide abrirlo desde el navegador, crea el repositorio —privado, si no
existía— y sube el sitio tal como está en el servidor.

Si el repositorio ya tenía contenido, en vez de subir enlaza la carpeta sin
pisar ningún archivo tuyo y te deja listo para escribir `subir`.

No hace falta el Git Version Control de cPanel, ni crear nada a mano en GitHub.

## Palabras

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

## El token

Uno clásico con permiso `repo` sirve tal cual. Si usas *fine-grained*, necesita
**Contents: Read and write** y, para que el panel pueda crear repositorios,
**Administration: Read and write**.

## config.php

Lo escribe el panel; sólo hay que tocarlo para cambiar algo:

```php
<?php
return [
    'token' => 'el-token-de-github',
    'clave' => 'la-clave-del-panel',
    'repo'  => 'midominio.cl',
];
```

`rama` es opcional y vale `main` si no se dice otra cosa. Este archivo se queda
en cada servidor: nunca viaja a GitHub.

## Lo que el panel cuida solo

- El token no aparece en pantalla ni entra al repositorio.
- Si GitHub va más adelante, no deja subir encima: primero manda a `traer`.
- Si las dos partes cambiaron lo mismo, se detiene sin romper nada y ofrece
  `traer github`, que guarda lo del servidor en una rama `respaldo-fecha`.
- Al instalar, no baja una portada que tape la que el sitio ya tiene.

## Un repositorio por dominio

Cada sitio necesita el suyo: si dos dominios apuntaran al mismo, el `subir` de
uno pisaría el sitio del otro.
