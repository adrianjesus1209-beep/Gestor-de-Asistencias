<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/JWTHelper.php';
require_once __DIR__ . '/../Utils/Validator.php';
require_once __DIR__ . '/../Utils/Security.php';

use App\Models\User;
use App\Utils\Security;
use App\Utils\Validator;

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
            $this->respond(false, 'Por favor, complete todos los campos.');
        }

        if (!Validator::email($email)) {
            $this->respond(false, 'El formato del correo electrónico es inválido.');
        }

        $userModel = new User($this->db);
        $user = $userModel->findByEmail($email);

        if (!$user) {
            $this->respond(false, 'Credenciales incorrectas.');
        }

        if (Security::isLockedOut($this->db, (int) $user['id'])) {
            $this->respond(false, 'Tu cuenta está bloqueada temporalmente. Inténtalo más tarde.');
        }

        $passwordMatches = password_verify($password, $user['password']) || $password === $user['password'];

        if (!$passwordMatches) {
            $loginState = Security::failedLogin($this->db, (int) $user['id']);

            if (!empty($loginState['locked'])) {
                $this->respond(false, 'Has superado el límite de intentos. Tu cuenta quedó bloqueada por 15 minutos.');
            }

            $this->respond(false, 'Credenciales incorrectas.');
        }

        if (($user['status'] ?? '') !== 'Active') {
            $this->respond(false, 'Tu cuenta aún no ha sido aprobada o está inactiva.');
        }

        Security::resetFailedLogins($this->db, (int) $user['id']);

        $sessionToken = Security::createSecureSession(
            $this->db,
            (int) $user['id'],
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        $token = \JWTHelper::generateToken($user['id'], $user['email'] ?? $email, $user['role'] ?? null);

        Security::setAuthCookie('jwt', $token, 0);
        Security::setAuthCookie('session_token', $sessionToken, 0);

        $role = $user['role'] ?? null;
        $redirect = 'index.php?dashboard_admin';

        if ($role === 'Admin') {
            $redirect = 'index.php?dashboard_admin';
        } elseif ($role === 'Teacher') {
            $redirect = 'index.php?dashboard_profesor';
        } elseif ($role === 'Student') {
            $redirect = 'index.php?dashboard_estudiante';
        }

        $this->respond(true, 'Inicio de sesión correcto.', [
            'token' => $token,
            'session_token' => $sessionToken,
            'redirect' => $redirect,
            'role' => $role,
        ]);
    }

    /**
     * Método para cerrar la sesión
     * Destruye la sesión y redirige al inicio
     */
    public function logout() {
        if (!empty($_COOKIE['session_token'])) {
            Security::invalidateSession($this->db, $_COOKIE['session_token']);
        }

        Security::clearAuthCookies(['jwt', 'session_token']);

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
        $password = (string) ($input['password'] ?? '');
        $firstName = Validator::sanitizarInput($input['first_name'] ?? '');
        $middleName = Validator::sanitizarInput($input['middle_name'] ?? '');
        $lastName = Validator::sanitizarInput($input['last_name'] ?? '');
        $secondLastName = Validator::sanitizarInput($input['second_last_name'] ?? '');
        $idNumber = Validator::normalizarCedula($input['id_number'] ?? '');
        $careerId = intval($input['career_id'] ?? 0);

        if (empty($email) || empty($password) || empty($firstName) || empty($lastName) || empty($idNumber)) {
            $this->respond(false, 'Faltan datos obligatorios.');
        }

        if (!Validator::email($email)) {
            $this->respond(false, 'El correo electrónico no es válido o excede el máximo permitido.');
        }

        if (!Validator::nombre($firstName) || !Validator::nombre($lastName) || ($middleName !== '' && !Validator::nombre($middleName)) || ($secondLastName !== '' && !Validator::nombre($secondLastName))) {
            $this->respond(false, 'Los nombres y apellidos solo pueden contener letras, espacios y acentos.');
        }

        if (!Validator::cedula($idNumber)) {
            $this->respond(false, 'La cédula debe tener entre 6 y 8 dígitos y puede incluir V al inicio.');
        }

        $passwordValidation = Validator::password($password);
        if (!$passwordValidation['valid']) {
            $this->respond(false, implode(' ', $passwordValidation['errors']));
        }

        if ($careerId <= 0) {
            $this->respond(false, 'La carrera seleccionada no es válida.');
        }

        $userModel = new \App\Models\User($this->db);
        
        // Verificar si el correo ya existe
        $queryCheck = "SELECT id FROM user WHERE email = :email";
        $stmt = $this->db->prepare($queryCheck);
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $this->respond(false, 'El correo electrónico ya está registrado.');
        }

        $success = $userModel->create([
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'id_number' => $idNumber,
            'career_id' => $careerId,
            'middle_name' => $middleName !== '' ? $middleName : null,
            'second_last_name' => $secondLastName !== '' ? $secondLastName : null,
        ]);

        if ($success) {
            $this->respond(true, 'Usuario registrado con éxito. Ahora puede iniciar sesión.');
        } else {
            $this->respond(false, 'Ocurrió un error al procesar el registro.');
        }
    }

    private function respond(bool $success, string $message, array $extra = []): void {
        $payload = array_merge([
            'success' => $success,
            'error' => $success ? null : $message,
            'message' => $message,
            'status' => $success ? 'success' : 'error',
        ], $extra);

        echo json_encode($payload);
        exit();
    }
}
