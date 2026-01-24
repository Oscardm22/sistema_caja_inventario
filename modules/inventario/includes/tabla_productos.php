<?php
// modules/inventario/includes/tabla_productos.php

// Verificar la variable de sesión correctamente
$usuario_rol = $_SESSION['rol'] ?? $_SESSION['user_role'] ?? 'cajero';
$es_admin = ($usuario_rol === 'admin');
?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <?php if (empty($productos)): ?>
        <div class="p-8 text-center text-gray-500">
            <i class="fas fa-box-open text-4xl mb-4 text-gray-300"></i>
            <p class="text-lg">No se encontraron productos</p>
            <p class="text-sm">Intenta con otros filtros o agrega nuevos productos</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Producto
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Código
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Precio (USD)
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Precio (BS)
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stock
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($productos as $producto): 
                        $stock_class = ($producto['stock'] <= $producto['stock_minimo']) ? 'stock-bajo' : '';
                        $stock_badge = ($producto['stock'] <= $producto['stock_minimo']) ? 
                            '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Bajo</span>' : '';
                    ?>
                    <tr class="hover:bg-gray-50 <?php echo $stock_class; ?>">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-box text-gray-500"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($producto['nombre']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded font-mono">
                                <?php echo htmlspecialchars($producto['codigo']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">
                                $<?php echo number_format($producto['precio_$'], 2); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-green-700">
                                Bs. <?php echo number_format($producto['precio_bs'] ?? 0, 2, ',', '.'); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 mr-2">
                                    <?php echo $producto['stock']; ?>
                                </span>
                                <?php echo $stock_badge; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full <?php echo ($producto['estado'] == 'activo') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo ucfirst($producto['estado']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <?php if ($es_admin): ?>
                                <a href="editar.php?id=<?php echo $producto['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                
                                <button onclick="mostrarDetalleProducto(<?php echo $producto['id']; ?>)" 
                                        class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <?php if ($es_admin): ?>
                                <button onclick="cambiarEstadoProducto(<?php echo $producto['id']; ?>, '<?php echo $producto['estado']; ?>')" 
                                        class="<?php echo ($producto['estado'] == 'activo') ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'; ?>">
                                    <i class="fas <?php echo ($producto['estado'] == 'activo') ? 'fa-pause' : 'fa-play'; ?>"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación (simple por ahora) -->
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                    Mostrando <span class="font-medium"><?php echo count($productos); ?></span> productos
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">
                        Anterior
                    </button>
                    <button class="px-3 py-1 border border-gray-300 rounded text-sm bg-blue-50 text-blue-600 border-blue-300">
                        1
                    </button>
                    <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Funciones para productos
function mostrarDetalleProducto(id) {
    alert('Detalle del producto ID: ' + id + '\n\nEsta función estará disponible pronto.');
}

function cambiarEstadoProducto(id, estadoActual) {
    if (!confirm('¿Estás seguro de cambiar el estado de este producto?')) {
        return;
    }
    
    const nuevoEstado = estadoActual === 'activo' ? 'inactivo' : 'activo';
    
    fetch('acciones.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest' // ← Añade este header
        },
        body: `action=cambiar_estado&id=${id}&estado=${nuevoEstado}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar notificación y recargar
            showNotification('success', data.message || 'Estado actualizado correctamente');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification('error', data.error || 'No se pudo cambiar el estado');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Error de conexión');
    });
}

// Función auxiliar para mostrar notificaciones
function showNotification(type, message) {
    // Puedes usar tu sistema de notificaciones existente
    // o esta simple implementación
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>