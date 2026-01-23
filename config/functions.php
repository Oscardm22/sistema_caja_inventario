<?php
// config/functions.php

function formatCurrency($amount) {
    return number_format($amount, 2, '.', ',');
}

function getDashboardStats($conn) {
    $stats = [];
    $hoy = date('Y-m-d');
    
    try {
        // 1. Estado de caja actual
        $query_caja = "SELECT monto_inicial, estado, monto_final FROM caja 
                       WHERE usuario_id = ? 
                       ORDER BY fecha_apertura DESC 
                       LIMIT 1";
        $stmt_caja = $conn->prepare($query_caja);
        $stmt_caja->bind_param("i", $_SESSION['user_id']);
        $stmt_caja->execute();
        $stats['caja_actual'] = $stmt_caja->get_result()->fetch_assoc();
        $stmt_caja->close();
        
        // 2. Ventas de hoy
        $query_ventas_hoy = "SELECT COUNT(*) as total_ventas, 
                             COALESCE(SUM(total), 0) as monto_total 
                             FROM ventas 
                             WHERE DATE(fecha_venta) = ? 
                             AND estado = 'completada'";
        $stmt_ventas = $conn->prepare($query_ventas_hoy);
        $stmt_ventas->bind_param("s", $hoy);
        $stmt_ventas->execute();
        $stats['ventas_hoy'] = $stmt_ventas->get_result()->fetch_assoc();
        $stmt_ventas->close();
        
        // 3. Total de productos activos
        $query_productos = "SELECT COUNT(*) as total_productos FROM productos WHERE estado = 'activo'";
        $stmt_productos = $conn->prepare($query_productos);
        $stmt_productos->execute();
        $stats['productos'] = $stmt_productos->get_result()->fetch_assoc();
        $stmt_productos->close();
        
        // 4. Total de clientes activos
        $query_clientes = "SELECT COUNT(*) as total_clientes FROM clientes WHERE estado = 'activo'";
        $stmt_clientes = $conn->prepare($query_clientes);
        $stmt_clientes->execute();
        $stats['clientes'] = $stmt_clientes->get_result()->fetch_assoc();
        $stmt_clientes->close();
        
        // 5. Productos con stock bajo
        $query_stock_bajo = "SELECT COUNT(*) as stock_bajo FROM productos 
                             WHERE estado = 'activo' 
                             AND stock <= stock_minimo";
        $stmt_stock = $conn->prepare($query_stock_bajo);
        $stmt_stock->execute();
        $stats['stock_bajo'] = $stmt_stock->get_result()->fetch_assoc();
        $stmt_stock->close();
        
        // 6. Ventas recientes
        $query_ventas_recientes = "SELECT v.numero_factura, v.total, v.fecha_venta, 
                                  c.nombre as cliente_nombre,
                                  u.nombre as vendedor
                                  FROM ventas v
                                  LEFT JOIN clientes c ON v.cliente_id = c.id
                                  INNER JOIN usuarios u ON v.usuario_id = u.id
                                  WHERE v.estado = 'completada'
                                  ORDER BY v.fecha_venta DESC 
                                  LIMIT 5";
        $stmt_recientes = $conn->prepare($query_ventas_recientes);
        $stmt_recientes->execute();
        $stats['ventas_recientes'] = $stmt_recientes->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_recientes->close();
        
        // 7. Lista de productos con stock bajo
        $query_lista_stock = "SELECT p.nombre, p.stock, p.stock_minimo, c.nombre as categoria
                              FROM productos p
                              LEFT JOIN categorias c ON p.categoria_id = c.id
                              WHERE p.estado = 'activo' 
                              AND p.stock <= p.stock_minimo
                              ORDER BY p.stock ASC
                              LIMIT 10";
        $stmt_lista_stock = $conn->prepare($query_lista_stock);
        $stmt_lista_stock->execute();
        $stats['lista_stock_bajo'] = $stmt_lista_stock->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_lista_stock->close();
        
    } catch (Exception $e) {
        error_log("Error en dashboard: " . $e->getMessage());
        $stats['error'] = DEBUG_MODE ? $e->getMessage() : "Error al cargar estadísticas";
    }
    
    return $stats;
}
?>