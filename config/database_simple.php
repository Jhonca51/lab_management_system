<?php
/**
 * Configuración simplificada de base de datos
 */

// Configuración de conexión a MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'maestra_bd');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// Variable global para la conexión PDO
$pdo = null;

/**
 * Función para obtener la conexión PDO
 * @return PDO
 */
function getDB() {
    global $pdo;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            logMessage("Error de conexión a la base de datos: " . $e->getMessage(), 'ERROR');
            die('Error de conexión a la base de datos. Por favor, contacte al administrador.');
        }
    }
    
    return $pdo;
}

/**
 * Función para ejecutar consultas preparadas
 * @param string $query
 * @param array $params
 * @return PDOStatement
 */
function executeQuery($query, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        logMessage("Error en consulta SQL: " . $e->getMessage() . " | Query: " . $query, 'ERROR');
        throw new Exception('Error en la consulta a la base de datos');
    }
}

/**
 * Función para obtener un registro
 * @param string $query
 * @param array $params
 * @return array|false
 */
function fetchOne($query, $params = []) {
    $stmt = executeQuery($query, $params);
    return $stmt->fetch();
}

/**
 * Función para obtener múltiples registros
 * @param string $query
 * @param array $params
 * @return array
 */
function fetchAll($query, $params = []) {
    $stmt = executeQuery($query, $params);
    return $stmt->fetchAll();
}

/**
 * Función para insertar un registro
 * @param string $table
 * @param array $data
 * @return string|false
 */
function insertRecord($table, $data) {
    $keys = array_keys($data);
    $fields = implode(', ', $keys);
    $placeholders = ':' . implode(', :', $keys);
    
    $query = "INSERT INTO $table ($fields) VALUES ($placeholders)";
    
    try {
        $stmt = executeQuery($query, $data);
        return getDB()->lastInsertId();
    } catch (Exception $e) {
        logMessage("Error en inserción: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Función para actualizar un registro
 * @param string $table
 * @param array $data
 * @param array $where
 * @return bool
 */
function updateRecord($table, $data, $where) {
    $setFields = [];
    foreach ($data as $key => $value) {
        $setFields[] = "$key = :$key";
    }
    $setClause = implode(', ', $setFields);
    
    $whereFields = [];
    foreach ($where as $key => $value) {
        $whereFields[] = "$key = :where_$key";
    }
    $whereClause = implode(' AND ', $whereFields);
    
    $query = "UPDATE $table SET $setClause WHERE $whereClause";
    
    // Combinar parámetros
    $params = $data;
    foreach ($where as $key => $value) {
        $params["where_$key"] = $value;
    }
    
    try {
        $stmt = executeQuery($query, $params);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        logMessage("Error en actualización: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Función para eliminar un registro
 * @param string $table
 * @param array $where
 * @return bool
 */
function deleteRecord($table, $where) {
    $whereFields = [];
    foreach ($where as $key => $value) {
        $whereFields[] = "$key = :$key";
    }
    $whereClause = implode(' AND ', $whereFields);
    
    $query = "DELETE FROM $table WHERE $whereClause";
    
    try {
        $stmt = executeQuery($query, $where);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        logMessage("Error en eliminación: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Función para contar registros
 * @param string $table
 * @param array $where
 * @return int
 */
function countRecords($table, $where = []) {
    $query = "SELECT COUNT(*) as total FROM $table";
    $params = [];
    
    if (!empty($where)) {
        $whereFields = [];
        foreach ($where as $key => $value) {
            $whereFields[] = "$key = :$key";
        }
        $query .= " WHERE " . implode(' AND ', $whereFields);
        $params = $where;
    }
    
    $result = fetchOne($query, $params);
    return (int) $result['total'];
}

/**
 * Función para verificar si existe un registro
 * @param string $table
 * @param array $where
 * @return bool
 */
function recordExists($table, $where) {
    return countRecords($table, $where) > 0;
}

/**
 * Función para obtener el estado de la conexión
 * @return bool
 */
function isConnected() {
    try {
        $db = getDB();
        $stmt = $db->query('SELECT 1');
        return $stmt !== false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Función para crear las tablas necesarias si no existen
 */
function createTablesIfNotExist() {
    $queries = [
        // Tabla de usuarios
        "CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            role ENUM('admin', 'user', 'viewer') DEFAULT 'user',
            activo TINYINT(1) DEFAULT 1,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci",
        
        // Verificar si la tabla buscador_lab existe
        "CREATE TABLE IF NOT EXISTS buscador_lab (
            id INT AUTO_INCREMENT PRIMARY KEY,
            Tipo_ID VARCHAR(50) NULL,
            Numero_ID VARCHAR(50) NOT NULL,
            Fecha_ID DATE NOT NULL,
            Nombre_Lab VARCHAR(50) NOT NULL,
            Ruta_Soporte VARCHAR(6000) NULL,
            estado_descarga ENUM('PENDIENTE', 'EN_PROCESO', 'COMPLETADO', 'FALLIDO') DEFAULT 'PENDIENTE',
            estado_organizacion ENUM('PENDIENTE', 'COMPLETADO') DEFAULT 'PENDIENTE',
            instancia_procesando INT DEFAULT NULL,
            fecha_inicio_proceso TIMESTAMP NULL,
            fecha_fin_proceso TIMESTAMP NULL,
            fecha_organizacion TIMESTAMP NULL,
            fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_numero_fecha (Numero_ID, Fecha_ID),
            INDEX idx_laboratorio (Nombre_Lab),
            INDEX idx_estado (estado_descarga)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci",
        
        // Tabla de logs
        "CREATE TABLE IF NOT EXISTS log_buscador_lab (
            id INT AUTO_INCREMENT PRIMARY KEY,
            Nombre_Lab TEXT NOT NULL,
            Ruta_Soporte TEXT NOT NULL,
            Observaciones TEXT NOT NULL,
            Fecha_Actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci"
    ];
    
    try {
        foreach ($queries as $query) {
            executeQuery($query);
        }
        logMessage("Tablas verificadas/creadas exitosamente", 'INFO');
        return true;
    } catch (Exception $e) {
        logMessage("Error creando tablas: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

// Crear las tablas al cargar la configuración
createTablesIfNotExist();
?>