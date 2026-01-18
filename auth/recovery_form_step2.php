<?php
// auth/recovery_form_step2.php - MODIFICADO: Texto de preguntas en blanco

// Verificar que tengamos un username válido
if (empty($_GET['username'])) {
    header('Location: login.php?recovery_step=1');
    exit();
}

$username = $_GET['username'];
$db = getDB();

// Obtener preguntas del usuario
$stmt = $db->prepare("SELECT pregunta_seguridad_1, pregunta_seguridad_2 FROM usuarios WHERE username = ? AND rol = 'admin' AND estado = 'activo'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $error = "Usuario no encontrado o no tiene permisos de administrador.";
    header('Location: login.php?recovery_step=1&error=' . urlencode($error));
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Verificar si el usuario tiene preguntas configuradas
if (empty($user['pregunta_seguridad_1']) || empty($user['pregunta_seguridad_2'])) {
    $error = "Este usuario no tiene configuradas preguntas de seguridad.";
    header('Location: login.php?recovery_step=1&error=' . urlencode($error));
    exit();
}
?>

<form method="POST" action="" class="space-y-6" autocomplete="on">
    <input type="hidden" name="recovery_mode" value="step2">
    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
    
    <div class="info-message px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-user-shield mr-3"></i>
        <span class="flex-1 text-sm">
            <strong>Recuperación de contraseña - Paso 2</strong><br>
            <span class="text-white/70 text-xs">Usuario: <?php echo htmlspecialchars($username); ?></span>
        </span>
    </div>
    
    <div class="space-y-4">
        <!-- Pregunta 1 -->
        <div class="input-group">
            <label class="block text-sm font-medium text-white/90 mb-2">
                <i class="fas fa-question-circle mr-2"></i>Pregunta de Seguridad 1
            </label>
            <div class="mb-3 px-4 py-3 bg-white/5 rounded-lg border border-white/10 question-display">
                <!-- CAMBIO AQUÍ: text-white en lugar de text-white/80 -->
                <p class="text-white text-sm"><?php echo htmlspecialchars($user['pregunta_seguridad_1']); ?></p>
            </div>
            <div class="input-container">
                <input 
                    type="text"
                    id="respuesta_1" 
                    name="respuesta_1" 
                    required
                    class="w-full px-4 py-3 rounded-lg focus:outline-none transition-all duration-300 text-sm"
                    placeholder="Tu respuesta a esta pregunta"
                    autocomplete="off"
                >
            </div>
        </div>
        
        <!-- Pregunta 2 -->
        <div class="input-group">
            <label class="block text-sm font-medium text-white/90 mb-2">
                <i class="fas fa-question-circle mr-2"></i>Pregunta de Seguridad 2
            </label>
            <div class="mb-3 px-4 py-3 bg-white/5 rounded-lg border border-white/10 question-display">
                <!-- CAMBIO AQUÍ: text-white en lugar de text-white/80 -->
                <p class="text-white text-sm"><?php echo htmlspecialchars($user['pregunta_seguridad_2']); ?></p>
            </div>
            <div class="input-container">
                <input 
                    type="text"
                    id="respuesta_2" 
                    name="respuesta_2" 
                    required
                    class="w-full px-4 py-3 rounded-lg focus:outline-none transition-all duration-300 text-sm"
                    placeholder="Tu respuesta a esta pregunta"
                    autocomplete="off"
                >
            </div>
        </div>
        
        <!-- Nueva Contraseña -->
        <div class="input-group">
            <label for="nueva_password" class="block text-sm font-medium text-white/90 mb-2">
                <i class="fas fa-lock mr-2"></i>Nueva Contraseña
            </label>
            <div class="input-container">
                <input 
                    type="password"
                    id="nueva_password" 
                    name="nueva_password" 
                    required
                    class="w-full px-4 py-3 rounded-lg focus:outline-none transition-all duration-300 text-sm"
                    placeholder="Mínimo 6 caracteres"
                    autocomplete="new-password"
                >
                <button 
                    type="button" 
                    onclick="togglePassword('nueva_password', 'toggleIconNueva')"
                    class="absolute right-3 top-3 text-white/50 hover:text-white transition-colors duration-200 focus:outline-none"
                    aria-label="Mostrar/ocultar contraseña"
                >
                    <i class="fas fa-eye text-sm" id="toggleIconNueva"></i>
                </button>
            </div>
        </div>

        <!-- Confirmar Contraseña -->
        <div class="input-group">
            <label for="confirmar_password" class="block text-sm font-medium text-white/90 mb-2">
                <i class="fas fa-lock mr-2"></i>Confirmar Contraseña
            </label>
            <div class="input-container">
                <input 
                    type="password"
                    id="confirmar_password" 
                    name="confirmar_password" 
                    required
                    class="w-full px-4 py-3 rounded-lg focus:outline-none transition-all duration-300 text-sm"
                    placeholder="Repite la nueva contraseña"
                    autocomplete="new-password"
                >
                <button 
                    type="button" 
                    onclick="togglePassword('confirmar_password', 'toggleIconConfirmar')"
                    class="absolute right-3 top-3 text-white/50 hover:text-white transition-colors duration-200 focus:outline-none"
                    aria-label="Mostrar/ocultar contraseña"
                >
                    <i class="fas fa-eye text-sm" id="toggleIconConfirmar"></i>
                </button>
            </div>
        </div>
    
    <!-- Botones -->
    <div class="space-y-3">
        <button 
            type="submit" 
            class="btn-recovery w-full py-3 px-4 rounded-lg font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-green-300"
        >
            <i class="fas fa-key mr-2"></i>Restablecer Contraseña
        </button>
        
        <div class="flex space-x-3">
            <a href="login.php?recovery_step=1" 
               class="flex-1 text-center text-sm text-white/70 hover:text-white py-2 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
            
            <a href="login.php" 
               class="flex-1 text-center text-sm text-white/70 hover:text-white py-2 transition">
                <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
            </a>
        </div>
    </div>
</form>