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
$filtros = $data['filtros'] ?? [];
$paginacion = $data['paginacion'] ?? [];
$tasa_bcv = $data['tasa_bcv'] ?? 0;

// Variables para filtros (para mantenerlas en el formulario)
$search = $filtros['search'] ?? '';
$categoria_filter = $filtros['categoria'] ?? '';
$estado_filter = $filtros['estado'] ?? 'activo';
$pagina_actual = $paginacion['pagina_actual'] ?? 1;
$total_paginas = $paginacion['total_paginas'] ?? 1;
$total_productos = $paginacion['total_productos'] ?? 0;
?>

<!-- Contenedor principal -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <!-- Contenedor de notificaciones -->
        <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
        
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Gestión de Inventario</h1>
            <div class="flex space-x-4">
                <!-- Widget de tasa BCV simplificado -->
                <div class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg border border-yellow-300">
                    <span class="font-semibold">Tasa BCV:</span>
                    <span class="font-bold" id="tasa-bcv-display">Bs. <?php echo number_format($tasa_bcv, 2, ',', '.'); ?></span>
                    <button onclick="actualizarTasaBCV()" class="ml-2 text-yellow-600 hover:text-yellow-800 transition-colors" id="btn-actualizar-tasa">
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
        
        // Componente de paginación
        if (!empty($productos) && file_exists($includes_dir . 'paginacion.php')) {
            require_once $includes_dir . 'paginacion.php';
        } else if (!empty($productos)) {
            // Paginación simple por defecto si no existe el componente
            ?>
            <div class="bg-white px-6 py-4 border-t border-gray-200 rounded-b-lg">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-700">
                        Mostrando <span class="font-medium"><?php echo $paginacion['inicio'] ?? 1; ?></span>
                        a <span class="font-medium"><?php echo $paginacion['fin'] ?? count($productos); ?></span>
                        de <span class="font-medium"><?php echo $total_productos; ?></span>
                        productos
                    </div>
                    
                    <?php if ($total_paginas > 1): ?>
                    <div class="flex space-x-2">
                        <!-- Botón Anterior -->
                        <?php if ($pagina_actual > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])); ?>" 
                           class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 transition-colors">
                            <i class="fas fa-chevron-left mr-1"></i> Anterior
                        </a>
                        <?php endif; ?>
                        
                        <!-- Números de página (simplificado) -->
                        <div class="flex space-x-1">
                            <?php for ($i = 1; $i <= $total_paginas; $i++): 
                                $clase = ($i == $pagina_actual) 
                                    ? 'px-3 py-1 border border-blue-300 rounded text-sm bg-blue-50 text-blue-600 font-medium' 
                                    : 'px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 transition-colors';
                            ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>" 
                               class="<?php echo $clase; ?>">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>
                        </div>
                        
                        <!-- Botón Siguiente -->
                        <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])); ?>" 
                           class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 transition-colors">
                            Siguiente <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        ?>
        
    </main>
</div>

<!-- Incluir JavaScript -->
<link rel="stylesheet" href="assets/css/inventario.css">
<script src="assets/js/tasa_bcv.js"></script>
<script src="assets/js/productos.js"></script>

<!-- Función de notificaciones mejorada -->
<script>
// Sistema de notificaciones mejorado
function mostrarNotificacion(tipo, mensaje, duracion = 5000) {
    // Crear contenedor si no existe
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
    }
    
    // Configurar colores según tipo
    const config = {
        success: {
            bg: 'bg-green-500',
            icon: 'fa-check-circle',
            title: 'Éxito'
        },
        error: {
            bg: 'bg-red-500',
            icon: 'fa-exclamation-circle',
            title: 'Error'
        },
        warning: {
            bg: 'bg-yellow-500',
            icon: 'fa-exclamation-triangle',
            title: 'Advertencia'
        },
        info: {
            bg: 'bg-blue-500',
            icon: 'fa-info-circle',
            title: 'Información'
        }
    };
    
    const conf = config[tipo] || config.info;
    
    // Crear notificación
    const notification = document.createElement('div');
    notification.className = `${conf.bg} text-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 translate-x-0 opacity-100`;
    notification.style.minWidth = '300px';
    notification.style.maxWidth = '400px';
    
    notification.innerHTML = `
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas ${conf.icon} text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-semibold">${conf.title}</p>
                    <p class="text-sm opacity-90 mt-1">${mensaje}</p>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="ml-4 text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Barra de progreso -->
            <div class="absolute bottom-0 left-0 h-1 bg-white bg-opacity-50 progress-bar" style="width: 100%; transition: width ${duracion}ms linear;"></div>
        </div>
    `;
    
    // Agregar al contenedor
    container.appendChild(notification);
    
    // Animar barra de progreso
    setTimeout(() => {
        const progressBar = notification.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = '0%';
        }
    }, 10);
    
    // Auto-eliminar después de la duración
    setTimeout(() => {
        if (notification && notification.parentNode) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, duracion);
}

function actualizarTasaBCV() {
    const btn = document.getElementById('btn-actualizar-tasa');
    const icon = btn.querySelector('i');
    
    // Deshabilitar botón y mostrar animación
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    icon.classList.add('fa-spin');
    
    fetch('api_tasa.php?action=actualizar')
        .then(response => response.json())
        .then(data => {
            // Re-habilitar botón
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            icon.classList.remove('fa-spin');
            
            if (data.success) {
                // Actualizar el display de tasa
                const tasaDisplay = document.getElementById('tasa-bcv-display');
                if (tasaDisplay) {
                    tasaDisplay.textContent = 'Bs. ' + data.tasa_formatted;
                }
                
                // Mostrar notificación de éxito
                mostrarNotificacion('success', ' Tasa BCV actualizada correctamente a Bs. ' + data.tasa_formatted);
            } else {
                // Mostrar notificación de error
                mostrarNotificacion('error', ' ' + (data.message || 'Error al actualizar la tasa BCV'));
            }
        })
        .catch(error => {
            // Re-habilitar botón
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            icon.classList.remove('fa-spin');
            
            // Mostrar notificación de error
            mostrarNotificacion('error', '❌ Error de conexión al actualizar la tasa');
            console.error('Error:', error);
        });
}

// Función para mantener los filtros al cambiar de página
document.addEventListener('DOMContentLoaded', function() {
    // Si hay un formulario de filtros, asegurar que resetee a página 1
    const filterForm = document.querySelector('form[method="GET"]');
    if (filterForm) {
        filterForm.addEventListener('submit', function() {
            // Crear o actualizar un campo hidden para página=1
            let paginaInput = document.querySelector('input[name="pagina"]');
            if (!paginaInput) {
                paginaInput = document.createElement('input');
                paginaInput.type = 'hidden';
                paginaInput.name = 'pagina';
                filterForm.appendChild(paginaInput);
            }
            paginaInput.value = '1';
        });
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>