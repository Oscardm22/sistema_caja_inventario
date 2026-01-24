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

    /**
     * Muestra el formulario para editar un producto
     */
    public function editarAction($id) {
        $model = new InventarioModel();
        
        // Obtener producto por ID
        $producto = $model->getProductoById($id);
        
        if (!$producto) {
            return false;
        }
        
        // Obtener categorías
        $categorias = $model->getCategorias();
        
        // Obtener tasa BCV
        $tasa_bcv = $this->tasaCache->getTasa();
        
        return [
            'producto' => $producto,
            'categorias' => $categorias,
            'tasa_bcv' => $tasa_bcv
        ];
    }

    /**
     * Procesa la edición de un producto
     */
    public function procesarEdicion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Método no permitido'];
        }
        
        // Validar ID
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($id <= 0) {
            return ['success' => false, 'error' => 'ID de producto inválido'];
        }
        
        // Validar datos requeridos
        $required_fields = ['codigo', 'nombre', 'precio_$', 'stock', 'stock_minimo', 'unidad_medida', 'estado'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                return ['success' => false, 'error' => "El campo " . str_replace('_', ' ', $field) . " es requerido"];
            }
        }
        
        // Preparar datos
        $datos = [
            'id' => $id,
            'codigo' => trim($_POST['codigo']),
            'nombre' => trim($_POST['nombre']),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio_$' => (float)$_POST['precio_$'],
            'stock' => (int)$_POST['stock'],
            'stock_minimo' => (int)$_POST['stock_minimo'],
            'categoria_id' => !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null,
            'unidad_medida' => $_POST['unidad_medida'],
            'estado' => $_POST['estado']
        ];
        
        // Manejar imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $imagen_info = $_FILES['imagen'];
            
            // Validar tipo de archivo
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($imagen_info['type'], $allowed_types)) {
                return ['success' => false, 'error' => 'Solo se permiten imágenes JPG, PNG o GIF'];
            }
            
            // Validar tamaño (máx 2MB)
            if ($imagen_info['size'] > 2 * 1024 * 1024) {
                return ['success' => false, 'error' => 'La imagen no debe superar los 2MB'];
            }
            
            // Generar nombre único para la imagen
            $extension = pathinfo($imagen_info['name'], PATHINFO_EXTENSION);
            $nombre_imagen = 'producto_' . $id . '_' . time() . '.' . $extension;
            $upload_dir = __DIR__ . '/../../uploads/products/';
            
            // Asegurar que el directorio existe
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Mover archivo
            if (move_uploaded_file($imagen_info['tmp_name'], $upload_dir . $nombre_imagen)) {
                $datos['imagen'] = $nombre_imagen;
                
                // Eliminar imagen anterior si no es default.jpg
                $model = new InventarioModel();
                $producto_actual = $model->getProductoById($id);
                if ($producto_actual['imagen'] && $producto_actual['imagen'] != 'default.jpg') {
                    $old_image_path = $upload_dir . $producto_actual['imagen'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
            }
        }
        
        // Actualizar producto
        $model = new InventarioModel();
        
        // Validar que el código no exista en otro producto
        if (!$model->codigoExiste($datos['codigo'], $id)) {
            return ['success' => false, 'error' => 'El código ya está en uso por otro producto', 'data' => $datos];
        }
        
        // Actualizar en la base de datos
        if ($model->actualizarProducto($datos)) {
            return ['success' => true, 'message' => 'Producto actualizado correctamente'];
        } else {
            return ['success' => false, 'error' => 'Error al actualizar el producto', 'data' => $datos];
        }
    }
}
?>