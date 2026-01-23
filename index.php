<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/functions.php';

checkAuth();

// Obtener la instancia de Database y la conexión
$database = Database::getInstance();
$conn = $database->getConnection();

$pageTitle = "Dashboard";
require_once 'includes/header.php';

// Obtener estadísticas usando función helper
$stats = getDashboardStats($conn);
$error_message = $stats['error'] ?? null;
?>

<!-- Contenedor principal con flex -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once 'includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <?php if (isset($error_message)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        <?php echo htmlspecialchars($error_message); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <div class="text-sm text-gray-500" id="live-clock">
                <?php echo date('d/m/Y H:i:s'); ?>
            </div>
        </div>
        
        <!-- Incluir componentes -->
        <?php require_once 'includes/dashboard_stats.php'; ?>
        <?php require_once 'includes/dashboard_content.php'; ?>
        <?php require_once 'includes/admin_actions.php'; ?>
        
    </main>
</div>

<script src="assets/js/dashboard.js"></script>
<?php require_once 'includes/footer.php'; ?>