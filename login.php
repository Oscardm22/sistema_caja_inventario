<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }
        
        .input-group {
            transition: all 0.3s ease;
        }
        
        .input-group:focus-within {
            transform: translateY(-2px);
        }
        
        .input-group:focus-within label {
            color: #ffffff;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .input-group:focus-within .input-icon {
            color: #ffffff;
        }
        
        .input-container {
            position: relative;
        }
        
        /* Mejora del contraste para texto del input */
        .input-container input {
            color: #f8fafc !important; /* Blanco más brillante */
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .input-container input:focus {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.4);
        }
        
        .error-message {
            animation: slideDown 0.3s ease-out;
            background: rgba(239, 68, 68, 0.25);
            border: 1px solid rgba(239, 68, 68, 0.4);
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
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.15);
        }
        
        .glass-effect {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.08));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        
        /* Mejora de contraste para placeholders */
        ::placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        
        /* Mejora de contraste para texto general */
        .text-gray-300 {
            color: rgba(255, 255, 255, 0.85) !important;
        }
        
        .text-gray-400 {
            color: rgba(255, 255, 255, 0.75) !important;
        }
        
        .text-gray-500 {
            color: rgba(255, 255, 255, 0.65) !important;
        }
        
        /* Mejora específica para los labels */
        label {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        
        /* Mejor contraste para los íconos */
        .input-icon, .fa-user, .fa-eye {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        
        /* Efecto hover mejorado para íconos */
        .fa-eye:hover, .fa-user {
            color: #ffffff !important;
        }
        
        /* Mejor contraste para bordes */
        .border-white\/30 {
            border-color: rgba(255, 255, 255, 0.4) !important;
        }
        
        /* Sombra de texto para mejor legibilidad */
        .text-shadow {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="w-full max-w-md mx-auto p-6 fade-in">
        <div class="login-card rounded-2xl p-8 glow">
            <!-- Logo/Header -->
            <div class="text-center mb-10 text-shadow">
                <div class="w-20 h-20 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg border border-white/20">
                    <i class="fas fa-cash-register text-4xl text-white"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-3 tracking-tight">Sistema de Caja</h1>
                <p class="text-white/90 text-lg">Gestión Integral de Negocios</p>
            </div>
            
            <!-- Formulario de Login -->
            <form method="POST" action="" class="space-y-8">
                <?php if ($error): ?>
                    <div class="error-message text-white px-5 py-4 rounded-xl flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3 text-lg"></i>
                        <span class="flex-1"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="space-y-6">
                    <!-- Username -->
                    <div class="input-group">
                        <label for="username" class="block text-base font-medium mb-2 flex items-center">
                            <i class="fas fa-user mr-2 input-icon"></i>Nombre de Usuario
                        </label>
                        <div class="input-container">
                            <input 
                                type="text"
                                id="username" 
                                name="username" 
                                required
                                class="w-full px-5 py-4 bg-white/20 border border-white/40 rounded-xl placeholder-white/70 focus:outline-none focus:bg-white/30 focus:border-white/80 transition-all duration-300 text-white"
                                placeholder="Ingresa tu usuario"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                autocomplete="username"
                            >
                            <div class="absolute right-4 top-4">
                                <i class="fas fa-user text-white/80"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contraseña -->
                    <div class="input-group">
                        <label for="password" class="block text-base font-medium mb-2 flex items-center">
                            <i class="fas fa-lock mr-2 input-icon"></i>Contraseña
                        </label>
                        <div class="input-container">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                class="w-full px-5 py-4 bg-white/20 border border-white/40 rounded-xl placeholder-white/70 focus:outline-none focus:bg-white/30 focus:border-white/80 transition-all duration-300 text-white pr-14"
                                placeholder="••••••••"
                                autocomplete="current-password"
                            >
                            <div class="absolute right-4 top-4 flex items-center space-x-2">
                                <button 
                                    type="button" 
                                    onclick="togglePassword()"
                                    class="text-white/80 hover:text-white transition-colors duration-200 focus:outline-none"
                                    aria-label="Mostrar/ocultar contraseña"
                                >
                                    <i class="fas fa-eye text-lg" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Opciones adicionales -->
                <div class="flex items-center justify-between pt-2">                    
                    <a href="#" class="text-sm text-white/80 hover:text-white transition-colors duration-200 hover:underline">
                        ¿Contraseña olvidada?
                    </a>
                </div>
                
                <!-- Botón de enviar -->
                <button 
                    type="submit" 
                    class="btn-login w-full text-purple-700 py-4 px-4 rounded-xl font-bold text-lg mt-2 focus:outline-none focus:ring-3 focus:ring-white/50"
                >
                    <i class="fas fa-sign-in-alt mr-3"></i>Iniciar Sesión
                </button>
            </form>
            
            <!-- Pie de página -->
            <div class="mt-10 pt-6 border-t border-white/20">
                <p class="text-sm text-white/80 text-center">
                    <i class="fas fa-copyright mr-1"></i>
                    <?php echo date('Y'); ?> Sistema de Caja 
                    <span class="mx-2">•</span>
                    <span class="text-white">v1.0.0</span>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // Mostrar/ocultar contraseña
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
                toggleIcon.classList.add('text-white');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
                toggleIcon.classList.remove('text-white');
            }
        }
        
        // Efectos de focus mejorados
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            
            inputs.forEach(input => {
                // Guardar valor inicial para efecto
                const originalValue = input.value;
                
                // Efecto al hacer focus
                input.addEventListener('focus', function() {
                    this.classList.add('ring-2', 'ring-white/50');
                    this.parentElement.classList.add('input-focused');
                });
                
                // Efecto al perder focus
                input.addEventListener('blur', function() {
                    this.classList.remove('ring-2', 'ring-white/50');
                    this.parentElement.classList.remove('input-focused');
                    
                    // Si tiene texto, mantener fondo más visible
                    if (this.value.trim() !== '') {
                        this.classList.add('has-text');
                        this.style.backgroundColor = 'rgba(255, 255, 255, 0.25)';
                    } else {
                        this.classList.remove('has-text');
                        this.style.backgroundColor = '';
                    }
                });
                
                // Efecto al escribir
                input.addEventListener('input', function() {
                    if (this.value.trim() !== '') {
                        this.classList.add('has-text');
                        this.style.backgroundColor = 'rgba(255, 255, 255, 0.25)';
                    } else {
                        this.classList.remove('has-text');
                        this.style.backgroundColor = '';
                    }
                });
                
                // Aplicar estilo inicial si ya hay texto (por ejemplo, después de error)
                if (input.value.trim() !== '') {
                    input.classList.add('has-text');
                    input.style.backgroundColor = 'rgba(255, 255, 255, 0.25)';
                }
            });
            
            // Efecto para el checkbox de "recordar"
            const rememberCheckbox = document.querySelector('input[name="remember"]');
            const checkIcon = rememberCheckbox?.closest('label')?.querySelector('.fa-check');
            
            if (rememberCheckbox && checkIcon) {
                rememberCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        checkIcon.classList.remove('opacity-0');
                        checkIcon.classList.add('opacity-100');
                        // Cambiar color de fondo del checkbox
                        this.closest('.relative').querySelector('div').style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
                    } else {
                        checkIcon.classList.remove('opacity-100');
                        checkIcon.classList.add('opacity-0');
                        this.closest('.relative').querySelector('div').style.backgroundColor = '';
                    }
                });
            }
            
            // Auto-focus en el campo de username al cargar
            const usernameInput = document.getElementById('username');
            if (usernameInput) {
                setTimeout(() => {
                    usernameInput.focus();
                }, 400);
            }
            
            // Validación en tiempo real
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value.trim();
                let hasError = false;
                
                // Resetear errores previos
                document.querySelectorAll('.input-error').forEach(el => {
                    el.classList.remove('input-error', 'border-red-400', 'shake');
                });
                
                if (!username) {
                    document.getElementById('username').classList.add('input-error', 'border-red-400', 'shake');
                    hasError = true;
                }
                if (!password) {
                    document.getElementById('password').classList.add('input-error', 'border-red-400', 'shake');
                    hasError = true;
                }
                
                if (hasError) {
                    e.preventDefault();
                    
                    // Remover clases después de la animación
                    setTimeout(() => {
                        document.querySelectorAll('.shake').forEach(el => {
                            el.classList.remove('shake');
                        });
                    }, 600);
                }
            });
            
            // Efecto hover para botones y enlaces
            document.querySelectorAll('a, button').forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-1px)';
                });
                element.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
        
        // Agregar efecto de "shake" para errores
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
            .shake {
                animation: shake 0.5s ease-in-out;
            }
            
            /* Mejorar el contraste del texto seleccionado */
            ::selection {
                background-color: rgba(255, 255, 255, 0.3);
                color: white;
            }
            
            /* Estilo para inputs con texto */
            .has-text {
                background-color: rgba(255, 255, 255, 0.25) !important;
            }
            
            /* Efecto para el foco del grupo */
            .input-focused .input-icon {
                color: #ffffff !important;
                transform: scale(1.1);
                transition: all 0.3s ease;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>