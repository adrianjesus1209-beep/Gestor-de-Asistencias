<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../Utils/Validator.php';
require_once __DIR__ . '/../Utils/Security.php';

use App\Models\User;
use App\Utils\Security;
use App\Utils\Validator;
use Exception;
use PDO;

class RegisterController {
    private $db;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db);
    }

    public function registerStudent($data) {
        try {
            if (!is_array($data)) {
                return $this->jsonResponse(false, null, 'Datos inválidos.', 400);
            }

            $data = $this->sanitizePayload($data);
            $email = trim((string) ($data['email'] ?? ''));
            $password = (string) ($data['password'] ?? '');
            $firstName = trim((string) ($data['first_name'] ?? ''));
            $middleName = trim((string) ($data['middle_name'] ?? ''));
            $lastName = trim((string) ($data['last_name'] ?? ''));
            $secondLastName = trim((string) ($data['second_last_name'] ?? ''));
            $idNumber = Validator::normalizarCedula($data['id_number'] ?? '');
            $careerId = (int) ($data['career_id'] ?? 0);

            if ($email === '' || $password === '' || $firstName === '' || $lastName === '' || $idNumber === '') {
                return $this->jsonResponse(false, null, 'Faltan datos obligatorios.', 400);
            }

            if (!Validator::email($email)) {
                return $this->jsonResponse(false, null, 'Correo inválido.', 400);
            }

            if (!Validator::nombre($firstName) || !Validator::nombre($lastName)) {
                return $this->jsonResponse(false, null, 'Nombre y apellido deben contener solo letras.', 400);
            }

            if ($middleName !== '' && !Validator::nombre($middleName)) {
                return $this->jsonResponse(false, null, 'El segundo nombre no es válido.', 400);
            }

            if ($secondLastName !== '' && !Validator::nombre($secondLastName)) {
                return $this->jsonResponse(false, null, 'El segundo apellido no es válido.', 400);
            }

            if (!Validator::cedula($idNumber)) {
                return $this->jsonResponse(false, null, 'Cédula inválida.', 400);
            }

            $passwordValidation = Validator::password($password);
            if (empty($passwordValidation['valid'])) {
                return $this->jsonResponse(false, null, implode(' ', $passwordValidation['errors'] ?? ['Contraseña inválida.']), 400);
            }

            if ($careerId <= 0 || !$this->careerExists($careerId)) {
                return $this->jsonResponse(false, null, 'Carrera no válida.', 400);
            }

            if ($this->profileExistsByIdNumber($idNumber)) {
                return $this->jsonResponse(false, null, 'Cédula ya registrada.', 400);
            }

            if ($this->userModel->findByEmail($email)) {
                return $this->jsonResponse(false, null, 'Correo ya registrado.', 400);
            }

            $userId = $this->createAccount('Student', [
                'email' => $email,
                'password' => $password,
                'first_name' => $firstName,
                'middle_name' => $middleName !== '' ? $middleName : null,
                'last_name' => $lastName,
                'second_last_name' => $secondLastName !== '' ? $secondLastName : null,
                'id_number' => $idNumber,
                'career_id' => $careerId,
            ], 'PendingApproval');

            return $this->jsonResponse(true, [
                'user_id' => $userId,
                'status' => 'pending_approval',
            ], 'Estudiante registrado. Pendiente de aprobación.', 201);
        } catch (Exception $e) {
            return $this->jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
        }
    }

    public function registerTeacher($data) {
        try {
            if (!is_array($data)) {
                return $this->jsonResponse(false, null, 'Datos inválidos.', 400);
            }

            $data = $this->sanitizePayload($data);
            $email = trim((string) ($data['email'] ?? ''));
            $password = (string) ($data['password'] ?? '');
            $firstName = trim((string) ($data['first_name'] ?? ''));
            $middleName = trim((string) ($data['middle_name'] ?? ''));
            $lastName = trim((string) ($data['last_name'] ?? ''));
            $secondLastName = trim((string) ($data['second_last_name'] ?? ''));
            $idNumber = Validator::normalizarCedula($data['id_number'] ?? '');

            if ($email === '' || $password === '' || $firstName === '' || $lastName === '' || $idNumber === '') {
                return $this->jsonResponse(false, null, 'Faltan datos obligatorios.', 400);
            }

            if (!Validator::email($email)) {
                return $this->jsonResponse(false, null, 'Correo inválido.', 400);
            }

            if (!Validator::nombre($firstName) || !Validator::nombre($lastName)) {
                return $this->jsonResponse(false, null, 'Nombre y apellido deben contener solo letras.', 400);
            }

            if ($middleName !== '' && !Validator::nombre($middleName)) {
                return $this->jsonResponse(false, null, 'El segundo nombre no es válido.', 400);
            }

            if ($secondLastName !== '' && !Validator::nombre($secondLastName)) {
                return $this->jsonResponse(false, null, 'El segundo apellido no es válido.', 400);
            }

            if (!Validator::cedula($idNumber)) {
                return $this->jsonResponse(false, null, 'Cédula inválida.', 400);
            }

            $passwordValidation = Validator::password($password);
            if (empty($passwordValidation['valid'])) {
                return $this->jsonResponse(false, null, implode(' ', $passwordValidation['errors'] ?? ['Contraseña inválida.']), 400);
            }

            if ($this->profileExistsByIdNumber($idNumber)) {
                return $this->jsonResponse(false, null, 'Cédula ya registrada.', 400);
            }

            if ($this->userModel->findByEmail($email)) {
                return $this->jsonResponse(false, null, 'Correo ya registrado.', 400);
            }

            $userId = $this->createAccount('Teacher', [
                'email' => $email,
                'password' => $password,
                'first_name' => $firstName,
                'middle_name' => $middleName !== '' ? $middleName : null,
                'last_name' => $lastName,
                'second_last_name' => $secondLastName !== '' ? $secondLastName : null,
                'id_number' => $idNumber,
                'career_id' => null,
            ], 'Active');

            return $this->jsonResponse(true, [
                'user_id' => $userId,
                'status' => 'pending_approval',
            ], 'Profesor registrado. Pendiente de aprobación.', 201);
        } catch (Exception $e) {
            return $this->jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
        }
    }

    public function getMyProfile($userId) {
        try {
            $profile = $this->userModel->getProfileData((int) $userId);

            if (!$profile) {
                return $this->jsonResponse(false, null, 'Perfil no encontrado.', 404);
            }

            $profile['is_approved'] = (($profile['status'] ?? '') === 'Active');
            unset($profile['password']);

            return $this->jsonResponse(true, $profile, 'Perfil obtenido.');
        } catch (Exception $e) {
            return $this->jsonResponse(false, null, $e->getMessage(), 500);
        }
    }

    public function updateMyProfile($userId, $data) {
        try {
            $userId = (int) $userId;
            if ($userId <= 0 || !is_array($data)) {
                return $this->jsonResponse(false, null, 'Datos inválidos.', 400);
            }

            $data = $this->sanitizePayload($data);
            $profileId = $this->getProfileIdByUserId($userId);
            if (!$profileId) {
                return $this->jsonResponse(false, null, 'Perfil no encontrado.', 404);
            }

            $updates = [];
            $params = [':profile_id' => $profileId];

            foreach (['first_name', 'middle_name', 'last_name', 'second_last_name'] as $field) {
                if (array_key_exists($field, $data) && $data[$field] !== '') {
                    $updates[] = $field . ' = :' . $field;
                    $params[':' . $field] = $data[$field];
                }
            }

            if (array_key_exists('email', $data) && trim((string) $data['email']) !== '') {
                $email = trim((string) $data['email']);
                if (!Validator::email($email)) {
                    return $this->jsonResponse(false, null, 'Correo inválido.', 400);
                }

                $existing = $this->userModel->findByEmail($email);
                if ($existing && (int) ($existing['id'] ?? 0) !== $userId) {
                    return $this->jsonResponse(false, null, 'Correo ya en uso.', 400);
                }

                $stmtEmail = $this->db->prepare('UPDATE user SET email = :email WHERE id = :user_id');
                $stmtEmail->execute([
                    ':email' => $email,
                    ':user_id' => $userId,
                ]);
            }

            if (!empty($updates)) {
                $sql = 'UPDATE profile SET ' . implode(', ', $updates) . ' WHERE id = :profile_id';
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }

            return $this->jsonResponse(true, null, 'Perfil actualizado.');
        } catch (Exception $e) {
            return $this->jsonResponse(false, null, $e->getMessage(), 500);
        }
    }

    public function uploadPhoto($userId, $files) {
        try {
            $userId = (int) $userId;
            if ($userId <= 0 || !is_array($files)) {
                return $this->jsonResponse(false, null, 'Datos inválidos.', 400);
            }

            if (!isset($files['photo']) || $files['photo']['error'] !== UPLOAD_ERR_OK) {
                return $this->jsonResponse(false, null, 'No se recibió la foto.', 400);
            }

            $file = $files['photo'];
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);

            if (!in_array($fileType, $allowedTypes, true)) {
                return $this->jsonResponse(false, null, 'Solo JPG, PNG o WEBP.', 400);
            }

            if ((int) $file['size'] > 5 * 1024 * 1024) {
                return $this->jsonResponse(false, null, 'Máximo 5MB.', 400);
            }

            $profileId = $this->getProfileIdByUserId($userId);
            if (!$profileId) {
                return $this->jsonResponse(false, null, 'Perfil no encontrado.', 404);
            }

            $uploadDir = __DIR__ . '/../../public/assets/img/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($extension, $allowedExtensions, true)) {
                return $this->jsonResponse(false, null, 'Extensión no permitida.', 400);
            }

            $filename = 'profile_' . $profileId . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                return $this->jsonResponse(false, null, 'Error al guardar la imagen.', 500);
            }

            $stmt = $this->db->prepare('UPDATE profile SET profile_picture = :profile_picture WHERE id = :profile_id');
            $stmt->execute([
                ':profile_picture' => $filename,
                ':profile_id' => $profileId,
            ]);

            return $this->jsonResponse(true, ['photo_url' => '/assets/img/profiles/' . $filename], 'Foto subida.');
        } catch (Exception $e) {
            return $this->jsonResponse(false, null, $e->getMessage(), 500);
        }
    }

    public function getMyQR($userId) {
        try {
            $userId = (int) $userId;
            if ($userId <= 0) {
                return $this->jsonResponse(false, null, 'Usuario inválido.', 400);
            }

            $user = $this->userModel->findById($userId);
            if (!$user) {
                return $this->jsonResponse(false, null, 'Usuario no encontrado.', 404);
            }

            if (($user['role'] ?? '') !== 'Student') {
                return $this->jsonResponse(false, null, 'Solo estudiantes tienen QR.', 403);
            }

            if (($user['status'] ?? '') !== 'Active') {
                return $this->jsonResponse(false, null, 'Usuario no activo.', 403);
            }

            $stmt = $this->db->prepare('SELECT qr_token, qr_status FROM qr_credential WHERE user_id = :user_id LIMIT 1');
            $stmt->execute([':user_id' => $userId]);
            $qrData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$qrData || ($qrData['qr_status'] ?? '') !== 'Active') {
                return $this->jsonResponse(false, null, 'QR no disponible.', 404);
            }

            return $this->jsonResponse(true, ['qr_token' => $qrData['qr_token']], 'QR obtenido.');
        } catch (Exception $e) {
            return $this->jsonResponse(false, null, $e->getMessage(), 500);
        }
    }

    public function requestEnrollment($userId, $data) {
        try {
            $userId = (int) $userId;
            if ($userId <= 0 || !is_array($data)) {
                return $this->jsonResponse(false, null, 'Datos inválidos.', 400);
            }

            $user = $this->userModel->findById($userId);
            if (!$user) {
                return $this->jsonResponse(false, null, 'Usuario no encontrado.', 404);
            }

            if (($user['role'] ?? '') !== 'Student') {
                return $this->jsonResponse(false, null, 'Solo estudiantes pueden inscribirse.', 403);
            }

            if (($user['status'] ?? '') !== 'Active') {
                return $this->jsonResponse(false, null, 'Usuario no activo.', 403);
            }

            $sectionId = (int) ($data['section_id'] ?? 0);
            if ($sectionId <= 0) {
                return $this->jsonResponse(false, null, 'section_id requerido.', 400);
            }

            $sectionStmt = $this->db->prepare('SELECT id FROM section WHERE id = :section_id AND section_status = "Active" LIMIT 1');
            $sectionStmt->execute([':section_id' => $sectionId]);
            if (!$sectionStmt->fetch(PDO::FETCH_ASSOC)) {
                return $this->jsonResponse(false, null, 'La sección no existe o está inactiva.', 404);
            }

            $existingEnrollment = $this->db->prepare('SELECT id FROM enrollment WHERE student_id = :student_id AND section_id = :section_id LIMIT 1');
            $existingEnrollment->execute([
                ':student_id' => $userId,
                ':section_id' => $sectionId,
            ]);
            if ($existingEnrollment->fetch(PDO::FETCH_ASSOC)) {
                return $this->jsonResponse(false, null, 'El estudiante ya está inscrito en esta sección.', 400);
            }

            $existingRequest = $this->db->prepare('SELECT id, status FROM enrollment_request WHERE student_id = :student_id AND section_id = :section_id ORDER BY requested_at DESC LIMIT 1');
            $existingRequest->execute([
                ':student_id' => $userId,
                ':section_id' => $sectionId,
            ]);
            $requestRow = $existingRequest->fetch(PDO::FETCH_ASSOC);

            if ($requestRow) {
                return $this->jsonResponse(false, null, 'Ya existe una solicitud para esta sección.', 400);
            }

            $stmt = $this->db->prepare('INSERT INTO enrollment_request (student_id, section_id, status) VALUES (:student_id, :section_id, "Pending")');
            $stmt->execute([
                ':student_id' => $userId,
                ':section_id' => $sectionId,
            ]);

            return $this->jsonResponse(true, [
                'request_id' => (int) $this->db->lastInsertId(),
                'status' => 'Pending',
            ], 'Solicitud enviada. Esperando aprobación del profesor.', 201);
        } catch (Exception $e) {
            return $this->jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
        }
    }

    public function getMyPendingRequests($userId) {
        try {
            $userId = (int) $userId;
            if ($userId <= 0) {
                return $this->jsonResponse(false, null, 'Usuario inválido.', 400);
            }

            $user = $this->userModel->findById($userId);
            if (!$user) {
                return $this->jsonResponse(false, null, 'Usuario no encontrado.', 404);
            }

            if (($user['role'] ?? '') !== 'Student') {
                return $this->jsonResponse(false, null, 'Acceso no autorizado.', 403);
            }

            $pendingStmt = $this->db->prepare('SELECT er.id, er.section_id, er.status, er.requested_at, s.section_name, sub.subject_name FROM enrollment_request er INNER JOIN section s ON er.section_id = s.id INNER JOIN subject sub ON s.subject_id = sub.id WHERE er.student_id = :student_id AND er.status = "Pending" ORDER BY er.requested_at DESC');
            $pendingStmt->execute([':student_id' => $userId]);

            $acceptedStmt = $this->db->prepare('SELECT e.id, e.section_id, e.enrollment_status, e.enrolled_at, s.section_name, sub.subject_name FROM enrollment e INNER JOIN section s ON e.section_id = s.id INNER JOIN subject sub ON s.subject_id = sub.id WHERE e.student_id = :student_id AND e.enrollment_status = "Active" ORDER BY e.enrolled_at DESC');
            $acceptedStmt->execute([':student_id' => $userId]);

            return $this->jsonResponse(true, [
                'pending_requests' => $pendingStmt->fetchAll(PDO::FETCH_ASSOC),
                'accepted_enrollments' => $acceptedStmt->fetchAll(PDO::FETCH_ASSOC),
            ], 'Solicitudes obtenidas.');
        } catch (Exception $e) {
            return $this->jsonResponse(false, null, $e->getMessage(), 500);
        }
    }

    public function getPendingStudents($adminUserId) {
        try {
            $adminUserId = (int) $adminUserId;
            if ($adminUserId <= 0) {
                return $this->jsonResponse(false, null, 'Usuario inválido.', 400);
            }

            if (!Security::checkRole($this->db, $adminUserId, ['Admin'])) {
                return $this->jsonResponse(false, null, 'No autorizado. Se requiere Admin.', 403);
            }

            $stmt = $this->db->prepare('SELECT u.id AS user_id, u.email, u.status, p.id_number, p.first_name, p.middle_name, p.last_name, p.second_last_name, c.career_name FROM user u INNER JOIN profile p ON u.profile_id = p.id LEFT JOIN career c ON p.career_id = c.id INNER JOIN roles r ON u.role_id = r.id WHERE u.status = "PendingApproval" AND r.role_name = "Student" ORDER BY p.first_name ASC, p.last_name ASC');
            $stmt->execute();

            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->jsonResponse(true, [
                'pending_count' => count($students),
                'students' => $students,
            ], 'Estudiantes pendientes.');
        } catch (Exception $e) {
            return $this->jsonResponse(false, null, $e->getMessage(), 500);
        }
    }

    public function approveStudent($adminUserId, $studentUserId) {
        try {
            $adminUserId = (int) $adminUserId;
            $studentUserId = (int) $studentUserId;

            if ($adminUserId <= 0 || $studentUserId <= 0) {
                return $this->jsonResponse(false, null, 'Datos inválidos.', 400);
            }

            if (!Security::checkRole($this->db, $adminUserId, ['Admin'])) {
                return $this->jsonResponse(false, null, 'No autorizado. Se requiere Admin.', 403);
            }

            $student = $this->userModel->findById($studentUserId);
            if (!$student) {
                return $this->jsonResponse(false, null, 'Estudiante no encontrado.', 404);
            }

            if (($student['role'] ?? '') !== 'Student') {
                return $this->jsonResponse(false, null, 'El usuario no es estudiante.', 400);
            }

            if (($student['status'] ?? '') !== 'PendingApproval') {
                return $this->jsonResponse(false, null, 'El estudiante no está pendiente.', 400);
            }

            $this->db->beginTransaction();

            $update = $this->db->prepare('UPDATE user SET status = "Active", force_password_change = 0 WHERE id = :user_id');
            $update->execute([':user_id' => $studentUserId]);

            $qrToken = $this->createOrReactivateQrToken($studentUserId);

            $this->db->commit();

            return $this->jsonResponse(true, [
                'user_id' => $studentUserId,
                'qr_token' => $qrToken,
                'status' => 'active',
            ], 'Estudiante aprobado.');
        } catch (Exception $e) {
            if ($this->db instanceof PDO && $this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return $this->jsonResponse(false, null, 'Error: ' . $e->getMessage(), 500);
        }
    }

    private function createAccount($roleName, array $data, string $status = 'PendingApproval') {
        $this->db->beginTransaction();

        try {
            $roleId = $this->getRoleIdByName($roleName);
            if (!$roleId) {
                throw new Exception('Rol ' . $roleName . ' no encontrado.');
            }

            $profileStmt = $this->db->prepare('INSERT INTO profile (id_number, first_name, middle_name, last_name, second_last_name, career_id) VALUES (:id_number, :first_name, :middle_name, :last_name, :second_last_name, :career_id)');
            $profileStmt->execute([
                ':id_number' => $data['id_number'],
                ':first_name' => $data['first_name'],
                ':middle_name' => $data['middle_name'],
                ':last_name' => $data['last_name'],
                ':second_last_name' => $data['second_last_name'],
                ':career_id' => $data['career_id'],
            ]);

            $profileId = (int) $this->db->lastInsertId();
            $passwordHash = password_hash((string) $data['password'], PASSWORD_DEFAULT);

            $userStmt = $this->db->prepare('INSERT INTO user (profile_id, role_id, email, password, status, force_password_change) VALUES (:profile_id, :role_id, :email, :password, :status, 1)');
            $userStmt->execute([
                ':profile_id' => $profileId,
                ':role_id' => $roleId,
                ':email' => $data['email'],
                ':password' => $passwordHash,
                ':status' => $status,
            ]);

            $userId = (int) $this->db->lastInsertId();

            if ($roleName === 'Student') {
                $qrToken = bin2hex(random_bytes(32));
                $qrStmt = $this->db->prepare('INSERT INTO qr_credential (user_id, qr_token, qr_status) VALUES (:user_id, :qr_token, "Active")');
                $qrStmt->execute([
                    ':user_id' => $userId,
                    ':qr_token' => $qrToken,
                ]);
            }

            $this->db->commit();

            return $userId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    private function createOrReactivateQrToken($userId) {
        $stmt = $this->db->prepare('SELECT qr_token FROM qr_credential WHERE user_id = :user_id LIMIT 1');
        $stmt->execute([':user_id' => $userId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing && !empty($existing['qr_token'])) {
            $update = $this->db->prepare('UPDATE qr_credential SET qr_status = "Active" WHERE user_id = :user_id');
            $update->execute([':user_id' => $userId]);

            return $existing['qr_token'];
        }

        $qrToken = bin2hex(random_bytes(32));
        $insert = $this->db->prepare('INSERT INTO qr_credential (user_id, qr_token, qr_status) VALUES (:user_id, :qr_token, "Active")');
        $insert->execute([
            ':user_id' => $userId,
            ':qr_token' => $qrToken,
        ]);

        return $qrToken;
    }

    private function getRoleIdByName($roleName) {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE role_name = :role_name LIMIT 1');
        $stmt->execute([':role_name' => $roleName]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($role['id'] ?? 0) ?: null;
    }

    private function careerExists($careerId) {
        $stmt = $this->db->prepare('SELECT id FROM career WHERE id = :career_id LIMIT 1');
        $stmt->execute([':career_id' => $careerId]);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function profileExistsByIdNumber($idNumber) {
        $stmt = $this->db->prepare('SELECT id FROM profile WHERE id_number = :id_number LIMIT 1');
        $stmt->execute([':id_number' => $idNumber]);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getProfileIdByUserId($userId) {
        $stmt = $this->db->prepare('SELECT profile_id FROM user WHERE id = :user_id LIMIT 1');
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($row['profile_id'] ?? 0) ?: null;
    }

    private function sanitizePayload(array $data) {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $sanitized[$key] = Validator::sanitizarInput((string) $value);
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private function jsonResponse($success, $data = null, $message = '', $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response = [
            'success' => $success,
            'status' => $success ? 'success' : 'error',
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!$success) {
            $response['error'] = $message;
        }

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}