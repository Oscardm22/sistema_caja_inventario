<?php
// modules/caja/index.php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once 'controllers/CajaController.php';

// Solo admin y cajero pueden acceder
checkPermission(['admin', 'cajero']);

$pageTitle = "Caja / Facturación";
require_once '../../includes/header.php';

// Instanciar controlador
$controller = new CajaController();
$data = $controller->indexAction();

// Extraer datos
$tasa_bcv = $data['tasa_bcv'] ?? 0;
$clientes_recientes = $data['clientes_recientes'] ?? [];
$productos_destacados = $data['productos_destacados'] ?? [];
$caja_abierta = $data['caja_abierta'] ?? false;
$ultima_venta = $data['ultima_venta'] ?? null;
$stats_caja = $data['stats'] ?? [];
$numero_factura = $data['numero_factura'] ?? 'FV-' . date('Ymd') . '-001';
?>

<!-- Contenedor principal -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <!-- Contenedor de notificaciones -->
        <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
        
        <!-- Encabezado de la página -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-cash-register text-blue-600"></i>
                Caja / Facturación
            </h1>
            <div class="mt-3 md:mt-0 flex items-center gap-3">
                <!-- Widget de tasa BCV (mismo estilo que en inventario) -->
                <div class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg border border-yellow-300">
                    <span class="font-semibold">Tasa BCV:</span>
                    <span class="font-bold" id="tasa-bcv-display">Bs. <?php echo number_format($tasa_bcv, 2, ',', '.'); ?></span>
                    <button onclick="actualizarTasaBCV()" class="ml-2 text-yellow-600 hover:text-yellow-800 transition-colors" id="btn-actualizar-tasa">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                
                <!-- Número de factura/venta -->
                <div class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg border border-blue-300">
                    <span class="font-semibold">Factura #:</span>
                    <span class="font-bold"><?php echo $numero_factura; ?></span>
                </div>
                
                <!-- Estado de caja -->
                <?php if ($caja_abierta): ?>
                <div class="px-4 py-2 bg-green-100 text-green-800 rounded-lg border border-green-300">
                    <i class="fas fa-check-circle mr-1"></i>
                    <span class="font-semibold">Caja abierta</span>
                </div>
                <?php else: ?>
                <a href="abrir_caja.php" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-draw-polygon mr-2"></i>Abrir caja
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Si la caja está cerrada, mostrar mensaje -->
        <?php if (!$caja_abierta): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        La caja está cerrada. Debes <a href="abrir_caja.php" class="font-medium underline text-yellow-700 hover:text-yellow-600">abrir la caja</a> antes de comenzar a facturar.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Contenido principal: Grid de 3 columnas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Columna izquierda: Búsqueda de productos y cliente (2 columnas en lg) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Tarjeta: Selección de cliente -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-user text-blue-600"></i>
                            Cliente
                        </h2>
                        <button onclick="abrirModalNuevoCliente()" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            <i class="fas fa-plus-circle"></i>
                            Nuevo cliente
                        </button>
                    </div>
                    <div class="p-6">
                        <!-- Buscador de clientes -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="buscadorCliente" 
                                   placeholder="Buscar cliente por nombre, CI o telefono..." 
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   autocomplete="off">
                        </div>
                        
                        <!-- Cliente seleccionado (inicialmente oculto) -->
                        <div id="clienteSeleccionado" class="mt-4 hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-blue-800" id="clienteNombre"></p>
                                    <p class="text-sm text-blue-600" id="clienteDetalle"></p>
                                </div>
                                <button onclick="cambiarCliente()" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-edit mr-1"></i>Cambiar
                                </button>
                            </div>
                        </div>
                        
                        <!-- Resultados de búsqueda -->
                        <div id="resultadosClientes" class="mt-2 hidden">
                            <!-- Los resultados se cargarán vía AJAX -->
                        </div>
                        
                        <!-- Clientes recientes (mockup mientras no hay búsqueda) -->
                        <?php if (!empty($clientes_recientes)): ?>
                        <div class="mt-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Clientes recientes</p>
                            <div class="space-y-2">
                                <?php foreach ($clientes_recientes as $cliente): ?>
                                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg cursor-pointer" onclick="seleccionarCliente(<?php echo $cliente['id']; ?>)">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700"><?php echo $cliente['nombre']; ?></p>
                                        <p class="text-xs text-gray-500"><?php echo $cliente['identificacion']; ?> • <?php echo $cliente['telefono']; ?></p>
                                    </div>
                                    <button class="text-blue-600 text-sm">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Tarjeta: Búsqueda y lista de productos -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-box text-blue-600"></i>
                            Productos
                        </h2>
                    </div>
                    <div class="p-6">
                        <!-- Buscador de productos -->
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-barcode text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       id="buscadorProducto" 
                                       placeholder="Buscar por nombre o código" 
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                       autofocus
                                       autocomplete="off">
                            </div>
                        </div>
                        
                        <!-- Resultados de búsqueda de productos -->
                        <div id="resultadosProductos" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                            <!-- Los resultados se cargarán vía AJAX -->
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta: Productos en la venta actual -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                            Venta actual
                            <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="contadorProductos">0</span>
                        </h2>
                        <button onclick="limpiarVenta()" class="text-sm text-red-600 hover:text-red-800 flex items-center gap-1">
                            <i class="fas fa-trash-alt"></i>
                            Limpiar todo
                        </button>
                    </div>
                    <div class="p-6">
                        <!-- Tabla de productos -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cant.</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio $</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Bs.</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total $</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Bs.</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaProductosVenta" class="bg-white divide-y divide-gray-200">
                                    <!-- Se llenará dinámicamente con JavaScript -->
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                            <i class="fas fa-box-open text-4xl mb-2 text-gray-300"></i>
                                            <p>No hay productos agregados a la venta</p>
                                            <p class="text-sm">Busca y selecciona productos para comenzar</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Columna derecha: Resumen y pago (1 columna en lg) -->
            <div class="space-y-6">
                <!-- Tarjeta: Resumen de venta -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-4">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-receipt text-blue-600"></i>
                            Resumen de venta
                        </h2>
                    </div>
                    <div class="p-6">
                        <!-- Líneas de resumen -->
                        <div class="space-y-3">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal ($):</span>
                                <span class="font-medium" id="subtotalUsd">$0,00</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal (Bs.):</span>
                                <span class="font-medium" id="subtotalVes">Bs. 0,00</span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-3 mt-3">
                                <div class="flex justify-between text-lg font-bold text-gray-800">
                                    <span>TOTAL:</span>
                                    <span id="totalUsd">$0,00</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold text-blue-600">
                                    <span>TOTAL Bs.:</span>
                                    <span id="totalVes">Bs. 0,00</span>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 p-3 rounded-lg mt-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Tasa BCV:</span>
                                    <span class="font-mono font-medium">Bs. <?php echo number_format($tasa_bcv, 2, ',', '.'); ?> / $</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Artículos:</span>
                                    <span class="font-mono font-medium" id="totalArticulos">0</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botón de procesar pago -->
                        <button onclick="abrirModalPago()" 
                                class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-4 rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                id="btnProcesarPago"
                                <?php echo !$caja_abierta ? 'disabled' : ''; ?>>
                            <i class="fas fa-credit-card"></i>
                            Procesar pago
                        </button>
                        
                        <?php if (!$caja_abierta): ?>
                        <p class="text-xs text-center text-red-500 mt-2">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Debes abrir la caja para facturar
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal para nuevo cliente -->
<div id="modalNuevoCliente" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50" onclick="cerrarModalSiFondo(event)">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Registrar nuevo cliente</h3>
            <button onclick="cerrarModalNuevoCliente()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="formNuevoCliente" onsubmit="guardarNuevoCliente(event)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre/Razón social *</label>
                    <input type="text" name="nombre" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de persona</label>
                    <select name="tipo_persona" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="natural">Natural</option>
                        <option value="juridica">Jurídica</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RIF/Cédula *</label>
                    <input type="text" name="identificacion" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="tel" name="telefono" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <textarea name="direccion" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="2"></textarea>
                </div>
            </div>
            
            <div class="flex justify-end gap-2">
                <button type="button" onclick="cerrarModalNuevoCliente()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Guardar cliente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para pago -->
<div id="modalPago" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50" onclick="cerrarModalSiFondo(event)">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Procesar pago</h3>
            <button onclick="cerrarModalPago()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Resumen rápido -->
        <div class="bg-gray-50 p-3 rounded-lg mb-4">
            <div class="flex justify-between text-sm">
                <span>Total a pagar:</span>
                <span class="font-bold" id="modalTotalUsd">$0,00</span>
            </div>
            <div class="flex justify-between text-sm text-blue-600">
                <span>Total Bs.:</span>
                <span class="font-bold" id="modalTotalVes">Bs. 0,00</span>
            </div>
        </div>
        
        <form id="formPago" onsubmit="procesarPago(event)">
            <!-- Opciones de pago -->
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monto recibido (Bs.)</label>
                    <input type="text" id="montoRecibidoBs" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0,00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monto recibido ($)</label>
                    <input type="text" id="montoRecibidoUsd" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0,00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Método de pago</label>
                    <select id="metodoPago" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="pago_movil">Pago móvil</option>
                        <option value="debito">Tarjeta de débito</option>
                        <option value="credito">Tarjeta de crédito</option>
                        <option value="mixto">Mixto</option>
                    </select>
                </div>
                <div id="cambioContainer" class="bg-yellow-50 p-3 rounded-lg text-sm text-yellow-800 hidden">
                    <i class="fas fa-info-circle mr-1"></i>
                    Cambio: <span id="cambioTexto">Bs. 0,00</span>
                </div>
            </div>
            
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="cerrarModalPago()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Confirmar pago
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Incluir JavaScript específico -->
<link rel="stylesheet" href="assets/css/caja.css">
<script src="assets/js/caja.js"></script>
<script src="assets/js/tasa_bcv.js"></script>
<script src="assets/js/notificaciones.js"></script>

<?php require_once '../../includes/footer.php'; ?>