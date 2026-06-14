<?php
namespace Models;
use PDO;
class Subject {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // 1. Busca las materias del pensum incluyendo sus respectivas secciones activas
    public function getAvailableWithSections($careerId) {
        $sql = "SELECT s.id AS subject_id, s.subject_code, s.subject_name,
                       sec.id AS section_id, sec.section_name, sec.day_of_week, sec.start_time, sec.end_time
                FROM `subject` s 
                INNER JOIN `pensum` p ON s.id = p.subject_id 
                INNER JOIN `section` sec ON s.id = sec.subject_id
                WHERE p.career_id = :career_id AND sec.section_status = 'Active'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['career_id' => $careerId]);
        
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $structured = [];

        foreach ($results as $row) {
            $subId = $row['subject_id'];
            if (!isset($structured[$subId])) {
                $structured[$subId] = [
                    "subject_id" => $row['subject_id'],
                    "subject_code" => $row['subject_code'],
                    "subject_name" => $row['subject_name'],
                    "sections" => []
                ];
            }
            if ($row['section_id']) {
                $structured[$subId]['sections'][] = [
                    "section_id" => $row['section_id'],
                    "section_name" => $row['section_name'],
                    "day_of_week" => $row['day_of_week'],
                    "start_time" => $row['start_time'],
                    "end_time" => $row['end_time']
                ];
            }
        }
        return array_values($structured);
    }

    public function belongsToCareer($subjectId, $careerId) {
        $sql = "SELECT 1 FROM `pensum` WHERE subject_id = :subject_id AND career_id = :career_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['subject_id' => $subjectId, 'career_id' => $careerId]);
        return (bool)$stmt->fetch();
    }

    public function getSubjectBySection($sectionId) {
        $sql = "SELECT s.* FROM `subject` s 
                INNER JOIN `section` sec ON s.id = sec.subject_id 
                WHERE sec.id = :section_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['section_id' => $sectionId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    //  Verifica duplicados tanto en solicitudes pendientes/aceptadas como en inscripciones
    public function isAlreadyEnrolled($userId, $sectionId) {
        $sql = "SELECT 1 FROM `enrollment_request` 
                WHERE student_id = :student_id AND section_id = :section_id AND status != 'Rejected'
                UNION 
                SELECT 1 FROM `enrollment` 
                WHERE student_id = :student_id AND section_id = :section_id AND enrollment_status != 'Withdrawn'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $userId, 'section_id' => $sectionId]);
        return (bool)$stmt->fetch();
    }

    // Inserta en enrollment_request usando 'Pending' y 'requested_at' (que se llena por defecto)
    public function createEnrollmentRequest($userId, $sectionId) {
        $sql = "INSERT INTO `enrollment_request` (student_id, section_id, status) 
                VALUES (:student_id, :section_id, 'Pending')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['student_id' => $userId, 'section_id' => $sectionId]);
    }

    // Cuenta los cupos reales basándose en la tabla de inscripciones definitivas de Gilbert
    public function hasAvailableCapacity($sectionId) {
        $sqlCount = "SELECT COUNT(*) FROM `enrollment` WHERE section_id = :section_id AND enrollment_status = 'Active'";
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute(['section_id' => $sectionId]);
        $enrolledCount = $stmtCount->fetchColumn();

        // Como tu tabla 'section' no tiene columna de capacidad máxima, definimos el estándar de la UNEFA (40 alumnos)
        $maxCapacity = 40; 
        return $enrolledCount < $maxCapacity;
    }

    // Apunta a e.enrolled_at de tu tabla enrollment
    public function getStudentSections($userId) {
        $sql = "SELECT sec.section_name, sec.day_of_week, sec.start_time, sec.end_time, sub.subject_name
                FROM `enrollment` e
                INNER JOIN `section` sec ON e.section_id = sec.id
                INNER JOIN `subject` sub ON sec.subject_id = sub.id
                WHERE e.student_id = :student_id AND e.enrollment_status = 'Active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Estructura exacta basada en tu tabla de asistencias de Gilbert
    public function getAttendanceHistory($userId) {
        $sql = "SELECT att.registered_at, cs.session_date, att.attendance_status AS status, sub.subject_name AS subject
                FROM `attendance` att
                INNER JOIN `class_session` cs ON att.session_id = cs.id
                INNER JOIN `section` sec ON cs.section_id = sec.id
                INNER JOIN `subject` sub ON sec.subject_id = sub.id
                WHERE att.student_id = :student_id
                ORDER BY cs.session_date DESC, att.registered_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    //Obtiene los estados de las solicitudes directo de enrollment_request para el Endpoint 5
    public function getStudentEnrollmentRequests($userId) {
        $sql = "SELECT sub.subject_name AS subject, sec.section_name AS section, er.status
                FROM `enrollment_request` er
                INNER JOIN `section` sec ON er.section_id = sec.id
                INNER JOIN `subject` sub ON sec.subject_id = sub.id
                WHERE er.student_id = :student_id
                ORDER BY er.requested_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}