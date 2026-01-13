<?php
session_start();
require_once 'config/database.php';

$error = '';
$recovery_mode = isset($_GET['recovery']) && $_GET['recovery'] === 'admin';

// Procesar login normal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Verificar si es modo recuperación
    $is_recovery = isset($_POST['recovery_mode']) && $_POST['recovery_mode'] === 'true';
    
    if ($is_recovery) {
        // MODO RECUPERACIÓN DE ADMINISTRADOR
        $nombre_completo = $_POST['nombre_completo'] ?? '';
        $nueva_password = $_POST['nueva_password'] ?? '';
        $confirmar_password = $_POST['confirmar_password'] ?? '';
        
        if (!empty($username) && !empty($nombre_completo) && !empty($nueva_password) && !empty($confirmar_password)) {
            if ($nueva_password === $confirmar_password) {
                if (strlen($nueva_password) >= 6) {
                    $db = getDB();
                    
                    // Verificar que el usuario sea administrador
                    $stmt = $db->prepare("SELECT id, nombre, username, rol, estado FROM usuarios WHERE username = ? AND rol = 'admin' AND estado = 'activo'");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        
                        // Verificar nombre completo (case-insensitive, sin espacios extras)
                        $nombre_bd = trim(strtolower($user['nombre']));
                        $nombre_ingresado = trim(strtolower($nombre_completo));
                        
                        if ($nombre_bd === $nombre_ingresado) {
                            // Actualizar contraseña
                            $hashed_password = password_hash($nueva_password, PASSWORD_DEFAULT);
                            $updateStmt = $db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                            $updateStmt->bind_param("si", $hashed_password, $user['id']);
                            
                            if ($updateStmt->execute()) {
                                // Redirigir al login con mensaje de éxito
                                header('Location: login.php?success=admin_recovery');
                                exit();
                            } else {
                                $error = 'Error al actualizar la contraseña. Intenta nuevamente.';
                            }
                            $updateStmt->close();
                        } else {
                            $error = 'El nombre completo no coincide. Verifica mayúsculas, acentos y espacios.';
                        }
                    } else {
                        $error = 'Usuario no encontrado o no tiene permisos de administrador.';
                    }
                    $stmt->close();
                } else {
                    $error = 'La contraseña debe tener al menos 6 caracteres.';
                }
            } else {
                $error = 'Las contraseñas no coinciden.';
            }
        } else {
            $error = 'Por favor completa todos los campos.';
        }
    } else {
        // MODO LOGIN NORMAL
        if (!empty($username) && !empty($password)) {
            $db = getDB();
            
            $stmt = $db->prepare("SELECT id, nombre, username, password, rol, estado FROM usuarios WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verificar contraseña
                if (password_verify($password, $user['password'])) {
                    if ($user['estado'] === 'activo') {
                        // Iniciar sesión
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['nombre'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_role'] = $user['rol'];
                        
                        // Actualizar último login
                        $updateStmt = $db->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
                        $updateStmt->bind_param("i", $user['id']);
                        $updateStmt->execute();
                        
                        // Redirigir según rol
                        header('Location: index.php');
                        exit();
                    } else {
                        $error = 'Tu cuenta está desactivada. Contacta al administrador.';
                    }
                } else {
                    $error = 'Credenciales incorrectas.';
                }
            } else {
                $error = 'Credenciales incorrectas.';
            }
            $stmt->close();
        } else {
            $error = 'Por favor ingresa nombre de usuario y contraseña.';
        }
    }
}

// Mensaje de éxito después de recuperación
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema Caja</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #1f2937; /* gray-800 */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .input-group {
            transition: all 0.3s ease;
        }
        
        .input-group:focus-within {
            transform: translateY(-2px);
        }
        
        .input-group:focus-within label {
            color: #ffffff;
        }
        
        .input-container {
            position: relative;
        }
        
        /* Estilos base para inputs */
        .input-container input {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }
        
        .input-container input:focus {
            background: rgba(255, 255, 255, 0.12) !important;
            border-color: rgba(59, 130, 246, 0.5) !important; /* blue-500 con opacidad */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        
        /* IMPORTANTE: Corregir autocomplete de Chrome */
        .input-container input:-webkit-autofill,
        .input-container input:-webkit-autofill:hover,
        .input-container input:-webkit-autofill:focus,
        .input-container input:-webkit-autofill:active {
            -webkit-text-fill-color: #ffffff !important;
            -webkit-box-shadow: 0 0 0px 1000px rgba(255, 255, 255, 0.08) inset !important;
            transition: background-color 5000s ease-in-out 0s !important;
            caret-color: #ffffff !important;
        }
        
        /* Para Firefox */
        .input-container input:-moz-autofill,
        .input-container input:-moz-autofill:hover,
        .input-container input:-moz-autofill:focus {
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }
        
        /* Para Edge */
        .input-container input:-ms-input-placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }
        
        .input-container input::-ms-reveal,
        .input-container input::-ms-clear {
            filter: invert(100%);
        }
        
        .btn-login {
            background: #3b82f6; /* blue-500 */
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: #2563eb; /* blue-600 */
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
        
        .btn-recovery {
            background: rgba(34, 197, 94, 0.2); /* green con opacidad */
            color: #86efac; /* green-300 */
            border: 1px solid rgba(34, 197, 94, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-recovery:hover {
            background: rgba(34, 197, 94, 0.3);
            transform: translateY(-2px);
        }
        
        .error-message {
            animation: slideDown 0.3s ease-out;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5; /* red-300 */
        }
        
        .success-message {
            animation: slideDown 0.3s ease-out;
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac; /* green-300 */
        }
        
        .info-message {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #93c5fd; /* blue-300 */
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .glow {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.1);
        }
        
        /* Placeholders */
        ::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }
        
        /* Iconos */
        .icon-blue {
            color: #3b82f6; /* blue-500 */
        }
        
        /* Textos */
        .text-blue-light {
            color: #93c5fd; /* blue-300 */
        }
        
        /* Bordes para focus */
        .ring-blue {
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
        }
        
        /* Enlace olvidó contraseña */
        .forgot-password {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.75rem;
            transition: all 0.2s ease;
        }
        
        .forgot-password:hover {
            color: #93c5fd; /* blue-300 */
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="w-full max-w-sm mx-auto p-6 fade-in">
        <div class="login-card rounded-xl p-8 glow">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-white/5 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg border border-white/10">
                    <i class="fas fa-cash-register text-3xl icon-blue"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Sistema de Caja</h1>
                <p class="text-blue-light text-sm">Control de Inventario y Ventas</p>
            </div>
            
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
            
            <!-- Formulario de Login -->
            <form method="POST" action="" class="space-y-6" autocomplete="on">
                <?php if ($recovery_mode): ?>
                    <!-- MODO RECUPERACIÓN DE ADMINISTRADOR -->
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
                            <i class="fas fa-arrow-left mr-2"></i>Volver al login normal
                        </a>
                    </div>
                    
                <?php else: ?>
                    <!-- MODO LOGIN NORMAL -->
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
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <script>
        // Función genérica para mostrar/ocultar contraseña
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput && toggleIcon) {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                    toggleIcon.classList.add('text-blue-300');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash', 'text-blue-300');
                    toggleIcon.classList.add('fa-eye');
                }
            }
        }
        
        // Para modo recuperación
        function toggleNewPassword() {
            togglePassword('nueva_password', 'toggleIconNueva');
        }
        
        function toggleConfirmPassword() {
            togglePassword('confirmar_password', 'toggleIconConfirmar');
        }
        
        // Efectos de focus
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            
            inputs.forEach(input => {
                // Establecer color inicial
                input.style.color = '#ffffff';
                
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('ring-blue');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('ring-blue');
                });
                
                // Forzar estilo cuando cambia el valor (para autocomplete)
                input.addEventListener('input', function() {
                    this.style.color = '#ffffff';
                    this.style.backgroundColor = 'rgba(255, 255, 255, 0.08)';
                });
                
                // Verificar si ya tiene valor (por autocomplete al cargar)
                if (input.value) {
                    input.style.color = '#ffffff';
                    input.style.backgroundColor = 'rgba(255, 255, 255, 0.08)';
                }
            });
            
            // Auto-focus en el campo de username
            const usernameInput = document.getElementById('username');
            if (usernameInput) {
                setTimeout(() => {
                    usernameInput.focus();
                }, 300);
            }
            
            // Validación simple para formulario de login normal
            const loginForm = document.querySelector('form');
            if (loginForm && !document.querySelector('input[name="recovery_mode"]')) {
                loginForm.addEventListener('submit', function(e) {
                    const username = document.getElementById('username')?.value.trim();
                    const password = document.getElementById('password')?.value.trim();
                    
                    if (!username || !password) {
                        e.preventDefault();
                        
                        if (!username) {
                            document.getElementById('username').classList.add('shake', 'border-red-500');
                        }
                        if (!password) {
                            document.getElementById('password').classList.add('shake', 'border-red-500');
                        }
                        
                        setTimeout(() => {
                            document.querySelectorAll('.shake').forEach(el => {
                                el.classList.remove('shake', 'border-red-500');
                            });
                        }, 600);
                    }
                });
            }
            
            // Validación para formulario de recuperación
            const recoveryForm = document.querySelector('form input[name="recovery_mode"]')?.closest('form');
            if (recoveryForm) {
                recoveryForm.addEventListener('submit', function(e) {
                    const username = document.getElementById('username')?.value.trim();
                    const nombreCompleto = document.getElementById('nombre_completo')?.value.trim();
                    const nuevaPassword = document.getElementById('nueva_password')?.value;
                    const confirmarPassword = document.getElementById('confirmar_password')?.value;
                    let hasError = false;
                    
                    // Remover errores previos
                    document.querySelectorAll('.input-error').forEach(el => {
                        el.classList.remove('input-error', 'border-red-500', 'shake');
                    });
                    
                    if (!username) {
                        document.getElementById('username').classList.add('input-error', 'border-red-500', 'shake');
                        hasError = true;
                    }
                    if (!nombreCompleto) {
                        document.getElementById('nombre_completo').classList.add('input-error', 'border-red-500', 'shake');
                        hasError = true;
                    }
                    if (!nuevaPassword) {
                        document.getElementById('nueva_password').classList.add('input-error', 'border-red-500', 'shake');
                        hasError = true;
                    }
                    if (!confirmarPassword) {
                        document.getElementById('confirmar_password').classList.add('input-error', 'border-red-500', 'shake');
                        hasError = true;
                    }
                    
                    if (nuevaPassword && nuevaPassword.length < 6) {
                        document.getElementById('nueva_password').classList.add('input-error', 'border-red-500', 'shake');
                        hasError = true;
                        setTimeout(() => alert('La contraseña debe tener al menos 6 caracteres.'), 10);
                    }
                    
                    if (nuevaPassword && confirmarPassword && nuevaPassword !== confirmarPassword) {
                        document.getElementById('nueva_password').classList.add('input-error', 'border-red-500', 'shake');
                        document.getElementById('confirmar_password').classList.add('input-error', 'border-red-500', 'shake');
                        hasError = true;
                        setTimeout(() => alert('Las contraseñas no coinciden.'), 10);
                    }
                    
                    if (hasError) {
                        e.preventDefault();
                        
                        setTimeout(() => {
                            document.querySelectorAll('.shake').forEach(el => {
                                el.classList.remove('shake');
                            });
                        }, 600);
                    }
                });
            }
            
            // Efecto hover para inputs
            inputs.forEach(input => {
                input.addEventListener('mouseenter', function() {
                    this.style.borderColor = 'rgba(59, 130, 246, 0.4)';
                });
                
                input.addEventListener('mouseleave', function() {
                    if (!this.matches(':focus')) {
                        this.style.borderColor = 'rgba(255, 255, 255, 0.2)';
                    }
                });
            });
            
            // Solución adicional para autocomplete
            setTimeout(() => {
                inputs.forEach(input => {
                    if (input.value) {
                        input.style.color = '#ffffff';
                        input.style.backgroundColor = 'rgba(255, 255, 255, 0.08)';
                        input.style.setProperty('-webkit-text-fill-color', '#ffffff', 'important');
                    }
                });
            }, 100);
        });
        
        // Efecto shake para errores
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-3px); }
                20%, 40%, 60%, 80% { transform: translateX(3px); }
            }
            .shake {
                animation: shake 0.4s ease-in-out;
            }
            
            ::selection {
                background-color: rgba(59, 130, 246, 0.3);
                color: white;
            }
            
            input:-webkit-autofill {
                -webkit-text-fill-color: #ffffff !important;
            }
            
            input {
                caret-color: #3b82f6;
            }
            
            .input-error {
                border-color: rgba(239, 68, 68, 0.5) !important;
                background: rgba(239, 68, 68, 0.1) !important;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>