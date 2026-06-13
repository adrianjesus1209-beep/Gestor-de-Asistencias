<?php
// Cargar datos reales del usuario autenticado
$db = \Config\Database::getInstance()->getConnection();
require_once __DIR__ . '/../../models/User.php';
$userModel = new \App\Models\User($db);
$profile = $userModel->getProfileData($authPayload['user_id'] ?? 0);

if (!$profile) {
    header('Location: index.php?dashboard_estudiante');
    exit;
}

$profilePic = $profile['profile_picture'] ?? 'default-profile.webp';
$picUrl = BASE_URL . '/assets/img/profiles/' . $profilePic;
$updated = isset($_GET['updated']);
$rawRole = strtolower((string) ($profile['role'] ?? 'student'));
$dashboardLink = in_array($rawRole, ['teacher', 'profesor'], true) ? 'index.php?dashboard_profesor' : 'index.php?dashboard_estudiante';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <?php if ($updated): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>Perfil actualizado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 px-4 fw-bold border-bottom">
                    <i class="bi bi-person-gear me-2 text-primary"></i>Editar Perfil
                </div>
                <div class="card-body p-4">
                    <form action="index.php?route=update-profile" method="POST" enctype="multipart/form-data">

                        <!-- Foto de Perfil -->
                        <div class="text-center mb-4">
                            <img id="profilePreview"
                                src="<?= $picUrl ?>"
                                alt="Foto de perfil"
                                class="rounded-circle shadow"
                                style="width:130px;height:130px;object-fit:cover;border:3px solid #e0e0e0;">
                            <div class="mt-3">
                                <label for="profile_pic" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-camera-fill me-1"></i> Cambiar foto
                                </label>
                                <input type="file" id="profile_pic" name="profile_pic" class="d-none" accept="image/*">
                            </div>
                            <small class="text-muted d-block mt-1">PNG, JPG o WEBP · Máx. 5 MB</small>
                        </div>

                        <!-- Campos de texto -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Primer Nombre</label>
                                <input type="text" name="first_name" class="form-control rounded-3"
                                    value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Segundo Nombre</label>
                                <input type="text" name="middle_name" class="form-control rounded-3"
                                    value="<?= htmlspecialchars($profile['middle_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Primer Apellido</label>
                                <input type="text" name="last_name" class="form-control rounded-3"
                                    value="<?= htmlspecialchars($profile['last_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Segundo Apellido</label>
                                <input type="text" name="second_last_name" class="form-control rounded-3"
                                    value="<?= htmlspecialchars($profile['second_last_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cédula</label>
                                <input type="text" class="form-control rounded-3 bg-light"
                                    value="<?= htmlspecialchars($profile['id_number'] ?? '') ?>" disabled>
                                <small class="text-muted">La cédula no puede modificarse.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Correo Electrónico</label>
                                <input type="text" class="form-control rounded-3 bg-light"
                                    value="<?= htmlspecialchars($profile['email'] ?? '') ?>" disabled>
                                <small class="text-muted">El correo no puede modificarse.</small>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="<?= htmlspecialchars($dashboardLink) ?>" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="bi bi-arrow-left me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                                <i class="bi bi-floppy-fill me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Previsualización instantánea de la foto elegida
document.getElementById('profile_pic').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('profilePreview').src = URL.createObjectURL(file);
    }
});
</script>