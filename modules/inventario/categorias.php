<?php
// modules/inventario/categorias.php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once 'models/InventarioModel.php';

// Solo admin puede gestionar categorías
checkPermission(['admin']);

$pageTitle = "Gestión de Categorías";
require_once '../../includes/header.php';

// Instanciar modelo
$model = new InventarioModel();

// Obtener categorías
$categorias = $model->getCategorias();

// Contador para estadísticas
$total_categorias = count($categorias);
$categorias_activas = 0;
foreach ($categorias as $cat) {
    if ($cat['estado'] == 'activa') {
        $categorias_activas++;
    }
}
?>

<!-- Contenedor principal -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Gestión de Categorías</h1>
            <button onclick="abrirModalCrear()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Nueva Categoría
            </button>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total de Categorías</p>
                        <p class="text-2xl font-bold"><?php echo $total_categorias; ?></p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-tags text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Categorías Activas</p>
                        <p class="text-2xl font-bold"><?php echo $categorias_activas; ?></p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de categorías -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <?php if (empty($categorias)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-tags text-4xl mb-4 text-gray-300"></i>
                    <p class="text-lg">No hay categorías creadas</p>
                    <p class="text-sm">Crea tu primera categoría para organizar los productos</p>
                    <button onclick="abrirModalCrear()" 
                            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Crear Categoría
                    </button>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nombre
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Productos
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Creación
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($categorias as $categoria): 
                                // Obtener conteo de productos para esta categoría
                                $productos_count = $model->contarProductosPorCategoria($categoria['id']);
                            ?>
                            <tr class="hover:bg-gray-50" id="categoria-<?php echo $categoria['id']; ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-tag text-blue-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate">
                                        <?php echo htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $productos_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $productos_count; ?> productos
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?php echo ($categoria['estado'] == 'activa') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo ucfirst($categoria['estado']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($categoria['fecha_creacion'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="abrirModalEditar(<?php echo $categoria['id']; ?>)" 
                                                class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="cambiarEstadoCategoria(<?php echo $categoria['id']; ?>, '<?php echo $categoria['estado']; ?>')" 
                                                class="<?php echo ($categoria['estado'] == 'activa') ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'; ?>">
                                            <i class="fas <?php echo ($categoria['estado'] == 'activa') ? 'fa-pause' : 'fa-play'; ?>"></i>
                                        </button>
                                        <?php if ($productos_count == 0): ?>
                                        <button onclick="eliminarCategoria(<?php echo $categoria['id']; ?>)" 
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal para crear/editar categoría -->
<div id="modal-categoria" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold" id="modal-titulo">Nueva Categoría</h3>
            <button onclick="cerrarModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="form-categoria" class="space-y-4">
            <input type="hidden" id="categoria-id" name="id" value="">
            
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre de la categoría *
                </label>
                <input type="text" 
                       id="nombre" 
                       name="nombre" 
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Ej: Electrónica, Ropa, Alimentos">
            </div>
            
            <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción
                </label>
                <textarea id="descripcion" 
                          name="descripcion" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Descripción breve de la categoría..."></textarea>
            </div>
            
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                    Estado
                </label>
                <select id="estado" 
                        name="estado" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="activa">Activa</option>
                    <option value="inactiva">Inactiva</option>
                </select>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" 
                        onclick="cerrarModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript para categorías -->
<script src="assets/js/categorias.js"></script>
<script>
// Funciones modales básicas
function abrirModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Nueva Categoría';
    document.getElementById('form-categoria').reset();
    document.getElementById('categoria-id').value = '';
    document.getElementById('modal-categoria').classList.remove('hidden');
}

function abrirModalEditar(id) {
    // Cargar datos de la categoría
    fetch(`acciones_categorias.php?action=obtener&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modal-titulo').textContent = 'Editar Categoría';
                document.getElementById('categoria-id').value = data.categoria.id;
                document.getElementById('nombre').value = data.categoria.nombre;
                document.getElementById('descripcion').value = data.categoria.descripcion || '';
                document.getElementById('estado').value = data.categoria.estado;
                document.getElementById('modal-categoria').classList.remove('hidden');
            } else {
                mostrarNotificacion('error', data.error || 'Error al cargar categoría');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error de conexión');
        });
}

function cerrarModal() {
    document.getElementById('modal-categoria').classList.add('hidden');
}

// Función para notificaciones
function mostrarNotificacion(tipo, mensaje) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${tipo === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    notification.textContent = mensaje;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<?php require_once '../../includes/footer.php'; ?>