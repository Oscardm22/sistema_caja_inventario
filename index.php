<?php
require_once 'config/session.php';
require_once 'config/database.php';
checkAuth();

// Obtener la instancia de Database y la conexión
$database = Database::getInstance();
$conn = $database->getConnection();

$pageTitle = "Dashboard";
require_once 'includes/header.php';

// Obtener estadísticas reales
$stats = [];

try {
    // 1. Estado de caja actual (última caja abierta)
    $query_caja = "SELECT monto_inicial, estado, monto_final FROM caja 
                   WHERE usuario_id = ? 
                   ORDER BY fecha_apertura DESC 
                   LIMIT 1";
    $stmt_caja = $conn->prepare($query_caja);
    $stmt_caja->bind_param("i", $_SESSION['user_id']);
    $stmt_caja->execute();
    $result_caja = $stmt_caja->get_result();
    $caja_actual = $result_caja->fetch_assoc();
    $stmt_caja->close();
    
    // 2. Ventas de hoy
    $hoy = date('Y-m-d');
    $query_ventas_hoy = "SELECT COUNT(*) as total_ventas, 
                         COALESCE(SUM(total), 0) as monto_total 
                         FROM ventas 
                         WHERE DATE(fecha_venta) = ? 
                         AND estado = 'completada'";
    $stmt_ventas = $conn->prepare($query_ventas_hoy);
    $stmt_ventas->bind_param("s", $hoy);
    $stmt_ventas->execute();
    $result_ventas = $stmt_ventas->get_result();
    $ventas_hoy = $result_ventas->fetch_assoc();
    $stmt_ventas->close();
    
    // 3. Total de productos activos
    $query_productos = "SELECT COUNT(*) as total_productos FROM productos WHERE estado = 'activo'";
    $stmt_productos = $conn->prepare($query_productos);
    $stmt_productos->execute();
    $result_productos = $stmt_productos->get_result();
    $productos = $result_productos->fetch_assoc();
    $stmt_productos->close();
    
    // 4. Total de clientes activos
    $query_clientes = "SELECT COUNT(*) as total_clientes FROM clientes WHERE estado = 'activo'";
    $stmt_clientes = $conn->prepare($query_clientes);
    $stmt_clientes->execute();
    $result_clientes = $stmt_clientes->get_result();
    $clientes = $result_clientes->fetch_assoc();
    $stmt_clientes->close();
    
    // 5. Productos con stock bajo
    $query_stock_bajo = "SELECT COUNT(*) as stock_bajo FROM productos 
                         WHERE estado = 'activo' 
                         AND stock <= stock_minimo";
    $stmt_stock = $conn->prepare($query_stock_bajo);
    $stmt_stock->execute();
    $result_stock = $stmt_stock->get_result();
    $stock_bajo = $result_stock->fetch_assoc();
    $stmt_stock->close();
    
    // 6. Ventas recientes (últimas 5)
    $query_ventas_recientes = "SELECT v.numero_factura, v.total, v.fecha_venta, 
                              c.nombre as cliente_nombre,
                              u.nombre as vendedor
                              FROM ventas v
                              LEFT JOIN clientes c ON v.cliente_id = c.id
                              INNER JOIN usuarios u ON v.usuario_id = u.id
                              WHERE v.estado = 'completada'
                              ORDER BY v.fecha_venta DESC 
                              LIMIT 5";
    $stmt_recientes = $conn->prepare($query_ventas_recientes);
    $stmt_recientes->execute();
    $result_recientes = $stmt_recientes->get_result();
    $ventas_recientes = $result_recientes->fetch_all(MYSQLI_ASSOC);
    $stmt_recientes->close();
    
    // 7. Lista de productos con stock bajo
    $query_lista_stock = "SELECT p.nombre, p.stock, p.stock_minimo, c.nombre as categoria
                          FROM productos p
                          LEFT JOIN categorias c ON p.categoria_id = c.id
                          WHERE p.estado = 'activo' 
                          AND p.stock <= p.stock_minimo
                          ORDER BY p.stock ASC
                          LIMIT 10";
    $stmt_lista_stock = $conn->prepare($query_lista_stock);
    $stmt_lista_stock->execute();
    $result_lista_stock = $stmt_lista_stock->get_result();
    $lista_stock_bajo = $result_lista_stock->fetch_all(MYSQLI_ASSOC);
    $stmt_lista_stock->close();
    
} catch (Exception $e) {
    // Manejo de errores
    error_log("Error en dashboard: " . $e->getMessage());
    $error_message = DEBUG_MODE ? $e->getMessage() : "Error al cargar estadísticas";
}

// Función para formatear moneda
function formatCurrency($amount) {
    return number_format($amount, 2, '.', ',');
}

// Determinar estado y color de caja
$estado_caja = 'No abierta';
$color_caja = 'gray';
$monto_caja = '0.00';

if ($caja_actual) {
    $estado_caja = ucfirst($caja_actual['estado']);
    $color_caja = $caja_actual['estado'] == 'abierta' ? 'green' : 'red';
    $monto_caja = $caja_actual['estado'] == 'abierta' 
        ? $caja_actual['monto_inicial'] 
        : ($caja_actual['monto_final'] ?? '0.00');
}
?>

<!-- Contenedor principal con flex -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once 'includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <?php if (isset($error_message)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        <?php echo htmlspecialchars($error_message); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <div class="text-sm text-gray-500">
                <?php echo date('d/m/Y H:i:s'); ?>
            </div>
        </div>
        
        <!-- Estadísticas rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Tarjeta de Caja Actual -->
            <a href="caja/" class="block hover:opacity-90 transition-opacity">
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-blue-500">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                            <i class="fas fa-cash-register text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Caja Actual</h3>
                            <p class="text-sm text-gray-500"><?php echo $estado_caja; ?></p>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-blue-600">Bs. <?php echo formatCurrency($monto_caja); ?></p>
                    <div class="mt-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo $color_caja; ?>-100 text-<?php echo $color_caja; ?>-800">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            <?php echo $estado_caja; ?>
                        </span>
                    </div>
                </div>
            </a>
            
            <!-- Tarjeta de Ventas Hoy -->
            <a href="ventas/" class="block hover:opacity-90 transition-opacity">
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-green-500">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                            <i class="fas fa-shopping-cart text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Ventas Hoy</h3>
                            <p class="text-sm text-gray-500"><?php echo date('d/m/Y'); ?></p>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-green-600">Bs. <?php echo formatCurrency($ventas_hoy['monto_total'] ?? 0); ?></p>
                    <p class="text-sm text-gray-500 mt-2">
                        <?php echo $ventas_hoy['total_ventas'] ?? 0; ?> ventas realizadas
                    </p>
                </div>
            </a>
            
            <!-- Tarjeta de Productos -->
            <a href="inventario/" class="block hover:opacity-90 transition-opacity">
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-purple-500">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                            <i class="fas fa-boxes text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Productos</h3>
                            <p class="text-sm text-gray-500">En inventario</p>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $productos['total_productos'] ?? 0; ?></p>
                    <?php if (($stock_bajo['stock_bajo'] ?? 0) > 0): ?>
                    <p class="text-sm text-red-600 mt-2 font-medium">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <?php echo $stock_bajo['stock_bajo']; ?> con stock bajo
                    </p>
                    <?php else: ?>
                    <p class="text-sm text-green-600 mt-2 font-medium">
                        <i class="fas fa-check-circle mr-1"></i>
                        Stock estable
                    </p>
                    <?php endif; ?>
                </div>
            </a>
            
            <!-- Tarjeta de Clientes -->
            <a href="clientes/" class="block hover:opacity-90 transition-opacity">
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-yellow-500">
                    <div class="flex items-center mb-4">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Clientes</h3>
                            <p class="text-sm text-gray-500">Registrados</p>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $clientes['total_clientes'] ?? 0; ?></p>
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                        Sistema de puntos activo
                    </p>
                </div>
            </a>
        </div>
        
        <!-- Sección de contenido adicional -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Ventas Recientes -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Ventas Recientes</h3>
                    <a href="ventas/" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Ver todas <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <?php if (!empty($ventas_recientes)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($ventas_recientes as $venta): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-blue-600">
                                    <?php echo htmlspecialchars($venta['numero_factura']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars($venta['cliente_nombre'] ?? 'Consumidor Final'); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-green-600">
                                    Bs. <?php echo formatCurrency($venta['total']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No hay ventas recientes</p>
                    <a href="ventas/nueva.php" class="inline-block mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Nueva Venta
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Productos con Stock Bajo -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Productos con Stock Bajo</h3>
                    <a href="inventario/?filter=stock_bajo" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Ver todos <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <?php if (!empty($lista_stock_bajo)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mínimo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($lista_stock_bajo as $producto): ?>
                            <?php 
                                $porcentaje = ($producto['stock'] / $producto['stock_minimo']) * 100;
                                $color_clase = $porcentaje <= 25 ? 'bg-red-100 text-red-800' : 
                                             ($porcentaje <= 50 ? 'bg-yellow-100 text-yellow-800' : 
                                             'bg-orange-100 text-orange-800');
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($producto['categoria'] ?? 'Sin categoría'); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $color_clase; ?>">
                                        <?php echo $producto['stock']; ?> unidades
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo $producto['stock_minimo']; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-4xl text-green-300 mb-3"></i>
                    <p class="text-gray-500">No hay productos con stock bajo</p>
                    <p class="text-sm text-gray-400 mt-1">Todo el inventario está en niveles óptimos</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Acciones rápidas para administrador -->
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-4">
                <i class="fas fa-user-shield mr-2"></i>Acciones de Administrador
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="usuarios/" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <i class="fas fa-user-cog text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-800">Gestionar Usuarios</h4>
                        <p class="text-sm text-gray-600">Administrar roles y permisos</p>
                    </div>
                </a>
                <a href="reportes/" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <i class="fas fa-chart-bar text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-800">Reportes Avanzados</h4>
                        <p class="text-sm text-gray-600">Estadísticas y análisis</p>
                    </div>
                </a>
                <a href="configuracion/" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <i class="fas fa-cog text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-800">Configuración</h4>
                        <p class="text-sm text-gray-600">Ajustes del sistema</p>
                    </div>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard cargado con datos reales');
    
    // Actualizar hora cada segundo
    function updateClock() {
        const now = new Date();
        const options = { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric',
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit',
            hour12: false 
        };
        const dateStr = now.toLocaleDateString('es-PE', options) + ' ' + 
                       now.toLocaleTimeString('es-PE', {hour12: false});
        const clockElement = document.querySelector('.text-gray-500.text-sm');
        if (clockElement) {
            clockElement.textContent = dateStr;
        }
    }
    
    updateClock();
    setInterval(updateClock, 1000);
    
    // Manejo de cache
    if (window.performance && window.performance.navigation.type === 2) {
        window.location.reload();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>