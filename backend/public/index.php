<?php
echo "Bienvenido a la Plataforma Nacional de Evaluación de Talento Deportivo";

$host = getenv('DB_HOST') ?: 'db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'root';
$dbname = getenv('DB_NAME') ?: 'sistema_captacion';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Conexión fallida: " . $conn->connect_error);
    }
    echo "<br>Conexión a la base de datos exitosa.";
} catch (Exception $e) {
    echo "<br>Error: " . $e->getMessage();
}
?>
