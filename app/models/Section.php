<?php
// app/models/Section.php

class Section {
    private $db;
    private $table = "section";

    public function __construct($database_connection) {
        $this->db = $database_connection;
    }

    /**
     * CREAR UNA NUEVA SECCIÓN
     * Cumple con la regla: "Siempre usar PDO con sentencias preparadas"
     */
    public function create($subject_id, $teacher_id, $section_name, $day_of_week, $start_time, $end_time, $default_delegate_id = null) {
        $query = "INSERT INTO " . $this->table . " 
                  (subject_id, teacher_id, section_name, day_of_week, start_time, end_time, default_delegate_id, section_status) 
                  VALUES (:subject_id, :teacher_id, :section_name, :day_of_week, :start_time, :end_time, :default_delegate_id, 'Active')";
        
        $stmt = $this->db->prepare($query);
        
        // Vinculación de parámetros seguros para evitar Inyección SQL
        $stmt->bindParam(":subject_id", $subject_id, PDO::PARAM_INT);
        $stmt->bindParam(":teacher_id", $teacher_id, PDO::PARAM_INT);
        $stmt->bindParam(":section_name", $section_name, PDO::PARAM_STR);
        $stmt->bindParam(":day_of_week", $day_of_week, PDO::PARAM_STR); // 'Monday', 'Tuesday', etc.
        $stmt->bindParam(":start_time", $start_time, PDO::PARAM_STR);   // 'HH:MM:SS'
        $stmt->bindParam(":end_time", $end_time, PDO::PARAM_STR);       // 'HH:MM:SS'
        
        // El delegado puede ser opcional (NULL)
        if ($default_delegate_id === null) {
            $stmt->bindValue(":default_delegate_id", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":default_delegate_id", $default_delegate_id, PDO::PARAM_INT);
        }
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId(); // Devuelve el ID de la sección recién creada
        }
        
        return false;
    }

    /**
     * OBTENER SECCIONES DE UN PROFESOR (Con el nombre de la materia)
     */
    public function getByTeacherId($teacher_id) {
        $query = "SELECT s.id, s.id AS section_id, s.section_name, s.day_of_week, s.start_time, s.end_time, sub.subject_name 
                  FROM " . $this->table . " s
                  INNER JOIN subject sub ON s.subject_id = sub.id
                  WHERE s.teacher_id = :teacher_id AND s.section_status = 'Active'";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":teacher_id", $teacher_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * VALIDACIÓN DE SEGURIDAD: Verifica la pertenencia de la sección al profesor
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