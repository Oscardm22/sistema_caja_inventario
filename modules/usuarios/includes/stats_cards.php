<?php if (isset($stats)): ?>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <!-- Tarjeta Total Usuarios -->
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Usuarios</p>
                <p class="text-2xl font-bold"><?php echo $stats['total']; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Tarjeta Activos -->
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <i class="fas fa-user-check text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Activos</p>
                <p class="text-2xl font-bold text-green-600"><?php echo $stats['activos']; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Tarjeta Administradores -->
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                <i class="fas fa-user-shield text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Administradores</p>
                <p class="text-2xl font-bold text-purple-600"><?php echo $stats['admins']; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Tarjeta Cajeros -->
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                <i class="fas fa-cash-register text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Cajeros</p>
                <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['cajeros']; ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>