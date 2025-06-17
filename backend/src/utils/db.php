<?php
function getDbConnection() {
    $host = getenv('DB_HOST') ?: 'db';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: 'root';
    $dbname = getenv('DB_NAME') ?: 'sistema_captacion';
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }
    return $conn;
}
?> 