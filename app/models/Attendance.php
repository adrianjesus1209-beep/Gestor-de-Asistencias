<?php
// Attendance.php
class Attendance {
    private $db;
    private $table = "attendance";

    public function __construct($database_connection) {
        $this->db = $database_connection;
    }

    /**
     * Obtener el student_id usando el qr_token
     */
    public function getStudentIdByToken($qr_token) {
        $query = "SELECT user_id FROM qr_credential WHERE qr_token = :token AND qr_status = 'Active' LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":token", $qr_token);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['user_id'] : null;
    }

    /**
     * Registrar presencia (Presente)
     */
    public function registerPresence($session_id, $student_id) {
        $query = "INSERT INTO " . $this->table . " 
                  (session_id, student_id, attendance_status, registered_at, modification_source) 
                  VALUES (:session_id, :student_id, 'Present', CURRENT_TIME(), 'Scanned')
                  ON DUPLICATE KEY UPDATE 
                  attendance_status = 'Present', 
                  registered_at = CURRENT_TIME(), 
                  modification_source = 'Scanned'";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":session_id", $session_id);
        $stmt->bindParam(":student_id", $student_id);
        return $stmt->execute();
    }

    /**
     * Obtener info del estudiante escaneado para feedback visual
     */
    public function getScannedStudentInfo($session_id, $student_id) {
        $query = "SELECT CONCAT(p.first_name, ' ', p.last_name) as student_name, a.attendance_status as status
                  FROM attendance a
                  JOIN user u ON a.student_id = u.id
                  JOIN profile p ON u.profile_id = p.id
                  WHERE a.session_id = :session_id AND a.student_id = :student_id LIMIT 1";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":session_id", $session_id);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Marcar ausentes masivamente al cerrar sesion
     */
    public function bulkMarkAbsent($session_id, $section_id) {
        $query = "INSERT INTO " . $this->table . " (session_id, student_id, attendance_status, modification_source)
                  SELECT :session_id, student_id, 'Absent', 'System'
                  FROM enrollment
                  WHERE section_id = :section_id AND enrollment_status = 'Active'
                  AND student_id NOT IN (SELECT student_id FROM attendance WHERE session_id = :session_id_check)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":session_id", $session_id);
        $stmt->bindParam(":section_id", $section_id);
        $stmt->bindParam(":session_id_check", $session_id);
        return $stmt->execute();
    }

    /**
     * Eliminar registro de asistencia (Volver a Ausente)
     */
    public function deleteAttendance($session_id, $student_id) {
        $query = "DELETE FROM " . $this->table . " WHERE session_id = :session_id AND student_id = :student_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":session_id", $session_id);
        $stmt->bindParam(":student_id", $student_id);
        return $stmt->execute();
    }
}
