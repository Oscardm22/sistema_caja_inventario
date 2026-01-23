<div class="bg-white p-4 rounded-lg shadow mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Búsqueda por nombre o usuario -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                   placeholder="Nombre o usuario" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <!-- Filtro por rol -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
            <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los roles</option>
                <option value="admin" <?php echo ($role_filter ?? '') === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                <option value="cajero" <?php echo ($role_filter ?? '') === 'cajero' ? 'selected' : ''; ?>>Cajero</option>
            </select>
        </div>
        
        <!-- Filtro por estado -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="activo" <?php echo ($status_filter ?? '') === 'activo' ? 'selected' : ''; ?>>Activo</option>
                <option value="inactivo" <?php echo ($status_filter ?? '') === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>
        
        <!-- Botones -->
        <div class="flex items-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors mr-2">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                <i class="fas fa-redo mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>