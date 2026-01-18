<?php
// auth/login.php - ARCHIVO COMPLETO
session_start();
require_once '../config/database.php';

// Incluir el procesamiento de login
require_once 'login_processing.php';

// Determinar si estamos en modo recuperación
$recovery_mode = isset($_GET['recovery']) && $_GET['recovery'] === 'admin';
$success = $_GET['success'] ?? '';

// Incluir el header
include 'login_header.php';
?>

<!-- Mensajes de éxito/error -->
<?php if ($success == 'admin_recovery'): ?>
    <div class="success-message px-4 py-3 rounded-lg flex items-center mb-6">
        <i class="fas fa-check-circle mr-3"></i>
        <span class="flex-1 text-sm">Contraseña de administrador actualizada exitosamente. Ya puedes iniciar sesión.</span>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="error-message px-4 py-3 rounded-lg flex items-center mb-6">
        <i class="fas fa-exclamation-circle mr-3"></i>
        <span class="flex-1 text-sm"><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<!-- Formulario según modo -->
<?php if ($recovery_mode): ?>
    <?php include 'recovery_form.php'; ?>
<?php else: ?>
    <?php include 'login_form.php'; ?>
<?php endif; ?>

</div> <!-- Cierre del div login-card -->
</div> <!-- Cierre del div container -->

<?php include 'login_scripts.php'; ?>
</body>
</html>