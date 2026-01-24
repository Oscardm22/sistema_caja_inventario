// modules/inventario/assets/js/crear_producto.js

document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const precioUsdInput = document.getElementById('precio_usd');
    const precioBsInput = document.getElementById('precio_bs');
    const margenInput = document.getElementById('margen_ganancia');
    const codigoInput = document.getElementById('codigo');
    const form = document.getElementById('form-crear-producto');
    
    // Elementos de display
    const precioUsdDisplay = document.getElementById('precio-usd-display');
    const precioBsDisplay = document.getElementById('precio-bs-display');
    const tasaDisplay = document.getElementById('tasa-display');
    
    // Inicializar display
    if (tasaDisplay) {
        tasaDisplay.textContent = 'Bs. ' + TASA_BCV.toLocaleString('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    // Función para actualizar precio en BS
    function actualizarPrecioBS() {
        const precioUSD = parseFloat(precioUsdInput.value) || 0;
        const margen = parseFloat(margenInput.value) || 30;
        
        if (precioUSD > 0) {
            const precioBS = calcularPrecioBS(precioUSD, margen);
            
            // Actualizar input
            precioBsInput.value = precioBS.toLocaleString('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            // Actualizar displays
            if (precioUsdDisplay) {
                precioUsdDisplay.textContent = '$' + precioUSD.toFixed(2);
            }
            if (precioBsDisplay) {
                precioBsDisplay.textContent = 'Bs. ' + precioBS.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        } else {
            precioBsInput.value = '0,00';
            if (precioUsdDisplay) precioUsdDisplay.textContent = '$0.00';
            if (precioBsDisplay) precioBsDisplay.textContent = 'Bs. 0,00';
        }
    }
    
    // Event listeners para actualización en tiempo real
    if (precioUsdInput) {
        precioUsdInput.addEventListener('input', actualizarPrecioBS);
    }
    
    if (margenInput) {
        margenInput.addEventListener('input', actualizarPrecioBS);
    }
    
    // Validar código único mientras se escribe
    if (codigoInput) {
        let timeout = null;
        
        codigoInput.addEventListener('input', function() {
            clearTimeout(timeout);
            
            timeout = setTimeout(function() {
                const codigo = codigoInput.value.trim();
                
                if (codigo.length > 2) {
                    validarCodigoUnico(codigo);
                }
            }, 500);
        });
    }
    
    // Validar formulario antes de enviar
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validarFormulario()) {
                e.preventDefault();
            }
        });
    }
    
    // Validar código único via AJAX
    function validarCodigoUnico(codigo) {
        fetch('acciones.php?action=validar_codigo&codigo=' + encodeURIComponent(codigo))
            .then(response => response.json())
            .then(data => {
                if (!data.disponible) {
                    mostrarErrorCodigo('Este código ya está en uso');
                } else {
                    limpiarErrorCodigo();
                }
            })
            .catch(error => {
                console.error('Error validando código:', error);
            });
    }
    
    // Mostrar error en código
    function mostrarErrorCodigo(mensaje) {
        limpiarErrorCodigo();
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'mt-1 text-sm text-red-600';
        errorDiv.id = 'codigo-error';
        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>' + mensaje;
        
        codigoInput.parentNode.appendChild(errorDiv);
        codigoInput.classList.add('border-red-500');
    }
    
    // Limpiar error de código
    function limpiarErrorCodigo() {
        const errorDiv = document.getElementById('codigo-error');
        if (errorDiv) {
            errorDiv.remove();
        }
        codigoInput.classList.remove('border-red-500');
    }
    
    // Validar formulario completo
    function validarFormulario() {
        let valido = true;
        
        // Validar precio
        const precioUSD = parseFloat(precioUsdInput.value);
        if (!precioUSD || precioUSD <= 0) {
            mostrarErrorGlobal('El precio en dólares debe ser mayor a 0');
            valido = false;
        }
        
        // Validar stock mínimo
        const stockMinimo = document.getElementById('stock_minimo');
        if (stockMinimo && parseInt(stockMinimo.value) < 1) {
            mostrarErrorGlobal('El stock mínimo debe ser al menos 1');
            valido = false;
        }
        
        return valido;
    }
    
    // Mostrar error global
    function mostrarErrorGlobal(mensaje) {
        // Puedes implementar un sistema de notificaciones aquí
        alert(mensaje);
    }
    
    // Calcular precio inicial si hay valor en el campo
    if (precioUsdInput && precioUsdInput.value) {
        actualizarPrecioBS();
    }
});