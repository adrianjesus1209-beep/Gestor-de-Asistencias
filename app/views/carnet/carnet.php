<?php
// Vista QR / Carnet del Estudiante

$user = [
    'first_name'      => 'Juan',
    'second_name'     => 'Alejandro',
    'first_lastname'  => 'Pérez',
    'second_lastname' => 'Rodríguez',
    'id_number'       => 'V-12345678',
    'photo'           => BASE_URL . '/assets/img/profiles/default-profile.webp',
    'token_qr'        => 'UNEFA-SEC-9283749218374'
];
$fullName = $user['first_name'] . ' ' . substr($user['second_name'], 0, 1) . '. ' 
            . $user['first_lastname'] . ' ' . substr($user['second_lastname'], 0, 1) . '.';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/carnet.css">

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="carnet-wrapper" id="carnet-completo">
                
                <!-- FRENTE -->
                <div class="carnet-side carnet-front" id="carnet-front">
                    <img src="<?= BASE_URL ?>/assets/img/carnet/carnet-frente.png" alt="Frente Carnet" class="carnet-bg">
                    
                    <div class="photo">
                        <img src="<?= $user['photo'] ?>" alt="Foto de perfil">
                    </div>
                    
                    <div class="datos">
                        <div><strong>Cédula:</strong> <?= $user['id_number'] ?></div>
                        <div><strong>Nombre:</strong> <?= $fullName ?></div>
                    </div>
                    
                    <div class="qr-wrapper">
                        <div id="qrcode-front" data-token="<?= $user['token_qr'] ?>"></div>
                    </div>
                </div>

                <!-- DORSO -->
                <div class="carnet-side carnet-back" id="carnet-back">
                    <?php
                    $dorsoPath = BASE_URL . '/assets/img/carnet/carnet-dorso.png';
                    $dorsoFile = __DIR__ . '/../../../public/assets/img/carnet/carnet-dorso.png';
                    if (file_exists($dorsoFile)):
                    ?>
                        <img src="<?= $dorsoPath ?>" alt="Dorso Carnet" class="carnet-bg">
                        <div class="dynamic-text">
                            Fecha de emisión: <?= date('d/m/Y') ?>
                        </div>
                    <?php else: ?>
                        <div style="background: #f8f9fa; border: 1px dashed #ccc; padding: 40px; text-align: center; border-radius: 12px;">
                            <p class="text-muted"> Imagen del dorso no encontrada.</p>
                            <p>Coloca <strong>carnet-dorso.png</strong> en <code>public/assets/img/carnet/</code></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-4">
                <button id="btnDescargar" class="btn btn-primary"><i class="bi bi-download"></i> Descargar Carnet (PNG)</button>
                <a href="index.php?route=dashboard" class="btn btn-outline-secondary"><i class="bi bi-arrow-return-left"></i> Volver</a>
            </div>
        </div>
    </div>
</div>

<!-- Librerias externas -->
<script src="<?= BASE_URL ?>/assets/qrcode/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="<?= BASE_URL ?>/js/carnet.js"></script>