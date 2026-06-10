<?php
//ClassSession.php
class ClassSession {
    private $db;
    private $table = "class_session";

    public function __construct($database_connection) {
        $this->db = $database_connection;
    }

    /**
     * Crea o recupera una sesión de clase activa para la fecha de hoy
     */
    public function createSession($section_id) {
        $existingSession = $this->getActiveSession($section_id);
        if ($existingSession) {
            return $existingSession['id'];
        }

        $query = "INSERT INTO " . $this->table . " (section_id, session_date, start_time, closure_type) 
                  VALUES (:section_id, CURDATE(), CURTIME(), 'In Progress')";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":section_id", $section_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Cierra la sesión de clase de forma manual guardando la hora actual
     */
    public function closeSession($session_id) {
        $query = "UPDATE " . $this->table . " 
                  SET actual_end_time = CURTIME(), closure_type = 'Manual' 
                  WHERE id = :session_id AND closure_type = 'In Progress'";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":session_id", $session_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Obtiene la sesión que esté actualmente "In Progress" para el día de hoy
     */
    public function getActiveSession($section_id) {
        $query = "SELECT id, session_date, start_time FROM " . $this->table . " 
                  WHERE section_id = :section_id AND closure_type = 'In Progress' AND session_date = CURDATE()
                  LIMIT 1";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":section_id", $section_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}