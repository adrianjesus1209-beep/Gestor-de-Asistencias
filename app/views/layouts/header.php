<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNEFA - Excelencia Educativa Abierta al Pueblo</title>
    <link href="<?= BASE_URL ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/estilos.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/login.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/registro.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/dashboard.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/lista-estudiantes.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/icons/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<div class="barra-superior">
    <img src="<?= BASE_URL ?>/assets/img/banners/cintilla.jpg" alt="Cintilla Gubernamental" class="barra-superior-cintilla">
</div>

<nav class="navbar navbar-expand-lg barra-navegacion-propia">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-3" href="index.php">
            <img src="<?= BASE_URL ?>/assets/img/logos/logo_unefa.png" alt="Logo UNEFA" class="rounded-circle logo-header">
            <div class="d-none d-md-block">
                <div class="fw-bold lh-1 unefa-sigla">UNEFA</div>
                <div class="text-muted lh-1 unefa-eslogan">Excelencia Educativa</div>
            </div>
        </a>

        <input type="checkbox" id="toggleMenuHeader" hidden>
        <label class="navbar-toggler border-0 shadow-none cursor-pointer" for="toggleMenuHeader">
            <i class="bi bi-list-task fs-4 icono-nav-toggler"></i>
        </label>

        <div class="collapse navbar-collapse justify-content-end" id="mainNav">
            <ul class="navbar-nav align-items-center gap-1">
                <li class="nav-item"><a class="enlace-navegacion nav-link" href="index.php"><i class="bi bi-house-fill me-1"></i>Inicio</a></li>
<?php if ($authPayload): ?>
                    <?php 
                        $dashboardLink = 'index.php?dashboard_estudiante';
                        if ($authPayload['role'] === 'Admin') $dashboardLink = 'index.php?dashboard_admin';
                        if ($authPayload['role'] === 'Teacher') $dashboardLink = 'index.php?dashboard_profesor';
                    ?>
                    <li class="nav-item">
                        <a href="<?= $dashboardLink ?>" class="boton-primario-personalizado btn btn-sm px-4 py-2 rounded-pill fw-bold">
                            <i class="bi bi-speedometer2 me-2"></i>Ir al Dashboard
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="index.php?logout" class="btn btn-outline-danger btn-sm px-3 py-2 rounded-pill fw-bold">
                            <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3">
                        <a href="index.php?login" class="boton-primario-personalizado btn btn-sm px-4 py-2 rounded-pill fw-bold">
                            <i class="bi bi-person-lock me-2"></i>Entrar al Sistema
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (!$authPayload): ?>
<div class="redes-sociales-flotantes">
    <a href="https://x.com/Unefa_VEN?t=FhK2uslLRmCrIa9sjQIEEA&s=09" target="_blank" class="red-x"><img src="<?= BASE_URL ?>/assets/img/redes/X-Twitter.webp" alt="X"></a>
    <a href="https://www.instagram.com/unefa_ve?igsh=MXJvcjFkMXJ5Z3NzMg%3D%3D" target="_blank" class="red-ig"><img src="<?= BASE_URL ?>/assets/img/redes/Instagram.webp" alt="Instagram"></a>
    <a href="https://www.facebook.com/share/1BKuAut1dg/" target="_blank" class="red-fb"><img src="<?= BASE_URL ?>/assets/img/redes/Facebook.webp" alt="Facebook"></a>
    <a href="https://www.youtube.com/channel/UCU1YFZgV-ENQkfHRspsK9nA" target="_blank" class="red-yt"><img src="<?= BASE_URL ?>/assets/img/redes/Youtube.webp" alt="YouTube"></a>
    <a href="https://www.tiktok.com/@unefa_ve?_t=8iwcWCLFEAA&_r=1" target="_blank" class="red-tk"><img src="<?= BASE_URL ?>/assets/img/redes/Tiktok.webp" alt="TikTok"></a>
</div>
<?php endif; ?>



