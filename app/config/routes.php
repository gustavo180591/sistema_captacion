<?php
// Import Router class
use Core\Router;

// Create a new Router instance
$router = new Router();

// Define routes

// Authentication routes
$router->add('login', 'AuthController@login', ['GET', 'POST']);
$router->add('logout', 'AuthController@logout');
$router->add('register', 'AuthController@register', ['GET', 'POST']);
$router->add('forgot-password', 'AuthController@forgotPassword', ['GET', 'POST']);
$router->add('reset-password/{token}', 'AuthController@resetPassword', ['GET', 'POST']);

// Dashboard route
$router->add('', 'DashboardController@index', 'GET', ['auth']);

// Admin routes
$router->group('admin', function($router) {
    // Dashboard
    $router->add('', 'Admin\DashboardController@index', 'GET', ['auth', 'role:Administrador']);
    
    // Users management
    $router->add('users', 'Admin\UserController@index', 'GET', ['auth', 'role:Administrador']);
    $router->add('users/create', 'Admin\UserController@create', ['GET', 'POST'], ['auth', 'role:Administrador']);
    $router->add('users/edit/{id}', 'Admin\UserController@edit', ['GET', 'POST'], ['auth', 'role:Administrador']);
    $router->add('users/delete/{id}', 'Admin\UserController@delete', 'POST', ['auth', 'role:Administrador']);
    
    // Zones management
    $router->add('zonas', 'Admin\ZonaController@index', 'GET', ['auth', 'role:Administrador']);
    $router->add('zonas/create', 'Admin\ZonaController@create', ['GET', 'POST'], ['auth', 'role:Administrador']);
    $router->add('zonas/edit/{id}', 'Admin\ZonaController@edit', ['GET', 'POST'], ['auth', 'role:Administrador']);
    $router->add('zonas/delete/{id}', 'Admin\ZonaController@delete', 'POST', ['auth', 'role:Administrador']);
    
    // Centers management
    $router->add('centros', 'Admin\CentroController@index', 'GET', ['auth', 'role:Administrador']);
    $router->add('centros/create', 'Admin\CentroController@create', ['GET', 'POST'], ['auth', 'role:Administrador']);
    $router->add('centros/edit/{id}', 'Admin\CentroController@edit', ['GET', 'POST'], ['auth', 'role:Administrador']);
    $router->add('centros/delete/{id}', 'Admin\CentroController@delete', 'POST', ['auth', 'role:Administrador']);
}, ['auth', 'role:Administrador']);

// Evaluator routes
$router->group('evaluador', function($router) {
    // Dashboard
    $router->add('', 'Evaluador\DashboardController@index', 'GET', ['auth', 'role:Evaluador']);
    
    // Athletes management
    $router->add('atletas', 'Evaluador\AtletaController@index', 'GET', ['auth', 'role:Evaluador']);
    $router->add('atletas/registrar', 'Evaluador\AtletaController@create', ['GET', 'POST'], ['auth', 'role:Evaluador']);
    $router->add('atletas/editar/{id}', 'Evaluador\AtletaController@edit', ['GET', 'POST'], ['auth', 'role:Evaluador']);
    $router->add('atletas/ver/{id}', 'Evaluador\AtletaController@view', 'GET', ['auth', 'role:Evaluador']);
    
    // Evaluation sessions
    $router->add('sesiones', 'Evaluador\SesionController@index', 'GET', ['auth', 'role:Evaluador']);
    $router->add('sesiones/crear', 'Evaluador\SesionController@create', ['GET', 'POST'], ['auth', 'role:Evaluador']);
    $router->add('sesiones/editar/{id}', 'Evaluador\SesionController@edit', ['GET', 'POST'], ['auth', 'role:Evaluador']);
    $router->add('sesiones/eliminar/{id}', 'Evaluador\SesionController@delete', 'POST', ['auth', 'role:Evaluador']);
    
    // Tests management
    $router->add('pruebas', 'Evaluador\PruebaController@index', 'GET', ['auth', 'role:Evaluador']);
    $router->add('pruebas/realizar/{sesion_id}/{atleta_id}', 'Evaluador\PruebaController@realizar', ['GET', 'POST'], ['auth', 'role:Evaluador']);
    $router->add('pruebas/resultados/{sesion_id}', 'Evaluador\PruebaController@resultados', 'GET', ['auth', 'role:Evaluador']);
    $router->add('pruebas/exportar/{sesion_id}', 'Evaluador\PruebaController@exportar', 'GET', ['auth', 'role:Evaluador']);
    
    // Reports
    $router->add('reportes', 'Evaluador\ReporteController@index', 'GET', ['auth', 'role:Evaluador']);
    $router->add('reportes/generar', 'Evaluador\ReporteController@generar', 'POST', ['auth', 'role:Evaluador']);
}, ['auth', 'role:Evaluador']);

// Athlete routes
$router->group('atleta', function($router) {
    // Dashboard
    $router->add('', 'Atleta\DashboardController@index', 'GET', ['auth', 'role:Atleta']);
    
    // Profile
    $router->add('perfil', 'Atleta\PerfilController@index', 'GET', ['auth', 'role:Atleta']);
    $router->add('perfil/editar', 'Atleta\PerfilController@edit', ['GET', 'POST'], ['auth', 'role:Atleta']);
    
    // Results
    $router->add('resultados', 'Atleta\ResultadoController@index', 'GET', ['auth', 'role:Atleta']);
    $router->add('resultados/ver/{id}', 'Atleta\ResultadoController@view', 'GET', ['auth', 'role:Atleta']);
}, ['auth', 'role:Atleta']);

// API routes
$router->group('api', function($router) {
    // Authentication
    $router->add('login', 'Api\AuthController@login', 'POST');
    $router->add('logout', 'Api\AuthController@logout', 'POST', ['auth']);
    
    // Data endpoints
    $router->add('centros', 'Api\CentroController@index', 'GET', ['auth']);
    $router->add('atletas', 'Api\AtletaController@index', 'GET', ['auth']);
    $router->add('pruebas', 'Api\PruebaController@index', 'GET', ['auth']);
    
    // Test results
    $router->add('resultados/guardar', 'Api\ResultadoController@store', 'POST', ['auth', 'role:Evaluador']);
    $router->add('resultados/exportar', 'Api\ResultadoController@export', 'GET', ['auth', 'role:Evaluador']);
}, ['api']);

// Error routes
$router->add('404', 'ErrorController@notFound');
$router->add('403', 'ErrorController@forbidden');
$router->add('500', 'ErrorController@serverError');

// Set 404 as default route
$router->setDefault('ErrorController@notFound');
