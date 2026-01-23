class UsuarioCreacion {
    constructor() {
        this.form = document.getElementById('crear-usuario-form');
        this.pregunta1Select = document.getElementById('pregunta_seguridad_1');
        this.pregunta2Select = document.getElementById('pregunta_seguridad_2');
        this.init();
    }
    
    init() {
        // Auto-focus en el primer campo
        document.getElementById('nombre').focus();
        
        // Configurar botones para mostrar/ocultar contraseña
        this.setupPasswordVisibility();
        
        // Configurar filtro de preguntas duplicadas
        this.setupPreguntasFilter();
        
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
    
    setupPreguntasFilter() {
        if (!this.pregunta1Select || !this.pregunta2Select) return;
        
        // Filtrar preguntas cuando cambie la primera
        this.pregunta1Select.addEventListener('change', () => this.filtrarPreguntas());
        
        // Inicializar filtro
        this.filtrarPreguntas();
    }
    
    filtrarPreguntas() {
        if (!this.pregunta1Select || !this.pregunta2Select) return;
        
        const pregunta1Value = this.pregunta1Select.value;
        
        // Habilitar todas las opciones primero
        for (let i = 0; i < this.pregunta2Select.options.length; i++) {
            const option = this.pregunta2Select.options[i];
            option.disabled = false;
            option.style.display = '';
        }
        
        // Deshabilitar la pregunta seleccionada en pregunta1
        if (pregunta1Value) {
            for (let i = 0; i < this.pregunta2Select.options.length; i++) {
                const option = this.pregunta2Select.options[i];
                
                if (option.value === pregunta1Value) {
                    option.disabled = true;
                    option.style.display = 'none';
                    
                    // Si la pregunta2 actual es la misma que la pregunta1, resetear
                    if (this.pregunta2Select.value === pregunta1Value) {
                        this.pregunta2Select.value = '';
                    }
                }
            }
        }
    }
    
    setupRealTimeValidation() {
        const fields = {
            'nombre': { min: 3, message: 'El nombre debe tener al menos 3 caracteres' },
            'username': { min: 3, message: 'El usuario debe tener al menos 3 caracteres' },
            'password': { min: 6, message: 'La contraseña debe tener al menos 6 caracteres' }
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
        
        // Validar preguntas diferentes
        if (this.pregunta1Select && this.pregunta2Select) {
            this.pregunta1Select.addEventListener('change', () => this.validatePreguntasDiferentes());
            this.pregunta2Select.addEventListener('change', () => this.validatePreguntasDiferentes());
        }
    }
    
    validateField(field, rules) {
        const value = field.value.trim();
        
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
        
        if (password.value !== confirmPassword.value) {
            this.showError(confirmPassword, 'Las contraseñas no coinciden');
            return false;
        }
        
        this.clearError(confirmPassword);
        return true;
    }
    
    validatePreguntasDiferentes() {
        if (!this.pregunta1Select || !this.pregunta2Select) return true;
        
        if (this.pregunta1Select.value && this.pregunta2Select.value && 
            this.pregunta1Select.value === this.pregunta2Select.value) {
            this.showError(this.pregunta2Select, 'Debes seleccionar preguntas diferentes');
            return false;
        }
        
        this.clearError(this.pregunta2Select);
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
                            submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Guardar Usuario';
                        }
                    }, 5000);
                }
            });
        }
    }
    
    validateForm() {
        let isValid = true;
        
        // Validar campos requeridos
        const requiredFields = ['nombre', 'username', 'password', 'confirm_password'];
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !field.value.trim()) {
                this.showError(field, 'Este campo es obligatorio');
                isValid = false;
            }
        });
        
        // Validar preguntas de seguridad
        if (!this.pregunta1Select || !this.pregunta1Select.value) {
            this.showError(this.pregunta1Select, 'Selecciona una pregunta');
            isValid = false;
        }
        
        const respuesta1 = document.querySelector('input[name="respuesta_seguridad_1"]');
        if (respuesta1 && !respuesta1.value.trim()) {
            this.showError(respuesta1, 'Ingresa una respuesta');
            isValid = false;
        }
        
        if (!this.pregunta2Select || !this.pregunta2Select.value) {
            this.showError(this.pregunta2Select, 'Selecciona una pregunta');
            isValid = false;
        }
        
        const respuesta2 = document.querySelector('input[name="respuesta_seguridad_2"]');
        if (respuesta2 && !respuesta2.value.trim()) {
            this.showError(respuesta2, 'Ingresa una respuesta');
            isValid = false;
        }
        
        // Validar preguntas diferentes
        if (!this.validatePreguntasDiferentes()) {
            isValid = false;
        }
        
        // Validar contraseñas
        if (!this.validatePasswordMatch()) {
            isValid = false;
        }
        
        return isValid;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    const usuarioCreacion = new UsuarioCreacion();
    
    // Validación antes de enviar
    const form = document.getElementById('crear-usuario-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!usuarioCreacion.validateForm()) {
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
    
    // Generar contraseña sugerida
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    if (passwordField && confirmPasswordField) {
        const generatePasswordBtn = document.createElement('button');
        generatePasswordBtn.type = 'button';
        generatePasswordBtn.innerHTML = '<i class="fas fa-key mr-1"></i>Generar Contraseña';
        generatePasswordBtn.className = 'mt-2 text-sm text-blue-600 hover:text-blue-800 flex items-center';
        
        generatePasswordBtn.addEventListener('click', () => {
            const generatedPassword = usuarioCreacion.generateRandomPassword();
            passwordField.value = generatedPassword;
            confirmPasswordField.value = generatedPassword;
            
            // Mostrar notificación
            const notification = document.createElement('div');
            notification.className = 'mt-2 p-2 bg-green-50 border border-green-200 rounded text-sm text-green-700';
            notification.innerHTML = `
                <i class="fas fa-check-circle mr-1"></i>
                Contraseña generada. No olvides guardarla en un lugar seguro.
            `;
            
            const parent = passwordField.parentElement;
            parent.appendChild(notification);
            
            // Remover notificación después de 5 segundos
            setTimeout(() => notification.remove(), 5000);
        });
        
        passwordField.parentElement.appendChild(generatePasswordBtn);
    }
});

// Método para generar contraseña aleatoria (fuera de la clase para uso global)
UsuarioCreacion.prototype.generateRandomPassword = function() {
    const length = 12;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "";
    
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    
    return password;
};