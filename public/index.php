<?php
// Start session
session_start();

// Define base path
$basePath = dirname(__DIR__);

// Load configuration
require $basePath . '/app/config/config.php';

// Load autoloader
require $basePath . '/app/config/autoload.php';

// Load routes
require $basePath . '/app/config/routes.php';

// Initialize the router
$router = new Router();
$router->dispatch();
