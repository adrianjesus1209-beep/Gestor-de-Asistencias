<?php
    require_once __DIR__ . '/../../models/User.php';
    $db = Config\Database::getInstance()->getConnection();
    $userModel = new App\Models\User($db);
    
    $userId = $authPayload['user_id'] ?? 0;
    $userData = $userModel->getProfileData($userId);

    // Mapear datos para la vista
    $user = [
        'role' => strtolower($userData['role'] ?? 'estudiante'),
        'first_name' => $userData['first_name'] ?? '',
        'second_name' => $userData['middle_name'] ?? '',
        'first_lastname' => $userData['last_name'] ?? '',
        'second_lastname' => $userData['second_last_name'] ?? '',
        'id_number' => $userData['id_number'] ?? '',
        'email' => $userData['email'] ?? '',
        'career' => $userData['career_name'] ?? 'N/A',
        'semester' => $userData['semester_name'] ?? 'N/A',
        'profile_pic' => $userData['profile_picture'] ?? 'default-profile.webp'
    ];
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <!-- Foto de perfil -->
            <div class="card shadow-sm text-center p-3">
                <img src="<?= BASE_URL ?>/assets/img/profiles/<?= $user['profile_pic'] ?>" 
                    alt="Foto de perfil" 
                    class="rounded-circle mx-auto d-block" 
                    style="width: 180px; height: 180px; object-fit: cover;">
                <h4 class="mt-3"><?= $user['first_name'] . ' ' . $user['second_name'] . ' ' . $user['first_lastname'] . ' ' . $user['second_lastname'] ?></h4>

                <?php if ($user['role'] === 'profesor'): ?>
                    <p class="text-muted">Profesor</p>
                <?php else: ?>
                    <p class="text-muted">Estudiante de <?= $user['career'] ?></p>
                <?php endif; ?>

                <?php if ($user['role'] === 'profesor'): ?>
                    <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#qrModal">
                        <i class="bi bi-qr-code-scan me-2"></i>Escanear QR
                    </button>
                <?php endif; ?>

                <a href="index.php?route=edit-profile" class="btn btn-outline-secondary mt-2">
                    <i class="bi bi-pencil-square me-2"></i>Editar Perfil
                </a>

                <?php if ($user['role'] === 'profesor'): ?>
                    <a href="index.php?dashboard_profesor" class="btn btn-outline-secondary mt-2">
                        <i class="bi bi-arrow-return-left me-2"></i>Volver
                    </a>
                <?php else: ?>
                    <a href="index.php?dashboard_estudiante" class="btn btn-outline-secondary mt-2">
                        <i class="bi bi-arrow-return-left me-2"></i>Volver
                    </a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-outline-secondary mt-2">
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
                            <span><?= $user['id_number'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-semibold">Correo electrónico:</span> 
                            <span><?= $user['email'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-semibold">Carrera:</span> 
                            <span><?= $user['career'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-semibold">Semestre:</span> 
                            <span><?= $user['semester'] ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para escanear QR (solo profesores) -->
<?php if ($user['role'] === 'profesor'): ?>
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">Escanear código QR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="qr-reader" style="width: 100%;"></div>
                    <div id="qr-result" class="mt-3 alert alert-info d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para html5-qrcode -->
    <script src="<?= BASE_URL ?>/assets/qrcode/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('qrModal');
            let html5QrCode = null;

            modalElement.addEventListener('shown.bs.modal', function () {
                if (!html5QrCode) {
                    html5QrCode = new Html5Qrcode("qr-reader");
                    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure);
                }
            });

            modalElement.addEventListener('hidden.bs.modal', function () {
                if (html5QrCode && html5QrCode.isScanning) {
                    html5QrCode.stop().then(() => {
                        html5QrCode.clear();
                        html5QrCode = null;
                    }).catch(err => console.error("Error al detener escáner:", err));
                }
            });

            function onScanSuccess(decodedText, decodedResult) {
                const resultDiv = document.getElementById('qr-result');
                resultDiv.textContent = 'Código escaneado: ' + decodedText;
                resultDiv.classList.remove('d-none');
                // detener escaner automaticamente despues de un exito
                setTimeout(() => {
                    if (html5QrCode && html5QrCode.isScanning) {
                        html5QrCode.stop();
                        document.getElementById('qrModal').querySelector('.btn-close').click();
                    }
                }, 2000);
            }

            function onScanFailure(error) {
                // No hace nada, solo sigue escaneando
            }
        });
    </script>
<?php endif; ?>