<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Login</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Creación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="usuarios-body">
                <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
                        <p>No se encontraron usuarios</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr class="hover:bg-gray-50 transition-colors" data-user-id="<?php echo $usuario['id']; ?>">
                        <!-- Información del usuario -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($usuario['nombre']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @<?php echo htmlspecialchars($usuario['username']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Rol -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($usuario['rol'] === 'admin'): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-user-shield mr-1"></i>Administrador
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-cash-register mr-1"></i>Cajero
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Estado -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($usuario['estado'] === 'activo'): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-circle text-xs mr-1"></i>Activo
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-circle text-xs mr-1"></i>Inactivo
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Último login -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if ($usuario['ultimo_login']): ?>
                                <?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_login'])); ?>
                            <?php else: ?>
                                <span class="text-gray-400">Nunca</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Fecha creación -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?>
                        </td>
                        
                        <!-- Acciones -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <!-- Editar -->
                                <a href="editar.php?id=<?php echo $usuario['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900 transition-colors"
                                   title="Editar usuario">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Cambiar estado -->
                                <?php if ($usuario['estado'] === 'activo'): ?>
                                    <button onclick="cambiarEstado(<?php echo $usuario['id']; ?>, 'inactivo')"
                                            class="text-yellow-600 hover:text-yellow-900 transition-colors"
                                            title="Desactivar usuario">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                <?php else: ?>
                                    <button onclick="cambiarEstado(<?php echo $usuario['id']; ?>, 'activo')"
                                            class="text-green-600 hover:text-green-900 transition-colors"
                                            title="Activar usuario">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <!-- No permitir eliminar si es el único admin -->
                                <?php if (!($usuario['rol'] === 'admin' && $stats['admins'] <= 1)): ?>
                                <button onclick="confirmarEliminacion(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>')"
                                        class="text-red-600 hover:text-red-900 transition-colors"
                                        title="Eliminar usuario">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <?php if (count($usuarios) > 0): ?>
    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-500">
                Mostrando <?php echo count($usuarios); ?> usuarios
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>