<?php
//Section.php
class Section {
    private $db;
    private $table = "section";

    public function __construct($database_connection) {
        $this->db = $database_connection;
    }

    /**
     * Obtener las secciones de un profesor uniendo con la tabla subject
     */
    public function getByTeacherId($teacher_id) {
        $query = "SELECT s.id, s.section_name, s.day_of_week, s.start_time, s.end_time, sub.subject_name 
                  FROM " . $this->table . " s
                  INNER JOIN subject sub ON s.subject_id = sub.id
                  WHERE s.teacher_id = :teacher_id AND s.section_status = 'Active'";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":teacher_id", $teacher_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * VALIDACIÓN: Verifica si la sección realmente le pertenece al profesor
     */
    public function verifyTeacherOwnership($section_id, $teacher_id) {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE id = :section_id AND teacher_id = :teacher_id 
                  LIMIT 1";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":section_id", $section_id, PDO::PARAM_INT);
        $stmt->bindParam(":teacher_id", $teacher_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}