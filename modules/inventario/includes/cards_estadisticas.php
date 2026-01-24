<?php
// modules/inventario/includes/cards_estadisticas.php
?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Tarjeta 1: Total Productos -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-600">Total Productos</p>
                <p class="text-2xl font-bold"><?php echo $stats['total_productos'] ?? 0; ?></p>
            </div>
            <div class="p-3 bg-blue-100 rounded-full">
                <i class="fas fa-box text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <!-- Tarjeta 2: Activos -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-600">Productos Activos</p>
                <p class="text-2xl font-bold"><?php echo $stats['productos_activos'] ?? 0; ?></p>
            </div>
            <div class="p-3 bg-green-100 rounded-full">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
        </div>
    </div>
    
    <!-- Tarjeta 3: Stock Bajo -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-600">Stock Bajo</p>
                <p class="text-2xl font-bold"><?php echo $stats['stock_bajo'] ?? 0; ?></p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-full">
                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
            </div>
        </div>
    </div>
    
    <!-- Tarjeta 4: Valor Total -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-600">Valor Total (USD)</p>
                <p class="text-2xl font-bold">$<?php echo number_format($stats['valor_total'] ?? 0, 2); ?></p>
            </div>
            <div class="p-3 bg-purple-100 rounded-full">
                <i class="fas fa-dollar-sign text-purple-600"></i>
            </div>
        </div>
    </div>
</div>