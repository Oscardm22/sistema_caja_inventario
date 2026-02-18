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
                                        <button onclick="abrirModalCambiarEstado(
                                            <?php echo $categoria['id']; ?>, 
                                            '<?php echo $categoria['estado']; ?>',
                                            '<?php echo htmlspecialchars($categoria['nombre']); ?>',
                                            <?php echo $productos_count; ?>
                                        )" 
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

<!-- Modal para cambiar estado de categoría -->
<div id="modal-cambiar-estado" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold" id="modal-estado-titulo">Cambiar Estado de Categoría</h3>
            <button onclick="cerrarModalEstado()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="text-center py-4">
            <!-- Icono dinámico según el estado -->
            <div id="estado-icono-container" class="mb-4">
                <i id="estado-icono" class="fas fa-pause-circle text-6xl text-yellow-500"></i>
            </div>
            
            <p class="text-lg mb-2" id="modal-estado-mensaje">¿Estás seguro de cambiar el estado de esta categoría?</p>
            <p class="text-sm text-gray-600 mb-2">
                <span id="categoria-nombre-modal" class="font-semibold"></span>
            </p>
            <p class="text-xs text-gray-500" id="estado-actual-info"></p>
            
            <!-- Estado actual y nuevo estado -->
            <div class="flex justify-center items-center space-x-4 my-4">
                <div class="text-center">
                    <span class="text-xs text-gray-500">Estado Actual</span>
                    <div id="estado-actual-badge" class="px-3 py-1 rounded-full text-sm font-medium mt-1">
                        <!-- Se llenará con JS -->
                    </div>
                </div>
                <i class="fas fa-arrow-right text-gray-400"></i>
                <div class="text-center">
                    <span class="text-xs text-gray-500">Nuevo Estado</span>
                    <div id="estado-nuevo-badge" class="px-3 py-1 rounded-full text-sm font-medium mt-1">
                        <!-- Se llenará con JS -->
                    </div>
                </div>
            </div>
            
            <!-- Advertencia si tiene productos -->
            <div id="productos-advertencia" class="hidden mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                <span class="text-sm text-yellow-700" id="productos-mensaje"></span>
            </div>
        </div>
        
        <form id="form-cambiar-estado" class="space-y-4">
            <input type="hidden" id="estado-categoria-id" name="id" value="">
            <input type="hidden" id="estado-categoria-nuevo" name="estado" value="">
            
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" 
                        onclick="cerrarModalEstado()" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" 
                        id="btn-confirmar-cambio"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-check mr-2"></i>Confirmar Cambio
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

// 🆕 NUEVA FUNCIÓN: Abrir modal para cambiar estado
function abrirModalCambiarEstado(id, estadoActual, nombreCategoria, tieneProductos = false) {
    const nuevoEstado = estadoActual === 'activa' ? 'inactiva' : 'activa';
    
    // Guardar datos en el modal
    document.getElementById('estado-categoria-id').value = id;
    document.getElementById('estado-categoria-nuevo').value = nuevoEstado;
    document.getElementById('categoria-nombre-modal').textContent = nombreCategoria;
    
    // Configurar icono y mensajes según el cambio
    const icono = document.getElementById('estado-icono');
    const mensaje = document.getElementById('modal-estado-mensaje');
    const estadoActualBadge = document.getElementById('estado-actual-badge');
    const estadoNuevoBadge = document.getElementById('estado-nuevo-badge');
    
    if (nuevoEstado === 'inactiva') {
        // Activando → Inactivando
        icono.className = 'fas fa-pause-circle text-6xl text-yellow-500';
        mensaje.textContent = '¿Estás seguro de desactivar esta categoría?';
        estadoActualBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
        estadoActualBadge.textContent = 'Activa';
        estadoNuevoBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
        estadoNuevoBadge.textContent = 'Inactiva';
    } else {
        // Inactivando → Activando
        icono.className = 'fas fa-play-circle text-6xl text-green-500';
        mensaje.textContent = '¿Estás seguro de activar esta categoría?';
        estadoActualBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
        estadoActualBadge.textContent = 'Inactiva';
        estadoNuevoBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
        estadoNuevoBadge.textContent = 'Activa';
    }
    
    // Mostrar advertencia si tiene productos (solo cuando se va a desactivar)
    const advertenciaDiv = document.getElementById('productos-advertencia');
    if (tieneProductos && nuevoEstado === 'inactiva') {
        advertenciaDiv.classList.remove('hidden');
        document.getElementById('productos-mensaje').textContent = 
            `Esta categoría tiene ${tieneProductos} producto(s) asociado(s). Al desactivarla, los productos no se eliminarán pero quedarán en una categoría inactiva.`;
    } else {
        advertenciaDiv.classList.add('hidden');
    }
    
    // Mostrar modal
    document.getElementById('modal-cambiar-estado').classList.remove('hidden');
}

// 🆕 NUEVA FUNCIÓN: Cerrar modal de estado
function cerrarModalEstado() {
    document.getElementById('modal-cambiar-estado').classList.add('hidden');
}

// 🆕 NUEVA FUNCIÓN: Configurar el formulario de cambio de estado
document.addEventListener('DOMContentLoaded', function() {
    const formCambiarEstado = document.getElementById('form-cambiar-estado');
    if (formCambiarEstado) {
        formCambiarEstado.addEventListener('submit', function(e) {
            e.preventDefault();
            cambiarEstadoCategoriaModal();
        });
    }
});

// 🆕 NUEVA FUNCIÓN: Cambiar estado desde el modal
function cambiarEstadoCategoriaModal() {
    const id = document.getElementById('estado-categoria-id').value;
    const nuevoEstado = document.getElementById('estado-categoria-nuevo').value;
    
    const formData = new FormData();
    formData.append('action', 'cambiar_estado');
    formData.append('id', id);
    formData.append('estado', nuevoEstado);
    
    // Deshabilitar botón mientras se procesa
    const btnConfirmar = document.getElementById('btn-confirmar-cambio');
    btnConfirmar.disabled = true;
    btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
    
    fetch('acciones_categorias.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('success', data.message);
            cerrarModalEstado();
            
            // Recargar la página después de un breve delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            mostrarNotificacion('error', data.error);
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = '<i class="fas fa-check mr-2"></i>Confirmar Cambio';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
        btnConfirmar.disabled = false;
        btnConfirmar.innerHTML = '<i class="fas fa-check mr-2"></i>Confirmar Cambio';
    });
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