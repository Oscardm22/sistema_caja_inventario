class UsuarioEdicion {
    constructor() {
        this.form = document.getElementById('editar-usuario-form');
        this.init();
    }
    
    init() {
        // Auto-focus en el primer campo
        document.getElementById('nombre').focus();
        
        // Configurar botones para mostrar/ocultar contraseña
        this.setupPasswordVisibility();
        
        // Validación en tiempo real
        this.setupRealTimeValidation();
        
        // Prevenir envío doble
        this.preventDoubleSubmit();
    }
    
    setupPasswordVisibility() {
        const passwordFields = ['password', 'confirm_password'];
        
        passwordFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field) return;
            
            const parent = field.parentElement;
            parent.classList.add('relative');
            
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            toggleBtn.className = 'absolute right-3 top-9 text-gray-500 hover:text-gray-700 focus:outline-none';
            toggleBtn.setAttribute('aria-label', 'Mostrar/ocultar contraseña');
            
            toggleBtn.addEventListener('click', () => {
                this.togglePasswordVisibility(field);
                this.updateEyeIcon(toggleBtn, field);
            });
            
            parent.appendChild(toggleBtn);
        });
    }
    
    togglePasswordVisibility(input) {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
    }
    
    updateEyeIcon(button, input) {
        const icon = button.querySelector('i');
        if (input.getAttribute('type') === 'password') {
            icon.className = 'fas fa-eye';
        } else {
            icon.className = 'fas fa-eye-slash';
        }
    }
    
    setupRealTimeValidation() {
        const fields = {
            'nombre': { min: 3, message: 'El nombre debe tener al menos 3 caracteres' },
            'username': { min: 3, message: 'El usuario debe tener al menos 3 caracteres' },
            'password': { min: 6, message: 'La contraseña debe tener al menos 6 caracteres', optional: true }
        };
        
        Object.keys(fields).forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field) return;
            
            field.addEventListener('blur', () => this.validateField(field, fields[fieldId]));
            field.addEventListener('input', () => this.clearError(field));
        });
        
        // Validar coincidencia de contraseñas
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password && confirmPassword) {
            confirmPassword.addEventListener('blur', () => this.validatePasswordMatch());
        }
    }
    
    validateField(field, rules) {
        const value = field.value.trim();
        
        // Campo opcional y vacío
        if (rules.optional && value === '') {
            this.clearError(field);
            return true;
        }
        
        if (value.length < rules.min) {
            this.showError(field, rules.message);
            return false;
        }
        
        this.clearError(field);
        return true;
    }
    
    validatePasswordMatch() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (!password || !confirmPassword) return true;
        
        if (password.value !== '' && password.value !== confirmPassword.value) {
            this.showError(confirmPassword, 'Las contraseñas no coinciden');
            return false;
        }
        
        this.clearError(confirmPassword);
        return true;
    }
    
    showError(field, message) {
        const parent = field.parentElement;
        field.classList.add('border-red-500');
        field.classList.remove('border-gray-300');
        
        // Remover mensaje de error existente
        this.clearError(field);
        
        // Agregar nuevo mensaje
        const errorElement = document.createElement('p');
        errorElement.className = 'mt-1 text-sm text-red-600';
        errorElement.textContent = message;
        errorElement.id = `${field.id}-error`;
        
        parent.appendChild(errorElement);
    }
    
    clearError(field) {
        const errorElement = document.getElementById(`${field.id}-error`);
        if (errorElement) {
            errorElement.remove();
        }
        
        field.classList.remove('border-red-500');
        field.classList.add('border-gray-300');
    }
    
    preventDoubleSubmit() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                const submitButton = this.form.querySelector('button[type="submit"]');
                
                if (submitButton && !submitButton.disabled) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
                    
                    // Re-habilitar después de 5 segundos por si hay error
                    setTimeout(() => {
                        if (submitButton.disabled) {
                            submitButton.disabled = false;
                            submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Actualizar Usuario';
                        }
                    }, 5000);
                }
            });
        }
    }
    
    validateForm() {
        let isValid = true;
        
        // Validar campos requeridos
        const requiredFields = ['nombre', 'username'];
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !field.value.trim()) {
                this.showError(field, 'Este campo es obligatorio');
                isValid = false;
            }
        });
        
        // Validar preguntas de seguridad
        const pregunta1 = document.getElementById('pregunta_seguridad_1');
        const respuesta1 = document.querySelector('input[name="respuesta_seguridad_1"]');
        const pregunta2 = document.getElementById('pregunta_seguridad_2');
        const respuesta2 = document.querySelector('input[name="respuesta_seguridad_2"]');
        
        if (pregunta1 && !pregunta1.value) {
            this.showError(pregunta1, 'Selecciona una pregunta');
            isValid = false;
        }
        
        if (respuesta1 && !respuesta1.value.trim()) {
            this.showError(respuesta1, 'Ingresa una respuesta');
            isValid = false;
        }
        
        if (pregunta2 && !pregunta2.value) {
            this.showError(pregunta2, 'Selecciona una pregunta');
            isValid = false;
        }
        
        if (respuesta2 && !respuesta2.value.trim()) {
            this.showError(respuesta2, 'Ingresa una respuesta');
            isValid = false;
        }
        
        // Validar contraseñas
        this.validatePasswordMatch();
        
        return isValid;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    const usuarioEdicion = new UsuarioEdicion();
    
    // Validación antes de enviar
    const form = document.getElementById('editar-usuario-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!usuarioEdicion.validateForm()) {
                e.preventDefault();
                
                // Desplazar al primer error
                const firstError = form.querySelector('.border-red-500');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }
    
    // Agregar eventos de teclado
    document.addEventListener('keydown', function(e) {
        // Ctrl+S para guardar
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const submitButton = document.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.click();
            }
        }
    });
});