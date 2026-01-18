// assets/js/login.js

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

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Configurar inputs
    const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
    
    inputs.forEach(input => {
        // Establecer color inicial
        input.style.color = '#ffffff';
        
        // Evento focus
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-blue');
        });
        
        // Evento blur
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