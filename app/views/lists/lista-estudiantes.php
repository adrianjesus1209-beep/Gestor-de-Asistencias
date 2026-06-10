<!-- Fernando Ruiz. 31.083.595 -->

<?php
require_once __DIR__ . '/../../models/Enrollment.php';
require_once __DIR__ . '/../../models/Section.php';

$db = Config\Database::getInstance()->getConnection();
$enrollmentModel = new Enrollment($db);
$sectionModel = new Section($db);

$teacher_id = $authPayload['user_id'] ?? 0;
$sections = $sectionModel->getByTeacherId($teacher_id);
$selected_section_name = "Sin sección seleccionada";
$students = [];

if (!empty($sections)) {
    $section_id = $_GET['section_id'] ?? $sections[0]['id'];
    $students = $enrollmentModel->getStudentsBySection($section_id);
    foreach($sections as $s) {
        if($s['id'] == $section_id) $selected_section_name = $s['subject_name'] . " - " . $s['section_name'];
    }
}
?>

<div class="card Lista shadow-sm border-0 rounded-4 overflow-hidden mb-4">
  <div class="card-header Lista_Titulo bg-white py-3 px-4 d-flex justify-content-between align-items-center">
    <h5 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2"></i>Lista de Estudiantes</h5>
    <span class="badge bg-primary rounded-pill"><?= $selected_section_name ?></span>
  </div>
  
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 campos">
      <thead class="bg-light">
        <tr>
          <th scope="col" class="px-4 py-3">Nombre Completo</th>
          <th scope="col" class="py-3">Cédula</th>
          <th scope="col" class="py-3">Carrera</th>
          <th scope="col" class="py-3 text-center">Estado</th>
          <th scope="col" class="py-3 text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($students)): ?>
            <tr>
                <td colspan="5" class="text-center py-5 text-muted">No hay estudiantes inscritos en esta sección.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($students as $student): ?>
            <tr>
                <td data-label="Nombre Completo" class="px-4 py-3 fw-semibold">
                    <?= $student['first_name'] . ' ' . $student['last_name'] ?>
                </td>
                <td data-label="Cédula"><?= $student['id_number'] ?></td>
                <td data-label="Carrera">Ing. Sistemas</td>
                <td data-label="Estado" class="text-center">
                    <span class="badge rounded-pill <?= $student['enrollment_status'] === 'Active' ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= $student['enrollment_status'] === 'Active' ? 'Activo' : 'Pendiente' ?>
                    </span>
                </td>
                <td data-label="Acciones" class="text-center">

                    <?php if ($student['enrollment_status'] === 'Pending' || $student['enrollment_status'] === 'Withdrawn'): ?>
                        <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold btn-update-status" 
                                data-id="<?= $student['enrollment_id'] ?? 0 ?>" 
                                data-status="Active">
                            <i class="bi bi-check-lg"></i> Admitir
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($student['enrollment_status'] === 'Active' || $student['enrollment_status'] === 'Pending'): ?>
                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold ms-1 btn-update-status" 
                                data-id="<?= $student['enrollment_id'] ?? 0 ?>" 
                                data-status="Withdrawn">
                            <i class="bi bi-x-lg"></i> Retirar
                        </button>
                    <?php endif; ?>

                    <?php if (empty($student['enrollment_id'])): ?>
                        <small class="text-muted">Demo</small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusButtons = document.querySelectorAll('.btn-update-status');
    
    statusButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const enrollmentId = this.getAttribute('data-id');
            const newStatus = this.getAttribute('data-status');
            const actionText = newStatus === 'Active' ? 'admitir' : 'retirar';
            const confirmColor = newStatus === 'Active' ? '#198754' : '#dc3545';

            if (enrollmentId == 0) {
                Swal.fire('Modo Demo', 'En la versión de prueba no se pueden modificar registros ficticios.', 'info');
                return;
            }

            Swal.fire({
                title: '¿Está seguro?',
                text: `Desea ${actionText} a este estudiante en la sección.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Enviar petición API
                    fetch('index.php?api=update_enrollment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            enrollment_id: enrollmentId,
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Éxito!', data.message, 'success')
                            .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                    });
                }
            });
        });
    });
});
    // FUNCIÓN PARA SIMULAR ESCANEO DE UN ALUMNO ESPECÍFICO
    window.simulateStudentScan = function(token, name) {
        if (!token) {
            Swal.fire('Error', 'Este estudiante no tiene un token QR generado.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Simulando Escaneo',
            text: `¿Registrar asistencia para ${name}?`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ 
                    title: 'Procesando...', 
                    didOpen: () => { Swal.showLoading() },
                    allowOutsideClick: false 
                });

                fetch('index.php?api=scan_qr', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        qr_token: token,
                        section_id: <?= $section_id ?>
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: `Asistencia de ${name} registrada.`,
                            icon: 'success',
                            confirmButtonText: 'Ver Asistencias'
                        }).then((res) => {
                            if (res.isConfirmed) {
                                window.location.href = 'index.php?attendance_list';
                            }
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Fallo en la comunicación con el servidor.', 'error');
                });
            }
        });
    }
</script>



<nav aria-label="...">
  <ul class="pagination pagination-sm">
    <li class="page-item active">
      <a class="page-link" aria-current="page">1</a>
    </li>
    <li class="page-item"><a class="page-link" href="lista-estudiantes.php">2</a></li>
    <li class="page-item"><a class="page-link" href="lista-estudiantes.php">3</a></li>
  </ul>
</nav>
</div>

