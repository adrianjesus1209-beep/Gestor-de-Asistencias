<?php
// asistencias.php - Control Maestro de Asistencias (Lista Completa)
require_once __DIR__ . '/../../Models/Section.php';
require_once __DIR__ . '/../../Models/Enrollment.php';

$db = Config\Database::getInstance()->getConnection();
$sectionModel = new Section($db);
$enrollmentModel = new Enrollment($db);

$teacher_id = $authPayload['user_id'] ?? 0;
$sections = $sectionModel->getByTeacherId($teacher_id);
$selected_section_id = intval($_GET['section_id'] ?? ($sections[0]['id'] ?? 0));

$allStudents = [];
$attendanceMap = [];

if ($selected_section_id > 0) {
    $allStudents = $enrollmentModel->getStudentsBySection($selected_section_id);
    
    $query = "SELECT student_id, attendance_status, registered_at 
              FROM attendance a
              JOIN class_session s ON a.session_id = s.id
              WHERE s.section_id = :section_id AND s.session_date = CURDATE()";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":section_id", $selected_section_id);
    $stmt->execute();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $attendanceMap[$row['student_id']] = $row;
    }
}

// Obtener todos los estudiantes del sistema que NO están en esta sección (para inscribir)
$queryAvailable = "SELECT u.id as student_id, p.first_name, p.last_name, p.id_number
                   FROM user u
                   JOIN profile p ON u.profile_id = p.id
                   WHERE u.role = 'Student'
                   AND u.id NOT IN (
                       SELECT student_id FROM enrollment WHERE section_id = :section_id
                   )
                   ORDER BY p.last_name, p.first_name";
$stmtAv = $db->prepare($queryAvailable);
$stmtAv->bindParam(":section_id", $selected_section_id);
$stmtAv->execute();
$availableStudents = $stmtAv->fetchAll(PDO::FETCH_ASSOC);

// Obtener las solicitudes pendientes para ESTA sección
$queryRequests = "SELECT er.id as request_id, u.id as student_id, p.first_name, p.last_name, p.id_number
                  FROM enrollment_request er
                  JOIN user u ON er.student_id = u.id
                  JOIN profile p ON u.profile_id = p.id
                  WHERE er.section_id = :section_id AND er.status = 'Pending'";
$stmtReq = $db->prepare($queryRequests);
$stmtReq->bindParam(":section_id", $selected_section_id);
$stmtReq->execute();
$pendingRequests = $stmtReq->fetchAll(PDO::FETCH_ASSOC);

// Filtrar las solicitudes fuera de los availableStudents si están en ambas partes, 
// o podemos añadir un flag a los availableStudents.
$pendingStudentIds = array_column($pendingRequests, 'student_id');
foreach ($availableStudents as &$av) {
    $av['is_requesting'] = in_array($av['student_id'], $pendingStudentIds);
    // Find the request_id
    if ($av['is_requesting']) {
        foreach($pendingRequests as $pr) {
            if ($pr['student_id'] == $av['student_id']) {
                $av['request_id'] = $pr['request_id'];
                break;
            }
        }
    }
}
unset($av);

// Nombre de la sección actual
$currentSection = array_filter($sections, fn($s) => $s['id'] == $selected_section_id);
$currentSection = reset($currentSection);
?>

<div class="dashboard-container p-4">
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-check-fill me-2"></i>Control de Asistencia</h5>
                <small class="text-muted"><?= date('d/m/Y') ?> · <?= htmlspecialchars($currentSection['subject_name'] ?? 'Sección') ?></small>
            </div>
            
            <div class="d-flex gap-2">
                <!-- Botón Inscribir Estudiante -->
                <button class="btn btn-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#enrollModal">
                    <i class="bi bi-person-plus-fill me-1"></i>Inscribir Estudiante
                </button>

                <!-- Dropdown Cambiar Sección -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                        Cambiar Sección
                    </button>
                    <ul class="dropdown-menu shadow border-0">
                        <?php foreach($sections as $s): ?>
                            <li><a class="dropdown-item <?= $s['id'] == $selected_section_id ? 'active' : '' ?>" 
                                   href="index.php?attendance_list&section_id=<?= $s['id'] ?>">
                                <?= htmlspecialchars($s['subject_name']) ?>
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4">Estudiante</th>
                        <th>Cédula</th>
                        <th class="text-center">Estado de Hoy</th>
                        <th class="text-center">Acción Manual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allStudents)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-5 d-block mb-2 opacity-25"></i>
                                No hay estudiantes inscritos en esta sección.<br>
                                <small>Usa el botón <strong>"Inscribir Estudiante"</strong> para agregar alumnos.</small>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($allStudents as $est): 
                            $isAbsent = !isset($attendanceMap[$est['student_id']]);
                            $regTime = !$isAbsent ? date('H:i', strtotime($attendanceMap[$est['student_id']]['registered_at'])) : '--:--';
                        ?>
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold"><?= htmlspecialchars($est['first_name'] . ' ' . $est['last_name']) ?></div>
                                    <small class="text-muted">Inscripción: <?= $est['enrollment_status'] ?></small>
                                </td>
                                <td><?= htmlspecialchars($est['id_number']) ?></td>
                                <td class="text-center">
                                    <?php if($isAbsent): ?>
                                        <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3">Ausente</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-success px-3">Presente (<?= $regTime ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if($isAbsent): ?>
                                        <button class="btn btn-sm btn-success rounded-pill px-3" onclick="toggleAttendance(<?= $est['student_id'] ?>, 'mark_present')">
                                            <i class="bi bi-check-circle me-1"></i>Marcar Presente
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="toggleAttendance(<?= $est['student_id'] ?>, 'mark_absent')">
                                            <i class="bi bi-x-circle me-1"></i>Poner Ausente
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Inscribir Estudiante -->
<div class="modal fade" id="enrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill text-success me-2"></i>Inscribir Estudiante</h5>
                    <small class="text-muted">Sección ID: <strong><?= $selected_section_id ?></strong></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <input type="hidden" id="modalSectionId" value="<?= $selected_section_id ?>">
            <div class="modal-body pt-2">
                <?php if ($selected_section_id == 0): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No hay sección activa seleccionada. Selecciona una sección primero.
                    </div>
                <?php elseif (empty($availableStudents)): ?>
                    <p class="text-muted text-center py-3">Todos los estudiantes registrados ya están inscritos en esta sección.</p>
                <?php else: ?>
                    <input type="text" id="searchStudent" class="form-control rounded-3 mb-3" placeholder="🔍 Buscar por nombre o cédula...">
                    <div id="studentList" style="max-height:350px; overflow-y:auto;">
                        <?php foreach($availableStudents as $av): ?>
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-3 border mb-2 student-row <?= $av['is_requesting'] ? 'border-warning shadow-sm bg-warning bg-opacity-10' : '' ?>"
                                 data-name="<?= strtolower($av['first_name'] . ' ' . $av['last_name']) ?>"
                                 data-id="<?= strtolower($av['id_number']) ?>">
                                <div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($av['first_name'] . ' ' . $av['last_name']) ?>
                                        <?php if ($av['is_requesting']): ?>
                                            <span class="badge bg-warning text-dark ms-1"><i class="bi bi-bell-fill"></i> Solicitud</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($av['id_number']) ?></small>
                                </div>
                                <div>
                                    <?php if ($av['is_requesting']): ?>
                                        <button class="btn btn-sm btn-success rounded-pill px-2 me-1"
                                                onclick="respondModalRequest(<?= $av['request_id'] ?>, <?= $av['student_id'] ?>, 'Accepted', '<?= htmlspecialchars($av['first_name'] . " " . $av['last_name']) ?>')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-2"
                                                onclick="respondModalRequest(<?= $av['request_id'] ?>, <?= $av['student_id'] ?>, 'Rejected', '<?= htmlspecialchars($av['first_name'] . " " . $av['last_name']) ?>')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-success rounded-pill px-3"
                                                onclick="enrollStudent(<?= $av['student_id'] ?>, '<?= htmlspecialchars($av['first_name'] . ' ' . $av['last_name']) ?>')">
                                            <i class="bi bi-plus-lg"></i> Inscribir
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const SECTION_ID = <?= $selected_section_id ?>;

// Buscador en tiempo real
document.getElementById('searchStudent')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.student-row').forEach(row => {
        const match = row.dataset.name.includes(q) || row.dataset.id.includes(q);
        row.style.display = match ? '' : 'none';
    });
});

// Inscribir estudiante en la sección
function enrollStudent(studentId, name) {
    const sectionId = parseInt(document.getElementById('modalSectionId').value);
    if (!sectionId || sectionId === 0) {
        Swal.fire('Error', 'No hay sección seleccionada. Recarga la página.', 'error');
        return;
    }

    Swal.fire({
        title: '¿Inscribir a ' + name + '?',
        text: 'Se agregará a esta sección y aparecerá en la lista de asistencia.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, inscribir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Inscribiendo...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

        fetch('index.php?api=enroll_student', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_id: studentId, section_id: sectionId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire('¡Inscrito!', name + ' ya aparece en tu lista.', 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Fallo de conexión', 'error');
        });
    });
}

function respondModalRequest(requestId, studentId, decision, name) {
    const sectionId = parseInt(document.getElementById('modalSectionId').value);
    const label = decision === 'Accepted' ? 'aceptar' : 'denegar';
    const color = decision === 'Accepted' ? '#198754' : '#dc3545';

    Swal.fire({
        title: '¿Confirmar?',
        text: `¿Desea ${label} la solicitud de ${name}?`,
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

// Marcado manual de asistencia
function toggleAttendance(studentId, action) {
    Swal.fire({ title: 'Actualizando...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

    fetch('index.php?api=toggle_attendance', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ student_id: studentId, section_id: SECTION_ID, action: action })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { 
            Swal.fire('¡Éxito!', data.message, 'success').then(() => location.reload()); 
        }
        else { Swal.fire('Error', data.message, 'error'); }
    })
    .catch(() => Swal.fire('Error', 'Fallo de conexión', 'error'));
}
</script>
