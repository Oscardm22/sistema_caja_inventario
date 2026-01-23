<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once 'controllers/usuarios_controller.php';

// Solo admin puede acceder
checkPermission(['admin']);

$pageTitle = "Crear Nuevo Usuario";
require_once '../../includes/header.php';

// Instanciar controlador
$controller = new UsuariosController();
$result = $controller->crearAction();

// Extraer datos
$errors = $result['errors'];
$security_questions = $result['security_questions'];
$formData = $result['formData'];
?>

<!-- Contenedor principal -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Crear Nuevo Usuario</h1>
            <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Volver al Listado
            </a>
        </div>
        
        <!-- Mensajes de error general -->
        <?php if (!empty($errors['general'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?php echo htmlspecialchars($errors['general']); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Incluir formulario de creación -->
        <?php require_once 'includes/crear_form.php'; ?>
        
    </main>
</div>

<!-- Incluir JavaScript -->
<script src="assets/js/crear_usuario.js"></script>

<?php require_once '../../includes/footer.php'; ?>