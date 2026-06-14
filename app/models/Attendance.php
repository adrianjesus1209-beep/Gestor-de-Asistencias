<?php
// app/models/Attendance.php

class Attendance {
    private $db;
    private $table = "attendance";

    public function __construct($database_connection) {
        $this->db = $database_connection;
    }

    /**
     * CONEXIÓN CON EL MÓDULO DE GABRIEL: Traducir QR Token a ID de Estudiante
     * Busca el token escaneado en la tabla qr_credential y verifica que esté 'Active'
     */
    public function getStudentIdByToken($qr_token) {
        $query = "SELECT user_id FROM qr_credential 
                  WHERE qr_token = :qr_token AND qr_status = 'Active' 
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":qr_token", $qr_token, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['user_id'] : false;
    }

    /**
     * REQUISITO DEL FLUJO QR (ENDPOINT 5)
     * Registra o actualiza la asistencia a 'Present' cuando un alumno escanea el código.
     * Utiliza 'ON DUPLICATE KEY UPDATE' debido al índice único (session_id, student_id).
     */
    public function registerQrAttendance($session_id, $student_id) {
        $query = "INSERT INTO " . $this->table . " 
                    (session_id, student_id, attendance_status, registered_at, modification_source) 
                  VALUES 
                    (:session_id, :student_id, 'Present', CURTIME(), 'Scanned')
                  ON DUPLICATE KEY UPDATE 
                    attendance_status = 'Present', 
                    registered_at = CURTIME(), 
                    modification_source = 'Scanned'";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":session_id", $session_id, PDO::PARAM_INT);
        $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

  /**
   * OBTIENE LOS DATOS DEL ESTUDIANTE ESCANEADO PARA RETORNAR AL PANEL
   */
  public function getScannedStudentInfo($session_id, $student_id) {
    $query = "SELECT a.session_id, a.student_id, a.attendance_status, a.registered_at, a.modification_source, p.first_name, p.last_name, p.id_number
          FROM " . $this->table . " a
          INNER JOIN user u ON a.student_id = u.id
          INNER JOIN profile p ON u.profile_id = p.id
          WHERE a.session_id = :session_id AND a.student_id = :student_id
          LIMIT 1";

    $stmt = $this->db->prepare($query);
    $stmt->bindParam(":session_id", $session_id, PDO::PARAM_INT);
    $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
  }

    /**
     * REQUISITO DEL PROFESOR: Modificación manual (Cambiar estado o justificar)
     * Permite al docente cambiar estados y añadir una justificación escrita (excuse_reason).
     */
    public function updateAttendanceManually($session_id, $student_id, $status, $excuse_reason = null) {
        $query = "INSERT INTO " . $this->table . " 
                    (session_id, student_id, attendance_status, modification_source, excuse_reason) 
                  VALUES 
                    (:session_id, :student_id, :status, 'Manual', :excuse_reason)
                  ON DUPLICATE KEY UPDATE 
                    attendance_status = :status_update, 
                    modification_source = 'Manual', 
                    excuse_reason = :excuse_reason_update";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":session_id", $session_id, PDO::PARAM_INT);
        $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
        $stmt->bindParam(":status", $status, PDO::PARAM_STR);
        $stmt->bindParam(":status_update", $status, PDO::PARAM_STR);
        
        if ($excuse_reason === null) {
            $stmt->bindValue(":excuse_reason", null, PDO::PARAM_NULL);
            $stmt->bindValue(":excuse_reason_update", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":excuse_reason", $excuse_reason, PDO::PARAM_STR);
            $stmt->bindParam(":excuse_reason_update", $excuse_reason, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

    /**
     * REQUISITO DE CIERRE (GILBERT): Inicialización o Inyección masiva de ausencias
     * Al cerrar la clase, este método busca a todos los alumnos inscritos en la sección 
     * que NO tengan ningún registro previo en esta sesión y les inserta 'Absent' de forma automática.
     */
    public function bulkAbsentForUnregisteredStudents($session_id, $section_id) {
        $query = "INSERT INTO " . $this->table . " (session_id, student_id, attendance_status, modification_source)
                  SELECT :session_id, e.student_id, 'Absent', 'System'
                  FROM enrollment e
                  WHERE e.section_id = :section_id AND e.enrollment_status = 'Active'
                    AND e.student_id NOT IN (
                        SELECT a.student_id 
                        FROM attendance a 
                        WHERE a.session_id = :session_id_sub
                    )";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":session_id", $session_id, PDO::PARAM_INT);
        $stmt->bindParam(":section_id", $section_id, PDO::PARAM_INT);
        $stmt->bindParam(":session_id_sub", $session_id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}