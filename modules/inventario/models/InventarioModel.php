<?php
// modules/inventario/models/InventarioModel.php

class InventarioModel {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    public function getProductos($filtros = []) {
        $sql = "SELECT p.*, c.nombre as categoria_nombre 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE 1=1";
        
        $conditions = [];
        $params = [];
        $types = "";
        
        if (!empty($filtros['search'])) {
            $conditions[] = "(p.nombre LIKE ? OR p.codigo LIKE ? OR p.descripcion LIKE ?)";
            $search_term = "%{$filtros['search']}%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= "sss";
        }
        
        if (!empty($filtros['categoria'])) {
            $conditions[] = "p.categoria_id = ?";
            $params[] = $filtros['categoria'];
            $types .= "i";
        }
        
        if (!empty($filtros['estado'])) {
            $conditions[] = "p.estado = ?";
            $params[] = $filtros['estado'];
            $types .= "s";
        }
        
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY p.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($params) {
            // Manejar los parámetros correctamente
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        $stmt->close();
        return $productos;
    }
    
    public function getCategorias() {
        $sql = "SELECT * FROM categorias WHERE estado = 'activa' ORDER BY nombre ASC";
        $result = $this->db->query($sql);
        
        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
        
        return $categorias;
    }
    
    public function getEstadisticas() {
        $stats = [];
        
        // Total productos
        $sql = "SELECT COUNT(*) as total FROM productos";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        $stats['total_productos'] = $row['total'];
        
        // Productos activos
        $sql = "SELECT COUNT(*) as activos FROM productos WHERE estado = 'activo'";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        $stats['productos_activos'] = $row['activos'];
        
        // Stock bajo
        $sql = "SELECT COUNT(*) as stock_bajo FROM productos WHERE stock <= stock_minimo AND estado = 'activo'";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        $stats['stock_bajo'] = $row['stock_bajo'];
        
        // Valor total inventario (aproximado)
        $sql = "SELECT SUM(precio_$ * stock) as valor_total FROM productos WHERE estado = 'activo'";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        $stats['valor_total'] = $row['valor_total'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Método simple para debugging
     */
    public function testConnection() {
        try {
            $result = $this->db->query("SELECT 1 as test");
            return $result ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function codigoExiste($codigo, $excluir_id = null) {
        $sql = "SELECT id FROM productos WHERE codigo = ?";
        $params = [$codigo];
        
        if ($excluir_id) {
            $sql .= " AND id != ?";
            $params[] = $excluir_id;
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($excluir_id) {
            $stmt->bind_param("si", $codigo, $excluir_id);
        } else {
            $stmt->bind_param("s", $codigo);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $existe = $result->num_rows > 0;
        $stmt->close();
        
        return !$existe; // Devuelve true si NO existe (para validación)
    }
    
    /**
     * Crea un nuevo producto
     */
    public function crearProducto($datos) {
        // Asegurarnos de que la imagen tenga un valor por defecto
        $imagen = $datos['imagen'] ?? 'default.jpg';
        
        $sql = "INSERT INTO productos 
                (codigo, nombre, descripcion, precio_\$, stock, stock_minimo, 
                 categoria_id, imagen, unidad_medida, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bind_param(
            "sssdiiisss",
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'],
            $datos['precio_$'],
            $datos['stock'],
            $datos['stock_minimo'],
            $datos['categoria_id'],
            $imagen,
            $datos['unidad_medida'],
            $datos['estado']
        );
        
        if ($stmt->execute()) {
            $producto_id = $stmt->insert_id;
            $stmt->close();
            return $producto_id;
        } else {
            error_log("Error al crear producto: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    /**
     * Cambia el estado de un producto
     */
    public function cambiarEstadoProducto($id, $nuevo_estado) {
        $sql = "UPDATE productos SET estado = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $nuevo_estado, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Registrar movimiento de inventario
            $this->registrarMovimientoEstado($id, $nuevo_estado);
            
            return true;
        } else {
            error_log("Error al cambiar estado: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    /**
     * Elimina un producto (cambia a inactivo en lugar de borrar)
     */
    public function eliminarProducto($id) {
        // En lugar de borrar, cambiamos el estado a inactivo
        return $this->cambiarEstadoProducto($id, 'inactivo');
    }
    
    /**
     * Obtiene un producto por ID
     */
    public function getProductoById($id) {
        $sql = "SELECT p.*, c.nombre as categoria_nombre 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row;
        }
        
        $stmt->close();
        return null;
    }
    
    /**
     * Registra un movimiento por cambio de estado
     */
    private function registrarMovimientoEstado($producto_id, $nuevo_estado) {
        // Por ahora es un placeholder
        // Más adelante implementaremos el sistema completo de movimientos
        return true;
    }

    /**
     * Actualiza un producto existente
     */
    public function actualizarProducto($datos) {
        // Determinar si hay imagen nueva
        if (isset($datos['imagen'])) {
            $sql = "UPDATE productos SET 
                    codigo = ?, 
                    nombre = ?, 
                    descripcion = ?, 
                    precio_\$ = ?, 
                    stock = ?, 
                    stock_minimo = ?, 
                    categoria_id = ?, 
                    imagen = ?, 
                    unidad_medida = ?, 
                    estado = ?
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                "sssdiiisssi",
                $datos['codigo'],
                $datos['nombre'],
                $datos['descripcion'],
                $datos['precio_$'],
                $datos['stock'],
                $datos['stock_minimo'],
                $datos['categoria_id'],
                $datos['imagen'],
                $datos['unidad_medida'],
                $datos['estado'],
                $datos['id']
            );
        } else {
            $sql = "UPDATE productos SET 
                    codigo = ?, 
                    nombre = ?, 
                    descripcion = ?, 
                    precio_\$ = ?, 
                    stock = ?, 
                    stock_minimo = ?, 
                    categoria_id = ?, 
                    unidad_medida = ?, 
                    estado = ?
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                "sssdiiissi",
                $datos['codigo'],
                $datos['nombre'],
                $datos['descripcion'],
                $datos['precio_$'],
                $datos['stock'],
                $datos['stock_minimo'],
                $datos['categoria_id'],
                $datos['unidad_medida'],
                $datos['estado'],
                $datos['id']
            );
        }
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log("Error al actualizar producto: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
}
?>