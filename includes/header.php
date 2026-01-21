<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Sistema Caja e Inventario'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="bg-gray-100">

<script>
// Prevenir volver atrás después de login
history.pushState(null, null, location.href);
window.onpopstate = function () {
    history.go(1);
};

// Deshabilitar cache para esta página
window.onpageshow = function(event) {
    if (event.persisted) {
        window.location.reload();
    }
};

// También prevenir navegación con teclas
document.onkeydown = function(e) {
    e = e || window.event;
    
    // Detectar tecla F5 o Ctrl+R
    if (e.keyCode == 116 || (e.ctrlKey && e.keyCode == 82)) {
        e.preventDefault();
        e.returnValue = false;
        return false;
    }
    
    // Detectar tecla "atrás" del navegador (Ctrl+Z o Alt+Left)
    if ((e.ctrlKey && e.keyCode == 90) || (e.altKey && e.keyCode == 37)) {
        e.preventDefault();
        e.returnValue = false;
        return false;
    }
};
</script>