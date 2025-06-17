<?php
$title = "Dashboard Evaluador";
require_once __DIR__ . '/../layouts/main.php';
?>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="icon">📝</div>
        <h3>Evaluaciones Pendientes</h3>
        <p>Evaluaciones por realizar: <?php echo $pendingEvaluations ?? 0; ?></p>
        <a href="/evaluations/pending" class="btn btn-primary">Ver Pendientes</a>
    </div>

    <div class="dashboard-card">
        <div class="icon">✅</div>
        <h3>Evaluaciones Completadas</h3>
        <p>Total de evaluaciones realizadas: <?php echo $completedEvaluations ?? 0; ?></p>
        <a href="/evaluations/completed" class="btn btn-primary">Ver Historial</a>
    </div>

    <div class="dashboard-card">
        <div class="icon">📊</div>
        <h3>Estadísticas</h3>
        <p>Ver estadísticas de evaluaciones</p>
        <a href="/evaluations/stats" class="btn btn-primary">Ver Estadísticas</a>
    </div>
</div>

<div class="dashboard-section">
    <h2>Próximas Evaluaciones</h2>
    <div class="evaluation-list">
        <?php if (!empty($upcomingEvaluations)): ?>
            <?php foreach ($upcomingEvaluations as $evaluation): ?>
                <div class="evaluation-item">
                    <div class="evaluation-info">
                        <h4><?php echo $evaluation['athlete_name']; ?></h4>
                        <p>Fecha: <?php echo $evaluation['date']; ?></p>
                        <p>Tipo: <?php echo $evaluation['type']; ?></p>
                    </div>
                    <div class="evaluation-actions">
                        <a href="/evaluations/view/<?php echo $evaluation['id']; ?>" class="btn btn-primary">Ver Detalles</a>
                        <a href="/evaluations/start/<?php echo $evaluation['id']; ?>" class="btn btn-secondary">Iniciar Evaluación</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay evaluaciones programadas</p>
        <?php endif; ?>
    </div>
</div> 