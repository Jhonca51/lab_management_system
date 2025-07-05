<?php
/**
 * Página Dashboard
 * Panel principal del sistema
 */

// Verificar autenticación
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Variables para el header
$pageTitle = 'Dashboard';
$breadcrumbs = [
    ['title' => 'Inicio', 'url' => 'index.php?page=dashboard']
];

// Obtener estadísticas
$laboratory = new Laboratory();
$stats = $laboratory->getStats();
$recentDocuments = $laboratory->getRecentDocuments(5);

// Incluir header
include INCLUDES_PATH . 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-tachometer-alt me-2"></i>Panel de Control</h1>
        <p class="text-muted mb-0">Bienvenido, <?php echo htmlspecialchars(getCurrentUser()['name']); ?></p>
    </div>
    <div>
        <a href="index.php?page=search" class="btn btn-primary btn-custom">
            <i class="fas fa-search me-2"></i>Buscar Documentos
        </a>
        <a href="index.php?page=laboratories" class="btn btn-outline-primary btn-custom">
            <i class="fas fa-flask me-2"></i>Laboratorios
        </a>
    </div>
</div>

<?php if (isset($stats['error'])): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Error al cargar estadísticas: <?php echo htmlspecialchars($stats['error']); ?>
    </div>
<?php else: ?>
    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card position-relative">
                <div class="stat-icon primary">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-value"><?php echo formatNumber($stats['total']); ?></div>
                <div class="text-muted">Total Documentos</div>
                <div class="mt-2">
                    <small class="text-success">
                        <i class="fas fa-arrow-up me-1"></i>
                        Último mes
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card position-relative">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo formatNumber($stats['completados'] ?? 0); ?></div>
                <div class="text-muted">Completados</div>
                <div class="mt-2">
                    <?php 
                    $completadosPercent = $stats['total'] > 0 ? round(($stats['completados'] ?? 0) / $stats['total'] * 100, 1) : 0;
                    ?>
                    <small class="text-muted">
                        <?php echo $completadosPercent; ?>% del total
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card position-relative">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo formatNumber($stats['pendientes'] ?? 0); ?></div>
                <div class="text-muted">Pendientes</div>
                <div class="mt-2">
                    <?php 
                    $pendientesPercent = $stats['total'] > 0 ? round(($stats['pendientes'] ?? 0) / $stats['total'] * 100, 1) : 0;
                    ?>
                    <small class="text-muted">
                        <?php echo $pendientesPercent; ?>% del total
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card position-relative">
                <div class="stat-icon danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-value"><?php echo formatNumber($stats['fallidos'] ?? 0); ?></div>
                <div class="text-muted">Fallidos</div>
                <div class="mt-2">
                    <?php 
                    $fallidosPercent = $stats['total'] > 0 ? round(($stats['fallidos'] ?? 0) / $stats['total'] * 100, 1) : 0;
                    ?>
                    <small class="text-muted">
                        <?php echo $fallidosPercent; ?>% del total
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Laboratorios -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-flask me-2"></i>Laboratorios</h5>
                    <a href="index.php?page=laboratories" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>Ver todos
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($stats['laboratories'])): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>No hay datos disponibles.</p>
                            <p>Ejecute el archivo <code>install.sql</code> para crear datos de ejemplo.</p>
                            <a href="#" class="btn btn-primary btn-sm">
                                <i class="fas fa-database me-1"></i>Configurar Base de Datos
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Laboratorio</th>
                                        <th>Documentos</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($stats['laboratories'], 0, 5) as $lab): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <i class="fas fa-flask text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($lab['Nombre_Lab']); ?></h6>
                                                        <small class="text-muted">Laboratorio médico</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6"><?php echo formatNumber($lab['count']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Activo</span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="index.php?page=search&lab=<?php echo urlencode($lab['Nombre_Lab']); ?>" 
                                                       class="btn btn-outline-primary" title="Ver documentos">
                                                        <i class="fas fa-search"></i>
                                                    </a>
                                                    <a href="index.php?page=laboratory&name=<?php echo urlencode($lab['Nombre_Lab']); ?>" 
                                                       class="btn btn-outline-info" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (count($stats['laboratories']) > 5): ?>
                            <div class="text-center mt-3">
                                <a href="index.php?page=laboratories" class="btn btn-outline-primary">
                                    Ver todos los laboratorios (<?php echo count($stats['laboratories']); ?>)
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Panel lateral -->
        <div class="col-md-4">
            <!-- Información del usuario -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-user me-2"></i>Mi Información</h5>
                </div>
                <div class="card-body">
                    <?php $currentUser = getCurrentUser(); ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <strong>Usuario:</strong> 
                            <span class="text-muted"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                        </div>
                        <div class="col-12">
                            <strong>Rol:</strong> 
                            <span class="badge bg-info"><?php echo htmlspecialchars($currentUser['role']); ?></span>
                        </div>
                        <div class="col-12">
                            <strong>Sesión desde:</strong> 
                            <span class="text-muted"><?php echo formatDateTime(date('Y-m-d H:i:s')); ?></span>
                        </div>
                    </div>
                    <hr>
                    <div class="d-grid">
                        <a href="index.php?page=profile" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-cog me-1"></i>Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Estado del sistema -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-server me-2"></i>Estado del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <strong>Sistema:</strong> 
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Operativo
                            </span>
                        </div>
                        <div class="col-12">
                            <strong>Versión:</strong> 
                            <span class="text-muted"><?php echo APP_VERSION; ?></span>
                        </div>
                        <div class="col-12">
                            <strong>Base de Datos:</strong> 
                            <span class="badge bg-success">
                                <i class="fas fa-database me-1"></i>Conectada
                            </span>
                        </div>
                        <div class="col-12">
                            <strong>Última actualización:</strong> 
                            <small class="text-muted d-block"><?php echo formatDateTime(date('Y-m-d H:i:s')); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Documentos recientes -->
    <?php if (!empty($recentDocuments) && !isset($recentDocuments['error'])): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-clock me-2"></i>Documentos Recientes</h5>
                        <a href="index.php?page=search" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search me-1"></i>Ver todos
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Laboratorio</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Última actualización</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentDocuments as $doc): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($doc['Numero_ID']); ?></strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-flask me-2 text-primary"></i>
                                                <?php echo htmlspecialchars($doc['Nombre_Lab']); ?>
                                            </td>
                                            <td><?php echo formatDate($doc['Fecha_ID']); ?></td>
                                            <td>
                                                <?php 
                                                $status = $doc['estado_descarga'] ?? 'PENDIENTE';
                                                $statusClass = getStatusClass($status);
                                                $statusIcon = getStatusIcon($status);
                                                ?>
                                                <span class="status-badge <?php echo $statusClass; ?>">
                                                    <i class="<?php echo $statusIcon; ?> me-1"></i>
                                                    <?php echo $status; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo formatDateTime($doc['fecha_actualizacion']); ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php 
// CSS adicional para esta página
$inlineJS = "
// Auto-refresh de estadísticas cada 5 minutos
setTimeout(function() {
    location.reload();
}, 300000);

// Tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle=\"tooltip\"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
";

// Incluir footer
include INCLUDES_PATH . 'footer.php';
?>