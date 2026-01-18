<?php
// auth/recovery_form_step1.php - Paso 1: Ingresar username
?>
<form method="POST" action="?recovery_step=1" class="space-y-6">
    <input type="hidden" name="recovery_mode" value="step1">
    
    <div class="info-message px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-user-shield mr-3"></i>
        <span class="flex-1 text-sm"><strong>Recuperación de contraseña - Paso 1</strong></span>
    </div>
    
    <div class="text-center mb-6">
        <p class="text-white/70 text-sm">Ingresa tu usuario de administrador para continuar con la recuperación.</p>
    </div>
    
    <div class="space-y-4">
        <!-- Usuario -->
        <div class="input-group">
            <label for="username" class="block text-sm font-medium text-white/90 mb-2">
                <i class="fas fa-user mr-2"></i>Usuario de Administrador
            </label>
            <div class="input-container">
                <input 
                    type="text"
                    id="username" 
                    name="username" 
                    required
                    class="w-full px-4 py-3 rounded-lg focus:outline-none transition-all duration-300 text-sm"
                    placeholder="Ingresa tu usuario"
                    autocomplete="username"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                >
            </div>
        </div>
    </div>
    
    <!-- Botones -->
    <div class="space-y-3">
        <button 
            type="submit" 
            class="btn-recovery w-full py-3 px-4 rounded-lg font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-green-300"
        >
            <i class="fas fa-arrow-right mr-2"></i>Continuar
        </button>
        
        <a href="login.php" 
           class="block text-center text-sm text-white/70 hover:text-white py-2 transition">
            <i class="fas fa-arrow-left mr-2"></i>Volver al inicio de sesión
        </a>
    </div>
</form>