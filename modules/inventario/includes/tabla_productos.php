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
                            Imagen
                        </th>
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
                        
                        // DETERMINAR LA RUTA DE LA IMAGEN
                        $imagen_path = '../../assets/img/no-image.png'; // Por defecto
                        
                        if (!empty($producto['imagen']) && $producto['imagen'] !== 'default.jpg') {
                            // Verificar si la imagen existe en la carpeta de uploads
                            $ruta_uploads = '../../uploads/products/' . $producto['imagen'];
                            if (file_exists($ruta_uploads)) {
                                $imagen_path = $ruta_uploads;
                            }
                        }
                    ?>
                    <tr class="hover:bg-gray-50 <?php echo $stock_class; ?>">
                        <!-- COLUMNA DE IMAGEN CON IMAGEN REAL -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-12 w-12 flex-shrink-0">
                                    <img class="h-12 w-12 rounded-lg object-cover border border-gray-200" 
                                         src="<?php echo $imagen_path; ?>" 
                                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                         onerror="this.src='../../assets/img/no-image.png'">
                                </div>
                            </div>
                        </td>
                        
                        <!-- COLUMNA DE PRODUCTO (solo nombre y categoría) -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($producto['nombre']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?>
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
                                
                                <!-- Llama a abrirModalDetalleProducto -->
                                <button onclick="abrirModalDetalleProducto(<?php echo $producto['id']; ?>)" 
                                        class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <?php if ($es_admin): ?>
                                <button onclick="abrirModalCambiarEstadoProducto(
                                    <?php echo $producto['id']; ?>, 
                                    '<?php echo $producto['estado']; ?>',
                                    '<?php echo htmlspecialchars($producto['nombre']); ?>',
                                    <?php echo $producto['stock']; ?>,
                                    <?php echo $producto['stock_minimo']; ?>
                                )" 
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
    <?php endif; ?>
</div>

<!-- Modal para ver detalles del producto -->
<div id="modal-detalle-producto" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold" id="modal-detalle-titulo">Detalles del Producto</h3>
            <button onclick="cerrarModalDetalle()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="flex flex-col md:flex-row gap-6" id="detalle-contenido">
            <!-- Columna de imagen -->
            <div class="md:w-1/3">
                <div class="bg-gray-100 rounded-lg p-4 flex justify-center items-center">
                    <img id="detalle-imagen" src="../../assets/img/no-image.png" 
                         alt="Imagen del producto" 
                         class="max-w-full max-h-48 object-contain rounded-lg"
                         onerror="this.src='../../assets/img/no-image.png'">
                </div>
            </div>
            
            <!-- Columna de información -->
            <div class="md:w-2/3 space-y-4">
                <div>
                    <h4 class="text-sm text-gray-500">Nombre del Producto</h4>
                    <p id="detalle-nombre" class="text-lg font-semibold text-gray-900">-</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm text-gray-500">Código</h4>
                        <p id="detalle-codigo" class="font-mono text-sm bg-gray-100 px-2 py-1 rounded inline-block">-</p>
                    </div>
                    <div>
                        <h4 class="text-sm text-gray-500">Categoría</h4>
                        <p id="detalle-categoria" class="font-medium">-</p>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-sm text-gray-500">Descripción</h4>
                    <p id="detalle-descripcion" class="text-gray-700 bg-gray-50 p-3 rounded-lg">-</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm text-gray-500">Precio (USD)</h4>
                        <p id="detalle-precio-usd" class="text-xl font-bold text-gray-900">$0.00</p>
                    </div>
                    <div>
                        <h4 class="text-sm text-gray-500">Precio (BS)</h4>
                        <p id="detalle-precio-bs" class="text-xl font-bold text-green-700">Bs. 0,00</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm text-gray-500">Stock Actual</h4>
                        <div class="flex items-center">
                            <span id="detalle-stock" class="text-2xl font-bold text-gray-900 mr-2">0</span>
                            <span id="detalle-unidad" class="text-sm text-gray-500">unidades</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm text-gray-500">Stock Mínimo</h4>
                        <p id="detalle-stock-minimo" class="text-lg font-medium text-gray-700">0 unidades</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm text-gray-500">Estado</h4>
                        <span id="detalle-estado" class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-800">Activo</span>
                    </div>
                    <div>
                        <h4 class="text-sm text-gray-500">Fecha de Creación</h4>
                        <p id="detalle-fecha" class="text-sm text-gray-600">-</p>
                    </div>
                </div>
                
                <?php if ($es_admin): ?>
                <div class="pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <a id="detalle-btn-editar" href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-edit mr-2"></i>Editar Producto
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado de producto -->
<div id="modal-cambiar-estado-producto" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold" id="modal-producto-titulo">Cambiar Estado de Producto</h3>
            <button onclick="cerrarModalProducto()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="text-center py-4">
            <!-- Icono dinámico según el estado -->
            <div id="producto-icono-container" class="mb-4">
                <i id="producto-icono" class="fas fa-pause-circle text-6xl text-yellow-500"></i>
            </div>
            
            <p class="text-lg mb-2" id="modal-producto-mensaje">¿Estás seguro de cambiar el estado de este producto?</p>
            <p class="text-sm text-gray-600 mb-2">
                <span id="producto-nombre-modal" class="font-semibold"></span>
            </p>
            
            <!-- Estado actual y nuevo estado -->
            <div class="flex justify-center items-center space-x-4 my-4">
                <div class="text-center">
                    <span class="text-xs text-gray-500">Estado Actual</span>
                    <div id="producto-estado-actual-badge" class="px-3 py-1 rounded-full text-sm font-medium mt-1">
                        <!-- Se llenará con JS -->
                    </div>
                </div>
                <i class="fas fa-arrow-right text-gray-400"></i>
                <div class="text-center">
                    <span class="text-xs text-gray-500">Nuevo Estado</span>
                    <div id="producto-estado-nuevo-badge" class="px-3 py-1 rounded-full text-sm font-medium mt-1">
                        <!-- Se llenará con JS -->
                    </div>
                </div>
            </div>
            
            <!-- Advertencia si tiene stock bajo -->
            <div id="producto-stock-advertencia" class="hidden mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                <span class="text-sm text-yellow-700" id="producto-stock-mensaje"></span>
            </div>
        </div>
        
        <form id="form-cambiar-estado-producto" class="space-y-4">
            <input type="hidden" id="producto-estado-id" name="id" value="">
            <input type="hidden" id="producto-estado-nuevo" name="estado" value="">
            
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" 
                        onclick="cerrarModalProducto()" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" 
                        id="btn-confirmar-cambio-producto"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-check mr-2"></i>Confirmar Cambio
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Variables globales
let productosData = {};

// Función para cargar datos de productos
function cargarDatosProductos() {
    <?php foreach ($productos as $producto): 
        // Determinar la ruta de la imagen para el detalle
        $detalle_imagen_path = '../../assets/img/no-image.png';
        if (!empty($producto['imagen']) && $producto['imagen'] !== 'default.jpg') {
            $ruta_uploads = '../../uploads/products/' . $producto['imagen'];
            if (file_exists($ruta_uploads)) {
                $detalle_imagen_path = $ruta_uploads;
            }
        }
    ?>
    productosData[<?php echo $producto['id']; ?>] = {
        id: <?php echo $producto['id']; ?>,
        nombre: '<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES); ?>',
        codigo: '<?php echo htmlspecialchars($producto['codigo'], ENT_QUOTES); ?>',
        descripcion: '<?php echo htmlspecialchars($producto['descripcion'] ?? 'Sin descripción', ENT_QUOTES); ?>',
        precio_usd: <?php echo $producto['precio_$']; ?>,
        precio_bs: <?php echo $producto['precio_bs'] ?? 0; ?>,
        stock: <?php echo $producto['stock']; ?>,
        stock_minimo: <?php echo $producto['stock_minimo']; ?>,
        categoria: '<?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría', ENT_QUOTES); ?>',
        estado: '<?php echo $producto['estado']; ?>',
        imagen: '<?php echo $detalle_imagen_path; ?>',
        fecha_creacion: '<?php echo date('d/m/Y H:i', strtotime($producto['fecha_creacion'] ?? 'now')); ?>',
        unidad_medida: '<?php echo htmlspecialchars($producto['unidad_medida'] ?? 'unidad', ENT_QUOTES); ?>'
    };
    <?php endforeach; ?>
}

// Función para abrir modal de detalles
function abrirModalDetalleProducto(id) {
    // Si no tenemos los datos cargados, los cargamos
    if (Object.keys(productosData).length === 0) {
        cargarDatosProductos();
    }
    
    const producto = productosData[id];
    
    if (!producto) {
        showNotification('error', 'Producto no encontrado');
        return;
    }
    
    // Actualizar imagen
    const imagenElement = document.getElementById('detalle-imagen');
    imagenElement.src = producto.imagen || '../../assets/img/no-image.png';
    
    // Actualizar campos de texto
    document.getElementById('detalle-nombre').textContent = producto.nombre;
    document.getElementById('detalle-codigo').textContent = producto.codigo;
    document.getElementById('detalle-categoria').textContent = producto.categoria;
    document.getElementById('detalle-descripcion').textContent = producto.descripcion;
    document.getElementById('detalle-precio-usd').textContent = '$' + producto.precio_usd.toFixed(2);
    document.getElementById('detalle-precio-bs').textContent = 'Bs. ' + producto.precio_bs.toFixed(2).replace('.', ',');
    document.getElementById('detalle-stock').textContent = producto.stock;
    document.getElementById('detalle-stock-minimo').textContent = producto.stock_minimo + ' unidades';
    document.getElementById('detalle-unidad').textContent = producto.unidad_medida;
    document.getElementById('detalle-fecha').textContent = producto.fecha_creacion;
    
    // Actualizar estado badge
    const estadoElement = document.getElementById('detalle-estado');
    estadoElement.textContent = producto.estado.charAt(0).toUpperCase() + producto.estado.slice(1);
    estadoElement.className = `px-3 py-1 text-xs rounded-full ${producto.estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
    
    // Actualizar botón de editar (solo para admin)
    const btnEditar = document.getElementById('detalle-btn-editar');
    if (btnEditar) {
        btnEditar.href = `editar.php?id=${producto.id}`;
    }
    
    // Mostrar modal
    document.getElementById('modal-detalle-producto').classList.remove('hidden');
}

// Función para cerrar modal de detalles
function cerrarModalDetalle() {
    document.getElementById('modal-detalle-producto').classList.add('hidden');
}

// Función para abrir modal de cambio de estado
function abrirModalCambiarEstadoProducto(id, estadoActual, nombreProducto, stock = 0, stockMinimo = 0) {
    const nuevoEstado = estadoActual === 'activo' ? 'inactivo' : 'activo';
    
    // Guardar datos en el modal
    document.getElementById('producto-estado-id').value = id;
    document.getElementById('producto-estado-nuevo').value = nuevoEstado;
    document.getElementById('producto-nombre-modal').textContent = nombreProducto;
    
    // Configurar icono y mensajes según el cambio
    const icono = document.getElementById('producto-icono');
    const mensaje = document.getElementById('modal-producto-mensaje');
    const estadoActualBadge = document.getElementById('producto-estado-actual-badge');
    const estadoNuevoBadge = document.getElementById('producto-estado-nuevo-badge');
    
    if (nuevoEstado === 'inactivo') {
        // Activo → Inactivo
        icono.className = 'fas fa-pause-circle text-6xl text-yellow-500';
        mensaje.textContent = '¿Estás seguro de desactivar este producto?';
        estadoActualBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
        estadoActualBadge.textContent = 'Activo';
        estadoNuevoBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
        estadoNuevoBadge.textContent = 'Inactivo';
    } else {
        // Inactivo → Activo
        icono.className = 'fas fa-play-circle text-6xl text-green-500';
        mensaje.textContent = '¿Estás seguro de activar este producto?';
        estadoActualBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
        estadoActualBadge.textContent = 'Inactivo';
        estadoNuevoBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
        estadoNuevoBadge.textContent = 'Activo';
    }
    
    // Mostrar advertencia si tiene stock bajo (solo cuando se va a desactivar)
    const advertenciaDiv = document.getElementById('producto-stock-advertencia');
    if (stock <= stockMinimo && nuevoEstado === 'inactivo') {
        advertenciaDiv.classList.remove('hidden');
        document.getElementById('producto-stock-mensaje').textContent = 
            `Este producto tiene stock bajo (${stock} unidades). Al desactivarlo, no estará disponible para la venta.`;
    } else {
        advertenciaDiv.classList.add('hidden');
    }
    
    // Mostrar modal
    document.getElementById('modal-cambiar-estado-producto').classList.remove('hidden');
}

// Función para cambiar estado desde el modal
function cambiarEstadoProductoModal() {
    const id = document.getElementById('producto-estado-id').value;
    const nuevoEstado = document.getElementById('producto-estado-nuevo').value;
    
    const formData = new FormData();
    formData.append('action', 'cambiar_estado');
    formData.append('id', id);
    formData.append('estado', nuevoEstado);
    
    // Deshabilitar botón mientras se procesa
    const btnConfirmar = document.getElementById('btn-confirmar-cambio-producto');
    btnConfirmar.disabled = true;
    btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
    
    fetch('acciones.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            'action': 'cambiar_estado',
            'id': id,
            'estado': nuevoEstado
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message || 'Estado actualizado correctamente');
            cerrarModalProducto();
            
            // Actualizar los datos locales
            if (productosData[id]) {
                productosData[id].estado = nuevoEstado;
            }
            
            // Recargar la página después de un breve delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification('error', data.error || 'Error al cambiar estado');
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = '<i class="fas fa-check mr-2"></i>Confirmar Cambio';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Error de conexión');
        btnConfirmar.disabled = false;
        btnConfirmar.innerHTML = '<i class="fas fa-check mr-2"></i>Confirmar Cambio';
    });
}

// Función para cerrar modal de estado
function cerrarModalProducto() {
    document.getElementById('modal-cambiar-estado-producto').classList.add('hidden');
}

// Función auxiliar para mostrar notificaciones
function showNotification(type, message) {
    // Limpiar notificaciones anteriores
    const notificacionesAnteriores = document.querySelectorAll('.notificacion-flotante');
    notificacionesAnteriores.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notificacion-flotante fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-[60] ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Cargar datos de productos
    cargarDatosProductos();
    
    // Listener para formulario de cambio de estado
    const formCambiarEstado = document.getElementById('form-cambiar-estado-producto');
    if (formCambiarEstado) {
        formCambiarEstado.addEventListener('submit', function(e) {
            e.preventDefault();
            cambiarEstadoProductoModal();
        });
    }
    
    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalDetalle();
            cerrarModalProducto();
        }
    });
    
    // Cerrar modal al hacer clic fuera del contenido
    const modalDetalle = document.getElementById('modal-detalle-producto');
    if (modalDetalle) {
        modalDetalle.addEventListener('click', function(e) {
            if (e.target === modalDetalle) {
                cerrarModalDetalle();
            }
        });
    }
    
    const modalEstado = document.getElementById('modal-cambiar-estado-producto');
    if (modalEstado) {
        modalEstado.addEventListener('click', function(e) {
            if (e.target === modalEstado) {
                cerrarModalProducto();
            }
        });
    }
});
</script>