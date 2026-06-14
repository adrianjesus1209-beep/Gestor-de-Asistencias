<?php
// app/controllers/TeacherController.php

class TeacherController {
    private $db;
    private $sectionModel;
    private $enrollmentModel;
    private $classSessionModel;
    private $attendanceModel;
    private $attendanceService;

    // El constructor recibe la BD, todos tus modelos y tu servicio orquestador
    public function __construct($database_connection, $sectionModel, $enrollmentModel, $classSessionModel, $attendanceModel, $attendanceService) {
        $this->db = $database_connection;
        $this->sectionModel = $sectionModel;
        $this->enrollmentModel = $enrollmentModel;
        $this->classSessionModel = $classSessionModel;
        $this->attendanceModel = $attendanceModel;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Auxiliar de Seguridad: Valida que el usuario sea estrictamente un Profesor
     * Cumple con la regla: "Verificar roles en CADA controlador"
     */
    private function checkTeacherAccess($userId) {
        if (!class_exists('Security') || !Security::checkRole($userId, ['Teacher'])) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Acceso denegado. Se requieren permisos de Profesor."]);
            exit;
        }
    }

    /**
     * RESPONSABILIDAD 1: Crear una nueva sección
     * POST /api/teacher/create-section
     */
    public function createSection($userId) {
        $this->checkTeacherAccess($userId);
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        
        // Limpiar datos de entrada con el validador de Cristian
        if (class_exists('Validator')) { $input = Validator::sanitizeInput($input); }

        if (empty($input['subject_id']) || empty($input['section_name']) || empty($input['day_of_week']) || empty($input['start_time']) || empty($input['end_time'])) {
            echo json_encode(["success" => false, "message" => "Faltan datos obligatorios para crear la sección."]);
            return;
        }

        $sectionId = $this->sectionModel->create(
            $input['subject_id'],
            $userId, // El teacher_id es el usuario autenticado
            $input['section_name'],
            $input['day_of_week'],
            $input['start_time'],
            $input['end_time'],
            $input['default_delegate_id'] ?? null
        );

        if ($sectionId) {
            echo json_encode(["success" => true, "message" => "Sección creada exitosamente.", "section_id" => $sectionId]);
        } else {
            echo json_encode(["success" => false, "message" => "Error interno al guardar la sección."]);
        }
    }

    /**
     * ACTUALIZAR EL ESTADO DE UNA MATRÍCULA
     */
    public function updateEnrollmentStatus($userId) {
        $this->checkTeacherAccess($userId);
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        if (class_exists('Validator')) { $input = Validator::sanitizeInput($input); }

        $enrollment_id = intval($input['enrollment_id'] ?? 0);
        $status = $input['status'] ?? null;

        if (!$enrollment_id || !$status) {
            echo json_encode(['success' => false, 'message' => 'ID de inscripción y estado son requeridos.']);
            return;
        }

        $allowedStatuses = ['Active', 'Pending', 'Withdrawn'];
        if (!in_array($status, $allowedStatuses, true)) {
            echo json_encode(['success' => false, 'message' => 'Estado de inscripción inválido.']);
            return;
        }

        $query = "SELECT section_id FROM enrollment WHERE id = :enrollment_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':enrollment_id', $enrollment_id, PDO::PARAM_INT);
        $stmt->execute();
        $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$enrollment) {
            echo json_encode(['success' => false, 'message' => 'La inscripción no existe.']);
            return;
        }

        if (!$this->sectionModel->verifyTeacherOwnership($enrollment['section_id'], $userId)) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos sobre esta inscripción.']);
            return;
        }

        $updated = $this->enrollmentModel->updateEnrollmentStatus($enrollment_id, $status);

        if ($updated) {
            echo json_encode(['success' => true, 'message' => 'Estado de inscripción actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado de inscripción.']);
        }
    }

    /**
     * RESPONSABILIDAD 2: Ver solicitudes de inscripción pendientes
     * GET /api/teacher/pending-requests
     */
    public function getPendingRequests($userId, $enrollmentRequestModel) {
        $this->checkTeacherAccess($userId);
        header('Content-Type: application/json');

        $requests = $enrollmentRequestModel->getPendingRequestsByTeacher($userId);
        echo json_encode(["success" => true, "data" => $requests]);
    }

    /**
     * RESPONSABILIDAD 3 y 4: Aprobar o Rechazar solicitudes de inscripción
     * POST /api/teacher/process-request
     */
    public function processRequest($userId) {
        $this->checkTeacherAccess($userId);
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $request_id = $input['request_id'] ?? null;
        $action = $input['action'] ?? null; // 'Accepted' o 'Rejected'

        if (!$request_id || !$action) {
            echo json_encode(["success" => false, "message" => "ID de solicitud y acción requeridos."]);
            return;
        }

        if ($action === 'Accepted') {
            $result = $this->enrollmentModel->approveRequest($request_id);
        } elseif ($action === 'Rejected') {
            $result = $this->enrollmentModel->rejectRequest($request_id);
        } else {
            echo json_encode(["success" => false, "message" => "Acción no válida."]);
            return;
        }

        if ($result) {
            echo json_encode(["success" => true, "message" => "Solicitud procesada correctamente como: " . $action]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al procesar la solicitud en la base de datos."]);
        }
    }

    /**
     * RESPONSABILIDAD: Abrir una nueva sesión de clase
     * POST /api/teacher/open-session
     */
    public function openClass($userId) {
        $this->checkTeacherAccess($userId);
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $section_id = $input['section_id'] ?? null;
        $session_type = $input['session_type'] ?? 'Regular';
        $extraordinary_reason = $input['extraordinary_reason'] ?? null;

        if (!$section_id) {
            echo json_encode(['success' => false, 'message' => 'Falta el parámetro obligatorio: section_id.']);
            return;
        }

        // Seguridad extra: verificar que la sección le pertenezca a este profesor
        if (!$this->sectionModel->verifyTeacherOwnership($section_id, $userId)) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos sobre esta sección de clase.']);
            return;
        }

        // Evitar duplicados si ya hay una abierta hoy
        $existing = $this->classSessionModel->getActiveSession($section_id);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una sesión de clase activa hoy para esta sección.']);
            return;
        }

        $sessionId = $this->classSessionModel->openSession($section_id, $session_type, $extraordinary_reason);

        if ($sessionId) {
            echo json_encode(['success' => true, 'message' => 'Sesión de clase iniciada exitosamente.', 'session_id' => $sessionId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al intentar abrir la sesión de clase.']);
        }
    }

    /**
     * RESPONSABILIDAD 5: Escaneo de QR (Endpoint /api/teacher/scan)
     * POST /api/teacher/scan
     */
    public function scanQr($userId) {
        $this->checkTeacherAccess($userId);
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $qr_token = $input['qr_token'] ?? null;
        $section_id = $input['section_id'] ?? null;

        if (!$qr_token || !$section_id) {
            echo json_encode(['success' => false, 'message' => 'Parámetros incompletos: qr_token y section_id son requeridos.']);
            return;
        }

        // Delegamos de forma segura la lógica transaccional al servicio
        $response = $this->attendanceService->processQrScan($qr_token, $section_id);
        echo json_encode($response);
    }

    /**
     * MODIFICACIÓN MANUAL: Cambiar estado o justificar inasistencia
     * POST /api/teacher/manual-override
     */
    public function manualOverride($userId) {
        $this->checkTeacherAccess($userId);
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $session_id = $input['session_id'] ?? null;
        $student_id = $input['student_id'] ?? null;
        $status = $input['status'] ?? null;
        $excuse_reason = $input['excuse_reason'] ?? null;

        if (!$session_id || !$student_id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Parámetros insuficientes para la modificación manual.']);
            return;
        }

        $allowedStatuses = ['Absent', 'Present', 'Excused', 'Withdrawn'];
        if (!in_array($status, $allowedStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Estado de asistencia inválido.']);
            return;
        }

        $updated = $this->attendanceModel->updateAttendanceManually($session_id, $student_id, $status, $excuse_reason);

        if ($updated) {
            echo json_encode(['success' => true, 'message' => 'Asistencia modificada manualmente de forma exitosa.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el registro de asistencia.']);
        }
    }

    /**
     * RESPONSABILIDAD: Cierre de sesiones de clase con ausencias masivas
     * POST /api/teacher/close-session
     */
    public function closeClass($userId) {
        $this->checkTeacherAccess($userId);
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $session_id = $input['session_id'] ?? null;
        $section_id = $input['section_id'] ?? null;

        if (!$session_id || !$section_id) {
            echo json_encode(['success' => false, 'message' => 'Faltan parámetros obligatorios para ejecutar el cierre.']);
            return;
        }

        $response = $this->attendanceService->closeSessionAndMarkAbsents($session_id, $section_id);
        echo json_encode($response);
    }
}