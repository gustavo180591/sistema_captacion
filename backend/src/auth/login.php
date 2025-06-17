<?php
session_start();
require_once __DIR__ . '/../utils/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDbConnection();
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, nombre, apellido, password, rol_id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_role'] = $user['rol_id'];
        header("Location: dashboard.php");
    } else {
        $error = "Email o contraseña incorrectos";
    }
    $stmt->close();
    $conn->close();
}
?> 