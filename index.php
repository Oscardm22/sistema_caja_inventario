<?php
require_once 'config/session.php';
require_once 'config/database.php';
checkAuth();

$pageTitle = "Dashboard";
require_once 'includes/header.php';
?>

<!-- Contenedor principal con flex -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once 'includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Tarjeta de Caja Actual -->
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <i class="fas fa-cash-register text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700">Caja Actual</h3>
                </div>
                <p class="text-3xl font-bold text-blue-600">S/ 0.00</p>
                <p class="text-sm text-gray-500 mt-2">Estado: <span class="text-green-600 font-medium">Abierta</span></p>
            </div>
            
            <!-- Tarjeta de Ventas Hoy -->
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <i class="fas fa-shopping-cart text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700">Ventas Hoy</h3>
                </div>
                <p class="text-3xl font-bold text-green-600">S/ 0.00</p>
                <p class="text-sm text-gray-500 mt-2">0 ventas realizadas</p>
            </div>
            
            <!-- Tarjeta de Productos -->
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <i class="fas fa-boxes text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700">Productos</h3>
                </div>
                <p class="text-3xl font-bold text-purple-600">0</p>
                <p class="text-sm text-gray-500 mt-2">En inventario</p>
            </div>
            
            <!-- Tarjeta de Clientes -->
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700">Clientes</h3>
                </div>
                <p class="text-3xl font-bold text-yellow-600">0</p>
                <p class="text-sm text-gray-500 mt-2">Clientes registrados</p>
            </div>
        </div>
        
        <!-- Sección adicional si quieres agregar más contenido -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Ventas Recientes</h3>
                <p class="text-gray-500 text-center py-4">No hay ventas recientes</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Productos con Stock Bajo</h3>
                <p class="text-gray-500 text-center py-4">No hay productos con stock bajo</p>
            </div>
        </div>
    </main>
</div>

<!-- JavaScript al final del body -->
<script>
// Mantén aquí el JavaScript que estaba en header.php o crea uno nuevo
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard cargado');
    
    // Deshabilitar cache
    if (window.performance && window.performance.navigation.type === 2) {
        window.location.reload();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>