<?php

namespace App\Models;

use PDO;
use Exception;

class User {
    private $db;
    private $table = 'user';

    public function __construct($db) {
        $this->db = $db;
    }

    public function findByEmail($email) {
        $query = "SELECT u.*, r.role_name AS role
                  FROM " . $this->table . " u
                  INNER JOIN roles r ON u.role_id = r.id
                  WHERE u.email = :email
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT u.*, r.role_name AS role
                  FROM " . $this->table . " u
                  INNER JOIN roles r ON u.role_id = r.id
                  WHERE u.id = :id
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Método para leer un usuario por correo electrónico y contraseña
     * Realiza una consulta a la base de datos para encontrar un usuario con el correo electrónico
     */
    public function read($email, $password) {
        $user = $this->findByEmail($email);

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
    public function getProfileData($userId) {
        // Datos simulados para IDs de prueba
        if ($userId == 0) {
            return [
                'first_name' => 'Admin', 'middle_name' => '', 'last_name' => 'General', 'second_last_name' => '',
                'id_number' => 'V-00000000', 'email' => 'admin@unefa.test', 'role' => 'Admin',
                'career_name' => 'Administracion de Sistemas', 'semester_name' => 'N/A', 'profile_picture' => 'default-profile.webp'
            ];
        }

        $query = "SELECT u.email, u.status, r.role_name AS role, p.*, c.career_name, q.qr_token 
                  FROM " . $this->table . " u
                  INNER JOIN profile p ON u.profile_id = p.id
                  LEFT JOIN career c ON p.career_id = c.id
                  LEFT JOIN qr_credential q ON u.id = q.user_id
              LEFT JOIN roles r ON u.role_id = r.id
                  WHERE u.id = :id LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo usuario y su perfil (Estudiante por defecto)
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();

            $roleId = $this->getRoleIdByName('Student');

            if (!$roleId) {
                throw new Exception('No se encontró el rol Student en la base de datos.');
            }

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
            $queryUser = "INSERT INTO user (email, password, role_id, profile_id) 
                          VALUES (:email, :password, :role_id, :profile_id)";
            $stmtUser = $this->db->prepare($queryUser);
            $stmtUser->execute([
                ':email'      => $data['email'],
                ':password'   => $hashedPassword,
                ':role_id'    => $roleId,
                ':profile_id' => $profileId
            ]);
            $userId = $this->db->lastInsertId();

            // 3. Generar QR token único
            $qrToken = bin2hex(random_bytes(32));
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

    private function getRoleIdByName($roleName) {
        $query = "SELECT id FROM roles WHERE role_name = :role_name LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':role_name' => $roleName]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        return $role['id'] ?? null;
    }
}

