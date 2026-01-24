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
}
?>