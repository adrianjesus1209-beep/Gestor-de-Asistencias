<?php
// app/models/ClassSession.php

class ClassSession {
    private $db;
    private $table = "class_session";

    public function __construct($database_connection) {
        $this->db = $database_connection;
    }

    /**
     * REQUISITO DE GILBERT: Abrir una nueva sesión de clase (Iniciar jornada)
     * Automatiza la búsqueda del delegado por defecto de la sección
     */
    public function openSession($section_id, $session_type = 'Regular', $extraordinary_reason = null) {
        // 1. Buscamos de forma automática el delegado por defecto que tiene asignado la sección
        $queryDelegate = "SELECT default_delegate_id FROM section WHERE id = :section_id LIMIT 1";
        $stmtDel = $this->db->prepare($queryDelegate);
        $stmtDel->bindParam(":section_id", $section_id, PDO::PARAM_INT);
        $stmtDel->execute();
        $section = $stmtDel->fetch(PDO::FETCH_ASSOC);
        
        $default_delegate = $section ? $section['default_delegate_id'] : null;

        // 2. Insertamos la sesión viva con fecha y hora actual del servidor
        $query = "INSERT INTO " . $this->table . " 
                    (section_id, current_delegate_id, session_date, start_time, closure_type, session_type, extraordinary_reason) 
                  VALUES 
                    (:section_id, :current_delegate_id, CURDATE(), CURTIME(), 'In Progress', :session_type, :extraordinary_reason)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":section_id", $section_id, PDO::PARAM_INT);
        $stmt->bindParam(":session_type", $session_type, PDO::PARAM_STR);
        
        if ($default_delegate === null) {
            $stmt->bindValue(":current_delegate_id", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":current_delegate_id", $default_delegate, PDO::PARAM_INT);
        }

        if ($extraordinary_reason === null) {
            $stmt->bindValue(":extraordinary_reason", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":extraordinary_reason", $extraordinary_reason, PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            return $this->db->lastInsertId(); // Retorna el ID para guardarlo en el flujo
        }
        return false;
    }

    /**
     * REQUISITO DE GILBERT: Cerrar manualmente una sesión de clase
     * Guarda la hora final exacta y cambia el estado de 'In Progress' a 'Manual'
     */
    public function closeSessionManually($session_id) {
        $query = "UPDATE " . $this->table . " 
                  SET closure_type = 'Manual', 
                      actual_end_time = CURTIME() 
                  WHERE id = :session_id AND closure_type = 'In Progress'";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":session_id", $session_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * CONTROL INTERNO PARA EL QR: Busca si hay una clase activa hoy para esta sección
     */
    public function getActiveSession($section_id) {
        $query = "SELECT id, current_delegate_id 
                  FROM " . $this->table . " 
                  WHERE section_id = :section_id 
                    AND session_date = CURDATE() 
                    AND closure_type = 'In Progress' 
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":section_id", $section_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}