<?php
/**
 * Sistema de Gestión de Laboratorios - Quimiosalud
 * Archivo principal refactorizado
 * Versión modular completamente funcional
 */

// Incluir configuración principal
require_once 'config/config.php';

// Obtener página solicitada
$page = sanitize($_GET['page'] ?? 'login');
$action = sanitize($_GET['action'] ?? '');

// Inicializar objetos principales
$user = new User();
$laboratory = new Laboratory();

// Manejar logout
if (isset($_GET['logout'])) {
    $user->logout();
    redirect('index.php?page=login', 'Sesión cerrada correctamente', 'success');
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    // Verificar CSRF token
    if (!verifyCSRFToken($csrfToken)) {
        redirect('index.php?page=login', 'Token de seguridad inválido', 'error');
    }
    
    if (empty($username) || empty($password)) {
        redirect('index.php?page=login', 'Por favor complete todos los campos', 'error');
    }
    
    $result = $user->authenticate($username, $password);
    
    if ($result['success']) {
        logActivity('login_success', 'Usuario autenticado: ' . $username);
        redirect('index.php?page=dashboard', 'Bienvenido al sistema', 'success');
    } else {
        logActivity('login_failed', 'Intento de login fallido: ' . $username);
        redirect('index.php?page=login', $result['error'], 'error');
    }
}

// Verificar autenticación para páginas protegidas
$protectedPages = ['dashboard', 'search', 'laboratories', 'profile', 'settings'];
if (in_array($page, $protectedPages) && !isLoggedIn()) {
    redirect('index.php?page=login', 'Debe iniciar sesión para acceder', 'warning');
}

// Redireccionar si ya está logueado y trata de acceder al login
if ($page === 'login' && isLoggedIn()) {
    redirect('index.php?page=dashboard');
}

// Definir páginas válidas
$validPages = [
    'login' => 'pages/login.php',
    'dashboard' => 'pages/dashboard.php',
    'search' => 'pages/search.php',
    'laboratories' => 'pages/laboratories.php',
    'laboratory' => 'pages/laboratory.php',
    'profile' => 'pages/profile.php',
    'settings' => 'pages/settings.php',
    'document' => 'pages/document.php',
    'edit_document' => 'pages/edit_document.php',
    'users' => 'pages/users.php',
    'reports' => 'pages/reports.php'
];

// Verificar si la página existe
if (!isset($validPages[$page])) {
    $page = isLoggedIn() ? 'dashboard' : 'login';
}

// Verificar permisos específicos por página
$pagePermissions = [
    'users' => 'admin',
    'settings' => 'admin',
    'reports' => 'manager'
];

if (isset($pagePermissions[$page]) && isLoggedIn()) {
    $requiredPermission = $pagePermissions[$page];
    $currentUser = getCurrentUser();
    
    if ($currentUser['role'] !== $requiredPermission && $currentUser['role'] !== 'admin') {
        redirect('index.php?page=dashboard', 'No tiene permisos para acceder a esta página', 'error');
    }
}

// Manejar acciones AJAX
if ($action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    switch ($action) {
        case 'update_status':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'error' => 'No autorizado']);
                exit;
            }
            
            $documentId = (int)($_POST['document_id'] ?? 0);
            $newStatus = sanitize($_POST['status'] ?? '');
            
            if ($documentId && $newStatus) {
                $result = $laboratory->updateDocumentStatus($documentId, $newStatus);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
            }
            exit;
            
        case 'get_stats':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'error' => 'No autorizado']);
                exit;
            }
            
            $stats = $laboratory->getStats();
            echo json_encode(['success' => true, 'data' => $stats]);
            exit;
            
        case 'search_documents':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'error' => 'No autorizado']);
                exit;
            }
            
            $filters = [
                'lab' => sanitize($_POST['lab'] ?? ''),
                'doc' => sanitize($_POST['doc'] ?? ''),
                'status' => sanitize($_POST['status'] ?? ''),
                'fecha_inicio' => sanitize($_POST['fecha_inicio'] ?? ''),
                'fecha_fin' => sanitize($_POST['fecha_fin'] ?? ''),
                'limit' => (int)($_POST['limit'] ?? 50)
            ];
            
            $results = $laboratory->searchDocuments($filters);
            echo json_encode(['success' => true, 'data' => $results]);
            exit;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no reconocida']);
            exit;
    }
}

// Cargar página solicitada
$pageFile = PAGES_PATH . $validPages[$page];

if (file_exists($pageFile)) {
    include $pageFile;
} else {
    // Página de error 404
    include PAGES_PATH . '404.php';
}
?>