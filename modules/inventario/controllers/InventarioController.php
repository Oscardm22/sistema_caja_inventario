<?php
// modules/inventario/controllers/InventarioController.php

class InventarioController {
    private $model;
    private $tasaCache;
    
    public function __construct() {
        require_once __DIR__ . '/../models/InventarioModel.php';
        require_once __DIR__ . '/../cache_tasa.php';
        
        $this->model = new InventarioModel();
        $this->tasaCache = new TasaBCVCache();
    }
    
    public function indexAction() {
        // Obtener filtros
        $filtros = [
            'search' => $_GET['search'] ?? '',
            'categoria' => $_GET['categoria'] ?? '',
            'estado' => $_GET['estado'] ?? 'activo'
        ];
        
        // Obtener datos
        $productos = $this->model->getProductos($filtros);
        $categorias = $this->model->getCategorias();
        $stats = $this->model->getEstadisticas();
        
        // Obtener tasa BCV actual
        $tasa_bcv = $this->tasaCache->getTasa();
        
        // Calcular precios en BS para cada producto (SIN margen)
        foreach ($productos as &$producto) {
            $producto['precio_bs'] = $this->calcularPrecioBS(
                $producto['precio_$'], 
                $tasa_bcv
            );
        }
        
        return [
            'productos' => $productos,
            'categorias' => $categorias,
            'estadisticas' => $stats,
            'filtros' => $filtros,
            'tasa_bcv' => $tasa_bcv
        ];
    }
    
    /**
     * Muestra el formulario de creación
     */
    public function crearAction() {
        // Obtener categorías
        $categorias = $this->model->getCategorias();
        
        // Obtener tasa BCV actual
        $tasa_bcv = $this->tasaCache->getTasa();
        
        return [
            'categorias' => $categorias,
            'tasa_bcv' => $tasa_bcv
        ];
    }
    
    /**
     * Procesa la creación de un producto
     */
    public function procesarCreacion() {
        try {
            // Validar datos
            $datos_validados = $this->validarDatosProducto($_POST);
            
            // Procesar imagen si se subió
            $nombre_imagen = $this->procesarImagen();
            if ($nombre_imagen) {
                $datos_validados['imagen'] = $nombre_imagen;
            }
            
            // Guardar producto
            $producto_id = $this->model->crearProducto($datos_validados);
            
            if ($producto_id) {
                return [
                    'success' => true,
                    'message' => 'Producto creado exitosamente',
                    'producto_id' => $producto_id
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo guardar el producto',
                    'data' => $_POST
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => $_POST
            ];
        }
    }
    
    /**
     * Valida los datos del producto
     */
    private function validarDatosProducto($datos) {
        $errores = [];
        
        // Validar campos requeridos
        $campos_requeridos = ['codigo', 'nombre', 'precio_usd'];
        foreach ($campos_requeridos as $campo) {
            if (empty($datos[$campo])) {
                $errores[] = "El campo " . str_replace('_', ' ', $campo) . " es requerido";
            }
        }
        
        // Validar código único (debe devolver true si NO existe)
        if (!$this->model->codigoExiste($datos['codigo'])) {
            $errores[] = "El código ya existe";
        }
        
        // Validar precios
        if (!is_numeric($datos['precio_usd']) || $datos['precio_usd'] <= 0) {
            $errores[] = "El precio en dólares debe ser un número mayor a 0";
        }
        
        if (!empty($errores)) {
            throw new Exception(implode('. ', $errores));
        }
        
        // Preparar datos para guardar (SIN margen_ganancia)
        return [
            'codigo' => trim($datos['codigo']),
            'nombre' => trim($datos['nombre']),
            'descripcion' => trim($datos['descripcion'] ?? ''),
            'precio_$' => (float) $datos['precio_usd'],
            'stock' => (int) ($datos['stock'] ?? 0),
            'stock_minimo' => (int) ($datos['stock_minimo'] ?? 5),
            'categoria_id' => !empty($datos['categoria_id']) ? (int) $datos['categoria_id'] : null,
            'unidad_medida' => $datos['unidad_medida'] ?? 'unidad',
            'estado' => 'activo'
            // SIN margen_ganancia
        ];
    }
    
    /**
     * Procesa la imagen subida
     */
    private function procesarImagen() {
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $archivo = $_FILES['imagen'];
        
        // Validar tipo de archivo
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($archivo['type'], $tipos_permitidos)) {
            throw new Exception('Formato de imagen no permitido. Use JPG, PNG o GIF');
        }
        
        // Validar tamaño (2MB máximo)
        $tamano_maximo = 2 * 1024 * 1024; // 2MB
        if ($archivo['size'] > $tamano_maximo) {
            throw new Exception('La imagen es muy grande. Máximo 2MB');
        }
        
        // Generar nombre único
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre_unico = 'producto_' . time() . '_' . uniqid() . '.' . $extension;
        
        // Ruta de destino
        $directorio_destino = __DIR__ . '/../../../uploads/products/';
        
        // Crear directorio si no existe
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0755, true);
        }
        
        $ruta_destino = $directorio_destino . $nombre_unico;
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            return $nombre_unico;
        }
        
        throw new Exception('Error al subir la imagen');
    }
    
    /**
     * Calcula el precio en bolívares (SIN margen, conversión directa)
     */
    private function calcularPrecioBS($precio_usd, $tasa_bcv) {
        // Conversión directa: precio_usd * tasa_bcv
        return round($precio_usd * $tasa_bcv, 2);
    }
}
?>