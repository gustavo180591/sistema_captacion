<?php
// Autoloader function
spl_autoload_register(function ($className) {
    // Convert namespace to full file path
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
    $filePath = dirname(__DIR__) . '/app/' . $file;
    
    // Check if the file exists
    if (file_exists($filePath)) {
        require $filePath;
        return true;
    }
    
    return false;
});

// Load helper functions
require dirname(__DIR__) . '/app/helpers/functions.php';
