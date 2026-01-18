<?php
// auth/login_form.php
?>
<form method="POST" action="" class="space-y-6" autocomplete="on">
    <div class="space-y-4">
        <!-- Username -->
        <div class="input-group">
            <label for="username" class="block text-sm font-medium text-white/90 mb-2">
                Usuario
            </label>
            <div class="input-container">
                <input 
                    type="text"
                    id="username" 
                    name="username" 
                    required
                    class="w-full px-4 py-3 rounded-lg focus:outline-none transition-all duration-300 text-sm"
                    placeholder="Ingresa tu usuario"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    autocomplete="username"
                >
            </div>
        </div>
        
        <!-- Contraseña -->
        <div class="input-group">
            <label for="password" class="block text-sm font-medium text-white/90 mb-2">
                Contraseña
            </label>
            <div class="input-container">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="w-full px-4 py-3 rounded-lg focus:outline-none transition-all duration-300 text-sm pr-12"
                    placeholder="••••••••"
                    autocomplete="current-password"
                >
                <div class="absolute right-3 top-3">
                    <button 
                        type="button" 
                        onclick="togglePassword('password', 'toggleIcon')"
                        class="text-white/50 hover:text-white transition-colors duration-200 focus:outline-none"
                        aria-label="Mostrar/ocultar contraseña"
                    >
                        <i class="fas fa-eye text-sm" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botón de enviar -->
    <button 
        type="submit" 
        class="btn-login w-full py-3 px-4 rounded-lg font-semibold text-sm mt-4 focus:outline-none focus:ring-2 focus:ring-blue-300"
    >
        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
    </button>
    
    <!-- Enlace para recuperación de administrador -->
    <div class="text-center mt-6 pt-4 border-t border-white/10">
        <a href="login.php?recovery=admin" 
           class="text-sm text-white/70 hover:text-blue-300 transition">
            <i class="fas fa-user-shield mr-2"></i>¿Olvidaste la contraseña?
        </a>
    </div>
</form>