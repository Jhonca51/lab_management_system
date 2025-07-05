/**
 * JavaScript principal del sistema
 * Sistema de Gestión de Laboratorios - Quimiosalud
 */

// Variables globales
let currentUser = null;
let notifications = [];
let activeRequests = new Set();

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Inicializar la aplicación
 */
function initializeApp() {
    // Ocultar pantalla de carga
    hideLoadingScreen();
    
    // Inicializar componentes comunes
    initializeCommonComponents();
    
    // Inicializar sidebar
    initializeSidebar();
    
    // Inicializar notificaciones
    initializeNotifications();
    
    // Inicializar atajos de teclado
    initializeKeyboardShortcuts();
    
    // Inicializar tooltips globales
    initializeTooltips();
    
    // Inicializar validación de formularios
    initializeFormValidation();
    
    // Configurar AJAX por defecto
    setupAjaxDefaults();
    
    // Inicializar actualizaciones automáticas
    startAutoUpdates();
    
    console.log('Sistema Quimiosalud inicializado correctamente');
}

/**
 * Ocultar pantalla de carga
 */
function hideLoadingScreen() {
    const loadingScreen = document.getElementById('loadingScreen');
    const pageContent = document.getElementById('pageContent');
    
    if (loadingScreen && pageContent) {
        setTimeout(() => {
            loadingScreen.classList.add('hidden');
            pageContent.style.opacity = '1';
            
            // Remover completamente después de la animación
            setTimeout(() => {
                loadingScreen.remove();
            }, 500);
        }, 800);
    }
}

/**
 * Inicializar componentes comunes
 */
function initializeCommonComponents() {
    // Inicializar Bootstrap components
    initializeBootstrapComponents();
    
    // Configurar elementos interactivos
    setupInteractiveElements();
    
    // Configurar formularios
    setupForms();
    
    // Configurar modales
    setupModals();
}

/**
 * Inicializar componentes de Bootstrap
 */
function initializeBootstrapComponents() {
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Inicializar popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Inicializar dropdowns
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(dropdown => {
        new bootstrap.Dropdown(dropdown);
    });
}

/**
 * Configurar elementos interactivos
 */
function setupInteractiveElements() {
    // Botones con efectos hover
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
    // Cards con efectos hover
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
    
    // Enlaces con animaciones
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function(e) {
            // Agregar efecto ripple
            createRippleEffect(e, this);
        });
    });
}

/**
 * Crear efecto ripple en elementos
 */
function createRippleEffect(event, element) {
    const rect = element.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    
    const ripple = document.createElement('span');
    ripple.className = 'ripple-effect';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    
    element.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

/**
 * Configurar formularios
 */
function setupForms() {
    // Validación en tiempo real
    document.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
    
    // Prevenir envío doble de formularios
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && submitBtn.disabled) {
                e.preventDefault();
                return false;
            }
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Enviar';
                }, 5000);
            }
        });
    });
}

/**
 * Configurar modales
 */
function setupModals() {
    // Limpiar formularios al cerrar modales
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                clearFormErrors(form);
            }
        });
    });
}

/**
 * Inicializar sidebar
 */
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (!sidebar || !sidebarToggle) return;
    
    // Manejar toggle del sidebar
    sidebarToggle.addEventListener('click', function() {
        toggleSidebar();
    });
    
    // Manejar clics en elementos del menú
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Agregar clase active
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Guardar estado en localStorage
            localStorage.setItem('activeMenuItem', this.getAttribute('href'));
        });
    });
    
    // Restaurar elemento activo
    const activeMenuItem = localStorage.getItem('activeMenuItem');
    if (activeMenuItem) {
        const activeLink = document.querySelector(`[href="${activeMenuItem}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }
}

/**
 * Toggle del sidebar
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth <= 768) {
        // Modo móvil
        sidebar.classList.toggle('open');
        document.body.classList.toggle('sidebar-open');
    } else {
        // Modo desktop
        sidebar.classList.toggle('collapsed');
        if (mainContent) {
            mainContent.classList.toggle('expanded');
        }
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
}

/**
 * Inicializar notificaciones
 */
function initializeNotifications() {
    // Cargar notificaciones iniciales
    loadNotifications();
    
    // Actualizar notificaciones cada 2 minutos
    setInterval(loadNotifications, 120000);
    
    // Configurar sonidos de notificación
    setupNotificationSounds();
}

/**
 * Cargar notificaciones
 */
function loadNotifications() {
    fetch('api/notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationsBadge(data.notifications.length);
                notifications = data.notifications;
                
                // Mostrar notificaciones nuevas
                data.notifications.forEach(notification => {
                    if (notification.is_new) {
                        showNotification(notification);
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error cargando notificaciones:', error);
        });
}

/**
 * Actualizar badge de notificaciones
 */
function updateNotificationsBadge(count) {
    const badge = document.querySelector('#notificationsDropdown .badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    }
}

/**
 * Mostrar notificación
 */
function showNotification(notification) {
    // Crear elemento de notificación
    const notificationEl = document.createElement('div');
    notificationEl.className = `notification notification-${notification.type}`;
    notificationEl.innerHTML = `
        <div class="notification-content">
            <div class="notification-title">${notification.title}</div>
            <div class="notification-message">${notification.message}</div>
        </div>
        <button class="notification-close" onclick="closeNotification(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Agregar al contenedor
    const container = getNotificationContainer();
    container.appendChild(notificationEl);
    
    // Animar entrada
    setTimeout(() => {
        notificationEl.classList.add('show');
    }, 100);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        closeNotification(notificationEl.querySelector('.notification-close'));
    }, 5000);
    
    // Reproducir sonido
    playNotificationSound(notification.type);
}

/**
 * Obtener contenedor de notificaciones
 */
function getNotificationContainer() {
    let container = document.getElementById('notificationContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notificationContainer';
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    return container;
}

/**
 * Cerrar notificación
 */
function closeNotification(button) {
    const notification = button.closest('.notification');
    if (notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

/**
 * Configurar sonidos de notificación
 */
function setupNotificationSounds() {
    // Precargar sonidos
    window.notificationSounds = {
        success: new Audio('assets/sounds/success.mp3'),
        error: new Audio('assets/sounds/error.mp3'),
        warning: new Audio('assets/sounds/warning.mp3'),
        info: new Audio('assets/sounds/info.mp3')
    };
    
    // Configurar volumen
    Object.values(window.notificationSounds).forEach(sound => {
        sound.volume = 0.3;
    });
}

/**
 * Reproducir sonido de notificación
 */
function playNotificationSound(type) {
    const sound = window.notificationSounds?.[type];
    if (sound) {
        sound.play().catch(e => {
            // Ignorar errores de reproducción (autoplay policy)
        });
    }
}

/**
 * Inicializar atajos de teclado
 */
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl + K - Búsqueda rápida
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            focusSearchInput();
        }
        
        // Ctrl + / - Mostrar atajos de teclado
        if (e.ctrlKey && e.key === '/') {
            e.preventDefault();
            showKeyboardShortcuts();
        }
        
        // Escape - Cerrar modales/overlays
        if (e.key === 'Escape') {
            closeTopModal();
        }
        
        // Alt + S - Toggle sidebar
        if (e.altKey && e.key === 's') {
            e.preventDefault();
            toggleSidebar();
        }
    });
}

/**
 * Enfocar campo de búsqueda
 */
function focusSearchInput() {
    const searchInput = document.querySelector('input[name="doc"], input[type="search"]');
    if (searchInput) {
        searchInput.focus();
        searchInput.select();
    }
}

/**
 * Mostrar atajos de teclado
 */
function showKeyboardShortcuts() {
    const shortcuts = [
        { keys: 'Ctrl + K', description: 'Búsqueda rápida' },
        { keys: 'Ctrl + /', description: 'Mostrar atajos' },
        { keys: 'Alt + S', description: 'Toggle sidebar' },
        { keys: 'Escape', description: 'Cerrar modal' }
    ];
    
    let html = '<div class="keyboard-shortcuts-list">';
    shortcuts.forEach(shortcut => {
        html += `
            <div class="shortcut-item">
                <div class="shortcut-keys">${shortcut.keys}</div>
                <div class="shortcut-description">${shortcut.description}</div>
            </div>
        `;
    });
    html += '</div>';
    
    showModal('Atajos de Teclado', html);
}

/**
 * Cerrar modal superior
 */
function closeTopModal() {
    const modals = document.querySelectorAll('.modal.show');
    if (modals.length > 0) {
        const topModal = modals[modals.length - 1];
        const modalInstance = bootstrap.Modal.getInstance(topModal);
        if (modalInstance) {
            modalInstance.hide();
        }
    }
}

/**
 * Inicializar tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]:not([data-bs-toggle="tooltip"])'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        tooltipTriggerEl.setAttribute('data-bs-toggle', 'tooltip');
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Inicializar validación de formularios
 */
function initializeFormValidation() {
    // Configurar validación personalizada
    window.addEventListener('load', function() {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    });
}

/**
 * Validar campo individual
 */
function validateField(field) {
    const isValid = field.checkValidity();
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        if (feedback) feedback.style.display = 'none';
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        if (feedback) feedback.style.display = 'block';
    }
    
    return isValid;
}

/**
 * Limpiar error de campo
 */
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) feedback.style.display = 'none';
}

/**
 * Limpiar errores de formulario
 */
function clearFormErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(field => {
        field.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.style.display = 'none';
    });
}

/**
 * Configurar AJAX por defecto
 */
function setupAjaxDefaults() {
    // Configurar headers por defecto
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        fetch = (function(originalFetch) {
            return function(url, options) {
                options = options || {};
                options.headers = options.headers || {};
                
                if (options.method === 'POST' || options.method === 'PUT' || options.method === 'DELETE') {
                    options.headers['X-CSRF-Token'] = csrfToken.getAttribute('content');
                }
                
                return originalFetch.apply(this, arguments);
            };
        })(fetch);
    }
}

/**
 * Inicializar actualizaciones automáticas
 */
function startAutoUpdates() {
    // Actualizar estadísticas cada 30 segundos
    setInterval(() => {
        updateStats();
    }, 30000);
    
    // Ping al servidor cada 5 minutos para mantener sesión
    setInterval(() => {
        fetch('api/ping.php').catch(() => {});
    }, 300000);
}

/**
 * Actualizar estadísticas
 */
function updateStats() {
    fetch('api/stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatsDisplay(data.stats);
            }
        })
        .catch(error => {
            console.error('Error actualizando estadísticas:', error);
        });
}

/**
 * Actualizar display de estadísticas
 */
function updateStatsDisplay(stats) {
    // Actualizar valores en las tarjetas de estadísticas
    document.querySelectorAll('.stat-value').forEach((element, index) => {
        const keys = ['total_documents', 'completados', 'pendientes', 'fallidos'];
        const key = keys[index];
        if (stats[key] !== undefined) {
            animateNumber(element, parseInt(element.textContent.replace(/[^\d]/g, '')), stats[key]);
        }
    });
}

/**
 * Animar cambio de número
 */
function animateNumber(element, from, to) {
    const duration = 1000;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = Math.floor(from + (to - from) * progress);
        element.textContent = formatNumber(current);
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

/**
 * Formatear número
 */
function formatNumber(num) {
    return new Intl.NumberFormat('es-ES').format(num);
}

/**
 * Mostrar modal genérico
 */
function showModal(title, content, size = '') {
    const modalId = 'genericModal';
    let modal = document.getElementById(modalId);
    
    if (!modal) {
        modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        document.body.appendChild(modal);
    }
    
    modal.innerHTML = `
        <div class="modal-dialog ${size}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        </div>
    `;
    
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
    
    return modalInstance;
}

/**
 * Mostrar alerta
 */
function showAlert(message, type = 'info', duration = 5000) {
    const alertId = 'alert-' + Date.now();
    const alertEl = document.createElement('div');
    alertEl.id = alertId;
    alertEl.className = `alert alert-${type} alert-dismissible fade show`;
    alertEl.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    `;
    
    alertEl.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${getAlertIcon(type)} me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertEl);
    
    if (duration > 0) {
        setTimeout(() => {
            if (document.getElementById(alertId)) {
                alertEl.remove();
            }
        }, duration);
    }
    
    return alertEl;
}

/**
 * Obtener icono para alerta
 */
function getAlertIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Confirmar acción
 */
function confirmAction(message, callback) {
    const confirmed = confirm(message);
    if (confirmed && typeof callback === 'function') {
        callback();
    }
    return confirmed;
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Utilitarios para fechas
 */
const DateUtils = {
    format: function(date, format = 'dd/mm/yyyy') {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        
        return format
            .replace('dd', day)
            .replace('mm', month)
            .replace('yyyy', year);
    },
    
    timeAgo: function(date) {
        const now = new Date();
        const diffInSeconds = Math.floor((now - new Date(date)) / 1000);
        
        if (diffInSeconds < 60) return 'Hace un momento';
        if (diffInSeconds < 3600) return `Hace ${Math.floor(diffInSeconds / 60)} minutos`;
        if (diffInSeconds < 86400) return `Hace ${Math.floor(diffInSeconds / 3600)} horas`;
        if (diffInSeconds < 2592000) return `Hace ${Math.floor(diffInSeconds / 86400)} días`;
        if (diffInSeconds < 31536000) return `Hace ${Math.floor(diffInSeconds / 2592000)} meses`;
        return `Hace ${Math.floor(diffInSeconds / 31536000)} años`;
    }
};

/**
 * Utilitarios para archivos
 */
const FileUtils = {
    formatSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    
    getExtension: function(filename) {
        return filename.split('.').pop().toLowerCase();
    },
    
    getIcon: function(filename) {
        const ext = this.getExtension(filename);
        const icons = {
            pdf: 'fas fa-file-pdf',
            doc: 'fas fa-file-word',
            docx: 'fas fa-file-word',
            xls: 'fas fa-file-excel',
            xlsx: 'fas fa-file-excel',
            jpg: 'fas fa-file-image',
            jpeg: 'fas fa-file-image',
            png: 'fas fa-file-image',
            gif: 'fas fa-file-image'
        };
        return icons[ext] || 'fas fa-file';
    }
};

// Exponer utilidades globalmente
window.App = {
    showAlert,
    showModal,
    confirmAction,
    formatNumber,
    DateUtils,
    FileUtils,
    debounce,
    throttle
};

// Estilos CSS dinámicos
const dynamicStyles = `
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        pointer-events: none;
    }
    
    .notification {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 10px;
        padding: 16px;
        border-left: 4px solid #007bff;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        pointer-events: auto;
        min-width: 300px;
    }
    
    .notification.show {
        opacity: 1;
        transform: translateX(0);
    }
    
    .notification-success {
        border-left-color: #28a745;
    }
    
    .notification-error {
        border-left-color: #dc3545;
    }
    
    .notification-warning {
        border-left-color: #ffc107;
    }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-title {
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .notification-close {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: #666;
        margin-left: 12px;
    }
    
    .keyboard-shortcuts-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .shortcut-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    
    .shortcut-keys {
        font-family: monospace;
        background: #f8f9fa;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
    }
`;

// Inyectar estilos dinámicos
const styleSheet = document.createElement('style');
styleSheet.textContent = dynamicStyles;
document.head.appendChild(styleSheet);

console.log('Sistema de Gestión de Laboratorios - Quimiosalud v1.0 cargado correctamente');