<?php
/**
 * API para descarga de documentos
 * Sistema de Gestión de Laboratorios - Quimiosalud
 */

// Iniciar sesión
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('No autorizado');
}

// Configuración
define('BASE_REPOSITORY_PATH', 'C:\\Users\\ESTADISTICA\\Documents\\GitHub\\Update_Lab\\lab_updater\\');

$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'maestra_bd',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Función para conectar a la base de datos
function connectDB() {
    global $DB_CONFIG;
    try {
        $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
        $pdo = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

try {
    // Obtener ID del documento
    $documentId = $_GET['id'] ?? null;
    
    if (!$documentId || !is_numeric($documentId)) {
        http_response_code(400);
        die('ID de documento inválido');
    }
    
    // Conectar a la base de datos
    $pdo = connectDB();
    
    // Buscar el documento
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            Numero_ID, 
            Fecha_ID, 
            Nombre_Lab, 
            Ruta_Soporte, 
            estado_descarga 
        FROM buscador_lab 
        WHERE id = ?
    ");
    $stmt->execute([$documentId]);
    $document = $stmt->fetch();
    
    if (!$document) {
        http_response_code(404);
        die('Documento no encontrado');
    }
    
    // Verificar que el documento esté completado
    if ($document['estado_descarga'] !== 'COMPLETADO') {
        http_response_code(400);
        die('El documento no está disponible para descarga');
    }
    
    // Construir ruta completa del archivo
    $relativePath = $document['Ruta_Soporte'];
    $fullPath = BASE_REPOSITORY_PATH . $relativePath;
    
    // Normalizar la ruta
    $fullPath = str_replace('/', DIRECTORY_SEPARATOR, $fullPath);
    
    // Verificar que el archivo existe
    if (!file_exists($fullPath)) {
        http_response_code(404);
        die('Archivo no encontrado en el servidor');
    }
    
    // Obtener información del archivo
    $fileSize = filesize($fullPath);
    $fileName = basename($fullPath);
    
    // Generar nombre descriptivo para descarga
    $downloadName = sprintf(
        'LAB_%s_%s_%s.pdf',
        preg_replace('/[^a-zA-Z0-9]/', '', $document['Nombre_Lab']),
        $document['Numero_ID'],
        date('Y-m-d', strtotime($document['Fecha_ID']))
    );
    
    // Configurar headers para descarga
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Limpiar buffer de salida
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Enviar el archivo
    readfile($fullPath);
    
    // Opcional: Registrar la descarga en logs
    $logEntry = sprintf(
        "[%s] Usuario: %s descargó documento ID: %s - %s\n",
        date('Y-m-d H:i:s'),
        $_SESSION['username'] ?? 'unknown',
        $documentId,
        $downloadName
    );
    
    // Crear directorio logs si no existe
    $logDir = dirname(__FILE__) . '/../logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logDir . 'downloads_' . date('Y-m-d') . '.log', $logEntry, FILE_APPEND | LOCK_EX);
    
} catch (Exception $e) {
    http_response_code(500);
    die('Error interno del servidor: ' . $e->getMessage());
}
?>