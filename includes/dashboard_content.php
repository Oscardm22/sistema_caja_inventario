<?php
// includes/dashboard_content.php
?>

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
        
        <?php if (!empty($stats['ventas_recientes'])): ?>
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
                    <?php foreach ($stats['ventas_recientes'] as $venta): ?>
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
        
        <?php if (!empty($stats['lista_stock_bajo'])): ?>
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
                    <?php foreach ($stats['lista_stock_bajo'] as $producto): ?>
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