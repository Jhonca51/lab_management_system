<?php
/**
 * Configuración inicial del sistema
 * Crea la base de datos, tablas y usuario administrador
 */

// Configurar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de base de datos
$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'maestra_bd',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Configuración del Sistema</title>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>";
echo "</head><body class='bg-light'>";

echo "<div class='container mt-4'>";
echo "<h1 class='text-primary'><i class='fas fa-cogs me-2'></i>Configuración del Sistema</h1>";

// Paso 1: Verificar conexión a MySQL
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h5>Paso 1: Verificar Conexión a MySQL</h5></div>";
echo "<div class='card-body'>";

try {
    $pdo = new PDO("mysql:host={$DB_CONFIG['host']};charset={$DB_CONFIG['charset']}", 
                   $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "<p class='text-success'><i class='fas fa-check me-2'></i>Conexión a MySQL exitosa</p>";
} catch (PDOException $e) {
    echo "<p class='text-danger'><i class='fas fa-times me-2'></i>Error: " . $e->getMessage() . "</p>";
    echo "<div class='alert alert-danger'>Verifique que MySQL esté ejecutándose y las credenciales sean correctas.</div>";
    echo "</div></div></div></body></html>";
    exit;
}

echo "</div></div>";

// Paso 2: Crear base de datos
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h5>Paso 2: Crear Base de Datos</h5></div>";
echo "<div class='card-body'>";

try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$DB_CONFIG['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci");
    echo "<p class='text-success'><i class='fas fa-check me-2'></i>Base de datos '{$DB_CONFIG['dbname']}' creada/verificada</p>";
} catch (PDOException $e) {
    echo "<p class='text-danger'><i class='fas fa-times me-2'></i>Error creando base de datos: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Paso 3: Conectar a la base de datos específica
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h5>Paso 3: Conectar a la Base de Datos</h5></div>";
echo "<div class='card-body'>";

try {
    $pdo = new PDO("mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}", 
                   $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "<p class='text-success'><i class='fas fa-check me-2'></i>Conectado a la base de datos '{$DB_CONFIG['dbname']}'</p>";
} catch (PDOException $e) {
    echo "<p class='text-danger'><i class='fas fa-times me-2'></i>Error: " . $e->getMessage() . "</p>";
    echo "</div></div></div></body></html>";
    exit;
}

echo "</div></div>";

// Paso 4: Crear tablas
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h5>Paso 4: Crear Tablas</h5></div>";
echo "<div class='card-body'>";

$tables = [
    'usuarios' => "CREATE TABLE IF NOT EXISTS usuarios (
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
    
    'buscador_lab' => "CREATE TABLE IF NOT EXISTS buscador_lab (
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
        fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci",
    
    'log_buscador_lab' => "CREATE TABLE IF NOT EXISTS log_buscador_lab (
        id INT AUTO_INCREMENT PRIMARY KEY,
        Nombre_Lab TEXT NOT NULL,
        Ruta_Soporte TEXT NOT NULL,
        Observaciones TEXT NOT NULL,
        Fecha_Actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci"
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "<p class='text-success'><i class='fas fa-check me-2'></i>Tabla '$name' creada/verificada</p>";
    } catch (PDOException $e) {
        echo "<p class='text-danger'><i class='fas fa-times me-2'></i>Error creando tabla '$name': " . $e->getMessage() . "</p>";
    }
}

echo "</div></div>";

// Paso 5: Crear usuario administrador
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h5>Paso 5: Crear Usuario Administrador</h5></div>";
echo "<div class='card-body'>";

try {
    // Verificar si ya existe un admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn() > 0;
    
    if ($adminExists) {
        echo "<p class='text-info'><i class='fas fa-info-circle me-2'></i>Usuario 'admin' ya existe</p>";
        
        // Actualizar la contraseña por si acaso
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE username = 'admin'");
        $stmt->execute([$hashedPassword]);
        echo "<p class='text-success'><i class='fas fa-check me-2'></i>Contraseña del admin actualizada</p>";
    } else {
        // Crear nuevo usuario admin
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (username, password, email, nombre, apellido, role, activo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $hashedPassword, 'admin@quimiosalud.com', 'Administrador', 'Sistema', 'admin', 1]);
        echo "<p class='text-success'><i class='fas fa-check me-2'></i>Usuario administrador creado exitosamente</p>";
    }
    
    // Mostrar información del usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    echo "<div class='mt-3'>";
    echo "<h6>Información del Usuario Administrador:</h6>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> " . htmlspecialchars($admin['username']) . "</li>";
    echo "<li><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</li>";
    echo "<li><strong>Nombre:</strong> " . htmlspecialchars($admin['nombre'] . ' ' . $admin['apellido']) . "</li>";
    echo "<li><strong>Rol:</strong> " . htmlspecialchars($admin['role']) . "</li>";
    echo "<li><strong>Activo:</strong> " . ($admin['activo'] ? 'Sí' : 'No') . "</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p class='text-danger'><i class='fas fa-times me-2'></i>Error creando usuario: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Paso 6: Insertar datos de ejemplo
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h5>Paso 6: Insertar Datos de Ejemplo</h5></div>";
echo "<div class='card-body'>";

try {
    // Verificar si ya hay datos
    $stmt = $pdo->query("SELECT COUNT(*) FROM buscador_lab");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insertar datos de ejemplo
        $sampleData = [
            ['Null', '12345678', '2024-01-15', 'nancy', 'data/repositorio/nancy/12345678/LAB_NANCY_2024-01-15.pdf', 'COMPLETADO'],
            ['Null', '87654321', '2024-01-16', 'sofilab', 'data/repositorio/sofilab/87654321/LAB_SOFILAB_2024-01-16.pdf', 'PENDIENTE'],
            ['Null', '11223344', '2024-01-17', 'murdch', 'data/repositorio/murdch/11223344/LAB_MURDCH_2024-01-17.pdf', 'COMPLETADO'],
            ['Null', '44332211', '2024-01-18', 'quimiosalud', 'data/repositorio/quimiosalud/44332211/LAB_QUIMIOSALUD_2024-01-18.pdf', 'FALLIDO'],
            ['Null', '55667788', '2024-01-19', 'nancy', 'data/repositorio/nancy/55667788/LAB_NANCY_2024-01-19.pdf', 'PENDIENTE'],
            ['Null', '99887766', '2024-01-20', 'sofilab', 'data/repositorio/sofilab/99887766/LAB_SOFILAB_2024-01-20.pdf', 'COMPLETADO']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO buscador_lab (Tipo_ID, Numero_ID, Fecha_ID, Nombre_Lab, Ruta_Soporte, estado_descarga) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($sampleData as $data) {
            $stmt->execute($data);
        }
        
        echo "<p class='text-success'><i class='fas fa-check me-2'></i>Datos de ejemplo insertados: " . count($sampleData) . " registros</p>";
    } else {
        echo "<p class='text-info'><i class='fas fa-info-circle me-2'></i>Ya existen $count registros en la base de datos</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='text-danger'><i class='fas fa-times me-2'></i>Error insertando datos: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Paso 7: Verificar configuración final
echo "<div class='card mb-3'>";
echo "<div class='card-header'><h5>Paso 7: Verificación Final</h5></div>";
echo "<div class='card-body'>";

try {
    // Verificar usuario admin
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin && password_verify('admin123', $admin['password'])) {
        echo "<p class='text-success'><i class='fas fa-check me-2'></i>Usuario admin verificado - Credenciales correctas</p>";
    } else {
        echo "<p class='text-danger'><i class='fas fa-times me-2'></i>Error: Credenciales del admin no funcionan</p>";
    }
    
    // Verificar datos
    $stmt = $pdo->query("SELECT COUNT(*) FROM buscador_lab");
    $docCount = $stmt->fetchColumn();
    echo "<p class='text-success'><i class='fas fa-check me-2'></i>Documentos en base de datos: $docCount</p>";
    
    // Verificar por laboratorio
    $stmt = $pdo->query("SELECT Nombre_Lab, COUNT(*) as count FROM buscador_lab GROUP BY Nombre_Lab");
    $labStats = $stmt->fetchAll();
    
    echo "<div class='mt-3'>";
    echo "<h6>Estadísticas por Laboratorio:</h6>";
    echo "<ul>";
    foreach ($labStats as $lab) {
        echo "<li><strong>" . htmlspecialchars($lab['Nombre_Lab']) . ":</strong> " . $lab['count'] . " documentos</li>";
    }
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p class='text-danger'><i class='fas fa-times me-2'></i>Error en verificación: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Resultado final
echo "<div class='card bg-success text-white'>";
echo "<div class='card-header'><h5><i class='fas fa-check-circle me-2'></i>Configuración Completada</h5></div>";
echo "<div class='card-body'>";
echo "<h4>¡Sistema listo para usar!</h4>";
echo "<p>La configuración se ha completado exitosamente. Ahora puedes:</p>";
echo "<div class='mt-3'>";
echo "<a href='index_final.php' class='btn btn-light me-2'><i class='fas fa-sign-in-alt me-2'></i>Iniciar Sesión</a>";
echo "<a href='login_working.php' class='btn btn-outline-light'><i class='fas fa-flask me-2'></i>Login Alternativo</a>";
echo "</div>";
echo "<div class='mt-4'>";
echo "<h6>Credenciales:</h6>";
echo "<p class='mb-0'><strong>Usuario:</strong> admin</p>";
echo "<p class='mb-0'><strong>Contraseña:</strong> admin123</p>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>