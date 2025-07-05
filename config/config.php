<?php
/**
 * Configuración Principal del Sistema
 * Quimiosalud - Sistema de Gestión de Laboratorios
 */

// Configurar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar sesión
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);
    ini_set('session.cookie_lifetime', 0);
    session_start();
}

// Constantes de la aplicación
define('APP_NAME', 'Quimiosalud - Gestión de Laboratorios');
define('APP_VERSION', '1.0.0');
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes/');
define('CLASSES_PATH', BASE_PATH . '/classes/');
define('PAGES_PATH', BASE_PATH . '/pages/');

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'maestra_bd');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Incluir archivos necesarios
require_once CLASSES_PATH . 'Database.php';
require_once CLASSES_PATH . 'User.php';
require_once CLASSES_PATH . 'Laboratory.php';
require_once INCLUDES_PATH . 'functions.php';

// Inicializar base de datos
$database = new Database();
$pdo = $database->getConnection();

// Verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Obtener usuario actual
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['user_role'],
            'name' => $_SESSION['user_name']
        ];
    }
    return null;
}
?>