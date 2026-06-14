<?php
// app/services/AttendanceService.php

class AttendanceService {
    private $db;
    private $attendanceModel;
    private $classSessionModel;
    private $enrollmentModel;

    // Recibimos la conexión y los modelos ya instanciados desde el controlador (Ideal para el Autoload)
    public function __construct($database_connection, $attendanceModel, $classSessionModel, $enrollmentModel) {
        $this->db = $database_connection;
        $this->attendanceModel = $attendanceModel;
        $this->classSessionModel = $classSessionModel;
        $this->enrollmentModel = $enrollmentModel;
    }

    /**
     * LÓGICA CENTRAL PARA EL ESCANEO DEL CÓDIGO QR (ENDPOINT 5)
     * Coordina la validación del token, sesión de clase, inscripción activa y registro.
     */
    public function processQrScan($qr_token, $section_id) {
        // 1. Traducir el token del QR al ID del estudiante (Seguridad de Gabriel Cobos)
        $student_id = $this->attendanceModel->getStudentIdByToken($qr_token);
        if (!$student_id) {
            return [
                'success' => false,
                'message' => 'El código QR es inválido, expiró o no se encuentra activo.'
            ];
        }

        // 2. Verificar que exista una sesión de clase activa ('In Progress') para hoy
        $activeSession = $this->classSessionModel->getActiveSession($section_id);
        if (!$activeSession) {
            return [
                'success' => false,
                'message' => 'No hay ninguna sesión de clase activa hoy para esta sección.'
            ];
        }
        $session_id = $activeSession['id'];

        // 3. Verificar si el estudiante está realmente inscrito y con estado 'Active'
        if (!$this->enrollmentModel->verifyStudentEnrollment($student_id, $section_id)) {
            return [
                'success' => false,
                'message' => 'Acceso denegado. Tu inscripción está en espera de aprobación por el profesor o inactiva.'
            ];
        }

        // 4. Registrar o actualizar la asistencia del estudiante a 'Present'
        $registered = $this->attendanceModel->registerQrAttendance($session_id, $student_id);
        if (!$registered) {
            return [
                'success' => false,
                'message' => 'Hubo un error interno al asentar la asistencia en el servidor.'
            ];
        }

        // 5. Obtener los datos del perfil del alumno para retornarlos en vivo al panel del profesor
        $student_info = $this->attendanceModel->getScannedStudentInfo($session_id, $student_id);
        
        return [
            'success' => true,
            'message' => 'Asistencia registrada con éxito.',
            'data' => $student_info
        ];
    }

    /**
     * LÓGICA CENTRAL: CIERRE DE SESIÓN DE CLASE
     * Cierra la sesión viva y ejecuta la inserción masiva de ausentes bajo una transacción segura.
     */
    public function closeSessionAndMarkAbsents($session_id, $section_id) {
        try {
            // Iniciamos la transacción para proteger la integridad de los datos
            $this->db->beginTransaction();

            // 1. Cerrar manualmente el registro de la clase (pasa a 'Manual' e inyecta CURTIME())
            $closed = $this->classSessionModel->closeSessionManually($session_id);
            if (!$closed) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'La sesión de clase no pudo ser cerrada, no existe o ya se encuentra finalizada.'
                ];
            }

            // 2. Ejecutar la inyección masiva de inasistencias en lote
            $this->attendanceModel->bulkAbsentForUnregisteredStudents($session_id, $section_id);

            // Si todo salió bien, guardamos los cambios definitivamente
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Sesión de clase cerrada exitosamente. Los alumnos ausentes fueron registrados.'
            ];

        } catch (Exception $e) {
            // Si algo falla en el proceso, deshacemos todo para evitar datos corruptos
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Fallo transaccional al cerrar la sesión: ' . $e->getMessage()
            ];
        }
    }
}