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

<!-- JavaScript para acciones -->
<script>
// Función para cambiar estado
function cambiarEstado(userId, nuevoEstado) {
    if (confirm('¿Estás seguro de cambiar el estado de este usuario?')) {
        fetch('acciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=cambiar_estado&id=${userId}&estado=${nuevoEstado}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error de conexión: ' + error);
        });
    }
}

// Función para eliminar usuario
function confirmarEliminacion(userId, nombre) {
    if (confirm(`¿Estás seguro de eliminar al usuario "${nombre}"?\nEsta acción no se puede deshacer.`)) {
        fetch('acciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=eliminar&id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error de conexión: ' + error);
        });
    }
}

// Auto-focus en búsqueda
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.focus();
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>