<?php
// Definir las rutas base
define('BASE_URL', '/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('VIEWS_PATH', __DIR__ . '/../../public/views/');

// Función para redirigir
function redirect($path) {
    header("Location: " . BASE_URL . ltrim($path, '/'));
    exit;
}

// Función para incluir vistas
function view($name, $data = []) {
    extract($data);
    require_once VIEWS_PATH . $name . '.php';
}
?> 