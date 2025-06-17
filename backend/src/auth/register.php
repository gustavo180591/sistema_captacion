<?php
require_once __DIR__ . '/../utils/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDbConnection();
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol_id = $_POST['rol_id'];

    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $nombre, $apellido, $email, $password, $rol_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header("Location: login.php");
}
?> 