<?php
// modules/inventario/crear.php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once 'controllers/InventarioController.php';

// Solo admin puede crear productos
checkPermission(['admin']);

$pageTitle = "Nuevo Producto";
require_once '../../includes/header.php';

// Instanciar controlador
$controller = new InventarioController();

// Verificar si se envió el formulario
$error = null;
$success = false;
$datos_formulario = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $controller->procesarCreacion();
    
    if ($resultado['success']) {
        $success = true;
        $_SESSION['success_message'] = $resultado['message'];
    } else {
        $error = $resultado['error'];
        $datos_formulario = $resultado['data'] ?? $_POST;
    }
}

// Si fue exitoso, redirigir
if ($success) {
    header('Location: index.php');
    exit();
}

// Obtener datos para el formulario
$datos = $controller->crearAction();
$categorias = $datos['categorias'] ?? [];
$tasa_bcv = $datos['tasa_bcv'] ?? 0;
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
                    <h1 class="text-2xl font-bold">Nuevo Producto</h1>
                    <p class="text-gray-600">Agrega un nuevo producto al inventario</p>
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
                                   value="<?php echo htmlspecialchars($datos_formulario['codigo'] ?? ''); ?>" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                   placeholder="PROD001">
                        </div>
                        
                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre *
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   value="<?php echo htmlspecialchars($datos_formulario['nombre'] ?? ''); ?>" 
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
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg"><?php echo htmlspecialchars($datos_formulario['descripcion'] ?? ''); ?></textarea>
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
                                        <?php echo (($datos_formulario['categoria_id'] ?? '') == $categoria['id']) ? 'selected' : ''; ?>>
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
                                       name="precio_usd" 
                                       value="<?php echo htmlspecialchars($datos_formulario['precio_usd'] ?? ''); ?>" 
                                       step="0.01"
                                       min="0.01"
                                       required
                                       class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg"
                                       placeholder="0.00">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Precio en BS: <span id="precio-bs-calculado" class="font-semibold text-green-700">Bs. 0,00</span>
                            </p>
                        </div>
                        
                        <!-- Stock -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Stock Inicial
                                </label>
                                <input type="number" 
                                       name="stock" 
                                       value="<?php echo htmlspecialchars($datos_formulario['stock'] ?? 0); ?>" 
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Stock Mínimo
                                </label>
                                <input type="number" 
                                       name="stock_minimo" 
                                       value="<?php echo htmlspecialchars($datos_formulario['stock_minimo'] ?? 5); ?>" 
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
                                <option value="unidad" <?php echo (($datos_formulario['unidad_medida'] ?? 'unidad') == 'unidad') ? 'selected' : ''; ?>>Unidad</option>
                                <option value="kg" <?php echo (($datos_formulario['unidad_medida'] ?? '') == 'kg') ? 'selected' : ''; ?>>Kilogramo</option>
                                <option value="gr" <?php echo (($datos_formulario['unidad_medida'] ?? '') == 'gr') ? 'selected' : ''; ?>>Gramo</option>
                                <option value="lt" <?php echo (($datos_formulario['unidad_medida'] ?? '') == 'lt') ? 'selected' : ''; ?>>Litro</option>
                                <option value="ml" <?php echo (($datos_formulario['unidad_medida'] ?? '') == 'ml') ? 'selected' : ''; ?>>Mililitro</option>
                            </select>
                        </div>
                        
                        <!-- Imagen -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Imagen del Producto
                            </label>
                            <input type="file" 
                                   name="imagen" 
                                   accept="image/*"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
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
                        <i class="fas fa-save mr-2"></i>Guardar Producto
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
                    <p class="font-medium" id="precio-usd-display">$0.00</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Precio BS:</p>
                    <p class="font-medium text-green-700" id="precio-bs-display">Bs. 0,00</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Tasa BCV desde PHP
const TASA_BCV = <?php echo $tasa_bcv; ?>;

// Función para calcular precio en BS
function calcularPrecioBS(precioUSD) {
    return precioUSD * TASA_BCV;
}

// Función para formatear número
function formatoNumero(num, decimales = 2) {
    return num.toFixed(decimales).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Actualizar precio en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const precioUsdInput = document.querySelector('input[name="precio_usd"]');
    const precioBsCalculado = document.getElementById('precio-bs-calculado');
    const precioUsdDisplay = document.getElementById('precio-usd-display');
    const precioBsDisplay = document.getElementById('precio-bs-display');
    
    if (precioUsdInput) {
        precioUsdInput.addEventListener('input', function() {
            const precioUSD = parseFloat(this.value) || 0;
            const precioBS = calcularPrecioBS(precioUSD);
            
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
        });
        
        // Calcular precio inicial si hay valor
        if (precioUsdInput.value) {
            precioUsdInput.dispatchEvent(new Event('input'));
        }
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>