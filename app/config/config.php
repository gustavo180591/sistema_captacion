<?php
// Load environment variables from .env file
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Database configuration
define('DB_HOST', 'db');
define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'sistema_captacion');
define('DB_USER', getenv('MYSQL_USER') ?: 'user');
define('DB_PASS', getenv('MYSQL_PASSWORD') ?: 'pass');

// Application paths
define('APP_ROOT', dirname(__DIR__, 2));
define('URL_ROOT', getenv('APP_URL') ?: 'http://localhost:8000');

// Application settings
define('APP_NAME', getenv('APP_NAME') ?: 'Sistema de Captación');
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: true, FILTER_VALIDATE_BOOLEAN));

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Lax');

// Timezone
date_default_timezone_set('America/Argentina/Buenos_Aires');
