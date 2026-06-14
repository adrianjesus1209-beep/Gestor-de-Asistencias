<?php
// Archivo temporal (front controller) - Versión con ruteo limpio
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pageScripts = [];

// Detectar automáticamente BASE_URL
function detectBaseUrl() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseDir = dirname($scriptName);
    $baseDir = str_replace(DIRECTORY_SEPARATOR, '/', $baseDir);
    return rtrim($baseDir, '/');
}
define('BASE_URL', detectBaseUrl());

// Obtener la ruta (por defecto 'home')
$route = $_GET['route'] ?? 'home';

// Parámetros adicionales (ej: id del héroe)
$heroeId = $_GET['id'] ?? null;

// Cargar datos de héroes solo si es necesario (para la vista de héroe)
$heroes = [];
if ($route === 'heroe') {
    $heroes = require_once __DIR__ . '/../app/models/heroes.php';
}

// Incluir el layout superior
require_once __DIR__ . '/../app/views/layouts/header.php';

// Ruteador (mapeo de rutas a archivos)
switch ($route) {
    case 'login':
        require_once __DIR__ . '/../app/views/auth/login.php';
        break;
    case 'change-password':
        require_once __DIR__ . '/../app/views/auth/change-password.php';
        break;
    case 'register':
        require_once __DIR__ . '/../app/views/auth/register.php';
        break;
    case 'dashboard':
        $role = $_GET['role'] ?? 'Student'; // Por ahora se pasa por GET, luego lo tomara del token
        require_once __DIR__ . '/../app/views/dashboard/dashboard.php';
    break;
    case 'profile':
        require_once __DIR__ . '/../app/views/profiles/profile.php';
        break;
    case 'edit-profile':
        require_once __DIR__ . '/../app/views/profiles/edit-profile.php';
        break;
    case 'attendance-control':
        $pageScripts = ['/js/attendance.js'];
        require_once __DIR__ . '/../app/views/teacher/attendance-control.php';
        break;
    case 'attendance-list':
        require_once __DIR__ . '/../app/views/student/lista-asistentes.php';
        break;
    case 'non-attendance-list':
        require_once __DIR__ . '/../app/views/student/lista-inasistentes.php';
        break;
    case 'inactive-list':
        require_once __DIR__ . '/../app/views/student/lista-inactivos.php';
        break;
    case 'create-matter':
        require_once __DIR__ . '/../app/views/teacher/create-matter.php';
        break;
    case 'requests-list':
        require_once __DIR__ . '/../app/views/teacher/requests.php';
        break;
    case 'manage-users':
        require_once __DIR__ . '/../app/views/admin/registro-profesores.php';
        break;
    case 'manage-actives-list':
        require_once __DIR__ . '/../app/views/admin/lista-profesores-activos.php';
        break;
    case 'manage-inactives-list':
        require_once __DIR__ . '/../app/views/admin/lista-profesores-inactivos.php';
        break;
    case 'carnet':
        require_once __DIR__ . '/../app/views/carnet/carnet.php';
        break;
    case 'security-questions':
        require_once __DIR__ . '/../app/views/security/security-questions.php';
        break;
    case 'logout':
        // Aqui iria la logica de cierre de sesion (limpiar sesion, cookies, etc.)
        // Por ahora, solo redirige al home
        header('Location: index.php?route=home');
        exit;
        break;
    case 'heroe':
        // Vista de heroe: requiere 'id' en la URL
        if ($heroeId && isset($heroes[$heroeId])) {
            $heroe = $heroes[$heroeId];
            require_once __DIR__ . '/../app/views/heroes/info.php';
        } else {
            // Si no hay heroe valido, redirigir al home
            header('Location: index.php?route=home');
            exit;
        }
        break;
    case 'home':
    default:
        require_once __DIR__ . '/../app/views/home.php';
        break;
}

// Incluir el layout inferior
require_once __DIR__ . '/../app/views/layouts/footer.php';