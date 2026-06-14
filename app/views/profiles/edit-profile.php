<?php
// edit-profile.php - Formulario de edición de perfil
// Variables esperadas del controlador:

// - $user['id'] (oculto)
// - $user['first_name'], $user['second_name'], $user['last_name'], $user['second_lastname']
// - $user['id_number'], $user['email']
// - $user['career_id'] (para estudiantes), $user['semester']
// - $user['profile_pic']
// - $user['role']
// Datos de ejemplo:

$user = [
    'role'            => 'Teacher', // o 'Teacher'
    'first_name'      => 'Juan',
    'second_name'     => 'Alejandro',
    'last_name'       => 'Pérez',
    'second_lastname' => 'Rodríguez',
    'id_number'       => 'V-12345678',
    'email'           => 'juan.perez@unefa.edu.ve',
    'career_id'       => 1,
    'semester'        => 6,
    'profile_pic'     => 'default-profile.webp'
];
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Editar Perfil</div>
                <div class="card-body">
                    <form action="index.php?route=update-profile" method="POST" enctype="multipart/form-data">
                        <!-- Campo oculto para identificar al usuario (cuando backend esté listo) -->
                        <!-- <input type="hidden" name="user_id" value="<?= $user['id'] ?? '' ?>"> -->

                        <div class="text-center mb-4">
                            <img id="profilePreview" src="<?= BASE_URL ?>/assets/img/profiles/<?= $user['profile_pic'] ?>"
                                alt="Previsualización" class="rounded-circle"
                                style="width: 150px; height: 150px; object-fit: cover; border: 2px solid #ddd;">
                            <div class="mt-2">
                                <label for="profile_pic" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-camera"></i> Cambiar foto
                                </label>
                                <input type="file" id="profile_pic" name="profile_pic" class="d-none" accept="image/*">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Primer Nombre</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Segundo Nombre</label>
                                <input type="text" name="second_name" class="form-control" value="<?= htmlspecialchars($user['second_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Primer Apellido</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Segundo Apellido</label>
                                <input type="text" name="second_lastname" class="form-control" value="<?= htmlspecialchars($user['second_lastname'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <?php if ($user['role'] === 'Student'): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Carrera</label>
                                    <select name="career_id" class="form-select" required>
                                        <option value="" disabled>Seleccione</option>
                                        <option value="1" <?= $user['career_id'] == 1 ? 'selected' : '' ?>>Ingeniería de Sistemas</option>
                                        <option value="2" <?= $user['career_id'] == 2 ? 'selected' : '' ?>>Telecomunicaciones</option>
                                        <option value="3" <?= $user['career_id'] == 3 ? 'selected' : '' ?>>Ingeniería Mecánica</option>
                                        <option value="4" <?= $user['career_id'] == 4 ? 'selected' : '' ?>>Administración y Gestión Municipal</option>
                                        <option value="5" <?= $user['career_id'] == 5 ? 'selected' : '' ?>>Ingeniería en Electrónica</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Semestre</label>
                                    <select name="semester" class="form-select" required>
                                        <option value="" disabled>Seleccione</option>
                                        <?php for ($i = 1; $i <= 9; $i++): ?>
                                            <?php if ($i == 1 || $i == 3): ?>
                                                <option value="<?= $i ?>" <?= $user['semester'] == $i ? 'selected' : '' ?>><?= $i ?>er Semestre</option>
                                            <?php elseif ($i == 2): ?>
                                                <option value="<?= $i ?>" <?= $user['semester'] == $i ? 'selected' : '' ?>><?= $i ?>do Semestre</option>
                                            <?php elseif ($i == 4 || $i == 5 || $i == 6): ?>
                                                <option value="<?= $i ?>" <?= $user['semester'] == $i ? 'selected' : '' ?>><?= $i ?>to Semestre</option>
                                            <?php elseif ($i == 7): ?>
                                                <option value="<?= $i ?>" <?= $user['semester'] == $i ? 'selected' : '' ?>><?= $i ?>mo Semestre</option>
                                            <?php elseif ($i == 8): ?>
                                                <option value="<?= $i ?>" <?= $user['semester'] == $i ? 'selected' : '' ?>><?= $i ?>vo Semestre</option>
                                            <?php elseif ($i == 9): ?>
                                                <option value="<?= $i ?>" <?= $user['semester'] == $i ? 'selected' : '' ?>><?= $i ?>no Semestre</option>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="index.php?route=profile" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/preview.js"></script>