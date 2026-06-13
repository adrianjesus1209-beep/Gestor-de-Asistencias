<?php
require_once __DIR__ . '/../../models/User.php';
$db = Config\Database::getInstance()->getConnection();
$userModel = new App\Models\User($db);

$userId = $authPayload['user_id'] ?? 0;
$userData = $userModel->getProfileData($userId);

$estadoUsuario = strtolower((string) ($userData['status'] ?? ''));
$estudianteActivo = in_array($estadoUsuario, ['active', 'activo'], true);
$tieneQRAgregado = (!empty($userData['qr_token'])) ? true : false;
$codigoSecretoQR = $userData['qr_token'] ?? "";
?>


<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white text-center">
                    <h4 class="mb-0">Código QR de Asistencia</h4>
                    <small class="text-muted">Presente este código para registrar su asistencia</small>
                </div>
                <div class="card-body text-center">
                    <?php if (!$estudianteActivo): ?>
                        <div class="alert alert-danger text-center">
                            <i class="bi bi-exclamation-triangle-fill"></i> Estudiante inactivo o retirado. No se puede generar QR.
                        </div>
                    <?php elseif (!$tieneQRAgregado): ?>
                        <div class="alert alert-warning">
                            No tiene un código QR asignado. Contacte al administrador.
                        </div>
                    <?php else: ?>
                        <div id="qrcode" class="d-flex justify-content-center my-3"></div>
                        <div class="mt-3">
                            <button class="btn btn-primary" onclick="descargarQR()">
                                <i class="bi bi-download"></i> Descargar QR
                            </button>
                            <button class="btn btn-secondary" onclick="imprimirQR()">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                        <hr>
                        <div class="text-muted small">
                            <i class="bi bi-info-circle"></i> Este código es personal e intransferible.
                        </div>
                        <a href="index.php?dashboard_estudiante" class="btn btn-outline-secondary mt-2">
                            <i class="bi bi-arrow-return-left me-2"></i>Volver
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Librería para generar QR -->
<script src="<?= BASE_URL ?>/assets/qrcode/qrcode.min.js"></script>
<script>
    // Generar QR al cargar la página (si el estudiante está activo y tiene QR)
    <?php if ($estudianteActivo && $tieneQRAgregado): ?>
    document.addEventListener("DOMContentLoaded", function() {
        new QRCode(document.getElementById("qrcode"), {
            text: "<?= $codigoSecretoQR ?>",
            width: 200,
            height: 200,
            colorDark: "#1e293b",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });
    <?php endif; ?>

    function descargarQR() {
        const qrContainer = document.getElementById('qrcode');
        const img = qrContainer.querySelector('img');
        const canvas = qrContainer.querySelector('canvas');
        let src = '';
        if (img && img.src) {
            src = img.src;
        } else if (canvas) {
            src = canvas.toDataURL("image/png");
        }
        if (src) {
            const link = document.createElement('a');
            link.href = src;
            link.download = 'QR_Asistencia.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert('No se pudo generar la imagen del QR.');
        }
    }

    function imprimirQR() {
        window.print();
    }
</script>