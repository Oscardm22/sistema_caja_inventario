<?php
// auth/recovery_form.php
?>
<form method="POST" action="" class="space-y-6" autocomplete="on">
    <input type="hidden" name="recovery_mode" value="true">
    
    <div class="info-message px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-user-shield mr-3"></i>
        <span class="flex-1 text-sm"><strong>Recuperación solo para administradores</strong></span>
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
                >
            </div>
        </div>
        
        <!-- Nombre Completo -->
        <div class="input-group">
            <label for="nombre_completo" class="block text-sm font-medium text-white/90 mb-2">
                <i class="fas fa-id-card mr-2"></i>Nombre Completo
            </label>
            <div class="input-container">
                <input 
                    type="text"
                    id="nombre_completo" 
                    name="nombre_completo" 
                    required
                    class="w-full px-4 py-3 rounded-lg focus:outline-none transition-all duration-300 text-sm"
                    placeholder="Ej: Juan Pérez Gómez"
                    autocomplete="name"
                >
            </div>
            <p class="text-xs text-white/50 mt-2">
                Debe coincidir exactamente con el nombre registrado.
            </p>
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
            </div>
        </div>
    </div>
    
    <!-- Botones modo recuperación -->
    <div class="space-y-3">
        <button 
            type="submit" 
            class="btn-recovery w-full py-3 px-4 rounded-lg font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-green-300"
        >
            <i class="fas fa-key mr-2"></i>Restablecer Contraseña
        </button>
        
        <a href="login.php" 
           class="block text-center text-sm text-white/70 hover:text-white py-2 transition">
            <i class="fas fa-arrow-left mr-2"></i>Volver al inicio de sesión
        </a>
    </div>
</form>