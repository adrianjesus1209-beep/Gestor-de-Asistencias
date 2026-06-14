<?php
// dashboard.php - Vista unificada para estudiantes, profesores, admin
// Variables esperadas del controlador:
// - $role (string): 'Student', 'Teacher', 'Admin'
// - $user (array): datos del usuario (contiene id, name, cedula, career, semester, photo, etc.)
// Por ahora, se usa simulación.

$role = $role ?? $_GET['role'] ?? $_SESSION['role_simulado'] ?? 'Student';

// Para pruebas, descomentar la línea deseada:
// $role = 'Student';
// $role = 'Teacher';
// $role = 'Admin';

// Simulación de datos del usuario (estudiante)
$studentUser = [
    'id' => 1,
    'name' => 'Juan Pérez',
    'cedula' => 'V-12345678',
    'career_id' => 1,
    'career_name' => 'Ingeniería de Sistemas',
    'semester' => 5,
    'photo' => BASE_URL . '/assets/img/profiles/default-profile.webp'
];

// Simulación de datos del profesor (para futuro)
$teacherUser = [
    'id' => 100,
    'name' => 'Dr. Luis Rojas'
];

// Datos del usuario actual (según rol)
if ($role === 'Student') {
    $currentUser = $studentUser;
} elseif ($role === 'Teacher') {
    $currentUser = $teacherUser;
} else {
    $currentUser = [];
}

// Simulación de materias disponibles (solo para estudiantes)
$availableSubjects = [
    [
        'id' => 1,
        'code' => 'SIS-401',
        'title' => 'Bases de Datos Avanzadas',
        'professor_name' => 'Dr. Luis Rojas',
        'career_id' => 1,
        'semester' => 5,
        'schedule' => [
            ['day' => 'Lunes', 'start' => '08:00', 'end' => '10:00'],
            ['day' => 'Miércoles', 'start' => '08:00', 'end' => '10:00']
        ]
    ],
    [
        'id' => 2,
        'code' => 'SIS-402',
        'title' => 'Ingeniería de Software II',
        'professor_name' => 'MSc. María Fernández',
        'career_id' => 1,
        'semester' => 5,
        'schedule' => [
            ['day' => 'Martes', 'start' => '14:00', 'end' => '16:00'],
            ['day' => 'Jueves', 'start' => '14:00', 'end' => '16:00']
        ]
    ],
    [
        'id' => 3,
        'code' => 'SIS-403',
        'title' => 'Redes de Computadoras II',
        'professor_name' => 'Ing. Carlos Méndez',
        'career_id' => 1,
        'semester' => 5,
        'schedule' => [
            ['day' => 'Lunes', 'start' => '10:00', 'end' => '12:00'],
            ['day' => 'Viernes', 'start' => '10:00', 'end' => '12:00']
        ]
    ],
    [
        'id' => 4,
        'code' => 'SIS-404',
        'title' => 'Diseño de Interfaces',
        'professor_name' => 'Ing. Carlos Méndez',
        'career_id' => 1,
        'semester' => 7,
        'schedule' => [
            ['day' => 'Sábado', 'start' => '10:00', 'end' => '12:00']
        ]
    ],
    // Materia de otra carrera (no debe mostrarse al estudiante de sistemas)
    [
        'id' => 5,
        'code' => 'ADM-201',
        'title' => 'Administración de Empresas',
        'professor_name' => 'Lic. Ana Gómez',
        'career_id' => 4,
        'semester' => 3,
        'schedule' => []
    ]
];

// Filtrar materias de la carrera del estudiante
$filteredSubjects = array_filter($availableSubjects, function($subject) use ($studentUser) {
    return $subject['career_id'] == $studentUser['career_id'];
});

// Agrupar por semestre
$groupedBySemester = [];
foreach ($filteredSubjects as $subject) {
    $sem = $subject['semester'];
    if (!isset($groupedBySemester[$sem])) {
        $groupedBySemester[$sem] = [];
    }
    $groupedBySemester[$sem][] = $subject;
}
ksort($groupedBySemester);
?>

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

            <?php if ($role === 'Student'): ?>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="index.php?route=carnet">Descargar Carnet</a>
                </li>
            <?php elseif ($role === 'Teacher'): ?>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="index.php?route=create-matter">Agregar Materias</a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="index.php?route=attendance-control">Control de Asistencias</a>
                </li>
                <li class="sidebar-item position-relative">
                    <a class="sidebar-link" href="index.php?route=requests-list">
                        Solicitudes de Inscripciones
                        <span id="requestsBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none; font-size: 0.7rem;">0</span>
                    </a>
                </li>
            <?php elseif ($role === 'Admin'): ?>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="index.php?route=manage-users">Agregar Profesores</a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="index.php?route=manage-actives-list">Gestionar Profesores</a>
                </li>
            <?php endif; ?>

            <li class="sidebar-item mt-3">
                <hr class="text-white-50">
                <a class="sidebar-link text-danger fw-bold" href="index.php?route=logout">
                    <i class="bi bi-box-arrow-left me-2"></i>Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="dashboard-container">
    <div class="welcome-card">
        <?php if ($role === 'Student'): ?>
            <h3 class="welcome-title">Bienvenido Estudiante</h3>
            <p class="welcome-text">Estas son las materias disponibles para tu carrera.</p>
        <?php elseif ($role === 'Teacher'): ?>
            <h3 class="welcome-title">Bienvenido Profesor</h3>
            <p class="welcome-text">Gestiona tus materias y control de asistencias.</p>
        <?php else: ?>
            <h3 class="welcome-title">Bienvenido Administrador</h3>
            <p class="welcome-text">Gestione a sus profesores.</p>
        <?php endif; ?>
    </div>
    <button class="btn-menu-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling">
        Menu
    </button>

    <?php if ($role === 'Student'): ?>
        <!-- Sección de materias disponibles para estudiantes (dinámica con JavaScript) -->
        <div class="subjects-section mt-4">
            <?php if (count($groupedBySemester) > 0): ?>
                <?php foreach ($groupedBySemester as $semester => $subjects): ?>
                    <div class="semester-group mb-5">
                        <h4 class="border-bottom pb-2 mb-3"><?= $semester ?>° Semestre</h4>
                        <div class="row g-4" data-semester="<?= $semester ?>">
                            <?php foreach ($subjects as $subject): ?>
                                <div class="col-md-6 col-lg-4 subject-card" data-subject-id="<?= $subject['id'] ?>">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($subject['code']) ?> - <?= htmlspecialchars($subject['title']) ?></h5>
                                            <p class="card-text"><strong>Profesor:</strong> <?= htmlspecialchars($subject['professor_name']) ?></p>
                                            <p class="card-text"><strong>Horario:</strong></p>
                                            <ul class="list-unstyled small">
                                                <?php foreach ($subject['schedule'] as $slot): ?>
                                                    <li><?= $slot['day'] ?>: <?= $slot['start'] ?> - <?= $slot['end'] ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <div class="subject-actions"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info mt-3">No hay materias disponibles para tu carrera en este momento.</div>
            <?php endif; ?>
        </div>
    <?php elseif ($role === 'Teacher'): ?>
        <!-- Panel de bienvenida para profesores -->
        <div class="teacher-panel mt-4">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-person-badge display-1 text-primary"></i>
                    <h4 class="mt-3">Panel de Gestión</h4>
                    <p class="text-muted">Utilice el menú lateral para:</p>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-plus-circle-fill text-success me-2"></i> Agregar nuevas materias</li>
                        <li><i class="bi bi-people-fill text-info me-2"></i> Control de asistencias</li>
                        <li><i class="bi bi-check-circle-fill text-warning me-2"></i> Revisar solicitudes de inscripción</li>
                    </ul>
                    <a href="index.php?route=create-matter" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-lg me-2"></i>Crear materia ahora
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($role === 'Student'): ?>
    <script>
        // Variables globales necesarias para student-dashboard.js
        const currentStudent = {
            id: <?= $currentUser['id'] ?>,
            name: "<?= addslashes($currentUser['name']) ?>",
            cedula: "<?= $currentUser['cedula'] ?>",
            career: "<?= addslashes($currentUser['career_name']) ?>",
            semester: <?= $currentUser['semester'] ?>,
            photo: "<?= $currentUser['photo'] ?>"
        };
        const allSubjectsData = <?= json_encode($availableSubjects) ?>;
    </script>
    <script src="<?= BASE_URL ?>/js/students-dashboard.js"></script>
<?php endif; ?>

<?php if ($role === 'Teacher'): ?>
    <script src="<?= BASE_URL ?>/js/teacher-badge.js"></script>
<?php endif; ?>