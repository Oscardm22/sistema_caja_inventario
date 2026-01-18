// Función genérica para mostrar/ocultar contraseña
function togglePassword(inputId, iconElement) {
    const passwordInput = document.getElementById(inputId);
    
    if (passwordInput && iconElement) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            iconElement.classList.remove('fa-eye');
            iconElement.classList.add('fa-eye-slash');
            iconElement.classList.add('text-blue-300');
        } else {
            passwordInput.type = 'password';
            iconElement.classList.remove('fa-eye-slash', 'text-blue-300');
            iconElement.classList.add('fa-eye');
        }
    }
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
    
    // Validación para formulario de recuperación CON PREGUNTAS DE SEGURIDAD
    const recoveryForm = document.querySelector('form input[name="recovery_mode"]')?.closest('form');
    if (recoveryForm) {
        recoveryForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username')?.value.trim();
            const respuesta1 = document.getElementById('respuesta_1')?.value.trim();
            const respuesta2 = document.getElementById('respuesta_2')?.value.trim();
            const nuevaPassword = document.getElementById('nueva_password')?.value;
            const confirmarPassword = document.getElementById('confirmar_password')?.value;
            let hasError = false;
            
            // Remover errores previos
            document.querySelectorAll('.input-error').forEach(el => {
                el.classList.remove('input-error', 'border-red-500', 'shake');
            });
            
            // Validar campos requeridos
            if (!username) {
                document.getElementById('username').classList.add('input-error', 'border-red-500', 'shake');
                hasError = true;
            }
            
            // Solo validar preguntas y contraseñas si las preguntas están visibles
            const pregunta1Group = document.getElementById('pregunta1-group');
            if (pregunta1Group && !respuesta1) {
                document.getElementById('respuesta_1').classList.add('input-error', 'border-red-500', 'shake');
                hasError = true;
            }
            
            const pregunta2Group = document.getElementById('pregunta2-group');
            if (pregunta2Group && !respuesta2) {
                document.getElementById('respuesta_2').classList.add('input-error', 'border-red-500', 'shake');
                hasError = true;
            }
            
            if (pregunta1Group && pregunta2Group) {
                if (!nuevaPassword) {
                    document.getElementById('nueva_password').classList.add('input-error', 'border-red-500', 'shake');
                    hasError = true;
                }
                if (!confirmarPassword) {
                    document.getElementById('confirmar_password').classList.add('input-error', 'border-red-500', 'shake');
                    hasError = true;
                }
                
                // Validar que las contraseñas coincidan
                if (nuevaPassword && confirmarPassword && nuevaPassword !== confirmarPassword) {
                    document.getElementById('confirmar_password').classList.add('input-error', 'border-red-500', 'shake');
                    hasError = true;
                    // Mostrar mensaje de error
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message mt-2 px-3 py-2 rounded text-sm';
                    errorMsg.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Las contraseñas no coinciden';
                    
                    const container = document.getElementById('confirmar_password').parentNode;
                    if (!container.querySelector('.error-message')) {
                        container.appendChild(errorMsg);
                    }
                }
                
                // Validar longitud mínima de contraseña
                if (nuevaPassword && nuevaPassword.length < 6) {
                    document.getElementById('nueva_password').classList.add('input-error', 'border-red-500', 'shake');
                    hasError = true;
                    
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message mt-2 px-3 py-2 rounded text-sm';
                    errorMsg.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Mínimo 6 caracteres';
                    
                    const container = document.getElementById('nueva_password').parentNode;
                    if (!container.querySelector('.error-message')) {
                        container.appendChild(errorMsg);
                    }
                }
            }
            
            if (hasError) {
                e.preventDefault();
                
                setTimeout(() => {
                    document.querySelectorAll('.shake').forEach(el => {
                        el.classList.remove('shake');
                    });
                }, 600);
                
                // Remover mensajes de error después de 3 segundos
                setTimeout(() => {
                    document.querySelectorAll('.error-message').forEach(el => {
                        el.remove();
                    });
                }, 3000);
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
    
    // Agregar botones para mostrar/ocultar contraseñas en modo recuperación
    const nuevaPasswordInput = document.getElementById('nueva_password');
    const confirmPasswordInput = document.getElementById('confirmar_password');
    
    if (nuevaPasswordInput) {
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'absolute right-3 top-3 text-white/50 hover:text-white transition-colors duration-200 focus:outline-none';
        toggleBtn.innerHTML = '<i class="fas fa-eye text-sm"></i>';
        toggleBtn.setAttribute('aria-label', 'Mostrar/ocultar contraseña');
        toggleBtn.addEventListener('click', function() {
            togglePassword('nueva_password', this.querySelector('i'));
        });
        
        // Asegurarse de que el contenedor tenga posición relativa
        if (nuevaPasswordInput.parentNode.style.position !== 'relative') {
            nuevaPasswordInput.parentNode.style.position = 'relative';
        }
        nuevaPasswordInput.parentNode.appendChild(toggleBtn);
    }
    
    if (confirmPasswordInput) {
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'absolute right-3 top-3 text-white/50 hover:text-white transition-colors duration-200 focus:outline-none';
        toggleBtn.innerHTML = '<i class="fas fa-eye text-sm"></i>';
        toggleBtn.setAttribute('aria-label', 'Mostrar/ocultar contraseña');
        toggleBtn.addEventListener('click', function() {
            togglePassword('confirmar_password', this.querySelector('i'));
        });
        
        // Asegurarse de que el contenedor tenga posición relativa
        if (confirmPasswordInput.parentNode.style.position !== 'relative') {
            confirmPasswordInput.parentNode.style.position = 'relative';
        }
        confirmPasswordInput.parentNode.appendChild(toggleBtn);
    }
    
    // Función para verificar usuario en tiempo real (opcional)
    const recoveryUsernameInput = document.getElementById('username');
    if (recoveryUsernameInput && recoveryForm) {
        let checkTimeout;
        
        recoveryUsernameInput.addEventListener('input', function() {
            clearTimeout(checkTimeout);
            
            // Esperar 500ms después de que el usuario deje de escribir
            checkTimeout = setTimeout(() => {
                const username = this.value.trim();
                if (username.length >= 3) {
                    // Aquí podrías implementar AJAX para verificar el usuario
                    // sin recargar la página, pero mantenemos simple por ahora
                }
            }, 500);
        });
    }
    
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
    
    // Limpiar validaciones cuando el usuario comienza a escribir
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('input-error', 'border-red-500', 'shake');
            
            // Remover mensajes de error específicos
            const errorMsg = this.parentNode.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        });
    });
});