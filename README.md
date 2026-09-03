# Base

Dos archivos y listo:

- `config.php` — token de GitHub, dirección del repositorio, rama y clave.
- `panel.php` — una sola caja: escribes una palabra y se ejecuta.

## Instalar

1. Copia `config-ejemplo.php` como `config.php` y completa los cuatro datos.
2. Entra a `tudominio.com/panel.php` y escribe la clave.

## Palabras

| Palabra | Qué hace |
|---|---|
| `estado` | cómo está la carpeta y qué falta por subir |
| `subir` | guarda todo y lo manda a GitHub |
| `subir cambié el logo` | igual, pero deja escrito qué hiciste |
| `traer` | guarda lo tuyo y baja lo nuevo de GitHub |
| `traer github` | deja la carpeta igual que GitHub; lo de aquí queda guardado aparte |
| `ayuda` | la lista de palabras |
| `salir` | cierra la sesión |

## Para mudarla a otro proyecto

Copia `panel.php`, `config-ejemplo.php` y la línea `config.php` del `.gitignore`.
Nada más depende del sitio.
