<?php
// includes/dashboard_stats.php
// Determinar estado y color de caja
$estado_caja = 'No abierta';
$color_caja = 'gray';
$monto_caja = '0.00';

if ($stats['caja_actual']) {
    $estado_caja = ucfirst($stats['caja_actual']['estado']);
    $color_caja = $stats['caja_actual']['estado'] == 'abierta' ? 'green' : 'red';
    $monto_caja = $stats['caja_actual']['estado'] == 'abierta' 
        ? $stats['caja_actual']['monto_inicial'] 
        : ($stats['caja_actual']['monto_final'] ?? '0.00');
}
?>

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
            <p class="text-3xl font-bold text-green-600">Bs. <?php echo formatCurrency($stats['ventas_hoy']['monto_total'] ?? 0); ?></p>
            <p class="text-sm text-gray-500 mt-2">
                <?php echo $stats['ventas_hoy']['total_ventas'] ?? 0; ?> ventas realizadas
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
            <p class="text-3xl font-bold text-purple-600"><?php echo $stats['productos']['total_productos'] ?? 0; ?></p>
            <?php if (($stats['stock_bajo']['stock_bajo'] ?? 0) > 0): ?>
            <p class="text-sm text-red-600 mt-2 font-medium">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <?php echo $stats['stock_bajo']['stock_bajo']; ?> con stock bajo
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
            <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['clientes']['total_clientes'] ?? 0; ?></p>
            <p class="text-sm text-gray-500 mt-2">
                <i class="fas fa-star text-yellow-500 mr-1"></i>
                Sistema de puntos activo
            </p>
        </div>
    </a>
</div>