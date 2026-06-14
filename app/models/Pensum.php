<?php
namespace Models;

use PDO;

class Pensum {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtiene los IDs de las materias asociadas a una carrera específica
     * CORREGIDO: Apunta a la tabla real 'pensum'
     */
    public function getSubjectsByCareer($careerId) {
        $sql = "SELECT subject_id FROM `pensum` WHERE career_id = :career_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['career_id' => $careerId]);
        
        // Retorna un array plano con los IDs de las materias (ej: [1, 4, 7, 12])
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Valida si una materia específica pertenece al pensum de una carrera
     */
    public function isSubjectInPensum($subjectId, $careerId) {
        $sql = "SELECT 1 FROM `pensum` WHERE subject_id = :subject_id AND career_id = :career_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'subject_id' => $subjectId,
            'career_id'  => $careerId
        ]);
        return (bool)$stmt->fetch();
    }
}
