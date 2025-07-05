<?php
/**
 * Sidebar común
 * Sistema de Gestión de Laboratorios - Quimiosalud
 */

// Obtener la página actual
$currentPage = $_GET['page'] ?? 'dashboard';

// Obtener información del usuario
$currentUser = getCurrentUser();
$userRole = $currentUser['role'] ?? 'user';

// Definir elementos del menú
$menuItems = [
    [
        'id' => 'dashboard',
        'title' => 'Panel de Control',
        'url' => 'index.php?page=dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'permission' => 'view'
    ],
    [
        'id' => 'search',
        'title' => 'Buscar Documentos',
        'url' => 'index.php?page=search',
        'icon' => 'fas fa-search',
        'permission' => 'view'
    ],
    [
        'id' => 'laboratories',
        'title' => 'Laboratorios',
        'url' => 'index.php?page=laboratories',
        'icon' => 'fas fa-flask',
        'permission' => 'view',
        'submenu' => [
            [
                'id' => 'nancy',
                'title' => 'Nancy Laboratorio',
                'url' => 'index.php?page=search&lab=nancy',
                'icon' => 'fas fa-circle'
            ],
            [
                'id' => 'sofilab',
                'title' => 'Sofilab',
                'url' => 'index.php?page=search&lab=sofilab',
                'icon' => 'fas fa-circle'
            ],
            [
                'id' => 'murdch',
                'title' => 'Murdch',
                'url' => 'index.php?page=search&lab=murdch',
                'icon' => 'fas fa-circle'
            ],
            [
                'id' => 'quimiosalud',
                'title' => 'Quimiosalud',
                'url' => 'index.php?page=search&lab=quimiosalud',
                'icon' => 'fas fa-circle'
            ]
        ]
    ],
    [
        'id' => 'reports',
        'title' => 'Reportes',
        'url' => 'index.php?page=reports',
        'icon' => 'fas fa-chart-bar',
        'permission' => 'view'
    ],
    [
        'id' => 'upload',
        'title' => 'Subir Documentos',
        'url' => 'index.php?page=upload',
        'icon' => 'fas fa-upload',
        'permission' => 'create'
    ],
    [
        'id' => 'manage',
        'title' => 'Gestión',
        'url' => 'index.php?page=manage',
        'icon' => 'fas fa-cogs',
        'permission' => 'manage',
        'submenu' => [
            [
                'id' => 'users',
                'title' => 'Usuarios',
                'url' => 'index.php?page=users',
                'icon' => 'fas fa-users'
            ],
            [
                'id' => 'settings',
                'title' => 'Configuración',
                'url' => 'index.php?page=settings',
                'icon' => 'fas fa-cog'
            ],
            [
                'id' => 'logs',
                'title' => 'Logs del Sistema',
                'url' => 'index.php?page=logs',
                'icon' => 'fas fa-file-alt'
            ]
        ]
    ]
];

// Filtrar elementos según permisos
$allowedMenuItems = array_filter($menuItems, function($item) {
    return !isset($item['permission']) || hasPermission($item['permission']);
});
?>
<!-- Sidebar básico -->
<div class="sidebar">
    <div class="sidebar-header">
        <h5><i class="fas fa-flask me-2"></i>Menu</h5>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php?page=dashboard" class="nav-link">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="index.php?page=search" class="nav-link">
            <i class="fas fa-search me-2"></i>Búsqueda
        </a>
    </nav>
</div>
<div class="sidebar" id="sidebar">
    <!-- Toggle button -->
    <button class="sidebar-toggle" id="sidebarToggle" title="Contraer/Expandir menú">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Header del sidebar -->
    <div class="sidebar-header">
        <img src="assets/images/logo.png" alt="Quimiosalud" class="sidebar-logo">
        <h3 class="sidebar-title">Quimiosalud</h3>
        <p class="sidebar-subtitle">Sistema de Laboratorios</p>
    </div>
    
    <!-- Información del usuario -->
    <div class="sidebar-user">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($currentUser['nombre'], 0, 1) . substr($currentUser['apellido'], 0, 1)); ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($currentUser['nombre'] . ' ' . $currentUser['apellido']); ?></div>
                <div class="user-role"><?php echo USER_ROLES[$userRole] ?? 'Usuario'; ?></div>
            </div>
        </div>
    </div>
    
    <!-- Navegación -->
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <?php foreach ($allowedMenuItems as $item): ?>
                <li class="nav-item <?php echo isset($item['submenu']) ? 'has-submenu' : ''; ?>">
                    <?php if (isset($item['submenu'])): ?>
                        <!-- Elemento con submenú -->
                        <a href="#" class="nav-link <?php echo $currentPage === $item['id'] ? 'active' : ''; ?>" 
                           data-bs-toggle="collapse" 
                           data-bs-target="#submenu-<?php echo $item['id']; ?>" 
                           aria-expanded="<?php echo $currentPage === $item['id'] ? 'true' : 'false'; ?>">
                            <i class="nav-icon <?php echo $item['icon']; ?>"></i>
                            <span class="nav-text"><?php echo $item['title']; ?></span>
                            <i class="nav-arrow fas fa-chevron-down"></i>
                        </a>
                        
                        <!-- Submenú -->
                        <div class="collapse <?php echo $currentPage === $item['id'] ? 'show' : ''; ?>" 
                             id="submenu-<?php echo $item['id']; ?>">
                            <ul class="submenu-list">
                                <?php foreach ($item['submenu'] as $subItem): ?>
                                    <li class="submenu-item">
                                        <a href="<?php echo $subItem['url']; ?>" 
                                           class="submenu-link <?php echo $currentPage === $subItem['id'] ? 'active' : ''; ?>">
                                            <i class="submenu-icon <?php echo $subItem['icon']; ?>"></i>
                                            <span class="submenu-text"><?php echo $subItem['title']; ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Elemento simple -->
                        <a href="<?php echo $item['url']; ?>" 
                           class="nav-link <?php echo $currentPage === $item['id'] ? 'active' : ''; ?>">
                            <i class="nav-icon <?php echo $item['icon']; ?>"></i>
                            <span class="nav-text"><?php echo $item['title']; ?></span>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    
    <!-- Sección de ayuda -->
    <div class="sidebar-help">
        <div class="help-card">
            <div class="help-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="help-content">
                <h6 class="help-title">¿Necesitas ayuda?</h6>
                <p class="help-text">Consulta la documentación o contacta al soporte técnico.</p>
                <a href="#" class="help-link" data-bs-toggle="modal" data-bs-target="#helpModal">
                    <i class="fas fa-external-link-alt me-1"></i>
                    Ver ayuda
                </a>
            </div>
        </div>
    </div>
    
    <!-- Footer del sidebar -->
    <div class="sidebar-footer">
        <div class="footer-content">
            <div class="app-version">
                <small>v<?php echo APP_VERSION; ?></small>
            </div>
            <div class="footer-links">
                <a href="index.php?page=profile" title="Perfil">
                    <i class="fas fa-user"></i>
                </a>
                <a href="index.php?page=settings" title="Configuración">
                    <i class="fas fa-cog"></i>
                </a>
                <a href="index.php?page=logout" title="Cerrar sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de ayuda -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpModalLabel">
                    <i class="fas fa-question-circle me-2"></i>
                    Centro de Ayuda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-book me-2"></i>Guías Rápidas</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-decoration-none">
                                <i class="fas fa-search me-2"></i>Cómo buscar documentos
                            </a></li>
                            <li><a href="#" class="text-decoration-none">
                                <i class="fas fa-upload me-2"></i>Subir nuevos archivos
                            </a></li>
                            <li><a href="#" class="text-decoration-none">
                                <i class="fas fa-download me-2"></i>Descargar documentos
                            </a></li>
                            <li><a href="#" class="text-decoration-none">
                                <i class="fas fa-chart-bar me-2"></i>Interpretar reportes
                            </a></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-headset me-2"></i>Soporte Técnico</h6>
                        <div class="support-info">
                            <p><strong>Email:</strong> soporte@quimiosalud.com</p>
                            <p><strong>Teléfono:</strong> +57 (5) 123-4567</p>
                            <p><strong>Horario:</strong> Lun-Vie 8:00 AM - 6:00 PM</p>
                        </div>
                        
                        <h6 class="mt-4"><i class="fas fa-keyboard me-2"></i>Atajos de Teclado</h6>
                        <div class="keyboard-shortcuts">
                            <div class="shortcut">
                                <kbd>Ctrl</kbd> + <kbd>K</kbd> - Búsqueda rápida
                            </div>
                            <div class="shortcut">
                                <kbd>Ctrl</kbd> + <kbd>U</kbd> - Subir archivo
                            </div>
                            <div class="shortcut">
                                <kbd>Ctrl</kbd> + <kbd>H</kbd> - Ir al inicio
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
                <a href="mailto:soporte@quimiosalud.com" class="btn btn-primary">
                    <i class="fas fa-envelope me-2"></i>
                    Contactar Soporte
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Overlay para móviles -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
/* Estilos específicos del sidebar */
.sidebar-user {
    padding: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 1rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(45deg, var(--app-secondary-color), var(--app-info-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.user-details {
    flex: 1;
}

.user-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: white;
    margin-bottom: 0.25rem;
}

.user-role {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.7);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sidebar.collapsed .user-info {
    flex-direction: column;
    text-align: center;
}

.sidebar.collapsed .user-details {
    display: none;
}

/* Navegación */
.nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin-bottom: 0.25rem;
}

.nav-item.has-submenu .nav-link {
    position: relative;
}

.nav-arrow {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.8rem;
    transition: transform 0.3s ease;
}

.nav-link[aria-expanded="true"] .nav-arrow {
    transform: translateY(-50%) rotate(180deg);
}

/* Submenú */
.submenu-list {
    list-style: none;
    padding: 0;
    margin: 0;
    background: rgba(0, 0, 0, 0.1);
}

.submenu-item {
    border-left: 2px solid rgba(255, 255, 255, 0.2);
    margin-left: 1rem;
}

.submenu-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem 0.75rem 1.5rem;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.submenu-link:hover {
    color: white;
    background: rgba(255, 255, 255, 0.05);
    padding-left: 2rem;
}

.submenu-link.active {
    color: var(--app-secondary-color);
    background: rgba(52, 152, 219, 0.1);
    border-left: 2px solid var(--app-secondary-color);
}

.submenu-icon {
    font-size: 0.7rem;
    margin-right: 0.75rem;
    width: 12px;
    text-align: center;
}

.submenu-text {
    font-size: 0.85rem;
}

.sidebar.collapsed .submenu-list {
    display: none;
}

/* Sección de ayuda */
.sidebar-help {
    margin-top: auto;
    padding: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.help-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    backdrop-filter: blur(10px);
}

.help-icon {
    font-size: 2rem;
    color: var(--app-secondary-color);
    margin-bottom: 0.5rem;
}

.help-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: white;
    margin-bottom: 0.5rem;
}

.help-text {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 1rem;
    line-height: 1.4;
}

.help-link {
    color: var(--app-secondary-color);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: color 0.3s ease;
}

.help-link:hover {
    color: white;
}

.sidebar.collapsed .help-card {
    padding: 0.5rem;
}

.sidebar.collapsed .help-title,
.sidebar.collapsed .help-text {
    display: none;
}

/* Footer del sidebar */
.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: auto;
}

.footer-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.app-version {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.75rem;
}

.footer-links {
    display: flex;
    gap: 0.5rem;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
    padding: 0.5rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.footer-links a:hover {
    color: white;
    background: rgba(255, 255, 255, 0.1);
}

.sidebar.collapsed .footer-content {
    flex-direction: column;
    gap: 0.5rem;
}

/* Overlay para móviles */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: none;
}

.sidebar-overlay.active {
    display: block;
}

/* Estilos del modal de ayuda */
.keyboard-shortcuts {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.shortcut {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.shortcut kbd {
    background: var(--app-light-color);
    color: var(--app-dark-color);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    border: 1px solid #ccc;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.support-info p {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
        z-index: 1050;
    }
    
    .sidebar.open {
        transform: translateX(0);
    }
    
    .sidebar-overlay.active {
        display: block;
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        max-width: 300px;
    }
    
    .sidebar-header {
        padding: 1rem;
    }
    
    .sidebar-title {
        font-size: 1.1rem;
    }
    
    .sidebar-subtitle {
        font-size: 0.8rem;
    }
    
    .nav-link {
        padding: 0.875rem 1rem;
    }
    
    .nav-text {
        font-size: 0.9rem;
    }
    
    .help-card {
        padding: 0.75rem;
    }
    
    .help-text {
        font-size: 0.75rem;
    }
}

/* Animaciones */
@keyframes slideIn {
    from {
        transform: translateX(-100%);
    }
    to {
        transform: translateX(0);
    }
}

.sidebar.open {
    animation: slideIn 0.3s ease-out;
}

/* Estados hover mejorados */
.nav-link:hover .nav-icon {
    transform: scale(1.1);
}

.submenu-link:hover .submenu-icon {
    transform: scale(1.1);
}

/* Indicadores de estado */
.nav-item.active > .nav-link::after {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    width: 4px;
    height: 100%;
    background: var(--app-secondary-color);
    border-radius: 2px 0 0 2px;
}

/* Mejoras de accesibilidad */
.nav-link:focus,
.submenu-link:focus {
    outline: 2px solid var(--app-secondary-color);
    outline-offset: 2px;
}

/* Scrollbar personalizada para el sidebar */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('mainContent');
    
    // Toggle del sidebar
    sidebarToggle.addEventListener('click', function() {
        if (window.innerWidth <= 992) {
            // Modo móvil
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
        } else {
            // Modo desktop
            sidebar.classList.toggle('collapsed');
            if (mainContent) {
                mainContent.classList.toggle('expanded');
            }
            
            // Guardar estado en localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }
    });
    
    // Cerrar sidebar en móviles al hacer clic en overlay
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
    });
    
    // Restaurar estado del sidebar en desktop
    if (window.innerWidth > 992) {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            if (mainContent) {
                mainContent.classList.add('expanded');
            }
        }
    }
    
    // Manejar redimensionamiento de ventana
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 992) {
            // Cambiar a modo móvil
            sidebar.classList.remove('collapsed');
            if (mainContent) {
                mainContent.classList.remove('expanded');
            }
        } else {
            // Cambiar a modo desktop
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
            
            // Restaurar estado colapsado
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                if (mainContent) {
                    mainContent.classList.add('expanded');
                }
            }
        }
    });
    
    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        // Ctrl + K - Búsqueda rápida
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            window.location.href = 'index.php?page=search';
        }
        
        // Ctrl + U - Subir archivo
        if (e.ctrlKey && e.key === 'u') {
            e.preventDefault();
            window.location.href = 'index.php?page=upload';
        }
        
        // Ctrl + H - Ir al inicio
        if (e.ctrlKey && e.key === 'h') {
            e.preventDefault();
            window.location.href = 'index.php?page=dashboard';
        }
    });
    
    // Highlight del elemento activo en el menú
    const currentPath = window.location.pathname + window.location.search;
    const navLinks = document.querySelectorAll('.nav-link, .submenu-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
            
            // Si es un submenú, expandir el menú padre
            const parentSubmenu = link.closest('.collapse');
            if (parentSubmenu) {
                parentSubmenu.classList.add('show');
                const parentLink = document.querySelector(`[data-bs-target="#${parentSubmenu.id}"]`);
                if (parentLink) {
                    parentLink.setAttribute('aria-expanded', 'true');
                    parentLink.classList.add('active');
                }
            }
        }
    });
    
    // Smooth scrolling para enlaces del sidebar
    document.querySelectorAll('.nav-link, .submenu-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Agregar efecto de clic
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
            
            // En móviles, cerrar el sidebar después de hacer clic
            if (window.innerWidth <= 992) {
                setTimeout(() => {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                }, 300);
            }
        });
    });
});
</script>