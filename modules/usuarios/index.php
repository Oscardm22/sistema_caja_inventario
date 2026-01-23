<?php
require_once '../../config/session.php';
require_once '../../config/database.php';

// Solo admin puede acceder
checkPermission(['admin']);

$pageTitle = "Gestión de Usuarios";
require_once '../../includes/header.php';

// Obtener conexión a la base de datos
$database = Database::getInstance();
$conn = $database->getConnection();

// Manejar búsqueda y filtros
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Construir consulta con filtros
$query = "SELECT id, nombre, username, rol, estado, ultimo_login, fecha_creacion 
          FROM usuarios 
          WHERE 1=1";
$params = [];
$types = '';

// Aplicar filtros
if (!empty($search)) {
    $query .= " AND (nombre LIKE ? OR username LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($role_filter)) {
    $query .= " AND rol = ?";
    $params[] = $role_filter;
    $types .= 's';
}

if (!empty($status_filter)) {
    $query .= " AND estado = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$query .= " ORDER BY fecha_creacion DESC";

// Ejecutar consulta
$stmt = $database->executeQuery($query, $params, $types);
$usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Contadores para estadísticas
$query_stats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as admins,
                SUM(CASE WHEN rol = 'cajero' THEN 1 ELSE 0 END) as cajeros
                FROM usuarios";
$stmt_stats = $conn->prepare($query_stats);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();
?>

<!-- Contenedor principal -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Gestión de Usuarios</h1>
            <a href="crear.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-user-plus mr-2"></i>Nuevo Usuario
            </a>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Usuarios</p>
                        <p class="text-2xl font-bold"><?php echo $stats['total']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <i class="fas fa-user-check text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Activos</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $stats['activos']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <i class="fas fa-user-shield text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Administradores</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo $stats['admins']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                        <i class="fas fa-cash-register text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Cajeros</p>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['cajeros']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Búsqueda por nombre o usuario -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Nombre o usuario" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Filtro por rol -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos los roles</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="cajero" <?php echo $role_filter === 'cajero' ? 'selected' : ''; ?>>Cajero</option>
                    </select>
                </div>
                
                <!-- Filtro por estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="activo" <?php echo $status_filter === 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $status_filter === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                
                <!-- Botones -->
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors mr-2">
                        <i class="fas fa-search mr-2"></i>Filtrar
                    </button>
                    <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                        <i class="fas fa-redo mr-2"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabla de usuarios -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Login</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Creación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
                                <p>No se encontraron usuarios</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <!-- Información del usuario -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($usuario['nombre']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                @<?php echo htmlspecialchars($usuario['username']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Rol -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($usuario['rol'] === 'admin'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-user-shield mr-1"></i>Administrador
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-cash-register mr-1"></i>Cajero
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Estado -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($usuario['estado'] === 'activo'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-circle text-xs mr-1"></i>Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-circle text-xs mr-1"></i>Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Último login -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($usuario['ultimo_login']): ?>
                                        <?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_login'])); ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">Nunca</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Fecha creación -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?>
                                </td>
                                
                                <!-- Acciones -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <!-- Editar -->
                                        <a href="editar.php?id=<?php echo $usuario['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900 transition-colors"
                                           title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- Cambiar estado -->
                                        <?php if ($usuario['estado'] === 'activo'): ?>
                                            <button onclick="cambiarEstado(<?php echo $usuario['id']; ?>, 'inactivo')"
                                                    class="text-yellow-600 hover:text-yellow-900 transition-colors"
                                                    title="Desactivar usuario">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button onclick="cambiarEstado(<?php echo $usuario['id']; ?>, 'activo')"
                                                    class="text-green-600 hover:text-green-900 transition-colors"
                                                    title="Activar usuario">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <!-- No permitir eliminar si es el único admin -->
                                        <?php if (!($usuario['rol'] === 'admin' && $stats['admins'] <= 1)): ?>
                                        <button onclick="confirmarEliminacion(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>')"
                                                class="text-red-600 hover:text-red-900 transition-colors"
                                                title="Eliminar usuario">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <?php if (count($usuarios) > 0): ?>
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Mostrando <?php echo count($usuarios); ?> usuarios
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Estilos para notificaciones -->
<style>
.notification {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    min-width: 300px;
    max-width: 400px;
    animation: slideIn 0.3s ease-out;
}

.notification.hide {
    animation: slideOut 0.3s ease-in forwards;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>

<!-- JavaScript para acciones -->
<script>
// Sistema de notificaciones
function showNotification(message, type = 'info', duration = 5000) {
    const notificationTypes = {
        'success': {
            icon: 'fas fa-check-circle',
            bgColor: 'bg-green-50',
            textColor: 'text-green-800',
            borderColor: 'border-green-200',
            iconColor: 'text-green-400'
        },
        'error': {
            icon: 'fas fa-exclamation-circle',
            bgColor: 'bg-red-50',
            textColor: 'text-red-800',
            borderColor: 'border-red-200',
            iconColor: 'text-red-400'
        },
        'warning': {
            icon: 'fas fa-exclamation-triangle',
            bgColor: 'bg-yellow-50',
            textColor: 'text-yellow-800',
            borderColor: 'border-yellow-200',
            iconColor: 'text-yellow-400'
        },
        'info': {
            icon: 'fas fa-info-circle',
            bgColor: 'bg-blue-50',
            textColor: 'text-blue-800',
            borderColor: 'border-blue-200',
            iconColor: 'text-blue-400'
        }
    };
    
    const config = notificationTypes[type] || notificationTypes.info;
    
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification ${config.bgColor} ${config.borderColor} border rounded-lg shadow-lg p-4 mb-2`;
    
    notification.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="${config.icon} ${config.iconColor} text-lg"></i>
            </div>
            <div class="ml-3 w-0 flex-1">
                <p class="text-sm font-medium ${config.textColor}">${message}</p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button type="button" class="inline-flex ${config.textColor} hover:opacity-75 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    // Agregar al contenedor de notificaciones
    let container = document.getElementById('notifications-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notifications-container';
        container.className = 'notifications';
        document.body.appendChild(container);
    }
    container.appendChild(notification);
    
    // Configurar botón de cerrar
    const closeBtn = notification.querySelector('button');
    closeBtn.onclick = () => {
        notification.classList.add('hide');
        setTimeout(() => notification.remove(), 300);
    };
    
    // Auto-cerrar después de la duración
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.add('hide');
                setTimeout(() => notification.remove(), 300);
            }
        }, duration);
    }
    
    return notification;
}

// Función para obtener la URL base correcta
function getBaseUrl() {
    const currentPath = window.location.pathname;
    
    if (currentPath.includes('/modules/usuarios/')) {
        const pathParts = currentPath.split('/modules/usuarios/');
        return window.location.origin + pathParts[0] + '/modules/usuarios/';
    }
    
    return window.location.origin + currentPath.substring(0, currentPath.lastIndexOf('/') + 1);
}

// Función para cambiar estado con notificación bonita
function cambiarEstado(userId, nuevoEstado) {
    const actionText = nuevoEstado === 'activo' ? 'activar' : 'desactivar';
    
    // Crear modal personalizado para confirmación
    const modalHtml = `
        <div id="confirm-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full ${nuevoEstado === 'activo' ? 'bg-green-100' : 'bg-yellow-100'}">
                        <i class="${nuevoEstado === 'activo' ? 'fas fa-user-check text-green-600' : 'fas fa-user-slash text-yellow-600'} text-xl"></i>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">
                        ${nuevoEstado === 'activo' ? 'Activar Usuario' : 'Desactivar Usuario'}
                    </h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            ¿Estás seguro de ${actionText} este usuario?
                        </p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button id="cancel-btn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-2 transition-colors">
                            Cancelar
                        </button>
                        <button id="confirm-btn" class="px-4 py-2 ${nuevoEstado === 'activo' ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700'} text-white rounded-md transition-colors">
                            <i class="${nuevoEstado === 'activo' ? 'fas fa-user-check' : 'fas fa-user-slash'} mr-2"></i>
                            ${nuevoEstado === 'activo' ? 'Activar' : 'Desactivar'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Agregar modal al body
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHtml;
    document.body.appendChild(modalContainer);
    
    // Configurar botones del modal
    const modal = document.getElementById('confirm-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const confirmBtn = document.getElementById('confirm-btn');
    
    // Función para cerrar modal
    const closeModal = () => {
        modal.style.opacity = '0';
        setTimeout(() => {
            if (modalContainer.parentNode) {
                modalContainer.remove();
            }
        }, 300);
    };
    
    // Evento cancelar
    cancelBtn.onclick = closeModal;
    
    // Evento confirmar
    confirmBtn.onclick = () => {
        closeModal();
        
        const baseUrl = getBaseUrl();
        const loadingNotification = showNotification('Procesando solicitud...', 'info', 0);
        
        fetch(baseUrl + 'acciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=cambiar_estado&id=${userId}&estado=${nuevoEstado}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            // Remover loading
            if (loadingNotification.parentNode) {
                loadingNotification.remove();
            }
            
            if (data.success) {
                showNotification(data.message, 'success');
                // Recargar después de 1.5 segundos
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(data.message, 'error', 6000);
            }
        })
        .catch(error => {
            // Remover loading
            if (loadingNotification.parentNode) {
                loadingNotification.remove();
            }
            
            console.error('Error:', error);
            showNotification('Error de conexión con el servidor', 'error', 6000);
        });
    };
    
    // Cerrar al hacer clic fuera del modal
    modal.onclick = (e) => {
        if (e.target === modal) {
            closeModal();
        }
    };
    
    // Mostrar modal con animación
    setTimeout(() => {
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.opacity = '1';
    }, 10);
}

// Función para eliminar usuario con modal bonito
function confirmarEliminacion(userId, nombre) {
    // Crear modal personalizado
    const modalHtml = `
        <div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Confirmar Eliminación</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            ¿Estás seguro de eliminar al usuario <span class="font-semibold">"${nombre}"</span>?
                        </p>
                        <p class="text-sm text-red-500 mt-2 font-medium">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Esta acción no se puede deshacer.
                        </p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button id="cancel-btn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-2 transition-colors">
                            Cancelar
                        </button>
                        <button id="confirm-btn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            <i class="fas fa-trash mr-2"></i>Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Agregar modal al body
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHtml;
    document.body.appendChild(modalContainer);
    
    // Configurar botones del modal
    const modal = document.getElementById('delete-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const confirmBtn = document.getElementById('confirm-btn');
    
    // Función para cerrar modal
    const closeModal = () => {
        modal.style.opacity = '0';
        setTimeout(() => {
            if (modalContainer.parentNode) {
                modalContainer.remove();
            }
        }, 300);
    };
    
    // Evento cancelar
    cancelBtn.onclick = closeModal;
    
    // Evento confirmar
    confirmBtn.onclick = () => {
        closeModal();
        
        const baseUrl = getBaseUrl();
        const loadingNotification = showNotification('Eliminando usuario...', 'info', 0);
        
        fetch(baseUrl + 'acciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=eliminar&id=${userId}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            // Remover loading
            if (loadingNotification.parentNode) {
                loadingNotification.remove();
            }
            
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(data.message, 'error', 6000);
            }
        })
        .catch(error => {
            // Remover loading
            if (loadingNotification.parentNode) {
                loadingNotification.remove();
            }
            
            console.error('Error:', error);
            showNotification('Error de conexión con el servidor', 'error', 6000);
        });
    };
    
    // Cerrar al hacer clic fuera del modal
    modal.onclick = (e) => {
        if (e.target === modal) {
            closeModal();
        }
    };
    
    // Mostrar modal con animación
    setTimeout(() => {
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.opacity = '1';
    }, 10);
}

// Auto-focus en búsqueda
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.focus();
    }
    
    // Mostrar mensajes de sesión si existen (para redirecciones desde crear/editar)
    <?php if (isset($_SESSION['success_message'])): ?>
    showNotification('<?php echo addslashes($_SESSION['success_message']); ?>', 'success');
    <?php unset($_SESSION['success_message']); endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
    showNotification('<?php echo addslashes($_SESSION['error_message']); ?>', 'error', 6000);
    <?php unset($_SESSION['error_message']); endif; ?>
});
</script>

<?php require_once '../../includes/footer.php'; ?>