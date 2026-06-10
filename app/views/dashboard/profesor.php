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
                <a class="sidebar-link" href="index.php?list">Lista de Estudiantes</a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="index.php?attendance_list">Control de Asistencias</a>
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
    <div class="welcome-card shadow-sm border-0 rounded-4 p-4 text-center">
        <h3 class="welcome-title fw-bold">Bienvenido Profesor</h3>
        <p class="welcome-text text-muted">
            Panel de control del sistema de asistencia QR.
        </p>
        
        <div class="row g-3 mt-3 justify-content-center">
            <div class="col-md-4">
                <button class="btn btn-primary w-100 py-3 rounded-4 shadow-sm h-100 d-flex flex-column align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
                    <i class="bi bi-qr-code-scan fs-1 mb-2"></i>
                    <strong>Escaneo QR</strong>
                </button>
            </div>
            <div class="col-md-4">
                <a href="index.php?attendance_list" class="btn btn-success w-100 py-3 rounded-4 shadow-sm h-100 d-flex flex-column align-items-center justify-content-center text-white">
                    <i class="bi bi-person-check fs-1 mb-2"></i>
                    <strong>Pase de Lista (Manual)</strong>
                </a>
            </div>
            <div class="col-md-4">
                <a href="index.php?list" class="btn btn-outline-primary w-100 py-3 rounded-4 shadow-sm h-100 d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-people-fill fs-2 mb-2"></i>
                    <strong>Mis Estudiantes</strong>
                </a>
            </div>
        </div>

        <div class="mt-4">
            <a href="index.php?logout" class="btn btn-danger px-4 py-2 rounded-pill fw-bold">
                <i class="bi bi-box-arrow-left me-2"></i>Cerrar Sesión
            </a>
        </div>
    </div>

    <!-- Solicitudes de Inscripción Pendientes -->
    <?php
    $dbProf = \Config\Database::getInstance()->getConnection();
    $qReqs = "SELECT er.id, er.student_id, er.section_id, er.status, er.requested_at,
                     p.first_name, p.last_name, p.id_number,
                     s.section_name, sub.subject_name
              FROM enrollment_request er
              JOIN user u ON er.student_id = u.id
              JOIN profile p ON u.profile_id = p.id
              JOIN section s ON er.section_id = s.id
              JOIN subject sub ON s.subject_id = sub.id
              WHERE er.status = 'Pending'
              ORDER BY er.requested_at DESC";
    $pendingReqs = $dbProf->query($qReqs)->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if (!empty($pendingReqs)): ?>
    <div class="card shadow-sm border-0 rounded-4 mt-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center border-bottom">
            <i class="bi bi-bell-fill text-warning me-2"></i>
            <h5 class="mb-0 fw-bold">Solicitudes de Inscripción Pendientes</h5>
            <span class="badge bg-warning text-dark ms-2"><?= count($pendingReqs) ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4">Estudiante</th>
                        <th>Cédula</th>
                        <th>Sección / Materia</th>
                        <th>Fecha Solicitud</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pendingReqs as $req): ?>
                        <tr>
                            <td class="px-4 fw-bold"><?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']) ?></td>
                            <td><?= htmlspecialchars($req['id_number']) ?></td>
                            <td>
                                <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($req['subject_name']) ?></span>
                                <small class="text-muted d-block"><?= htmlspecialchars($req['section_name']) ?></small>
                            </td>
                            <td><small><?= date('d/m/Y H:i', strtotime($req['requested_at'])) ?></small></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-success rounded-pill px-3 me-1"
                                        onclick="respondRequest(<?= $req['id'] ?>, <?= $req['student_id'] ?>, <?= $req['section_id'] ?>, 'Accepted')">
                                    <i class="bi bi-check-lg me-1"></i>Aceptar
                                </button>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        onclick="respondRequest(<?= $req['id'] ?>, <?= $req['student_id'] ?>, <?= $req['section_id'] ?>, 'Rejected')">
                                    <i class="bi bi-x-lg me-1"></i>Denegar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <script>
    function respondRequest(requestId, studentId, sectionId, decision) {
        const label = decision === 'Accepted' ? 'aceptar' : 'denegar';
        const color = decision === 'Accepted' ? '#198754' : '#dc3545';

        Swal.fire({
            title: '¿Confirmar?',
            text: `¿Desea ${label} esta solicitud de inscripción?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${label}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Procesando...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

            fetch('index.php?api=respond_request', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ request_id: requestId, student_id: studentId, section_id: sectionId, decision: decision })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Listo!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Fallo de conexión', 'error'));
        });
    }
    </script>

    <div class="modal fade" id="qrScannerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Escanear Código QR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div id="qr-reader" class="rounded-4 overflow-hidden border bg-light" style="width: 100%; min-height: 250px;"></div>
                    
                    <!-- MÉTODO ALTERNATIVO (PARA ENTORNOS SIN CÁMARA) -->
                    <div class="mt-3 p-3 bg-light rounded-4 border border-dashed">
                        <p class="text-muted small mb-2">¿La cámara no carga? Usa el método alternativo:</p>
                        <input type="file" id="qr-input-file" accept="image/*" capture="environment" class="d-none">
                        <button type="button" class="btn btn-outline-primary w-100 rounded-pill btn-sm fw-bold" onclick="document.getElementById('qr-input-file').click()">
                            <i class="bi bi-camera-fill me-2"></i>Tomar Foto o Subir QR
                        </button>
                    </div>

                    <div id="qr-result" class="mt-3 alert d-none"></div>
                    <p class="text-muted small mt-3">
                        En modo alternativo, tome una foto nítida del código QR del estudiante.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <button class="btn-menu-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling">
        Menu
    </button>
</div>

<!-- SCRIPTS PARA EL ESCÁNER -->
<script src="<?= BASE_URL ?>/assets/qrcode/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('qrScannerModal');
    const resultDiv = document.getElementById('qr-result');
    let html5QrCode = null;

    modalElement.addEventListener('shown.bs.modal', function () {
        resultDiv.classList.add('d-none');
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qr-reader");
            const config = { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0 
            };
            
            html5QrCode.start(
                { facingMode: "environment" }, 
                config, 
                onScanSuccess, 
                onScanFailure
            ).catch(err => {
                console.error("No se pudo iniciar la cámara:", err);
                resultDiv.className = 'mt-3 alert alert-danger';
                resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Error: No se pudo acceder a la cámara. Verifique los permisos o use una conexión segura (HTTPS).';
                resultDiv.classList.remove('d-none');
            });
        }
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                html5QrCode = null;
            });
        }
    });

    // MANEJAR ESCANEO POR ARCHIVO (VERSION OPTIMIZADA CON CANVAS)
    const fileInput = document.getElementById('qr-input-file');
    fileInput.addEventListener('change', e => {
        if (e.target.files.length == 0) return;
        
        const imageFile = e.target.files[0];
        const reader = new FileReader();

        resultDiv.className = 'mt-3 alert alert-info';
        resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Optimizando imagen...';
        resultDiv.classList.remove('d-none');

        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                // REDIMENSIONADO PROFESIONAL PARA EVITAR BLOQUEOS
                const canvas = document.createElement('canvas');
                const MAX_WIDTH = 800;
                let width = img.width;
                let height = img.height;

                if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width;
                    width = MAX_WIDTH;
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                if (!html5QrCode) html5QrCode = new Html5Qrcode("qr-reader");

                resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Buscando código QR...';

                html5QrCode.scanFile(imageFile, false)
                    .then(decodedText => {
                        onScanSuccess(decodedText);
                    })
                    .catch(err => {
                        // Re-intento con el canvas por si la imagen original falló
                        console.warn("Re-intentando con canvas escalado...");
                        html5QrCode.scanFile(canvas, false)
                            .then(onScanSuccess)
                            .catch(() => {
                                Swal.fire('QR no detectado', 'No pudimos encontrar un código en la foto. Intenta tomarla más de cerca.', 'warning');
                                resultDiv.classList.add('d-none');
                            });
                    });
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(imageFile);
    });

    // FUNCIÓN DE SIMULACIÓN PARA EL USUARIO
    window.simulateScan = function(token) {
        onScanSuccess(token);
    };

    function onScanSuccess(decodedText) {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop(); // Detener escaneo mientras se procesa
        }

        resultDiv.className = 'mt-3 alert alert-info';
        resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Procesando asistencia...';
        resultDiv.classList.remove('d-none');

        // Obtener la primera sección del profesor (Simulado para la prueba)
        <?php 
            require_once __DIR__ . '/../../models/Section.php';
            require_once __DIR__ . '/../../models/Enrollment.php';
            require_once __DIR__ . '/../../models/ClassSession.php';
            $db = Config\Database::getInstance()->getConnection();
            $sModel = new Section($db);
            $sections = $sModel->getByTeacherId($authPayload['user_id'] ?? 0);
            $firstSectionId = !empty($sections) ? $sections[0]['id'] : 0;
        ?>
        const sectionId = <?= $firstSectionId ?>;

        fetch('index.php?api=scan_qr', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                qr_token: decodedText,
                section_id: sectionId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Asistencia Registrada!',
                    text: data.message,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-list-check"></i> Ver Asistencias',
                    cancelButtonText: 'Seguir escaneando',
                    confirmButtonColor: '#198754'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'index.php?attendance_list';
                    } else {
                        bootstrap.Modal.getInstance(modalElement).hide();
                    }
                });
            } else {
                Swal.fire('Error', data.message, 'error').then(() => {
                    // Reiniciar escaneo si el usuario cierra el error
                    html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, onScanSuccess);
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Fallo en la conexión con el servidor.', 'error');
        });
    }

    function onScanFailure(error) {
        // Silencioso para evitar logs excesivos
    }
});
</script>
