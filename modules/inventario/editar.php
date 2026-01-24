<?php
// modules/inventario/editar.php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once 'controllers/InventarioController.php';

// Solo admin puede editar productos
checkPermission(['admin']);

$pageTitle = "Editar Producto";
require_once '../../includes/header.php';

// Obtener ID del producto
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit();
}

// Instanciar controlador
$controller = new InventarioController();

// Verificar si se envió el formulario
$error = null;
$success = false;
$datos_formulario = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar edición
    $resultado = $controller->procesarEdicion();
    
    if ($resultado['success']) {
        $success = true;
        $_SESSION['success_message'] = $resultado['message'];
        header('Location: index.php');
        exit();
    } else {
        $error = $resultado['error'];
        $datos_formulario = $resultado['data'] ?? $_POST;
    }
}

// Obtener datos del producto y categorías
$datos = $controller->editarAction($id);

if (!$datos || !isset($datos['producto'])) {
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">';
    echo '<p>Producto no encontrado.</p>';
    echo '</div>';
    echo '<a href="index.php" class="mt-4 inline-block text-blue-600 hover:text-blue-800">← Volver al inventario</a>';
    echo '</div>';
    require_once '../../includes/footer.php';
    exit();
}

$producto = $datos['producto'];
$categorias = $datos['categorias'] ?? [];
$tasa_bcv = $datos['tasa_bcv'] ?? 0;

// Si hay datos del formulario (después de error), usar esos, si no usar el producto
$producto = !empty($datos_formulario) ? array_merge($producto, $datos_formulario) : $producto;
?>

<!-- Contenedor principal -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <!-- Encabezado -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Editar Producto</h1>
                    <p class="text-gray-600">Modifica la información del producto</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg border border-yellow-300">
                        <span class="font-semibold">Tasa BCV:</span>
                        <span class="font-bold">Bs. <?php echo number_format($tasa_bcv, 2, ',', '.'); ?></span>
                    </div>
                    <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Mensaje de error -->
        <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Columna izquierda -->
                    <div class="space-y-4">
                        <!-- Código -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Código *
                            </label>
                            <input type="text" 
                                   name="codigo" 
                                   id="codigo"
                                   value="<?php echo htmlspecialchars($producto['codigo']); ?>" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                   placeholder="PROD001"
                                   onblur="validarCodigo(<?php echo $producto['id']; ?>)">
                            <div id="codigo-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>
                        
                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre *
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   value="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                   placeholder="Nombre del producto">
                        </div>
                        
                        <!-- Descripción -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea name="descripcion" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                        </div>
                        
                        <!-- Categoría -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Categoría
                            </label>
                            <select name="categoria_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">Sin categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id']; ?>" 
                                        <?php echo (($producto['categoria_id'] ?? '') == $categoria['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Columna derecha -->
                    <div class="space-y-4">
                        <!-- Precio USD -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Precio en USD *
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">$</span>
                                </div>
                                <input type="number" 
                                       name="precio_$" 
                                       id="precio_usd"
                                       value="<?php echo number_format($producto['precio_$'], 2, '.', ''); ?>" 
                                       step="0.01"
                                       min="0.01"
                                       required
                                       class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg"
                                       placeholder="0.00"
                                       oninput="calcularPrecioBS()">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Precio en BS: <span id="precio-bs-calculado" class="font-semibold text-green-700">
                                    Bs. <?php echo number_format($producto['precio_$'] * $tasa_bcv, 2, ',', '.'); ?>
                                </span>
                            </p>
                        </div>
                        
                        <!-- Stock -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Stock Actual
                                </label>
                                <input type="number" 
                                       name="stock" 
                                       value="<?php echo htmlspecialchars($producto['stock']); ?>" 
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Stock Mínimo
                                </label>
                                <input type="number" 
                                       name="stock_minimo" 
                                       value="<?php echo htmlspecialchars($producto['stock_minimo']); ?>" 
                                       min="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                        
                        <!-- Unidad de medida -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Unidad de Medida
                            </label>
                            <select name="unidad_medida" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="unidad" <?php echo ($producto['unidad_medida'] == 'unidad') ? 'selected' : ''; ?>>Unidad</option>
                                <option value="kg" <?php echo ($producto['unidad_medida'] == 'kg') ? 'selected' : ''; ?>>Kilogramo</option>
                                <option value="gr" <?php echo ($producto['unidad_medida'] == 'gr') ? 'selected' : ''; ?>>Gramo</option>
                                <option value="lt" <?php echo ($producto['unidad_medida'] == 'lt') ? 'selected' : ''; ?>>Litro</option>
                                <option value="ml" <?php echo ($producto['unidad_medida'] == 'ml') ? 'selected' : ''; ?>>Mililitro</option>
                                <option value="par" <?php echo ($producto['unidad_medida'] == 'par') ? 'selected' : ''; ?>>Par</option>
                                <option value="docena" <?php echo ($producto['unidad_medida'] == 'docena') ? 'selected' : ''; ?>>Docena</option>
                                <option value="metro" <?php echo ($producto['unidad_medida'] == 'metro') ? 'selected' : ''; ?>>Metro</option>
                                <option value="caja" <?php echo ($producto['unidad_medida'] == 'caja') ? 'selected' : ''; ?>>Caja</option>
                                <option value="paquete" <?php echo ($producto['unidad_medida'] == 'paquete') ? 'selected' : ''; ?>>Paquete</option>
                            </select>
                        </div>
                        
                        <!-- Imagen -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Imagen del Producto
                            </label>
                            <div class="flex items-center space-x-4">
                                <div class="h-24 w-24 bg-gray-200 rounded-lg flex items-center justify-center overflow-hidden">
                                    <?php if (!empty($producto['imagen']) && $producto['imagen'] != 'default.jpg'): ?>
                                    <img src="/uploads/products/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                         class="h-full w-full object-cover">
                                    <?php else: ?>
                                    <i class="fas fa-box text-gray-400 text-2xl"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <input type="file" 
                                           name="imagen" 
                                           accept="image/*"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="text-xs text-gray-500 mt-1">Deja vacío para mantener la imagen actual</p>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Estado
                            </label>
                            <select name="estado" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="activo" <?php echo ($producto['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                                <option value="inactivo" <?php echo ($producto['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Botones -->
                <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-4">
                    <a href="index.php" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Calculadora simple -->
        <div class="mt-6 bg-gray-50 rounded-lg p-4">
            <h3 class="font-semibold mb-3">Calculadora de Precios</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Tasa BCV:</p>
                    <p class="font-medium">Bs. <?php echo number_format($tasa_bcv, 2, ',', '.'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Precio USD:</p>
                    <p class="font-medium" id="precio-usd-display">$<?php echo number_format($producto['precio_$'], 2); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Precio BS:</p>
                    <p class="font-medium text-green-700" id="precio-bs-display">
                        Bs. <?php echo number_format($producto['precio_$'] * $tasa_bcv, 2, ',', '.'); ?>
                    </p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Tasa BCV desde PHP
const TASA_BCV = <?php echo $tasa_bcv; ?>;

// Función para calcular precio en BS
function calcularPrecioBS() {
    const precioUsdInput = document.getElementById('precio_usd');
    const precioBsCalculado = document.getElementById('precio-bs-calculado');
    const precioUsdDisplay = document.getElementById('precio-usd-display');
    const precioBsDisplay = document.getElementById('precio-bs-display');
    
    if (!precioUsdInput) return;
    
    const precioUSD = parseFloat(precioUsdInput.value) || 0;
    const precioBS = precioUSD * TASA_BCV;
    
    // Formatear para mostrar
    const precioBSFormateado = precioBS.toLocaleString('es-VE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    // Actualizar displays
    if (precioBsCalculado) {
        precioBsCalculado.textContent = 'Bs. ' + precioBSFormateado;
    }
    if (precioUsdDisplay) {
        precioUsdDisplay.textContent = '$' + precioUSD.toFixed(2);
    }
    if (precioBsDisplay) {
        precioBsDisplay.textContent = 'Bs. ' + precioBSFormateado;
    }
}

// Función para validar código único
function validarCodigo(excluirId) {
    const codigoInput = document.getElementById('codigo');
    const codigo = codigoInput.value.trim();
    const errorDiv = document.getElementById('codigo-error');
    
    if (codigo.length < 2) {
        return;
    }
    
    fetch(`acciones.php?action=validar_codigo&codigo=${encodeURIComponent(codigo)}&excluir_id=${excluirId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.disponible) {
                errorDiv.textContent = 'Este código ya está en uso por otro producto';
                errorDiv.classList.remove('hidden');
                codigoInput.classList.add('border-red-500');
            } else {
                errorDiv.classList.add('hidden');
                codigoInput.classList.remove('border-red-500');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Calcular precio inicial al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    calcularPrecioBS();
});
</script>

<?php require_once '../../includes/footer.php'; ?>