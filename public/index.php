<?php
session_set_cookie_params(0);
session_start();
use App\Controllers\AuthController;
use Config\Database;

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

// Archivo principal de ruteo
if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false || $_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['api'])) {
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// PREVENIR CACHE DEL NAVEGADOR
// Esto fuerza a que si el usuario da al botón "Atrás", se recargue el estado desde el servidor
// garantizando que si cerró sesión la vea bloqueada.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Detectar automáticamente BASE_URL
if (!function_exists('detectBaseUrl')) {
    function detectBaseUrl() {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $baseDir = dirname($scriptName);
        $baseDir = str_replace('\\', '/', $baseDir);
        
        // Si estamos en la raíz (htdocs), BASE_URL debe incluir /public para los assets
        // a menos que el usuario los haya movido a la raíz.
        $baseResult = rtrim($baseDir, '/');
        
        // Si entramos por el wrapper de la raíz, necesitamos que BASE_URL apunte a public
        // para que las rutas de assets (CSS, imágenes) no se rompan.
        if (defined('APP_INDEX_ROOT_WRAPPER')) {
            return $baseResult . '/public';
        }
        
        return $baseResult;
    }
}
define('BASE_URL', detectBaseUrl());

// Manejar POST de Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password']) && !isset($_GET['api'])) {
    header('Content-Type: application/json');
    $db = Database::getInstance()->getConnection();
    $authController = new AuthController($db);
    $authController->login();
    exit;
}

// Manejar API de Registro
if (($_GET['api'] ?? '') === 'register') {
    header('Content-Type: application/json');
    $db = Database::getInstance()->getConnection();
    $authController = new AuthController($db);
    $authController->register();
    exit;
}

// Manejar Logout
if (isset($_GET['logout'])) {
    $db = Database::getInstance()->getConnection();
    $authController = new AuthController($db);
    $authController->logout();
    exit;
}

// Cargar datos de héroes
$heroes_file = __DIR__ . '/../app/models/heroes.php';
$heroes = file_exists($heroes_file) ? require_once $heroes_file : [];

// Obtener parámetros de ruta
$route = $_GET['route'] ?? null;
$showLogin = isset($_GET['login']);
$showRegister = isset($_GET['register']);
$showDashboardEstudiante = isset($_GET['dashboard_estudiante']);
$showDashboardProfesor = isset($_GET['dashboard_profesor']);
$showDashboardAdmin = isset($_GET['dashboard_admin']);
$showList = isset($_GET['list']);
$showQR = isset($_GET['qr']);
$heroeId = $_GET['id'] ?? null;

// Almacenar datos de autenticación globales
$authPayload = null;
require_once __DIR__ . '/../app/helpers/JWTHelper.php';
$token = $_COOKIE['jwt'] ?? null;
if ($token) {
    $authPayload = JWTHelper::validateToken($token);
}

// Seguridad: Validar JWT para Dashboards y secciones privadas
if ($showDashboardAdmin || $showDashboardEstudiante || $showDashboardProfesor || $showList || $showQR || $route === 'profile' || $route === 'edit-profile') {
    if (!$authPayload) {
        header('Location: index.php?login');
        exit;
    }
}

// Layout Superior
if (!isset($_GET['api'])) {
    require_once __DIR__ . '/../app/views/layouts/header.php';
}

// Ruteador
if ($showLogin) {
    require_once __DIR__ . '/../app/views/auth/login.php';
} elseif ($showRegister) {
    require_once __DIR__ . '/../app/views/auth/register.php';
} elseif ($showDashboardEstudiante) {
    require_once __DIR__ . '/../app/views/dashboard/estudiante.php';
} elseif ($showDashboardProfesor) {
    require_once __DIR__ . '/../app/views/dashboard/profesor.php';
} elseif ($showDashboardAdmin) {
    require_once __DIR__ . '/../app/views/dashboard/admin.php';
} elseif ($route === 'update-profile') {
    // Procesar actualización de perfil (POST)
    if (!$authPayload) { header('Location: index.php?login'); exit; }
    
    $db = Database::getInstance()->getConnection();
    $userId = $authPayload['user_id'];
    
    // Obtener el profile_id del usuario actual
    $stmtP = $db->prepare("SELECT profile_id FROM user WHERE id = :id");
    $stmtP->execute([':id' => $userId]);
    $userRow = $stmtP->fetch();
    $profileId = $userRow['profile_id'] ?? null;
    
    if ($profileId) {
        $updates = [];
        $params = [':profile_id' => $profileId];
        
        // Campos de texto
        $fields = ['first_name', 'middle_name', 'last_name', 'second_last_name'];
        foreach ($fields as $field) {
            if (!empty($_POST[$field])) {
                $updates[] = "$field = :$field";
                $params[":$field"] = htmlspecialchars(trim($_POST[$field]));
            }
        }
        
        // Subida de foto de perfil
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_pic'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                $filename = 'profile_' . $userId . '_' . time() . '.' . $ext;
                $dest = __DIR__ . '/assets/img/profiles/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $updates[] = "profile_picture = :profile_picture";
                    $params[':profile_picture'] = $filename;
                }
            }
        }
        
        if (!empty($updates)) {
            $sql = "UPDATE profile SET " . implode(', ', $updates) . " WHERE id = :profile_id";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        }
    }
    
    header('Location: index.php?route=profile&updated=1');
    exit;
} elseif ($route === 'edit-profile') {
    require_once __DIR__ . '/../app/views/profiles/edit-profile.php';
} elseif ($route === 'profile') {
    require_once __DIR__ . '/../app/views/profiles/profile.php';
} elseif ($showList) {
    require_once __DIR__ . '/../app/views/lists/lista-estudiantes.php';
} elseif (isset($_GET['attendance_list'])) {
    require_once __DIR__ . '/../app/views/lists/asistencias.php';
} elseif ($heroeId && isset($heroes[$heroeId])) {
    $heroe = $heroes[$heroeId];
    require_once __DIR__ . '/../app/views/heroes/info.php';
} elseif ($showQR) {
    require_once __DIR__ . '/../app/views/qr/qr.php';
} elseif (isset($_GET['api'])) {
    require_once __DIR__ . '/../app/models/Section.php';
    require_once __DIR__ . '/../app/models/Enrollment.php';
    require_once __DIR__ . '/../app/models/ClassSession.php';
    require_once __DIR__ . '/../app/models/Attendance.php';
    require_once __DIR__ . '/../app/services/AttendanceService.php';
    require_once __DIR__ . '/../app/controllers/TeacherController.php';

    $db = Database::getInstance()->getConnection();
    $attendanceModel = new Attendance($db);
    $enrollmentModel = new Enrollment($db);
    $classSessionModel = new ClassSession($db);

    $teacherController = new TeacherController(
        new Section($db),
        $enrollmentModel,
        $classSessionModel,
        new AttendanceService($attendanceModel, $classSessionModel, $enrollmentModel)
    );

    if ($_GET['api'] === 'update_enrollment') {
        $teacherController->updateEnrollmentStatus();
    } elseif ($_GET['api'] === 'scan_qr') {
        $teacherController->scanQrCode();
    } elseif ($_GET['api'] === 'toggle_attendance') {
        $teacherController->toggleAttendanceManual();
    } elseif ($_GET['api'] === 'enroll_student') {
        $input = json_decode(file_get_contents("php://input"), true);
        $student_id  = intval($input['student_id'] ?? 0);
        $section_id  = intval($input['section_id'] ?? 0);
        if (!$student_id || !$section_id) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']); exit;
        }
        $chk = $db->prepare("SELECT id FROM enrollment WHERE student_id = :s AND section_id = :sec");
        $chk->execute([':s' => $student_id, ':sec' => $section_id]);
        if ($chk->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El estudiante ya está inscrito.']); exit;
        }
        $ins = $db->prepare("INSERT INTO enrollment (student_id, section_id, enrollment_status) VALUES (:s, :sec, 'Active')");
        $ok = $ins->execute([':s' => $student_id, ':sec' => $section_id]);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Inscrito correctamente.' : 'Error al inscribir.']);
        exit;
    } elseif ($_GET['api'] === 'respond_request') {
        $input = json_decode(file_get_contents("php://input"), true);
        $request_id = intval($input['request_id'] ?? 0);
        $student_id = intval($input['student_id'] ?? 0);
        $section_id = intval($input['section_id'] ?? 0);
        $decision   = $input['decision'] ?? '';

        if (!$request_id || !in_array($decision, ['Accepted', 'Rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']); exit;
        }

        // Actualizar estado de la solicitud
        $upd = $db->prepare("UPDATE enrollment_request SET status = :status WHERE id = :id");
        $upd->execute([':status' => $decision, ':id' => $request_id]);

        if ($decision === 'Accepted') {
            // Verificar si ya está inscrito
            $chk = $db->prepare("SELECT id FROM enrollment WHERE student_id = :s AND section_id = :sec");
            $chk->execute([':s' => $student_id, ':sec' => $section_id]);
            if (!$chk->fetch()) {
                $ins = $db->prepare("INSERT INTO enrollment (student_id, section_id, enrollment_status) VALUES (:s, :sec, 'Active')");
                $ins->execute([':s' => $student_id, ':sec' => $section_id]);
            }
            echo json_encode(['success' => true, 'message' => 'Solicitud aceptada. El estudiante fue inscrito.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Solicitud denegada.']);
        }
        exit;
    }
    exit;
} else {
    require_once __DIR__ . '/../app/views/home.php';
}

// Layout Inferior
if (!isset($_GET['api'])) {
    require_once __DIR__ . '/../app/views/layouts/footer.php';
}
