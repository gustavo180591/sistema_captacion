<?php
$title = "Dashboard Administrador";
require_once __DIR__ . '/../layouts/main.php';
?>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="icon">👥</div>
        <h3>Usuarios</h3>
        <p>Total de usuarios registrados: <?php echo $totalUsers ?? 0; ?></p>
        <a href="/users" class="btn btn-primary">Gestionar Usuarios</a>
    </div>

    <div class="dashboard-card">
        <div class="icon">📊</div>
        <h3>Evaluaciones</h3>
        <p>Evaluaciones pendientes: <?php echo $pendingEvaluations ?? 0; ?></p>
        <a href="/evaluations" class="btn btn-primary">Ver Evaluaciones</a>
    </div>

    <div class="dashboard-card">
        <div class="icon">🏃</div>
        <h3>Atletas</h3>
        <p>Atletas registrados: <?php echo $totalAthletes ?? 0; ?></p>
        <a href="/athletes" class="btn btn-primary">Gestionar Atletas</a>
    </div>

    <div class="dashboard-card">
        <div class="icon">📈</div>
        <h3>Reportes</h3>
        <p>Generar reportes y estadísticas</p>
        <a href="/reports" class="btn btn-primary">Ver Reportes</a>
    </div>
</div>

<div class="dashboard-section">
    <h2>Actividad Reciente</h2>
    <div class="activity-list">
        <?php if (!empty($recentActivity)): ?>
            <?php foreach ($recentActivity as $activity): ?>
                <div class="activity-item">
                    <span class="activity-time"><?php echo $activity['time']; ?></span>
                    <span class="activity-description"><?php echo $activity['description']; ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay actividad reciente</p>
        <?php endif; ?>
    </div>
</div> 