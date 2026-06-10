<?php
//AttendanceService.php
class AttendanceService {
    private $attendanceModel;
    private $classSessionModel;
    private $enrollmentModel;

    // El constructor recibe los modelos mediante inyección de dependencias para el Autoload
    public function __construct($attendanceModel, $classSessionModel, $enrollmentModel) {
        $this->attendanceModel = $attendanceModel;
        $this->classSessionModel = $classSessionModel;
        $this->enrollmentModel = $enrollmentModel;
    }

    /**
     * Lógica centralizada para procesar el escaneo del código QR (Endpoint 5)
     */
    public function processQrScan($qr_token, $section_id) {
        // 1. Traducir el token QR al ID del estudiante (Tabla qr_credential)
        $student_id = $this->attendanceModel->getStudentIdByToken($qr_token);
        if (!$student_id) {
            return [
                'success' => false, 
                'error_code' => 'INVALID_QR',
                'message' => 'Código QR inválido, bloqueado o expirado.'
            ];
        }

        // 2. Buscar la sesión en curso ('In Progress') para el día de hoy
        $activeSession = $this->classSessionModel->getActiveSession($section_id);
        
        if (!$activeSession) {
            // AUTO-INCIAR SESIÓN si no hay una activa para hoy
            $newSessionId = $this->classSessionModel->createSession($section_id);
            if (!$newSessionId) {
                return [
                    'success' => false, 
                    'error_code' => 'SESSION_CREATE_ERROR',
                    'message' => 'No hay sesión activa y no se pudo iniciar una automáticamente.'
                ];
            }
            $session_id = $newSessionId;
        } else {
            $session_id = $activeSession['id'];
        }

        // 3. Verifica que el estudiante esté realmente inscrito y activo en esta sección
        if (!$this->enrollmentModel->verifyStudentEnrollment($student_id, $section_id)) {
            return [
                'success' => false, 
                'error_code' => 'NOT_ENROLLED',
                'message' => 'El estudiante no se encuentra inscrito o activo en esta sección.'
            ];
        }

        // 4. Registrar la presencia en la base de datos (con ON DUPLICATE KEY UPDATE)
        $register = $this->attendanceModel->registerPresence($session_id, $student_id);
        if (!$register) {
            return [
                'success' => false, 
                'error_code' => 'DB_INSERT_ERROR',
                'message' => 'Error al registrar la asistencia en la base de datos.'
            ];
        }

        // 5. Obtener los datos cruzados con 'profile' para retornar al profesor
        $student_info = $this->attendanceModel->getScannedStudentInfo($session_id, $student_id);

        if (!$student_info) {
            return [
                'success' => false, 
                'error_code' => 'PROFILE_NOT_FOUND',
                'message' => 'Asistencia registrada, pero no se encontraron datos del perfil del alumno.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Asistencia registrada con éxito.',
            'data' => [
                'student_name' => $student_info['student_name'],
                'status' => $student_info['status']
            ]
        ];
    }

    /**
     * Lógica para cerrar la sesión de clase de forma segura (Endpoint 6)
     */
    public function closeClassSession($session_id, $section_id) {
        // 1. Cambiar el estado de la clase a cerrado guardando el tiempo actual en 'actual_end_time'
        $closed = $this->classSessionModel->closeSession($session_id);
        if (!$closed) {
            return [
                'success' => false, 
                'error_code' => 'SESSION_NOT_MODIFIED',
                'message' => 'La sesión ya está cerrada, no existe o no se pudo actualizar.'
            ];
        }

        // 2. Ejecutar la inserción masiva de inasistencias en la tabla 'attendance'
        $bulkAbsent = $this->attendanceModel->bulkMarkAbsent($session_id, $section_id);
        if (!$bulkAbsent) {
            return [
                'success' => false, 
                'error_code' => 'BULK_ABSENT_ERROR',
                'message' => 'La clase se cerró, pero hubo un problema al procesar los ausentes del sistema.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Sesión cerrada exitosamente y alumnos inasistentes registrados.'
        ];
    }
}