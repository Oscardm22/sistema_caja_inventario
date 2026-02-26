<?php
// modules/caja/models/CajaModel.php

class CajaModel {
    private $db;
    
    public function __construct() {
        // Obtener conexión MySQLi
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtener la tasa BCV actual del caché usando la clase del inventario
     */
    public function obtenerTasaBCV() {
        try {
            // Verificar si existe el archivo de la clase TasaBCVCache
            if (file_exists('../../modules/inventario/cache_tasa.php')) {
                require_once '../../modules/inventario/cache_tasa.php';
                
                // Instanciar la clase y obtener la tasa
                if (class_exists('TasaBCVCache')) {
                    $tasaCache = new TasaBCVCache();
                    $tasa = $tasaCache->getTasa();
                    return floatval($tasa);
                }
            }
            
            // Fallback: consultar directamente la tabla
            $query = "SELECT tasa_usd FROM tasa_bcv_cache ORDER BY id DESC LIMIT 1";
            $result = $this->db->query($query);
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return floatval($row['tasa_usd']);
            }
            
            return 0;
            
        } catch (Exception $e) {
            error_log("Error en CajaModel::obtenerTasaBCV: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener clientes recientes para mostrar en la vista
     */
    public function obtenerClientesRecientes($limite = 5) {
        try {
            $limite = intval($limite);
            $query = "SELECT id, nombre, identificacion, telefono, email, direccion 
                      FROM clientes 
                      WHERE estado = 'activo' 
                      ORDER BY id DESC 
                      LIMIT {$limite}";
            
            $result = $this->db->query($query);
            
            $clientes = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $clientes[] = $row;
                }
            }
            
            return $clientes;
            
        } catch (Exception $e) {
            error_log("Error en CajaModel::obtenerClientesRecientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar clientes por término
     */
    public function buscarClientes($termino) {
        try {
            $termino = $this->db->real_escape_string($termino);
            
            $query = "SELECT id, nombre, identificacion, telefono, email, direccion 
                      FROM clientes 
                      WHERE estado = 'activo' 
                      AND (nombre LIKE '%{$termino}%' 
                           OR identificacion LIKE '%{$termino}%' 
                           OR telefono LIKE '%{$termino}%' 
                           OR email LIKE '%{$termino}%')
                      ORDER BY nombre ASC 
                      LIMIT 10";
            
            $result = $this->db->query($query);
            
            $clientes = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $clientes[] = $row;
                }
            }
            
            return $clientes;
            
        } catch (Exception $e) {
            error_log("Error en CajaModel::buscarClientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener cliente por ID
     */
    public function obtenerClientePorId($id) {
        try {
            $id = intval($id);
            $query = "SELECT id, nombre, identificacion, telefono, email, direccion 
                      FROM clientes 
                      WHERE id = {$id} AND estado = 'activo'";
            
            $result = $this->db->query($query);
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error en CajaModel::obtenerClientePorId: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nuevo cliente
     */
    public function crearCliente($datos) {
        try {
            $nombre = $this->db->real_escape_string($datos['nombre']);
            $identificacion = $this->db->real_escape_string($datos['identificacion']);
            $telefono = $this->db->real_escape_string($datos['telefono'] ?? '');
            $email = $this->db->real_escape_string($datos['email'] ?? '');
            $direccion = $this->db->real_escape_string($datos['direccion'] ?? '');
            $tipo_persona = $this->db->real_escape_string($datos['tipo_persona'] ?? 'natural');
            
            $query = "INSERT INTO clientes (nombre, identificacion, telefono, email, direccion, tipo_persona, estado) 
                      VALUES ('{$nombre}', '{$identificacion}', '{$telefono}', '{$email}', '{$direccion}', '{$tipo_persona}', 'activo')";
            
            if ($this->db->query($query)) {
                return $this->db->insert_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error en CajaModel::crearCliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener productos destacados para mostrar en la vista
     */
    public function obtenerProductosDestacados($limite = 6) {
        try {
            $limite = intval($limite);
            
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                      FROM productos p 
                      LEFT JOIN categorias c ON p.categoria_id = c.id 
                      WHERE p.estado = 'activo' AND p.stock > 0
                      ORDER BY p.id DESC 
                      LIMIT {$limite}";
            
            $result = $this->db->query($query);
            
            $productos = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $productos[] = $row;
                }
            }
            
            return $productos;
            
        } catch (Exception $e) {
            error_log("Error en CajaModel::obtenerProductosDestacados: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar productos por término (nombre o código)
     */
    public function buscarProductos($termino) {
        try {
            $termino = $this->db->real_escape_string($termino);
            
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                      FROM productos p 
                      LEFT JOIN categorias c ON p.categoria_id = c.id 
                      WHERE p.estado = 'activo' 
                      AND p.stock > 0
                      AND (p.nombre LIKE '%{$termino}%' 
                           OR p.codigo LIKE '%{$termino}%' 
                           OR p.descripcion LIKE '%{$termino}%')
                      ORDER BY p.nombre ASC 
                      LIMIT 10";
            
            $result = $this->db->query($query);
            
            $productos = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $productos[] = $row;
                }
            }
            
            return $productos;
            
        } catch (Exception $e) {
            error_log("Error en CajaModel::buscarProductos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener producto por ID
     */
    public function obtenerProductoPorId($id) {
        try {
            $id = intval($id);
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                      FROM productos p 
                      LEFT JOIN categorias c ON p.categoria_id = c.id 
                      WHERE p.id = {$id} AND p.estado = 'activo'";
            
            $result = $this->db->query($query);
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error en CajaModel::obtenerProductoPorId: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verificar si hay una caja abierta
     */
    public function verificarCajaAbierta() {
        try {
            $query = "SELECT id, fecha_apertura, monto_inicial 
                      FROM caja 
                      WHERE estado = 'abierta' 
                      ORDER BY id DESC 
                      LIMIT 1";
            
            $result = $this->db->query($query);
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error en CajaModel::verificarCajaAbierta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener última venta realizada
     */
    public function obtenerUltimaVenta() {
        try {
            $query = "SELECT v.*, c.nombre as cliente_nombre 
                      FROM ventas v 
                      LEFT JOIN clientes c ON v.cliente_id = c.id 
                      ORDER BY v.id DESC 
                      LIMIT 1";
            
            $result = $this->db->query($query);
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error en CajaModel::obtenerUltimaVenta: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generar número de factura siguiente
     */
    public function generarNumeroFactura() {
        try {
            $anio = date('Y');
            $mes = date('m');
            $dia = date('d');
            
            $query = "SELECT COUNT(*) as total FROM ventas WHERE DATE(fecha) = CURDATE()";
            $result = $this->db->query($query);
            
            $total = 0;
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $total = $row['total'];
            }
            
            $correlativo = str_pad($total + 1, 3, '0', STR_PAD_LEFT);
            
            return "FV-{$anio}{$mes}{$dia}-{$correlativo}";
            
        } catch (Exception $e) {
            error_log("Error en CajaModel::generarNumeroFactura: " . $e->getMessage());
            return 'FV-' . date('Ymd') . '-001';
        }
    }
    
    /**
     * Guardar una venta
     */
    public function guardarVenta($datos_venta, $detalles_venta) {
        try {
            // Iniciar transacción
            $this->db->begin_transaction();
            
            // Insertar venta
            $numero_factura = $this->db->real_escape_string($datos_venta['numero_factura']);
            $cliente_id = $datos_venta['cliente_id'] ? intval($datos_venta['cliente_id']) : 'NULL';
            $usuario_id = intval($datos_venta['usuario_id']);
            $subtotal_usd = floatval($datos_venta['subtotal_usd']);
            $subtotal_ves = floatval($datos_venta['subtotal_ves']);
            $iva_usd = floatval($datos_venta['iva_usd']);
            $iva_ves = floatval($datos_venta['iva_ves']);
            $total_usd = floatval($datos_venta['total_usd']);
            $total_ves = floatval($datos_venta['total_ves']);
            $tasa_bcv = floatval($datos_venta['tasa_bcv']);
            $metodo_pago = $this->db->real_escape_string($datos_venta['metodo_pago']);
            $estado = $this->db->real_escape_string($datos_venta['estado']);
            
            $query = "INSERT INTO ventas (numero_factura, cliente_id, usuario_id, subtotal_usd, subtotal_ves, 
                                         iva_usd, iva_ves, total_usd, total_ves, tasa_bcv, metodo_pago, estado) 
                      VALUES ('{$numero_factura}', {$cliente_id}, {$usuario_id}, 
                              {$subtotal_usd}, {$subtotal_ves}, {$iva_usd}, {$iva_ves}, 
                              {$total_usd}, {$total_ves}, {$tasa_bcv}, '{$metodo_pago}', '{$estado}')";
            
            if (!$this->db->query($query)) {
                throw new Exception("Error al insertar venta: " . $this->db->error);
            }
            
            $venta_id = $this->db->insert_id;
            
            // Insertar detalles de la venta
            foreach ($detalles_venta as $detalle) {
                $producto_id = intval($detalle['producto_id']);
                $cantidad = intval($detalle['cantidad']);
                $precio_usd = floatval($detalle['precio_usd']);
                $precio_ves = floatval($detalle['precio_ves']);
                $total_detalle_usd = floatval($detalle['total_usd']);
                $total_detalle_ves = floatval($detalle['total_ves']);
                
                $query_detalle = "INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_usd, precio_ves, total_usd, total_ves) 
                                  VALUES ({$venta_id}, {$producto_id}, {$cantidad}, 
                                          {$precio_usd}, {$precio_ves}, {$total_detalle_usd}, {$total_detalle_ves})";
                
                if (!$this->db->query($query_detalle)) {
                    throw new Exception("Error al insertar detalle de venta: " . $this->db->error);
                }
                
                // Actualizar stock del producto
                $query_stock = "UPDATE productos SET stock = stock - {$cantidad} WHERE id = {$producto_id}";
                if (!$this->db->query($query_stock)) {
                    throw new Exception("Error al actualizar stock: " . $this->db->error);
                }
            }
            
            $this->db->commit();
            return $venta_id;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en CajaModel::guardarVenta: " . $e->getMessage());
            return false;
        }
    }
}