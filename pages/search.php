<?php
/**
 * Página de Búsqueda
 * Búsqueda y filtrado de documentos
 */

// Verificar autenticación
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Variables para el header
$pageTitle = 'Búsqueda de Documentos';
$breadcrumbs = [
    ['title' => 'Inicio', 'url' => 'index.php?page=dashboard'],
    ['title' => 'Búsqueda', 'url' => 'index.php?page=search']
];

// Inicializar objetos
$laboratory = new Laboratory();
$searchResults = [];
$totalResults = 0;
$currentPage = 1;
$resultsPerPage = 20;

// Obtener laboratorios para el filtro
$laboratories = $laboratory->getAllLaboratories();

// Procesar búsqueda
$searchPerformed = false;
$filters = [];

if (!empty($_GET) && (isset($_GET['search']) || isset($_GET['lab']) || isset($_GET['doc']) || isset($_GET['status']))) {
    $searchPerformed = true;
    
    // Recopilar filtros
    $filters = [
        'lab' => sanitize($_GET['lab'] ?? ''),
        'doc' => sanitize($_GET['doc'] ?? ''),
        'status' => sanitize($_GET['status'] ?? ''),
        'fecha_inicio' => sanitize($_GET['fecha_inicio'] ?? ''),
        'fecha_fin' => sanitize($_GET['fecha_fin'] ?? ''),
        'limit' => $resultsPerPage
    ];
    
    // Realizar búsqueda
    $searchResults = $laboratory->searchDocuments($filters);
    
    // Log de actividad
    logActivity('search', 'Búsqueda realizada con filtros: ' . json_encode($filters));
}

// Incluir header
include INCLUDES_PATH . 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-search me-2"></i>Búsqueda de Documentos</h1>
        <p class="text-muted mb-0">Encuentra documentos utilizando los filtros disponibles</p>
    </div>
    <div>
        <a href="index.php?page=dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
        </a>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#helpModal">
            <i class="fas fa-question-circle me-2"></i>Ayuda
        </button>
    </div>
</div>

<!-- Formulario de búsqueda -->
<div class="card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-filter me-2"></i>Filtros de Búsqueda</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" id="searchForm">
            <input type="hidden" name="page" value="search">
            <input type="hidden" name="search" value="1">
            
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="lab" class="form-label">Laboratorio</label>
                    <select name="lab" id="lab" class="form-select">
                        <option value="">Todos los laboratorios</option>
                        <?php foreach ($laboratories as $lab): ?>
                            <option value="<?php echo htmlspecialchars($lab['Nombre_Lab']); ?>" 
                                    <?php echo ($filters['lab'] ?? '') === $lab['Nombre_Lab'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lab['Nombre_Lab']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="doc" class="form-label">Número de Documento</label>
                    <input type="text" 
                           name="doc" 
                           id="doc" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($filters['doc'] ?? ''); ?>" 
                           placeholder="Buscar por número...">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Estado</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Todos los estados</option>
                        <?php foreach (getStatusOptions() as $value => $label): ?>
                            <option value="<?php echo $value; ?>" 
                                    <?php echo ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="SIN_ESTADO" <?php echo ($filters['status'] ?? '') === 'SIN_ESTADO' ? 'selected' : ''; ?>>
                            Sin Estado
                        </option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" 
                           name="fecha_inicio" 
                           id="fecha_inicio" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($filters['fecha_inicio'] ?? ''); ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" 
                           name="fecha_fin" 
                           id="fecha_fin" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($filters['fecha_fin'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Buscar
                    </button>
                    <a href="index.php?page=search" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-eraser me-2"></i>Limpiar Filtros
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="exportResults()">
                        <i class="fas fa-download me-2"></i>Exportar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resultados -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-list me-2"></i>Resultados de Búsqueda</h5>
        <?php if ($searchPerformed && !empty($searchResults) && !isset($searchResults['error'])): ?>
            <span class="badge bg-primary fs-6">
                <?php echo count($searchResults); ?> documentos encontrados
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!$searchPerformed): ?>
            <!-- Estado inicial -->
            <div class="text-center text-muted py-5">
                <i class="fas fa-search fa-4x mb-3 opacity-50"></i>
                <h4>Realizar Búsqueda</h4>
                <p>Utilice los filtros disponibles para buscar documentos en el sistema.</p>
                <div class="row justify-content-center mt-4">
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded">
                                    <i class="fas fa-flask text-primary mb-2"></i>
                                    <h6>Por Laboratorio</h6>
                                    <small class="text-muted">Filtre por laboratorio específico</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded">
                                    <i class="fas fa-file-alt text-info mb-2"></i>
                                    <h6>Por Documento</h6>
                                    <small class="text-muted">Busque por número de documento</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded">
                                    <i class="fas fa-calendar text-warning mb-2"></i>
                                    <h6>Por Fecha</h6>
                                    <small class="text-muted">Filtre por rango de fechas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php elseif (isset($searchResults['error'])): ?>
            <!-- Error en búsqueda -->
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error en la búsqueda:</strong> <?php echo htmlspecialchars($searchResults['error']); ?>
            </div>
            
        <?php elseif (empty($searchResults)): ?>
            <!-- Sin resultados -->
            <div class="text-center text-muted py-5">
                <i class="fas fa-search-minus fa-4x mb-3 opacity-50"></i>
                <h4>Sin Resultados</h4>
                <p>No se encontraron documentos que coincidan con los criterios de búsqueda.</p>
                <div class="mt-3">
                    <a href="index.php?page=search" class="btn btn-outline-primary">
                        <i class="fas fa-redo me-1"></i>Nueva Búsqueda
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Resultados encontrados -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Número</th>
                            <th>Laboratorio</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Última Actualización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searchResults as $index => $doc): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox" 
                                           value="<?php echo $doc['id']; ?>">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($doc['Numero_ID']); ?></strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-flask text-primary me-2"></i>
                                        <?php echo htmlspecialchars($doc['Nombre_Lab']); ?>
                                    </div>
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
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" 
                                                class="btn btn-outline-primary" 
                                                onclick="viewDocument(<?php echo $doc['id']; ?>)"
                                                title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-warning" 
                                                onclick="editDocument(<?php echo $doc['id']; ?>)"
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-success" 
                                                onclick="downloadDocument(<?php echo $doc['id']; ?>)"
                                                title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Acciones en lote -->
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllRows()">
                        <i class="fas fa-check-square me-1"></i>Seleccionar Todo
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                        <i class="fas fa-square me-1"></i>Limpiar Selección
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="bulkAction('download')">
                        <i class="fas fa-download me-1"></i>Descargar Seleccionados
                    </button>
                </div>
                <div>
                    <small class="text-muted">
                        Mostrando <?php echo count($searchResults); ?> resultados
                        <?php if (count($searchResults) == $resultsPerPage): ?>
                            (limitado a <?php echo $resultsPerPage; ?>)
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Ayuda -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle me-2"></i>Ayuda de Búsqueda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Cómo utilizar la búsqueda:</h6>
                <ul>
                    <li><strong>Laboratorio:</strong> Seleccione un laboratorio específico para filtrar los resultados.</li>
                    <li><strong>Número de Documento:</strong> Ingrese parte o el número completo del documento.</li>
                    <li><strong>Estado:</strong> Filtre por el estado de procesamiento del documento.</li>
                    <li><strong>Fechas:</strong> Utilice el rango de fechas para encontrar documentos en un período específico.</li>
                </ul>
                
                <h6 class="mt-3">Estados de documentos:</h6>
                <div class="row g-2">
                    <div class="col-md-6">
                        <span class="status-badge status-pendiente">
                            <i class="fas fa-clock me-1"></i>PENDIENTE
                        </span>
                        <small class="d-block text-muted">Documento en espera de procesamiento</small>
                    </div>
                    <div class="col-md-6">
                        <span class="status-badge status-completado">
                            <i class="fas fa-check-circle me-1"></i>COMPLETADO
                        </span>
                        <small class="d-block text-muted">Documento procesado exitosamente</small>
                    </div>
                    <div class="col-md-6">
                        <span class="status-badge status-en_proceso">
                            <i class="fas fa-spinner me-1"></i>EN PROCESO
                        </span>
                        <small class="d-block text-muted">Documento siendo procesado</small>
                    </div>
                    <div class="col-md-6">
                        <span class="status-badge status-fallido">
                            <i class="fas fa-times-circle me-1"></i>FALLIDO
                        </span>
                        <small class="d-block text-muted">Error en el procesamiento</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php 
// JavaScript para esta página
$inlineJS = "
// Selección múltiple
function selectAllRows() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const selectAll = document.getElementById('selectAll');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const selectAll = document.getElementById('selectAll');
    checkboxes.forEach(cb => cb.checked = false);
    selectAll.checked = false;
}

// Acciones de documentos
function viewDocument(id) {
    // Implementar vista de documento
    window.open('index.php?page=document&id=' + id, '_blank');
}

function editDocument(id) {
    // Implementar edición de documento
    window.location.href = 'index.php?page=edit_document&id=' + id;
}

function downloadDocument(id) {
    // Implementar descarga de documento
    window.open('api/download.php?id=' + id, '_blank');
}

function bulkAction(action) {
    const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Por favor seleccione al menos un documento');
        return;
    }
    
    if (action === 'download') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'api/bulk_download.php';
        
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
}

function exportResults() {
    const form = document.getElementById('searchForm');
    const exportForm = form.cloneNode(true);
    exportForm.action = 'api/export.php';
    exportForm.style.display = 'none';
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
}

// Evento para el checkbox principal
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// Auto-submit en cambio de laboratorio
document.getElementById('lab').addEventListener('change', function() {
    if (this.value !== '') {
        document.getElementById('searchForm').submit();
    }
});
";

// Incluir footer
include INCLUDES_PATH . 'footer.php';
?>
        '