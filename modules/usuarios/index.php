<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once 'controllers/usuarios_controller.php';

// Solo admin puede acceder
checkPermission(['admin']);

$pageTitle = "Gestión de Usuarios";
require_once '../../includes/header.php';

// Instanciar controlador
$controller = new UsuariosController();
$data = $controller->indexAction();

// Extraer datos
$usuarios = $data['usuarios'];
$stats = $data['estadisticas'];
$search = $data['filtros']['search'];
$role_filter = $data['filtros']['role'];
$status_filter = $data['filtros']['status'];
?>

<!-- Contenedor principal -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Gestión de Usuarios</h1>
            <a href="crear.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-user-plus mr-2"></i>Nuevo Usuario
            </a>
        </div>

        <!-- Componentes incluidos -->
        <?php require_once __DIR__ . '/includes/stats_cards.php'; ?>
        <?php require_once __DIR__ . '/includes/filtros_form.php'; ?>
        <?php require_once __DIR__ . '/includes/usuarios_table.php'; ?>
        
    </main>
</div>

<!-- Incluir JavaScript con rutas relativas -->
<link rel="stylesheet" href="assets/css/usuarios.css">
<script src="assets/js/notifications.js"></script>
<script src="assets/js/usuarios_actions.js"></script>
<script src="assets/js/main.js"></script>

<?php require_once '../../includes/footer.php'; ?>