<?php

namespace App\Controllers;

use Models\Subject;
use Models\Profile;
use App\Services\ScheduleService;
use App\Services\AttendancePdfService;

class StudentController {
    private $db;
    private $subjectModel;
    private $profileModel;
    private $scheduleService;
    private $pdfService;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->subjectModel = new Subject($dbConnection);
        $this->profileModel = new Profile($dbConnection);
        $this->scheduleService = new ScheduleService();
        $this->pdfService = new AttendancePdfService();
    }

    //GET /api/student/available-subjects
    public function getAvailableSubjects($userId) {
        $profile = $this->profileModel->getCompleteProfile($userId);
        if (!$profile) {
            return json_encode(["success" => false, "message" => "Perfil no encontrado"]);
        }

        $careerId = $profile['career_id'] ?? null;
        if (!$careerId) {
            return json_encode(["success" => false, "message" => "El estudiante no tiene una carrera asignada"]);
        }

        $subjects = $this->subjectModel->getAvailableWithSections($careerId);
        return json_encode(["success" => true, "data" => $subjects]);
    }

    //POST /api/student/enroll
    public function enroll($request, $userId) {
        $sectionId = $request['section_id'] ?? null;

        if (!$sectionId) {
            return json_encode(["success" => false, "message" => "ID de sección requerido"]);
        }

        $subject = $this->subjectModel->getSubjectBySection($sectionId);
        if (!$subject) {
            return json_encode(["success" => false, "message" => "La sección o materia no existe"]);
        }

        $profile = $this->profileModel->getCompleteProfile($userId);
        $careerId = $profile['career_id'] ?? null;

        if (!$this->subjectModel->belongsToCareer($subject['id'], $careerId)) {
            return json_encode(["success" => false, "message" => "Esta materia no pertenece a tu plan de estudios"]);
        }

        if ($this->subjectModel->isAlreadyEnrolled($userId, $sectionId)) {
            return json_encode(["success" => false, "message" => "Ya posees una solicitud o inscripción activa en esta sección"]);
        }

        if (!$this->subjectModel->hasAvailableCapacity($sectionId)) {
            return json_encode(["success" => false, "message" => "No hay cupos disponibles en la sección seleccionada"]);
        }

        $success = $this->subjectModel->createEnrollmentRequest($userId, $sectionId);

        if ($success) {
            $requestId = $this->db->lastInsertId();
            return json_encode([
                "success" => true, 
                "message" => "Solicitud enviada",
                "data" => [
                    "enrollment_id" => (int)$requestId
                ]
            ]);
        }

        return json_encode(["success" => false, "message" => "Error interno al procesar la inscripción"]);
    }

    //  GET /api/student/my-schedule
    public function getMySchedule($userId) {
        $rawSchedule = $this->subjectModel->getStudentSections($userId);
        $formattedSchedule = $this->scheduleService->format($rawSchedule);

        return json_encode(["success" => true, "data" => $formattedSchedule]);
    }

    // GET /api/student/my-attendance
    public function getMyAttendance($userId) {
        $attendance = $this->subjectModel->getAttendanceHistory($userId);
        
        $formattedAttendance = array_map(function($row) {
            return [
                "date" => $row['session_date'],
                "subject" => $row['subject'],
                "status" => $row['status'],
                "time" => $row['registered_at'] ? substr($row['registered_at'], 0, 5) : '--:--'
            ];
        }, $attendance);

        return json_encode(["success" => true, "data" => $formattedAttendance]);
    }

    // GET /api/student/my-enrollments
    public function getMyEnrollments($userId) {
        $requests = $this->subjectModel->getStudentEnrollmentRequests($userId);
        return json_encode(["success" => true, "data" => $requests]);
    }

    // GENERACIÓN Y VISTA PREVIA DEL PDF
    public function downloadAttendancePdf($userId) {
        $attendance = $this->subjectModel->getAttendanceHistory($userId);
        $profile = $this->profileModel->getCompleteProfile($userId);
        
        if (!$profile) {
            return json_encode(["success" => false, "message" => "Perfil no encontrado para generar reporte"]);
        }

        $pdfResult = $this->pdfService->generate($profile, $attendance);
        
        if (ob_get_length()) {
            ob_clean();
        }
        
       
        $pdfResult['dompdf']->stream($pdfResult['filename'], ['Attachment' => false]);
        exit;
    }
}