<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Sistema de Captación'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <nav class="main-nav">
            <div class="nav-brand">
                <a href="/dashboard.php">Sistema de Captación</a>
            </div>
            <div class="nav-menu">
                <?php if (isset($_SESSION['user_role'])): ?>
                    <?php if ($_SESSION['user_role'] == 1): // Administrador ?>
                        <a href="/admin/zonas.php"><i class="fas fa-map-marker-alt"></i> Zonas</a>
                        <a href="/admin/centros.php"><i class="fas fa-building"></i> Centros</a>
                        <a href="/admin/evaluadores.php"><i class="fas fa-user-tie"></i> Evaluadores</a>
                    <?php elseif ($_SESSION['user_role'] == 2): // Evaluador ?>
                        <a href="/evaluador/atletas.php"><i class="fas fa-users"></i> Atletas</a>
                        <a href="/evaluador/sesiones.php"><i class="fas fa-clipboard-list"></i> Sesiones</a>
                    <?php elseif ($_SESSION['user_role'] == 3): // Atleta ?>
                        <a href="/atleta/resultados.php"><i class="fas fa-chart-line"></i> Mis Resultados</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="nav-user">
                <?php if (isset($_SESSION['user_name'])): ?>
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="/auth/logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php echo $content ?? ''; ?>
    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> Sistema de Captación de Talento Deportivo</p>
    </footer>
</body>
</html> 