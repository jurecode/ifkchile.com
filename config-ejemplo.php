<?php
/**
 * Configuración del panel.
 *
 * Copia este archivo como "config.php" y completa los dos primeros datos.
 * config.php no se sube a GitHub: el token se queda sólo en este servidor.
 */

return [

    // Token de GitHub.
    // GitHub → Settings → Developer settings → Personal access tokens.
    // Permiso necesario: "repo" (o Contents: Read and write, y Administration
    // si quieres que el panel pueda crear el repositorio solo).
    'token' => 'pega-aqui-tu-token',

    // Clave para entrar al panel (invéntala tú).
    'clave' => 'cambia-esta-clave',


    /* Lo de abajo casi nunca hay que tocarlo */

    // Repositorio. Vacío = usa el nombre del dominio (midominio.cl).
    // También sirve "midominio.cl" o la dirección completa de GitHub.
    'repo'  => '',

    // Rama.
    'rama'  => 'main',

];
