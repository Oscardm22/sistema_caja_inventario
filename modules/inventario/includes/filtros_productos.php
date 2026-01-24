<?php
// modules/inventario/includes/filtros_productos.php
?>
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Búsqueda -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input type="text" 
                   name="search" 
                   value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Nombre, código..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <!-- Categoría -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
            <select name="categoria" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todas</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id']; ?>" 
                        <?php echo ($categoria_filter == $categoria['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Estado -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="activo" <?php echo ($estado_filter == 'activo') ? 'selected' : ''; ?>>Activos</option>
                <option value="inactivo" <?php echo ($estado_filter == 'inactivo') ? 'selected' : ''; ?>>Inactivos</option>
                <option value="">Todos</option>
            </select>
        </div>
        
        <!-- Botones -->
        <div class="flex items-end space-x-2">
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="index.php" 
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                <i class="fas fa-redo mr-2"></i>Limpiar
            </a>
        </div>
    </form>
</div>