<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/JWTHelper.php';

use App\Models\User;

class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Método que maneja el proceso de inicio de sesión
     * Valida los datos de entrada, verifica las credenciales y establece la sesión del usuario si es exitoso
     * Responde con JSON
     */
    public function login() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit();
        }

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Por favor, complete todos los campos.'
            ]);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'El formato del correo electrónico es inválido.'
            ]);
            exit();
        }

        $userModel = new User($this->db);
        $user = $userModel->read($email, $password);

        if ($user) {
            // Generar token JWT.
            $token = \JWTHelper::generateToken($user['id'], $user['email'] ?? $email, $user['role'] ?? null);

            // Establecer cookie HttpOnly de sesión (expira al cerrar el navegador)
            setcookie('jwt', $token, [
                'expires' => 0, 
                'path' => '/',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            // Determinar la redirección según el rol
            $role = $user['role'] ?? null;
            $redirect = 'index.php?dashboard_admin'; // Default
            
            if ($role === 'Admin') {
                $redirect = 'index.php?dashboard_admin';
            } elseif ($role === 'Teacher') {
                $redirect = 'index.php?dashboard_profesor';
            } elseif ($role === 'Student') {
                $redirect = 'index.php?dashboard_estudiante';
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Inicio de sesión correcto.',
                'token' => $token,
                'redirect' => $redirect
            ]);
            exit();
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Credenciales incorrectas.'
        ]);
        exit();
    }

    /**
     * Método para cerrar la sesión
     * Destruye la sesión y redirige al inicio
     */
    public function logout() {
        // 1. Limpiar Cookie JWT
        if (isset($_COOKIE['jwt'])) {
            setcookie('jwt', '', time() - 3600, '/');
            unset($_COOKIE['jwt']);
        }

        // 2. Destruir sesión PHP si existe
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        // 3. Limpieza profunda en el cliente y redirección
        echo "
        <script>
            localStorage.clear();
            sessionStorage.clear();
            window.location.href = 'index.php';
        </script>
        ";
        exit();
    }

    /**
     * Maneja el proceso de registro de nuevos estudiantes
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit();

        $input = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');
        $firstName = trim($input['first_name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');
        $idNumber = trim($input['id_number'] ?? '');
        $careerId = intval($input['career_id'] ?? 1);

        if (empty($email) || empty($password) || empty($firstName) || empty($idNumber)) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios.']);
            exit();
        }

        $userModel = new \App\Models\User($this->db);
        
        // Verificar si el correo ya existe
        $queryCheck = "SELECT id FROM user WHERE email = :email";
        $stmt = $this->db->prepare($queryCheck);
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'El correo electrónico ya está registrado.']);
            exit();
        }

        $success = $userModel->create([
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'id_number' => $idNumber,
            'career_id' => $careerId
        ]);

        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Usuario registrado con éxito. Ahora puede iniciar sesión.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error al procesar el registro.']);
        }
        exit();
    }
}
