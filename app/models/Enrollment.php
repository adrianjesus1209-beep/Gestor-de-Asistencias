<?php
//Enrollment.php
class Enrollment {
    private $db;
    private $table = "enrollment";

    public function __construct($database_connection) {
        $this->db = $database_connection;
    }

    /**
     * Lista de estudiantes inscritos en una sección (Ordenados Alfabéticamente)
     * Trae todos los estados (Active, Pending, Withdrawn) para que el profesor los vea
     */
    public function getStudentsBySection($section_id) {
        $query = "SELECT e.id AS enrollment_id, u.id AS student_id, p.id_number, 
                         p.first_name, p.last_name, e.enrollment_status, q.qr_token
                  FROM " . $this->table . " e
                  INNER JOIN user u ON e.student_id = u.id
                  INNER JOIN profile p ON u.profile_id = p.id
                  LEFT JOIN qr_credential q ON u.id = q.user_id
                  WHERE e.section_id = :section_id
                  ORDER BY p.first_name ASC, p.last_name ASC";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":section_id", $section_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Endpoints 3 y 4: El profesor aprueba (cambia a 'Active') o rechaza/retira (cambia a 'Withdrawn')
     */
    public function updateStatus($enrollment_id, $status) {
        $query = "UPDATE " . $this->table . " 
                  SET enrollment_status = :status 
                  WHERE id = :enrollment_id";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":status", $status, PDO::PARAM_STR);
        $stmt->bindParam(":enrollment_id", $enrollment_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * CRÍTICO (ACTUALIZADO): Bloquea el QR si el alumno está en 'Pending'
     * Solo permite registrar asistencia si el estado es estrictamente 'Active'
     */
    public function verifyStudentEnrollment($student_id, $section_id) {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE student_id = :student_id AND section_id = :section_id AND enrollment_status = 'Active' 
                  LIMIT 1";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
        $stmt->bindParam(":section_id", $section_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}