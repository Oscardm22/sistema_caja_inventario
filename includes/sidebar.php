<?php
// sidebar.php
// Determinar la ruta base automáticamente
$current_path = $_SERVER['PHP_SELF'];
$base_path = '';

// Si estamos dentro de modules/, necesitamos retroceder 2 niveles
if (strpos($current_path, '/modules/') !== false) {
    $base_path = '../../';
} elseif (strpos($current_path, '/auth/') !== false) {
    // Si estamos en auth/
    $base_path = '../';
}
// Si estamos en la raíz, $base_path queda vacío

// Determinar qué menú está activo de forma más precisa
$current_script = basename($_SERVER['PHP_SELF']);
$full_path = $_SERVER['PHP_SELF'];

// Variables para cada menú
$dashboard_active = false;
$caja_active = false;
$inventario_active = false;
$ventas_active = false;
$clientes_active = false;
$usuarios_active = false;
$reportes_active = false;

// Verificar rutas de forma más específica
if ($current_script == 'index.php' && strpos($full_path, 'modules/') === false) {
    // Solo activar dashboard si estamos en el index.php de la raíz
    $dashboard_active = true;
} elseif (strpos($full_path, 'modules/caja/') !== false) {
    $caja_active = true;
} elseif (strpos($full_path, 'modules/inventario/') !== false) {
    $inventario_active = true;
} elseif (strpos($full_path, 'modules/ventas/') !== false) {
    $ventas_active = true;
} elseif (strpos($full_path, 'modules/clientes/') !== false) {
    $clientes_active = true;
} elseif (strpos($full_path, 'modules/usuarios/') !== false) {
    $usuarios_active = true;
} elseif (strpos($full_path, 'modules/reportes/') !== false) {
    $reportes_active = true;
}
?>

<aside class="w-64 bg-gray-800 text-white min-h-screen fixed left-0 top-0">
    <div class="p-4">
        <h2 class="text-xl font-bold">Sistema Caja</h2>
        <p class="text-sm text-gray-400">Bienvenido, <?php echo $_SESSION['user_name'] ?? 'Usuario'; ?></p>
    </div>
    
    <nav class="mt-6">
        <a href="<?php echo $base_path; ?>index.php" 
           class="block py-2 px-4 hover:bg-gray-700 <?php echo $dashboard_active ? 'bg-gray-700' : ''; ?>">
            <i class="fas fa-home mr-2"></i> Dashboard
        </a>
        
        <a href="<?php echo $base_path; ?>modules/caja/index.php" 
           class="block py-2 px-4 hover:bg-gray-700 <?php echo $caja_active ? 'bg-gray-700' : ''; ?>">
            <i class="fas fa-cash-register mr-2"></i> Caja
        </a>
        
        <a href="<?php echo $base_path; ?>modules/inventario/index.php" 
           class="block py-2 px-4 hover:bg-gray-700 <?php echo $inventario_active ? 'bg-gray-700' : ''; ?>">
            <i class="fas fa-boxes mr-2"></i> Inventario
        </a>
        
        <a href="<?php echo $base_path; ?>modules/ventas/index.php" 
           class="block py-2 px-4 hover:bg-gray-700 <?php echo $ventas_active ? 'bg-gray-700' : ''; ?>">
            <i class="fas fa-shopping-cart mr-2"></i> Ventas
        </a>
        
        <a href="<?php echo $base_path; ?>modules/clientes/index.php" 
           class="block py-2 px-4 hover:bg-gray-700 <?php echo $clientes_active ? 'bg-gray-700' : ''; ?>">
            <i class="fas fa-users mr-2"></i> Clientes
        </a>
        
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="<?php echo $base_path; ?>modules/usuarios/index.php" 
           class="block py-2 px-4 hover:bg-gray-700 <?php echo $usuarios_active ? 'bg-gray-700' : ''; ?>">
            <i class="fas fa-user-cog mr-2"></i> Usuarios
        </a>
        
        <a href="<?php echo $base_path; ?>modules/reportes/ventas.php" 
           class="block py-2 px-4 hover:bg-gray-700 <?php echo $reportes_active ? 'bg-gray-700' : ''; ?>">
            <i class="fas fa-chart-bar mr-2"></i> Reportes
        </a>
        <?php endif; ?>
        
        <a href="<?php echo $base_path; ?>logout.php" class="block py-2 px-4 hover:bg-gray-700 mt-6">
            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
        </a>
    </nav>
</aside>