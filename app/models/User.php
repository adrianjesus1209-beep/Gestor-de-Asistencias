<?php

namespace App\Models;

use PDO;
use Exception;

class User
{
    private $db;
    private $table = 'user';

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Método para leer un usuario por correo electrónico y contraseña
     * Realiza una consulta a la base de datos para encontrar un usuario con el correo electrónico
     */
    public function read($email, $password)
    {

        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        // Primero intentar verificar con el hash moderno. 
        // Se mantiene el fallback para contraseñas en texto plano por compatibilidad legacy si fuera necesario, 
        // aunque se recomienda que todas sean hasheadas.
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            return $user;
        }

        return false;
    }

    /**
     * Obtener datos completos del perfil del usuario
     */
    public function getProfileData($userId)
    {
        // Datos simulados para IDs de prueba
        if ($userId == 0) {
            return [
                'first_name' => 'Admin',
                'middle_name' => '',
                'last_name' => 'General',
                'second_last_name' => '',
                'id_number' => 'V-00000000',
                'email' => 'admin@unefa.test',
                'role' => 'Admin',
                'career_name' => 'Administracion de Sistemas',
                'semester_name' => 'N/A',
                'profile_picture' => 'default-profile.webp'
            ];
        }

        $query = "SELECT u.email, u.role, p.*, c.career_name, q.qr_token 
                  FROM " . $this->table . " u
                  INNER JOIN profile p ON u.profile_id = p.id
                  LEFT JOIN career c ON p.career_id = c.id
                  LEFT JOIN qr_credential q ON u.id = q.user_id
                  WHERE u.id = :id LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo usuario y su perfil (Estudiante por defecto)
     */
    public function create($data)
    {
        try {
            $this->db->beginTransaction();

            // 1. Insertar en tabla PROFILE (campos exactos de la BD real)
            $queryProfile = "INSERT INTO profile (first_name, middle_name, last_name, second_last_name, id_number, career_id) 
                             VALUES (:first_name, :middle_name, :last_name, :second_last_name, :id_number, :career_id)";
            $stmtProfile = $this->db->prepare($queryProfile);
            $stmtProfile->execute([
                ':first_name'       => $data['first_name'],
                ':middle_name'      => $data['middle_name'] ?? null,
                ':last_name'        => $data['last_name'],
                ':second_last_name' => $data['second_last_name'] ?? null,
                ':id_number'        => $data['id_number'],
                ':career_id'        => $data['career_id']
            ]);
            $profileId = $this->db->lastInsertId();

            // 2. Insertar en tabla USER
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $queryUser = "INSERT INTO user (email, password, role, profile_id) 
                          VALUES (:email, :password, 'Student', :profile_id)";
            $stmtUser = $this->db->prepare($queryUser);
            $stmtUser->execute([
                ':email'      => $data['email'],
                ':password'   => $hashedPassword,
                ':profile_id' => $profileId
            ]);
            $userId = $this->db->lastInsertId();

            // 3. Generar QR token único
            $prefix = strtoupper(substr($data['first_name'], 0, 1) . substr($data['last_name'], 0, 1));
            $qrToken = "UNEFA-" . $prefix . "-" . $data['id_number'] . "-" . substr(uniqid(), -4);
            $queryQR = "INSERT INTO qr_credential (user_id, qr_token) VALUES (:user_id, :qr_token)";
            $stmtQR = $this->db->prepare($queryQR);
            $stmtQR->execute([':user_id' => $userId, ':qr_token' => $qrToken]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en User::create() -> " . $e->getMessage());
            return false;
        }
    }

    public function findByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmailAndIdNumber($email, $idNumber)
    {
        $query = "SELECT u.* FROM " . $this->table . " u
                  INNER JOIN profile p ON u.profile_id = p.id
                  WHERE u.email = :email AND p.id_number = :id_number LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email, ':id_number' => $idNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword(int $userId, string $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE " . $this->table . " SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':password' => $hashedPassword, ':id' => $userId]);
    }

    /**
     * Busca al usuario por email y cédula y devuelve hasta dos preguntas de seguridad
     *
     * Si no existen preguntas reales en la base de datos, retorna preguntas demo
     * para probar el flujo sin datos persistidos.
     *
     * Esta función hace dos cosas:
     * 1) Valida identidad mediante email + cédula.
     * 2) Carga hasta $limit preguntas de seguridad asociadas al usuario.
     *
     * El modo demo permite que el flujo de recuperación funcione aún cuando la tabla
     * de respuestas de seguridad no está poblada.
     */
    public function getSecurityQuestionsForUser(string $email, string $idNumber, int $limit = 2)
    {
        $query = "SELECT u.id as user_id FROM " . $this->table . " u
                  INNER JOIN profile p ON u.profile_id = p.id
                  WHERE u.email = :email AND p.id_number = :id_number LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email, ':id_number' => $idNumber]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        $query = "SELECT q.id as question_id, q.question_text
                  FROM user_security_answers usa
                  INNER JOIN security_questions q ON q.id = usa.question_id
                  WHERE usa.user_id = :user_id
                  ORDER BY RAND()
                  LIMIT " . intval($limit);
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $user['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$questions) {
            // Si no existen preguntas de seguridad guardadas para este usuario,
            // devolvemos un par de preguntas de prueba para permitir la validación
            // del flujo durante la fase de desarrollo local
            //
            // Estas preguntas no están en la base de datos, pero el código de
            // verificación también acepta un par de respuestas simuladas
            return [
                'user_id' => (int)$user['user_id'],
                'questions' => [
                    ['question_id' => 9991, 'question_text' => '¿Cuál es el nombre de tu primera mascota?'],
                    ['question_id' => 9992, 'question_text' => '¿Cuál es la marca de tu primer celular?']
                ],
                'demo_mode' => true
            ];
        }

        return [
            'user_id' => (int)$user['user_id'],
            'questions' => $questions
        ];
    }

    /**
     * Verifica que las respuestas entregadas coincidan con los hashes guardados
     *
     * Si no existen respuestas reales, admite respuestas demo para prueba local.
     *
     * Recibe un arreglo con la forma:
     * [
     *     ['question_id' => 1, 'answer' => '...'],
     *     ['question_id' => 2, 'answer' => '...']
     * ]
     *
     * El método compara los hashes almacenados o, en modo demo, compara con valores secretos predefinidos.
     */
    public function verifySecurityAnswers(int $userId, array $answers)
    {
        if (empty($answers) || count($answers) < 2) {
            return false;
        }

        $questionIds = array_map('intval', array_column($answers, 'question_id'));
        if (count($questionIds) !== count(array_unique($questionIds))) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $query = "SELECT question_id, answer_hash FROM user_security_answers WHERE user_id = ? AND question_id IN ($placeholders)";
        $stmt = $this->db->prepare($query);
        $params = array_merge([$userId], $questionIds);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) !== count($questionIds)) {
            // Si el usuario no tiene respuestas almacenadas aún, usamos valores demo
            // para poder probar la funcionalidad sin datos en la base
            if (count($rows) === 0) {
                $demoAnswers = [
                    9991 => 'firulais',
                    9992 => 'nokia'
                ];

                foreach ($answers as $answer) {
                    $questionId = intval($answer['question_id'] ?? 0);
                    $response = trim($answer['answer'] ?? '');

                    if (!isset($demoAnswers[$questionId]) || strcasecmp($demoAnswers[$questionId], $response) !== 0) {
                        return false;
                    }
                }

                return true;
            }

            return false;
        }

        $storedAnswers = [];
        foreach ($rows as $row) {
            $storedAnswers[(int)$row['question_id']] = $row['answer_hash'];
        }

        foreach ($answers as $answer) {
            $questionId = intval($answer['question_id'] ?? 0);
            $response = trim($answer['answer'] ?? '');

            if ($questionId === 0 || $response === '' || !isset($storedAnswers[$questionId])) {
                return false;
            }

            $hash = $storedAnswers[$questionId];
            if (!password_verify($response, $hash) && $response !== $hash) {
                return false;
            }
        }

        return true;
    }
}
