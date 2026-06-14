<?php
// app/models/Enrollment.php

class Enrollment {
    private $db;

    public function __construct($database_connection) {
        $this->db = $database_connection;
    }

    /**
     * ENDPOINT 2: Obtener estudiantes activos en una sección (Ordenados alfabéticamente)
     * Une 'enrollment' con 'user' y 'profile' para traer los datos limpios de la A a la Z
     */
    public function getStudentsBySection($section_id) {
        $query = "SELECT p.id_number, p.first_name, p.last_name, e.enrollment_status, u.id AS student_id, e.id AS enrollment_id
                  FROM enrollment e
                  INNER JOIN user u ON e.student_id = u.id
                  INNER JOIN profile p ON u.profile_id = p.id
                  WHERE e.section_id = :section_id AND e.enrollment_status = 'Active'
                  ORDER BY p.first_name ASC, p.last_name ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":section_id", $section_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ACTUALIZA EL ESTADO DE UNA MATRÍCULA
     */
    public function updateEnrollmentStatus($enrollment_id, $status) {
        $query = "UPDATE enrollment 
                  SET enrollment_status = :status 
                  WHERE id = :enrollment_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":enrollment_id", $enrollment_id, PDO::PARAM_INT);
        $stmt->bindParam(":status", $status, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * VERIFICA SI UN ESTUDIANTE ESTÁ INSCRITO Y ACTIVO EN UNA SECCIÓN
     */
    public function verifyStudentEnrollment($student_id, $section_id) {
        $query = "SELECT id 
                  FROM enrollment 
                  WHERE student_id = :student_id 
                    AND section_id = :section_id 
                    AND enrollment_status = 'Active' 
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
        $stmt->bindParam(":section_id", $section_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * OBTIENE LAS SOLICITUDES PENDIENTES DE UN PROFESOR
     */
    public function getPendingRequestsByTeacher($teacher_id) {
        $query = "SELECT er.id AS request_id, er.student_id, er.section_id, p.first_name, p.last_name, p.id_number, s.section_name, sub.subject_name, er.requested_at
                  FROM enrollment_request er
                  INNER JOIN section s ON er.section_id = s.id
                  INNER JOIN subject sub ON s.subject_id = sub.id
                  INNER JOIN user u ON er.student_id = u.id
                  INNER JOIN profile p ON u.profile_id = p.id
                  WHERE s.teacher_id = :teacher_id AND er.status = 'Pending'
                  ORDER BY er.requested_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":teacher_id", $teacher_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ENDPOINT 3: Aprobar solicitud de inscripción
     * MEJORADO: Obtiene los datos de estudiante y sección directamente desde la solicitud en una transacción segura
     */
    public function approveRequest($request_id) {
        try {
            $this->db->beginTransaction();

            // 1. Primero buscamos el student_id y section_id asociados a esta solicitud
            $queryFind = "SELECT student_id, section_id FROM enrollment_request WHERE id = :request_id LIMIT 1";
            $stmtFind = $this->db->prepare($queryFind);
            $stmtFind->bindParam(":request_id", $request_id, PDO::PARAM_INT);
            $stmtFind->execute();
            $requestData = $stmtFind->fetch(PDO::FETCH_ASSOC);

            if (!$requestData) {
                $this->db->rollBack();
                return false;
            }

            // 2. Actualizar la solicitud en 'enrollment_request' a 'Accepted'
            $queryRequest = "UPDATE enrollment_request 
                             SET status = 'Accepted' 
                             WHERE id = :request_id";
            $stmtReq = $this->db->prepare($queryRequest);
            $stmtReq->bindParam(":request_id", $request_id, PDO::PARAM_INT);
            $stmtReq->execute();

            // 3. Insertar al estudiante en la tabla 'enrollment' como 'Active'
            $queryEnroll = "INSERT INTO enrollment (student_id, section_id, enrollment_status) 
                            VALUES (:student_id, :section_id, 'Active')";
            $stmtEnroll = $this->db->prepare($queryEnroll);
            $stmtEnroll->bindParam(":student_id", $requestData['student_id'], PDO::PARAM_INT);
            $stmtEnroll->bindParam(":section_id", $requestData['section_id'], PDO::PARAM_INT);
            $stmtEnroll->execute();

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * ENDPOINT 4: Rechazar solicitud de inscripción
     * Solo cambia el estado en 'enrollment_request' a 'Rejected'
     */
    public function rejectRequest($request_id) {
        $query = "UPDATE enrollment_request 
                  SET status = 'Rejected' 
                  WHERE id = :request_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":request_id", $request_id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}