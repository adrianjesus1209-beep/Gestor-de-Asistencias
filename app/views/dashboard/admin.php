<div class="dashboard-container">
    <div class="welcome-card admin-special text-center py-5">
        <div class="mb-4">
            <i class="bi bi-shield-lock-fill display-1 text-primary"></i>
        </div>
        <h2 class="welcome-title fw-bold">Panel de Administración</h2>
        <p class="welcome-text text-muted fs-5">
            Bienvenido al centro de control maestro del sistema UNEFA QR.
        </p>
        
        <div class="row g-4 mt-4 justify-content-center">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 transition-hover">
                    <i class="bi bi-person-badge fs-1 text-primary mb-3"></i>
                    <h5 class="fw-bold">Gestionar Usuarios</h5>
                    <p class="small text-muted">Control de alumnos y docentes.</p>
                    <button class="btn btn-primary w-100 rounded-pill">Entrar</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 transition-hover">
                    <i class="bi bi-journals fs-1 text-success mb-3"></i>
                    <h5 class="fw-bold">Secciones</h5>
                    <p class="small text-muted">Apertura y cierre de clases.</p>
                    <button class="btn btn-success w-100 rounded-pill text-white">Gestionar</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 transition-hover">
                    <i class="bi bi-graph-up-arrow fs-1 text-warning mb-3"></i>
                    <h5 class="fw-bold">Reportes</h5>
                    <p class="small text-muted">Estadísticas de asistencia.</p>
                    <button class="btn btn-warning w-100 rounded-pill text-white">Ver</button>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <a href="index.php?logout" class="btn btn-outline-danger px-5 py-2 rounded-pill fw-bold">
                <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión Segura
            </a>
        </div>
    </div>
</div>

<style>
    .admin-special {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 2rem;
    }
    .transition-hover {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .transition-hover:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
</style>
