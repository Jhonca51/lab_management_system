<?php
/**
 * Funciones auxiliares del sistema
 * Quimiosalud - Sistema de Gestión de Laboratorios
 */

/**
 * Sanitizar entrada de datos
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    return $date->format($format);
}

/**
 * Formatear fecha y hora
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    return formatDate($datetime, $format);
}

/**
 * Redireccionar con mensaje
 */
function redirect($url, $message = null, $type = 'info') {
    if ($message) {
        $_SESSION['flash_message'] = [
            'text' => $message,
            'type' => $type
        ];
    }
    
    header("Location: $url");
    exit;
}

/**
 * Mostrar mensaje flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Verificar permisos
 */
function checkPermission($permission) {
    $user = new User();
    if (!$user->hasPermission($permission)) {
        redirect('index.php?page=dashboard', 'No tienes permisos para realizar esta acción', 'error');
    }
}

/**
 * Log de actividades
 */
function logActivity($action, $details = '') {
    try {
        $db = Database::getInstance();
        $user = getCurrentUser();
        
        $sql = "INSERT INTO activity_log (user_id, username, action, details, ip_address, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $db->query($sql, [
            $user['id'] ?? null,
            $user['username'] ?? 'system',
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    } catch (Exception $e) {
        // Log silencioso - no interrumpir la aplicación
        error_log("Error logging activity: " . $e->getMessage());
    }
}

/**
 * Obtener opciones de estado
 */
function getStatusOptions() {
    return [
        'PENDIENTE' => 'Pendiente',
        'COMPLETADO' => 'Completado',
        'EN_PROCESO' => 'En Proceso',
        'FALLIDO' => 'Fallido'
    ];
}

/**
 * Obtener clase CSS para estado
 */
function getStatusClass($status) {
    $classes = [
        'PENDIENTE' => 'status-pendiente',
        'COMPLETADO' => 'status-completado',
        'EN_PROCESO' => 'status-en_proceso',
        'FALLIDO' => 'status-fallido'
    ];
    
    return $classes[strtoupper($status)] ?? 'status-pendiente';
}

/**
 * Obtener icono para estado
 */
function getStatusIcon($status) {
    $icons = [
        'PENDIENTE' => 'fas fa-clock',
        'COMPLETADO' => 'fas fa-check-circle',
        'EN_PROCESO' => 'fas fa-spinner',
        'FALLIDO' => 'fas fa-times-circle'
    ];
    
    return $icons[strtoupper($status)] ?? 'fas fa-clock';
}

/**
 * Formatear número
 */
function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

/**
 * Generar paginación
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    $html = '<nav aria-label="Paginación">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Botón anterior
    if ($currentPage > 1) {
        $prevPage = $currentPage - 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$baseUrl}&page={$prevPage}'>Anterior</a></li>";
    }
    
    // Números de página
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $currentPage) ? 'active' : '';
        $html .= "<li class='page-item {$active}'><a class='page-link' href='{$baseUrl}&page={$i}'>{$i}</a></li>";
    }
    
    // Botón siguiente
    if ($currentPage < $totalPages) {
        $nextPage = $currentPage + 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$baseUrl}&page={$nextPage}'>Siguiente</a></li>";
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Generar breadcrumbs
 */
function generateBreadcrumbs($items) {
    $html = '<nav aria-label="breadcrumb">';
    $html .= '<ol class="breadcrumb">';
    
    $count = count($items);
    foreach ($items as $index => $item) {
        $isLast = ($index == $count - 1);
        
        if ($isLast) {
            $html .= "<li class='breadcrumb-item active' aria-current='page'>{$item['title']}</li>";
        } else {
            $html .= "<li class='breadcrumb-item'><a href='{$item['url']}'>{$item['title']}</a></li>";
        }
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    
    return $html;
}
?>