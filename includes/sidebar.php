<aside class="w-64 bg-gray-800 text-white min-h-screen">
    <div class="p-4">
        <h2 class="text-xl font-bold">Sistema Caja</h2>
        <p class="text-sm text-gray-400">Bienvenido, <?php echo $_SESSION['user_name'] ?? 'Usuario'; ?></p>
    </div>
    
    <nav class="mt-6">
        <a href="index.php" class="block py-2 px-4 hover:bg-gray-700">
            <i class="fas fa-home mr-2"></i> Dashboard
        </a>
        
        <a href="../modules/caja/index.php" class="block py-2 px-4 hover:bg-gray-700">
            <i class="fas fa-cash-register mr-2"></i> Caja
        </a>
        
        <a href="../modules/inventario/index.php" class="block py-2 px-4 hover:bg-gray-700">
            <i class="fas fa-boxes mr-2"></i> Inventario
        </a>
        
        <a href="../modules/ventas/index.php" class="block py-2 px-4 hover:bg-gray-700">
            <i class="fas fa-shopping-cart mr-2"></i> Ventas
        </a>
        
        <a href="../modules/clientes/index.php" class="block py-2 px-4 hover:bg-gray-700">
            <i class="fas fa-users mr-2"></i> Clientes
        </a>
        
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="../modules/usuarios/index.php" class="block py-2 px-4 hover:bg-gray-700">
            <i class="fas fa-user-cog mr-2"></i> Usuarios
        </a>
        
        <a href="../modules/reportes/ventas.php" class="block py-2 px-4 hover:bg-gray-700">
            <i class="fas fa-chart-bar mr-2"></i> Reportes
        </a>
        <?php endif; ?>
        
        <a href="../logout.php" class="block py-2 px-4 hover:bg-gray-700 mt-6">
            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
        </a>
    </nav>
</aside>