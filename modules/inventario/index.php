<?php
// modules/inventario/index.php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once 'controllers/InventarioController.php';

// Solo admin y cajero pueden acceder
checkPermission(['admin', 'cajero']);

$pageTitle = "Gestión de Inventario";
require_once '../../includes/header.php';

// Instanciar controlador
$controller = new InventarioController();
$data = $controller->indexAction();

// Extraer datos
$productos = $data['productos'] ?? [];
$stats = $data['estadisticas'] ?? [];
$categorias = $data['categorias'] ?? [];
$search = $data['filtros']['search'] ?? '';
$categoria_filter = $data['filtros']['categoria'] ?? '';
$estado_filter = $data['filtros']['estado'] ?? 'activo';
$tasa_bcv = $data['tasa_bcv'] ?? 0;
?>

<!-- Contenedor principal -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Gestión de Inventario</h1>
            <div class="flex space-x-4">
                <!-- Widget de tasa BCV simplificado -->
                <div class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg border border-yellow-300">
                    <span class="font-semibold">Tasa BCV:</span>
                    <span class="font-bold">Bs. <?php echo number_format($tasa_bcv, 2, ',', '.'); ?></span>
                    <button onclick="actualizarTasaBCV()" class="ml-2 text-yellow-600 hover:text-yellow-800">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <a href="categorias.php" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-tags mr-2"></i>Categorías
                </a>
                <a href="crear.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Nuevo Producto
                </a>
            </div>
        </div>

        <!-- Componentes incluidos -->
        <?php 
        // Incluir componentes si existen
        $includes_dir = __DIR__ . '/includes/';
        
        if (file_exists($includes_dir . 'cards_estadisticas.php')) {
            require_once $includes_dir . 'cards_estadisticas.php';
        }
        
        if (file_exists($includes_dir . 'filtros_productos.php')) {
            require_once $includes_dir . 'filtros_productos.php';
        }
        
        if (file_exists($includes_dir . 'tabla_productos.php')) {
            require_once $includes_dir . 'tabla_productos.php';
        } else {
            echo '<div class="bg-white rounded-lg shadow p-6">';
            echo '<p class="text-gray-500">No hay productos para mostrar.</p>';
            echo '</div>';
        }
        ?>
        
    </main>
</div>

<!-- Incluir JavaScript -->
<link rel="stylesheet" href="assets/css/inventario.css">
<script src="assets/js/tasa_bcv.js"></script>
<script src="assets/js/productos.js"></script>
<script>
function actualizarTasaBCV() {
    const btn = event.target;
    const icon = btn.querySelector('i') || btn;
    icon.classList.add('updating');
    
    fetch('api_tasa.php?action=actualizar')
        .then(response => response.json())
        .then(data => {
            icon.classList.remove('updating');
            
            if (data.success) {
                // Actualizar el display de tasa
                const tasaDisplay = document.querySelector('.font-bold span') || 
                                   document.getElementById('tasa-bcv-display');
                if (tasaDisplay) {
                    tasaDisplay.textContent = data.tasa_formatted;
                }
                
                // Mostrar notificación
                if (typeof mostrarNotificacion === 'function') {
                    mostrarNotificacion('success', 'Tasa BCV actualizada correctamente');
                } else {
                    alert('Tasa actualizada: Bs. ' + data.tasa_formatted);
                }
            }
        })
        .catch(error => {
            icon.classList.remove('updating');
            console.error('Error:', error);
        });
}
</script>

<?php require_once '../../includes/footer.php'; ?>