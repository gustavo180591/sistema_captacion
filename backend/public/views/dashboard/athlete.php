<?php
$title = "Dashboard Atleta";
require_once __DIR__ . '/../layouts/main.php';
?>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="icon">📊</div>
        <h3>Mi Progreso</h3>
        <p>Última evaluación: <?php echo $lastEvaluation ?? 'No disponible'; ?></p>
        <a href="/athlete/progress" class="btn btn-primary">Ver Progreso</a>
    </div>

    <div class="dashboard-card">
        <div class="icon">📅</div>
        <h3>Próximas Evaluaciones</h3>
        <p>Evaluaciones programadas: <?php echo $scheduledEvaluations ?? 0; ?></p>
        <a href="/athlete/evaluations" class="btn btn-primary">Ver Calendario</a>
    </div>

    <div class="dashboard-card">
        <div class="icon">📝</div>
        <h3>Historial</h3>
        <p>Total de evaluaciones: <?php echo $totalEvaluations ?? 0; ?></p>
        <a href="/athlete/history" class="btn btn-primary">Ver Historial</a>
    </div>
</div>

<div class="dashboard-section">
    <h2>Últimos Resultados</h2>
    <div class="results-list">
        <?php if (!empty($recentResults)): ?>
            <?php foreach ($recentResults as $result): ?>
                <div class="result-item">
                    <div class="result-info">
                        <h4><?php echo $result['evaluation_type']; ?></h4>
                        <p>Fecha: <?php echo $result['date']; ?></p>
                        <p>Evaluador: <?php echo $result['evaluator_name']; ?></p>
                    </div>
                    <div class="result-score">
                        <span class="score"><?php echo $result['score']; ?></span>
                        <span class="score-label">Puntos</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay resultados disponibles</p>
        <?php endif; ?>
    </div>
</div>

<div class="dashboard-section">
    <h2>Recomendaciones</h2>
    <div class="recommendations-list">
        <?php if (!empty($recommendations)): ?>
            <?php foreach ($recommendations as $recommendation): ?>
                <div class="recommendation-item">
                    <h4><?php echo $recommendation['title']; ?></h4>
                    <p><?php echo $recommendation['description']; ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay recomendaciones disponibles</p>
        <?php endif; ?>
    </div>
</div> 