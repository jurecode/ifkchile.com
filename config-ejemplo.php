<?php
/**
 * Configuración del panel.
 *
 * Copia este archivo como "config.php" y completa los datos.
 * config.php nunca se sube a GitHub (está en .gitignore), así el token
 * se queda solamente en el servidor donde lo escribes.
 */

return [

    // Token de GitHub.
    // GitHub → Settings → Developer settings → Personal access tokens.
    // Permiso necesario: "repo" (o Contents: Read and write).
    'token' => 'pega-aqui-tu-token',

    // Dirección del repositorio.
    'repo'  => 'https://github.com/usuario/proyecto.git',

    // Rama con la que trabajas.
    'rama'  => 'main',

    // Clave para entrar al panel (invéntala tú).
    'clave' => 'cambia-esta-clave',

];
