<?php
// profile.php - Vista de perfil de usuario
// Variables esperadas del controlador:

// - $user['first_name']
// - $user['last_name'] (y opcionalmente second_name, second_lastname)
// - $user['id_number'], $user['email']
// - $user['career'], $user['semester'] (para estudiantes)
// - $user['profile_pic'] (ruta de la imagen)
// - $user['role'] (Student, Teacher)
// Datos de ejemplo para maqueta:

$user = [
    'role'            => 'Admin', // 'Student', 'Teacher', 'Admin'
    'first_name'      => 'Juan',
    'second_name'     => 'Alejandro',
    'last_name'       => 'Pérez',
    'second_lastname' => 'Rodríguez',
    'id_number'       => 'V-12345678',
    'email'           => 'juan.perez@unefa.edu.ve',
    'career'          => 'Ingeniería de Sistemas',
    'semester'        => '6to Semestre',
    'profile_pic'     => 'default-profile.webp'
];
// Construir nombre completo
$fullName = trim($user['first_name'] . ' ' . ($user['second_name'] ?? '') . ' ' . $user['last_name'] . ' ' . ($user['second_lastname'] ?? ''));
?>
<div class="container py-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm text-center p-3">
                <img src="<?= BASE_URL ?>/assets/img/profiles/<?= $user['profile_pic'] ?>"
                    alt="Foto de perfil"
                    class="rounded-circle mx-auto d-block"
                    style="width: 180px; height: 180px; object-fit: cover;">
                <h4 class="mt-3"><?= htmlspecialchars($fullName) ?></h4>

                <?php if ($user['role'] === 'Teacher'): ?>
                    <p class="text-muted">Profesor</p>
                <?php elseif ($user['role'] === 'Admin'): ?>
                    <p class="text-muted">Administrador</p>
                <?php else: ?>
                    <p class="text-muted">Estudiante de <?= htmlspecialchars($user['career']) ?></p>
                <?php endif; ?>

                <a href="index.php?route=edit-profile" class="btn btn-outline-secondary mt-2">
                    <i class="bi bi-pencil-square me-2"></i>Editar Perfil
                </a>

                <a href="index.php?route=dashboard" class="btn btn-outline-secondary mt-2">
                    <i class="bi bi-arrow-return-left me-2"></i>Volver al Dashboard
                </a>

                <a href="index.php?route=logout" class="btn btn-outline-danger mt-2">
                    <i class="bi bi-box-arrow-left me-2"></i>Cerrar Sesión
                </a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Información Personal</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-semibold">Cédula:</span>
                            <span><?= htmlspecialchars($user['id_number']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-semibold">Correo electrónico:</span>
                            <span><?= htmlspecialchars($user['email']) ?></span>
                        </li>
                        <?php if ($user['role'] === 'Student'): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-semibold">Carrera:</span>
                                <span><?= htmlspecialchars($user['career']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-semibold">Semestre:</span>
                                <span><?= htmlspecialchars($user['semester']) ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>