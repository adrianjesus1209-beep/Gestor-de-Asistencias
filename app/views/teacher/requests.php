<?php
// requests-list.php - Lista de solicitudes de inscripción (vista del profesor)
?>
<div class="container py-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-bell-fill text-warning me-2"></i> Solicitudes de Inscripción Pendientes</span>
            <a href="index.php?route=dashboard" class="btn btn-outline-secondary mt-2">
                <i class="bi bi-arrow-return-left me-2"></i>Volver al Dashboard
            </a>
        </div>
        <div class="card-body">
            <div id="requests-container" class="row g-4">
                <div class="col-12 text-center text-muted">No hay solicitudes pendientes.</div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/requests.js"></script>