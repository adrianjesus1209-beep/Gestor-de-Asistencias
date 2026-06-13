<?php
//TeacherController.php
require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../models/Enrollment.php';
require_once __DIR__ . '/../models/ClassSession.php';

class TeacherController {
    private $sectionModel;
    private $enrollmentModel;
    private $classSessionModel;
    private $attendanceService;

    // El Autoload inyectará los modelos y servicios correspondientes aquí
    public function __construct($sectionModel, $enrollmentModel, $classSessionModel, $attendanceService) {
        $this->sectionModel = $sectionModel;
        $this->enrollmentModel = $enrollmentModel;
        $this->classSessionModel = $classSessionModel;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Helper para despachar respuestas en formato JSON estándar
     */
    private function sendJSON($data, $statusCode = 200) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Endpoint 1: Obtener las secciones activas de un profesor
     * GET /api/teacher/my-sections?teacher_id=X
     */
    public function getMySections() {
        $teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : null;

        if (!$teacher_id) {
            $this->sendJSON(['success' => false, 'message' => 'Falta el ID del profesor.'], 400);
        }

        try {
            $sections = $this->sectionModel->getByTeacherId($teacher_id);
            $this->sendJSON(['success' => true, 'data' => $sections]);
        } catch (Exception $e) {
            $this->sendJSON(['success' => false, 'message' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Endpoint 2: Obtener estudiantes inscritos (Ordenados A-Z con verificación de seguridad)
     * GET /api/teacher/section-students?section_id=X&teacher_id=Y
     */
    public function getSectionStudents() {
        $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : null;
        $teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : null;

        if (!$section_id || !$teacher_id) {
            $this->sendJSON(['success' => false, 'message' => 'Parámetros insuficientes.'], 400);
        }

        try {
            // Verificación de seguridad: ¿La sección realmente pertenece a este profesor?
            if (!$this->sectionModel->verifyTeacherOwnership($section_id, $teacher_id)) {
                $this->sendJSON(['success' => false, 'message' => 'Acceso denegado. Esta sección no le pertenece.'], 403);
            }

            $students = $this->enrollmentModel->getStudentsBySection($section_id);
            $this->sendJSON(['success' => true, 'data' => $students]);
        } catch (Exception $e) {
            $this->sendJSON(['success' => false, 'message' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Endpoints 3 y 4: Cambiar estado de inscripción (Aceptar/Rechazar)
     * PUT /api/teacher/enrollment/update-status
     */
    public function updateEnrollmentStatus(array $input = null) {
        $input = $input ?? json_decode(file_get_contents("php://input"), true);
        
        $enrollment_id = isset($input['enrollment_id']) ? intval($input['enrollment_id']) : null;
        $status = isset($input['status']) ? $input['status'] : null; // 'Active' o 'Withdrawn' (Según el ENUM de SQL)

        if (!$enrollment_id || !in_array($status, ['Active', 'Withdrawn'])) {
            $this->sendJSON(['success' => false, 'message' => 'Datos inválidos o estado incorrecto.'], 400);
        }

        try {
            $updated = $this->enrollmentModel->updateStatus($enrollment_id, $status);
            if ($updated) {
                $this->sendJSON(['success' => true, 'message' => "Estado de inscripción actualizado a $status con éxito."]);
            } else {
                $this->sendJSON(['success' => false, 'message' => 'No se pudo actualizar el registro.'], 500);
            }
        } catch (Exception $e) {
            $this->sendJSON(['success' => false, 'message' => 'Error interno en la operación.'], 500);
        }
    }

    /**
     * Endpoint para INICIAR/ABRIR una clase
     * POST /api/teacher/session/start
     */
    public function startClassSession(array $input = null) {
        $input = $input ?? json_decode(file_get_contents("php://input"), true);
        $section_id = isset($input['section_id']) ? intval($input['section_id']) : null;

        if (!$section_id) {
            $this->sendJSON(['success' => false, 'message' => 'Falta el ID de la sección.'], 400);
        }

        try {
            $session_id = $this->classSessionModel->createSession($section_id);
            if ($session_id) {
                $this->sendJSON([
                    'success' => true, 
                    'message' => 'Sesión de clase iniciada en vivo correctamente.',
                    'session_id' => $session_id
                ]);
            } else {
                $this->sendJSON(['success' => false, 'message' => 'No se pudo abrir la sesión de clase.'], 500);
            }
        } catch (Exception $e) {
            $this->sendJSON(['success' => false, 'message' => 'Error de base de datos al abrir clase.'], 500);
        }
    }

    /**
     * Endpoint 5: Procesar el escaneo del código QR
     * POST /api/teacher/attendance/scan-qr
     */
    public function scanQrCode(array $input = null) {
        $input = $input ?? json_decode(file_get_contents("php://input"), true);
        
        $qr_token = isset($input['qr_token']) ? $input['qr_token'] : null;
        $section_id = isset($input['section_id']) ? intval($input['section_id']) : null;

        if (!$qr_token || !$section_id) {
            $this->sendJSON(['success' => false, 'message' => 'Datos de escaneo incompletos.'], 400);
        }

        // Delegamos de manera limpia la orquestación de modelos al servicio
        $result = $this->attendanceService->processQrScan($qr_token, $section_id);

        if ($result['success']) {
            $this->sendJSON($result, 200);
        } else {
            $this->sendJSON($result, 400);
        }
    }

    /**
     * Endpoint 6: Cerrar clase y registrar ausentes en lote
     * POST /api/teacher/session/close
     */
    public function closeClassSession(array $input = null) {
        $input = $input ?? json_decode(file_get_contents("php://input"), true);
        
        $session_id = isset($input['session_id']) ? intval($input['session_id']) : null;
        $section_id = isset($input['section_id']) ? intval($input['section_id']) : null;

        if (!$session_id || !$section_id) {
            $this->sendJSON(['success' => false, 'message' => 'Datos insuficientes para efectuar el cierre.'], 400);
        }

        $result = $this->attendanceService->closeClassSession($session_id, $section_id);

        if ($result['success']) {
            $this->sendJSON($result, 200);
        } else {
            $this->sendJSON($result, 400);
        }
    }

    public function toggleAttendanceManual(array $input = null) {
        $input = $input ?? json_decode(file_get_contents("php://input"), true);
        $student_id = intval($input['student_id'] ?? 0);
        $section_id = intval($input['section_id'] ?? 0);
        $action = $input['action'] ?? '';
        
        $db = \Config\Database::getInstance()->getConnection();
        require_once __DIR__ . '/../models/Attendance.php';
        $attModel = new \Attendance($db);
        
        $session = $this->classSessionModel->getActiveSession($section_id);
        $sid = $session ? $session['id'] : ($action === 'mark_present' ? $this->classSessionModel->createSession($section_id) : null);
        
        if ($action === 'mark_present' && $sid) {
            $res = $attModel->registerPresence($sid, $student_id);
            $this->sendJSON(['success' => $res, 'message' => 'Estudiante marcado como presente.']);
        } else if ($action === 'mark_absent' && $sid) {
            $res = $attModel->deleteAttendance($sid, $student_id);
            $this->sendJSON(['success' => $res, 'message' => 'Asistencia eliminada para este estudiante.']);
        } else {
            $this->sendJSON(['success' => false, 'message' => 'Error al procesar la acción manual.'], 400);
        }
    }
}