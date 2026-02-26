<?php
// modules/caja/controllers/CajaController.php

require_once __DIR__ . '/../models/CajaModel.php';

class CajaController {
    private $model;
    private $usuario_id;
    
    public function __construct() {
        $this->model = new CajaModel();
        $this->usuario_id = $_SESSION['usuario_id'] ?? 0;
    }
    
    /**
     * Acción principal del módulo de caja
     */
    public function indexAction() {
        // Obtener datos necesarios para la vista
        $tasa_bcv = $this->model->obtenerTasaBCV();
        $clientes_recientes = $this->model->obtenerClientesRecientes();
        $productos_destacados = $this->model->obtenerProductosDestacados();
        $caja_abierta = $this->model->verificarCajaAbierta();
        $ultima_venta = $this->model->obtenerUltimaVenta();
        $numero_factura = $this->model->generarNumeroFactura();
        
        // Estadísticas básicas (podrían ampliarse después)
        $stats = [
            'ventas_hoy' => 0,
            'total_hoy_usd' => 0,
            'total_hoy_ves' => 0
        ];
        
        return [
            'tasa_bcv' => $tasa_bcv,
            'clientes_recientes' => $clientes_recientes,
            'productos_destacados' => $productos_destacados,
            'caja_abierta' => $caja_abierta ? true : false,
            'ultima_venta' => $ultima_venta,
            'stats' => $stats,
            'numero_factura' => $numero_factura
        ];
    }
    
    /**
     * Acción para buscar clientes (vía AJAX)
     */
    public function buscarClientesAction() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_GET['term']) || empty(trim($_GET['term']))) {
                echo json_encode([]);
                return;
            }
            
            $termino = trim($_GET['term']);
            $clientes = $this->model->buscarClientes($termino);
            
            echo json_encode($clientes);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al buscar clientes']);
        }
    }
    
    /**
     * Acción para crear un cliente (vía AJAX)
     */
    public function crearClienteAction() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
                return;
            }
            
            // Validar campos requeridos
            $campos_requeridos = ['nombre', 'identificacion'];
            foreach ($campos_requeridos as $campo) {
                if (empty($_POST[$campo])) {
                    echo json_encode(['error' => "El campo {$campo} es requerido"]);
                    return;
                }
            }
            
            $datos = [
                'nombre' => $_POST['nombre'],
                'identificacion' => $_POST['identificacion'],
                'telefono' => $_POST['telefono'] ?? '',
                'email' => $_POST['email'] ?? '',
                'direccion' => $_POST['direccion'] ?? '',
                'tipo_persona' => $_POST['tipo_persona'] ?? 'natural'
            ];
            
            $cliente_id = $this->model->crearCliente($datos);
            
            if ($cliente_id) {
                // Obtener el cliente recién creado
                $cliente = $this->model->obtenerClientePorId($cliente_id);
                echo json_encode([
                    'success' => true,
                    'message' => 'Cliente creado exitosamente',
                    'cliente' => $cliente
                ]);
            } else {
                echo json_encode(['error' => 'Error al crear el cliente']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al procesar la solicitud']);
        }
    }
    
    /**
     * Acción para buscar productos (vía AJAX)
     */
    public function buscarProductosAction() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_GET['term']) || empty(trim($_GET['term']))) {
                echo json_encode([]);
                return;
            }
            
            $termino = trim($_GET['term']);
            $productos = $this->model->buscarProductos($termino);
            
            // Agregar precios en bolívares usando la tasa actual
            $tasa = $this->model->obtenerTasaBCV();
            
            foreach ($productos as &$producto) {
                $producto['precio_ves'] = $producto['precio_dolar'] * $tasa;
                $producto['precio_dolar_formato'] = '$' . number_format($producto['precio_dolar'], 2, ',', '.');
                $producto['precio_ves_formato'] = 'Bs. ' . number_format($producto['precio_ves'], 2, ',', '.');
            }
            
            echo json_encode($productos);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al buscar productos']);
        }
    }
    
    /**
     * Acción para obtener un producto por ID (vía AJAX)
     */
    public function obtenerProductoAction() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_GET['id']) || empty($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de producto requerido']);
                return;
            }
            
            $producto = $this->model->obtenerProductoPorId($_GET['id']);
            
            if ($producto) {
                $tasa = $this->model->obtenerTasaBCV();
                $producto['precio_ves'] = $producto['precio_dolar'] * $tasa;
                $producto['precio_dolar_formato'] = '$' . number_format($producto['precio_dolar'], 2, ',', '.');
                $producto['precio_ves_formato'] = 'Bs. ' . number_format($producto['precio_ves'], 2, ',', '.');
                
                echo json_encode($producto);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener el producto']);
        }
    }
    
    /**
     * Acción para procesar el pago de una venta
     */
    public function procesarPagoAction() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
                return;
            }
            
            // Verificar que hay caja abierta
            $caja = $this->model->verificarCajaAbierta();
            if (!$caja) {
                echo json_encode(['error' => 'No hay una caja abierta']);
                return;
            }
            
            // Decodificar los datos de la venta
            $datos = json_decode(file_get_contents('php://input'), true);
            
            if (!$datos) {
                echo json_encode(['error' => 'Datos de venta inválidos']);
                return;
            }
            
            // Validar datos mínimos
            if (empty($datos['productos']) || count($datos['productos']) === 0) {
                echo json_encode(['error' => 'No hay productos en la venta']);
                return;
            }
            
            // Preparar datos de la venta
            $venta = [
                'numero_factura' => $this->model->generarNumeroFactura(),
                'cliente_id' => $datos['cliente_id'] ?? null,
                'usuario_id' => $this->usuario_id,
                'subtotal_usd' => $datos['subtotal_usd'],
                'subtotal_ves' => $datos['subtotal_ves'],
                'iva_usd' => $datos['iva_usd'],
                'iva_ves' => $datos['iva_ves'],
                'total_usd' => $datos['total_usd'],
                'total_ves' => $datos['total_ves'],
                'tasa_bcv' => $datos['tasa_bcv'],
                'metodo_pago' => $datos['metodo_pago'] ?? 'efectivo',
                'estado' => 'completada'
            ];
            
            // Preparar detalles
            $detalles = [];
            foreach ($datos['productos'] as $item) {
                $detalles[] = [
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_usd' => $item['precio_dolar'],
                    'precio_ves' => $item['precio_dolar'] * $datos['tasa_bcv'],
                    'total_usd' => $item['precio_dolar'] * $item['cantidad'],
                    'total_ves' => $item['precio_dolar'] * $item['cantidad'] * $datos['tasa_bcv']
                ];
            }
            
            // Guardar la venta
            $venta_id = $this->model->guardarVenta($venta, $detalles);
            
            if ($venta_id) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Venta procesada exitosamente',
                    'venta_id' => $venta_id,
                    'numero_factura' => $venta['numero_factura']
                ]);
            } else {
                echo json_encode(['error' => 'Error al guardar la venta']);
            }
            
        } catch (Exception $e) {
            error_log("Error en procesarPagoAction: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al procesar el pago']);
        }
    }
    
    /**
     * Acción para guardar una venta como pendiente
     */
    public function guardarPendienteAction() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
                return;
            }
            
            // Similar a procesarPago pero con estado 'pendiente'
            $datos = json_decode(file_get_contents('php://input'), true);
            
            if (!$datos || empty($datos['productos'])) {
                echo json_encode(['error' => 'Datos de venta inválidos']);
                return;
            }
            
            $venta = [
                'numero_factura' => $this->model->generarNumeroFactura(),
                'cliente_id' => $datos['cliente_id'] ?? null,
                'usuario_id' => $this->usuario_id,
                'subtotal_usd' => $datos['subtotal_usd'],
                'subtotal_ves' => $datos['subtotal_ves'],
                'iva_usd' => $datos['iva_usd'],
                'iva_ves' => $datos['iva_ves'],
                'total_usd' => $datos['total_usd'],
                'total_ves' => $datos['total_ves'],
                'tasa_bcv' => $datos['tasa_bcv'],
                'metodo_pago' => 'pendiente',
                'estado' => 'pendiente'
            ];
            
            $detalles = [];
            foreach ($datos['productos'] as $item) {
                $detalles[] = [
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_usd' => $item['precio_dolar'],
                    'precio_ves' => $item['precio_dolar'] * $datos['tasa_bcv'],
                    'total_usd' => $item['precio_dolar'] * $item['cantidad'],
                    'total_ves' => $item['precio_dolar'] * $item['cantidad'] * $datos['tasa_bcv']
                ];
            }
            
            $venta_id = $this->model->guardarVenta($venta, $detalles);
            
            if ($venta_id) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Venta guardada como pendiente',
                    'venta_id' => $venta_id
                ]);
            } else {
                echo json_encode(['error' => 'Error al guardar la venta']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar la venta']);
        }
    }
    
    /**
     * Acción para obtener la tasa BCV actualizada (vía AJAX)
     */
    public function actualizarTasaAction() {
        header('Content-Type: application/json');
        
        try {
            // Verificar si existe el archivo de la clase TasaBCVCache
            if (file_exists('../../modules/inventario/cache_tasa.php')) {
                require_once '../../modules/inventario/cache_tasa.php';
                
                // Instanciar la clase y forzar actualización
                if (class_exists('TasaBCVCache')) {
                    $tasaCache = new TasaBCVCache();
                    $tasa = $tasaCache->forceUpdate(); // forceUpdate() hace scraping y actualiza
                    
                    echo json_encode([
                        'success' => true,
                        'tasa' => $tasa,
                        'formato' => 'Bs. ' . number_format($tasa, 2, ',', '.')
                    ]);
                } else {
                    echo json_encode(['error' => 'Sistema de tasa no disponible']);
                }
            } else {
                echo json_encode(['error' => 'Sistema de tasa no disponible']);
            }
            
        } catch (Exception $e) {
            error_log("Error en actualizarTasaAction: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar la tasa']);
        }
    }
}