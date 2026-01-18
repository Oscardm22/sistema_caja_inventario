<?php
// auth/login.php - MODIFICADO PARA FLUJO DE DOS PASOS

session_start();
require_once '../config/database.php';

// Incluir el procesamiento de login
require_once 'login_processing.php';

// Determinar en qué paso estamos
$recovery_step = isset($_GET['recovery_step']) ? (int)$_GET['recovery_step'] : 0;
$username = $_GET['username'] ?? '';
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

<!-- Formulario según paso -->
<?php if ($recovery_step == 1): ?>
    <?php include 'recovery_form_step1.php'; ?>
<?php elseif ($recovery_step == 2): ?>
    <?php include 'recovery_form_step2.php'; ?>
<?php else: ?>
    <?php include 'login_form.php'; ?>
<?php endif; ?>

</div> <!-- Cierre del div login-card -->
</div> <!-- Cierre del div container -->

<!-- Incluir JavaScript -->
<script src="../assets/js/login.js"></script>
</body>
</html>