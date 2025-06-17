<?php
session_start();
require_once __DIR__ . '/../utils/db.php';
require_once __DIR__ . '/../config/routes.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDbConnection();
    
    // Obtener y limpiar datos
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $rol_id = (int)$_POST['rol_id'];

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($rol_id)) {
        $error = "Todos los campos son obligatorios";
    } elseif ($password !== $password_confirm) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no es válido";
    } else {
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "El email ya está registrado";
        } else {
            // Hash de la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar usuario
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $nombre, $apellido, $email, $password_hash, $rol_id);
            
            if ($stmt->execute()) {
                $success = "Usuario registrado correctamente";
                // Redirigir al login después de 2 segundos
                header("refresh:2;url=/auth/login.php");
            } else {
                $error = "Error al registrar el usuario";
            }
        }
        $stmt->close();
    }
    $conn->close();
}

// Incluir la vista
require_once __DIR__ . '/../../public/views/register.php';
?> 