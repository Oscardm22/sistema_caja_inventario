<?php
require_once 'config/session.php';
require_once 'config/database.php';
checkAuth();

$pageTitle = "Dashboard";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<main class="p-6">
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Tarjetas de resumen -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700">Caja Actual</h3>
            <p class="text-3xl font-bold text-blue-600">S/ 0.00</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700">Ventas Hoy</h3>
            <p class="text-3xl font-bold text-green-600">S/ 0.00</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700">Productos</h3>
            <p class="text-3xl font-bold text-purple-600">0</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700">Clientes</h3>
            <p class="text-3xl font-bold text-yellow-600">0</p>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>