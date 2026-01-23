<?php
// includes/admin_actions.php
if ($_SESSION['user_role'] === 'admin'): 
?>

<!-- Acciones rápidas para administrador -->
<div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
    <h3 class="text-lg font-semibold text-blue-800 mb-4">
        <i class="fas fa-user-shield mr-2"></i>Acciones de Administrador
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="modules/usuarios/" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <i class="fas fa-user-cog text-xl"></i>
            </div>
            <div>
                <h4 class="font-medium text-gray-800">Gestionar Usuarios</h4>
                <p class="text-sm text-gray-600">Administrar roles y permisos</p>
            </div>
        </a>
        <a href="reportes/" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <i class="fas fa-chart-bar text-xl"></i>
            </div>
            <div>
                <h4 class="font-medium text-gray-800">Reportes Avanzados</h4>
                <p class="text-sm text-gray-600">Estadísticas y análisis</p>
            </div>
        </a>
        <a href="configuracion/" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                <i class="fas fa-cog text-xl"></i>
            </div>
            <div>
                <h4 class="font-medium text-gray-800">Configuración</h4>
                <p class="text-sm text-gray-600">Ajustes del sistema</p>
            </div>
        </a>
    </div>
</div>

<?php endif; ?>