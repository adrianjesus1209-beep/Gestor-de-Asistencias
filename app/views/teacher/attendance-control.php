<?php
// attendance-control.php - Control de asistencias para el profesor
// Simulación de materias del profesor (en backend vendrán de BD)
$teacherId = 100; // simulado
$teacherSubjects = [
    [
        'id' => 1,
        'code' => 'SIS-401',
        'title' => 'Bases de Datos Avanzadas',
        'career_id' => 1,
        'career_name' => 'Ingeniería de Sistemas',
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
        'career_id' => 1,
        'career_name' => 'Ingeniería de Sistemas',
        'semester' => 5,
        'schedule' => [
            ['day' => 'Martes', 'start' => '14:00', 'end' => '16:00'],
            ['day' => 'Jueves', 'start' => '14:00', 'end' => '16:00']
        ]
    ],
    [
        'id' => 3,
        'code' => 'COM-101',
        'title' => 'Comunicación Oral y Escrita',
        'career_id' => 2,
        'career_name' => 'Telecomunicaciones',
        'semester' => 1,
        'schedule' => [
            ['day' => 'Viernes', 'start' => '10:00', 'end' => '12:00']
        ]
    ]
];

// Agrupar por carrera y semestre
$groupedSubjects = [];
foreach ($teacherSubjects as $subject) {
    $career = $subject['career_name'];
    $semester = $subject['semester'];
    if (!isset($groupedSubjects[$career])) {
        $groupedSubjects[$career] = [];
    }
    if (!isset($groupedSubjects[$career][$semester])) {
        $groupedSubjects[$career][$semester] = [];
    }
    $groupedSubjects[$career][$semester][] = $subject;
}
?>
<div class="container py-5">
    <h2 class="mb-4">Control de Asistencias</h2>
    <?php foreach ($groupedSubjects as $career => $semesters): ?>
        <div class="mb-5">
            <h3 class="text-primary"><?= htmlspecialchars($career) ?></h3>
            <?php foreach ($semesters as $semester => $subjects): ?>
                <div class="semester-group mb-4">
                    <h4 class="border-bottom pb-2"><?= $semester ?>° Semestre</h4>
                    <div class="row g-4 mt-2">
                        <?php foreach ($subjects as $subject): ?>
                            <div class="col-md-6 col-lg-4" data-subject-id="<?= $subject['id'] ?>">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($subject['code']) ?> - <?= htmlspecialchars($subject['title']) ?></h5>
                                        <p class="card-text"><strong>Horario:</strong></p>
                                        <ul class="list-unstyled small">
                                            <?php foreach ($subject['schedule'] as $slot): ?>
                                                <li><?= $slot['day'] ?>: <?= $slot['start'] ?> - <?= $slot['end'] ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <div class="d-flex gap-2 mt-3">
                                            <button class="btn btn-success start-class-btn" data-subject-id="<?= $subject['id'] ?>" data-subject-code="<?= $subject['code'] ?>" data-subject-title="<?= $subject['title'] ?>">
                                                <i class="bi bi-play-fill"></i> Comenzar clase
                                            </button>
                                            <a href="index.php?route=attendance-list&subject_id=<?= $subject['id'] ?>" class="btn btn-outline-secondary">
                                                <i class="bi bi-list-check"></i> Ver asistencia
                                            </a>
                                        </div>
                                        <div class="mt-2 end-class-container" style="display: none;">
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-outline-primary w-50 resume-scan-btn" data-subject-id="<?= $subject['id'] ?>">
                                                    <i class="bi bi-camera-fill"></i> Escanear
                                                </button>
                                                <button class="btn btn-danger w-50 end-class-btn" data-subject-id="<?= $subject['id'] ?>">
                                                    <i class="bi bi-stopwatch-fill"></i> Cerrar clase
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal para escanear QR (sin boton cerrar clase) -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Escanear codigo QR del estudiante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qr-result" class="mt-3 alert d-none"></div>
                <p class="text-muted small mt-3">Acerque el codigo QR del carnet del estudiante a la camara.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>