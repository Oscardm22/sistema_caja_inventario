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