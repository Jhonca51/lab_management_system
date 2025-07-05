<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<?php if (isLoggedIn()): ?>
    <!-- Navbar para usuarios autenticados -->
    <nav class="navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center w-100">
                <a class="navbar-brand" href="index.php?page=dashboard">
                    <i class="fas fa-flask me-2"></i>
                    Quimiosalud
                </a>
                
                <!-- Menú de navegación -->
                <div class="navbar-nav me-auto ms-4">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bars me-1"></i> Menú
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="index.php?page=search">
                                <i class="fas fa-search me-2"></i>Búsqueda
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=laboratories">
                                <i class="fas fa-flask me-2"></i>Laboratorios
                            </a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Usuario -->
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i>
                            <?php echo htmlspecialchars(getCurrentUser()['name']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">
                                <i class="fas fa-user me-2"></i>
                                <?php echo htmlspecialchars(getCurrentUser()['username']); ?>
                            </h6></li>
                            <li><span class="dropdown-item-text">
                                <small class="text-muted">Rol: <?php echo htmlspecialchars(getCurrentUser()['role']); ?></small>
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=profile">
                                <i class="fas fa-user-cog me-2"></i>Mi Perfil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="index.php?logout=1">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Breadcrumbs -->
    <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
        <div class="container mt-3">
            <?php echo generateBreadcrumbs($breadcrumbs); ?>
        </div>
    <?php endif; ?>
    
    <!-- Mensajes Flash -->
    <?php 
    $flashMessage = getFlashMessage();
    if ($flashMessage): 
    ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo htmlspecialchars($flashMessage['text']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Contenido principal -->
    <div class="container mt-4">
<?php endif; ?>