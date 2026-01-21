<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
checkAuth();
checkPermission(['admin', 'cajero', 'almacen']);

$database = Database::getInstance();
$conn = $database->getConnection();

$pageTitle = "Inventario - Productos";
require_once '../../includes/header.php';

// Parámetros de búsqueda y filtrado
$search = $_GET['search'] ?? '';
$categoria_id = $_GET['categoria_id'] ?? '';
$stock_filter = $_GET['stock_filter'] ?? 'todos';
$estado = $_GET['estado'] ?? 'activo';

// Construir consulta base
$query = "SELECT p.*, c.nombre as categoria_nombre, c.color as categoria_color 
          FROM productos p 
          LEFT JOIN categorias c ON p.categoria_id = c.id 
          WHERE 1=1";

$params = [];
$types = '';

// Aplicar filtros
if (!empty($search)) {
    $query .= " AND (p.nombre LIKE ? OR p.codigo LIKE ? OR p.descripcion LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
    $types .= 'sss';
}

if (!empty($categoria_id) && $categoria_id !== 'todas') {
    $query .= " AND p.categoria_id = ?";
    $params[] = $categoria_id;
    $types .= 'i';
}

if ($estado !== 'todos') {
    $query .= " AND p.estado = ?";
    $params[] = $estado;
    $types .= 's';
}

// Filtro de stock
if ($stock_filter === 'bajo') {
    $query .= " AND p.stock <= p.stock_minimo";
} elseif ($stock_filter === 'agotado') {
    $query .= " AND p.stock = 0";
} elseif ($stock_filter === 'optimo') {
    $query .= " AND p.stock > p.stock_minimo";
}

// Ordenar
$query .= " ORDER BY p.nombre ASC";

// Obtener productos
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$productos = $stmt->get_result();

// Obtener categorías para el filtro
$query_categorias = "SELECT id, nombre FROM categorias WHERE estado = 'activa' ORDER BY nombre";
$categorias_result = $conn->query($query_categorias);
$categorias = $categorias_result->fetch_all(MYSQLI_ASSOC);

// Estadísticas rápidas
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN stock <= stock_minimo THEN 1 ELSE 0 END) as stock_bajo,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as agotados,
                SUM(stock * precio_venta) as valor_total
                FROM productos WHERE estado = 'activo'";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<div class="flex">
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Inventario de Productos</h1>
                <p class="text-gray-600">Gestión completa de productos y stock</p>
            </div>
            <div class="flex space-x-3">
                <a href="categorias/" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-tags mr-2"></i>Categorías
                </a>
                <a href="movimientos.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-exchange-alt mr-2"></i>Movimientos
                </a>
                <a href="nuevo.php" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Nuevo Producto
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Búsqueda -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <div class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nombre, código...">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Categoría -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                    <select name="categoria_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="todas" <?php echo $categoria_id === 'todas' || empty($categoria_id) ? 'selected' : ''; ?>>Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoria_id == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Estado Stock -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Stock</label>
                    <select name="stock_filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="todos" <?php echo $stock_filter === 'todos' ? 'selected' : ''; ?>>Todos</option>
                        <option value="optimo" <?php echo $stock_filter === 'optimo' ? 'selected' : ''; ?>>Stock Óptimo</option>
                        <option value="bajo" <?php echo $stock_filter === 'bajo' ? 'selected' : ''; ?>>Stock Bajo</option>
                        <option value="agotado" <?php echo $stock_filter === 'agotado' ? 'selected' : ''; ?>>Agotado</option>
                    </select>
                </div>
                
                <!-- Estado Producto -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="activo" <?php echo $estado === 'activo' ? 'selected' : ''; ?>>Activos</option>
                        <option value="inactivo" <?php echo $estado === 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                        <option value="todos" <?php echo $estado === 'todos' ? 'selected' : ''; ?>>Todos</option>
                    </select>
                </div>
                
                <!-- Botones -->
                <div class="md:col-span-4 flex justify-between mt-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-filter mr-2"></i>Filtrar
                    </button>
                    <a href="?" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        <i class="fas fa-redo mr-2"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                        <i class="fas fa-box text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Productos Activos</p>
                        <p class="text-2xl font-bold"><?php echo $stats['total'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-red-100 text-red-600 mr-3">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Stock Bajo</p>
                        <p class="text-2xl font-bold"><?php echo $stats['stock_bajo'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                        <i class="fas fa-times-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Agotados</p>
                        <p class="text-2xl font-bold"><?php echo $stats['agotados'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                        <i class="fas fa-dollar-sign text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Valor Total</p>
                        <p class="text-2xl font-bold">Bs. <?php echo number_format($stats['valor_total'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Productos -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precios</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($productos->num_rows > 0): ?>
                            <?php while ($producto = $productos->fetch_assoc()): 
                                // Determinar clase de stock
                                $stock_class = '';
                                if ($producto['stock'] == 0) {
                                    $stock_class = 'bg-red-100 text-red-800';
                                } elseif ($producto['stock'] <= $producto['stock_minimo']) {
                                    $stock_class = 'bg-yellow-100 text-yellow-800';
                                } else {
                                    $stock_class = 'bg-green-100 text-green-800';
                                }
                                
                                // Determinar clase de estado
                                $estado_class = $producto['estado'] == 'activo' 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-gray-100 text-gray-800';
                            ?>
                            <tr class="hover:bg-gray-50">
                                <!-- Código -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm font-semibold text-blue-600">
                                        <?php echo htmlspecialchars($producto['codigo']); ?>
                                    </span>
                                </td>
                                
                                <!-- Producto -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                            <?php if ($producto['imagen'] && $producto['imagen'] != 'default.jpg'): ?>
                                                <img src="../../uploads/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                                     class="h-10 w-10 rounded-lg object-cover">
                                            <?php else: ?>
                                                <i class="fas fa-box text-gray-400"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                <?php echo htmlspecialchars($producto['nombre']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500 truncate max-w-xs">
                                                <?php echo htmlspecialchars(substr($producto['descripcion'] ?? '', 0, 50)); ?>...
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Categoría -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($producto['categoria_nombre']): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" 
                                              style="background-color: <?php echo $producto['categoria_color'] ?? '#E5E7EB'; ?>20; color: <?php echo $producto['categoria_color'] ?? '#6B7280'; ?>;">
                                            <i class="fas fa-tag mr-1"></i>
                                            <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">Sin categoría</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Precios -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">
                                        <div class="font-semibold text-green-600">
                                            Venta: Bs. <?php echo number_format($producto['precio_venta'], 2); ?>
                                        </div>
                                        <div class="text-gray-500">
                                            Compra: Bs. <?php echo number_format($producto['precio_compra'], 2); ?>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Stock -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $stock_class; ?>">
                                            <?php echo $producto['stock']; ?> <?php echo htmlspecialchars($producto['unidad_medida']); ?>
                                        </span>
                                        <div class="text-xs text-gray-500">
                                            Mín: <?php echo $producto['stock_minimo']; ?>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Estado -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $estado_class; ?>">
                                        <i class="fas fa-circle text-xs mr-1"></i>
                                        <?php echo ucfirst($producto['estado']); ?>
                                    </span>
                                </td>
                                
                                <!-- Acciones -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <!-- Botón Ajustar Stock -->
                                        <button onclick="ajustarStock(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars($producto['nombre']); ?>', <?php echo $producto['stock']; ?>)" 
                                                class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors text-xs"
                                                title="Ajustar stock">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <!-- Botón Editar -->
                                        <a href="editar.php?id=<?php echo $producto['id']; ?>" 
                                           class="px-3 py-1 bg-yellow-100 text-yellow-600 rounded hover:bg-yellow-200 transition-colors text-xs"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- Botón Eliminar/Activar -->
                                        <?php if ($producto['estado'] == 'activo'): ?>
                                            <a href="eliminar.php?id=<?php echo $producto['id']; ?>" 
                                               class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors text-xs"
                                               title="Desactivar"
                                               onclick="return confirm('¿Estás seguro de desactivar este producto?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="eliminar.php?id=<?php echo $producto['id']; ?>&activar=1" 
                                               class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition-colors text-xs"
                                               title="Activar"
                                               onclick="return confirm('¿Estás seguro de activar este producto?');">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-gray-400 mb-3">
                                        <i class="fas fa-box-open text-4xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-700 mb-2">No hay productos</h3>
                                    <p class="text-gray-500 mb-4">No se encontraron productos con los filtros seleccionados.</p>
                                    <a href="nuevo.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-plus mr-2"></i>Crear primer producto
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación (si es necesaria) -->
            <?php if ($productos->num_rows > 0): ?>
            <div class="bg-white px-6 py-3 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Mostrando <span class="font-medium"><?php echo $productos->num_rows; ?></span> productos
                    </div>
                    <div class="flex space-x-2">
                        <!-- Aquí podrías agregar paginación si tienes muchos productos -->
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal para ajustar stock -->
<div id="modalAjustarStock" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Ajustar Stock</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="formAjustarStock" class="space-y-4">
            <input type="hidden" id="producto_id" name="producto_id">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                <input type="text" id="producto_nombre" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Actual</label>
                <input type="text" id="stock_actual" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Ajuste</label>
                <select id="tipo_ajuste" name="tipo_ajuste" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="entrada">Entrada (Aumentar stock)</option>
                    <option value="salida">Salida (Disminuir stock)</option>
                    <option value="ajuste">Ajuste (Establecer nuevo valor)</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                <input type="number" id="cantidad" name="cantidad" min="1" step="1" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                       required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                <select id="motivo" name="motivo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="compra">Compra</option>
                    <option value="ajuste">Ajuste de inventario</option>
                    <option value="devolucion">Devolución</option>
                    <option value="daño">Daño/Perdida</option>
                    <option value="caducado">Producto caducado</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                <textarea id="observaciones" name="observaciones" rows="2" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="closeModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Guardar Ajuste
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Funciones para el modal de ajuste de stock
function ajustarStock(productoId, productoNombre, stockActual) {
    document.getElementById('producto_id').value = productoId;
    document.getElementById('producto_nombre').value = productoNombre;
    document.getElementById('stock_actual').value = stockActual;
    document.getElementById('cantidad').value = '';
    document.getElementById('observaciones').value = '';
    
    document.getElementById('modalAjustarStock').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modalAjustarStock').classList.add('hidden');
}

// Manejar envío del formulario de ajuste de stock
document.getElementById('formAjustarStock').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('ajax/actualizar_stock.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Stock actualizado correctamente');
            closeModal();
            location.reload(); // Recargar la página para ver cambios
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el stock');
    });
});

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Cerrar modal al hacer clic fuera
document.getElementById('modalAjustarStock').addEventListener('click', function(e) {
    if (e.target.id === 'modalAjustarStock') {
        closeModal();
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>