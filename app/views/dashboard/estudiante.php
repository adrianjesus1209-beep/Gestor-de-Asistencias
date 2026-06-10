<div class="custom-sidebar offcanvas offcanvas-start" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="offcanvasScrolling" aria-labelledby="offcanvasScrollingLabel">
    <div class="sidebar-header">
        <img src="<?= BASE_URL ?>/assets/img/logos/logo_unefa.png" alt="Unefa" class="sidebar-logo">
        <h5 class="sidebar-brand-title" id="offcanvasScrollingLabel">
            <span class="brand-highlight">UNEFA</span><br>Excelencia Educativa
        </h5>
        <button type="button" class="btn-close-custom" data-bs-dismiss="offcanvas" aria-label="Close">×</button>
    </div>

    <div class="sidebar-body">
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a class="sidebar-link" href="index.php?route=profile">Mi perfil</a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="index.php?qr">Descargar Carnet</a>
            </li>
            <li class="sidebar-item mt-5">
                <hr class="text-white-50">
                <a class="sidebar-link text-danger fw-bold" href="index.php?logout">
                    <i class="bi bi-box-arrow-left me-2"></i>Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="dashboard-container">
    <div class="welcome-card">
        <h3 class="welcome-title">Bienvenido Estudiante</h3>
            <p class="welcome-text">
                Panel de control del sistema de asistencia QR.
            </p>
            <div class="mt-4">
                <a href="index.php?logout" class="btn btn-danger px-4 py-2 rounded-pill fw-bold">
                    <i class="bi bi-box-arrow-left me-2"></i>Cerrar Sesión
                </a>
            </div>
    </div>

        <button class="btn-menu-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling">
            Menu
        </button>
</div>