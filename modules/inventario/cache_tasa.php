<?php
// modules/inventario/cache_tasa.php

class TasaBCVCache {
    private $db;
    private $cache_time = 3600; // 1 hora en segundos
    private $api_url = 'https://ve.dolarapi.com/v1/dolares/oficial';
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtiene la tasa BCV actual (desde cache o API)
     */
    public function getTasa() {
        // Primero intentar obtener del cache
        $cached_rate = $this->getLatestCache();
        
        if ($cached_rate && !$this->isCacheExpired($cached_rate)) {
            error_log("Usando tasa desde cache: " . $cached_rate['tasa_usd']);
            return (float) $cached_rate['tasa_usd'];
        }
        
        // Si el cache está expirado o no existe, obtener de la API
        error_log("Cache expirado o no existe, obteniendo desde API...");
        return $this->fetchAndUpdateAPI();
    }
    
    /**
     * Obtiene el último registro del cache
     */
    private function getLatestCache() {
        $sql = "SELECT * FROM tasa_bcv_cache 
                ORDER BY fecha_consulta DESC 
                LIMIT 1";
        
        try {
            $result = $this->db->query($sql);
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener tasa desde cache: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si el cache está expirado
     */
    private function isCacheExpired($cache_data) {
        if (!$cache_data) {
            return true;
        }
        
        $cache_time = strtotime($cache_data['fecha_consulta']);
        $current_time = time();
        $elapsed_time = $current_time - $cache_time;
        
        return $elapsed_time > $this->cache_time;
    }
    
    /**
     * Obtiene la tasa desde la API y actualiza el cache existente
     */
    private function fetchAndUpdateAPI() {
        try {
            $tasa_usd = $this->fetchTasaFromAPI();
            
            if ($tasa_usd === null || $tasa_usd <= 0) {
                error_log("API devolvió tasa inválida, usando fallback");
                return $this->getFallbackRate();
            }
            
            // Actualizar o crear registro en cache
            $this->updateOrCreateCache($tasa_usd);
            
            error_log("Tasa actualizada desde API: " . $tasa_usd . " Bs/USD");
            
            return $tasa_usd;
            
        } catch (Exception $e) {
            error_log("Error fetching from API: " . $e->getMessage());
            return $this->getFallbackRate();
        }
    }
    
    /**
     * Obtiene la tasa desde la API externa
     */
    private function fetchTasaFromAPI() {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: Mozilla/5.0 (compatible; InventarioApp/1.0)'
            ]
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: " . $error_msg);
        }
        
        curl_close($ch);
        
        if ($http_code !== 200) {
            throw new Exception("API responded with code: " . $http_code);
        }
        
        $data = json_decode($response, true);
        
        if (!$data) {
            throw new Exception("Invalid API response format");
        }
        
        return $this->parseAPIResponse($data);
    }
    
    /**
     * Parsea la respuesta de la API
     */
    private function parseAPIResponse($data) {
        // Usar el promedio si está disponible
        if (isset($data['promedio']) && $data['promedio'] > 0) {
            return (float) $data['promedio'];
        }
        
        // Si no hay promedio, usar un cálculo entre compra y venta
        if (isset($data['compra']) && isset($data['venta']) && 
            $data['compra'] > 0 && $data['venta'] > 0) {
            return (float) (($data['compra'] + $data['venta']) / 2);
        }
        
        // Si solo hay venta
        if (isset($data['venta']) && $data['venta'] > 0) {
            return (float) $data['venta'];
        }
        
        // Si solo hay compra
        if (isset($data['compra']) && $data['compra'] > 0) {
            return (float) $data['compra'];
        }
        
        throw new Exception("No valid rate found in API response");
    }
    
    /**
     * Actualiza el registro existente o crea uno nuevo si no existe
     */
    private function updateOrCreateCache($tasa_usd) {
        // Primero verificar si existe un registro para hoy
        $sql_check = "SELECT id FROM tasa_bcv_cache 
                     WHERE DATE(fecha_actualizacion) = CURDATE() 
                     LIMIT 1";
        
        $result = $this->db->query($sql_check);
        
        if ($result && $result->num_rows > 0) {
            // Actualizar registro existente
            $sql = "UPDATE tasa_bcv_cache 
                    SET tasa_usd = ?, 
                        fecha_consulta = NOW(),
                        fuente = ?
                    WHERE DATE(fecha_actualizacion) = CURDATE()";
            
            $stmt = $this->db->prepare($sql);
            $fuente = 've.dolarapi.com';
            $stmt->bind_param("ds", $tasa_usd, $fuente);
            
            if ($stmt->execute()) {
                error_log("Registro actualizado en cache para hoy");
            } else {
                error_log("Error al actualizar cache: " . $stmt->error);
            }
            $stmt->close();
            
        } else {
            // Crear nuevo registro para hoy
            $sql = "INSERT INTO tasa_bcv_cache 
                    (tasa_usd, tasa_eur, fecha_consulta, fecha_actualizacion, fuente) 
                    VALUES (?, ?, NOW(), CURDATE(), ?)";
            
            $stmt = $this->db->prepare($sql);
            
            $tasa_eur = 0.0;
            $fuente = 've.dolarapi.com';
            
            $stmt->bind_param("dds", $tasa_usd, $tasa_eur, $fuente);
            
            if ($stmt->execute()) {
                error_log("Nuevo registro creado en cache para hoy");
            } else {
                error_log("Error al crear cache: " . $stmt->error);
            }
            $stmt->close();
        }
        
        // Limpiar registros antiguos (más de 30 días)
        $this->cleanOldCache(30);
    }
    
    /**
     * Limpia cache antiguo
     */
    private function cleanOldCache($days = 30) {
        $sql = "DELETE FROM tasa_bcv_cache 
                WHERE fecha_actualizacion < DATE_SUB(CURDATE(), INTERVAL ? DAY)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $days);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                error_log("Registros antiguos eliminados: " . $stmt->affected_rows);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error cleaning old cache: " . $e->getMessage());
        }
    }
    
    /**
     * Tasa de respaldo si la API falla
     */
    private function getFallbackRate() {
        $last_rate = $this->getLatestCache();
        if ($last_rate) {
            error_log("Usando tasa de cache como fallback: " . $last_rate['tasa_usd']);
            return (float) $last_rate['tasa_usd'];
        }
        
        // Si no hay nada en cache, usar tasa por defecto
        $fallback_rate = 36.50;
        error_log("Usando tasa por defecto como fallback: " . $fallback_rate);
        return $fallback_rate;
    }
    
    /**
     * Fuerza la actualización de la tasa
     */
    public function forceUpdate() {
        try {
            $tasa_usd = $this->fetchTasaFromAPI();
            
            if ($tasa_usd === null || $tasa_usd <= 0) {
                throw new Exception("API devolvió tasa inválida");
            }
            
            // Actualizar o crear registro
            $this->updateOrCreateCache($tasa_usd);
            
            return $tasa_usd;
            
        } catch (Exception $e) {
            error_log("Error en forceUpdate: " . $e->getMessage());
            return $this->getFallbackRate();
        }
    }
    
    /**
     * Obtiene el historial de tasas (solo un registro por día)
     */
    public function getHistory($days = 30) {
        $sql = "SELECT 
                    DATE(fecha_actualizacion) as fecha,
                    AVG(tasa_usd) as tasa_usd,
                    MAX(fuente) as fuente,
                    MAX(fecha_consulta) as ultima_consulta
                FROM tasa_bcv_cache 
                WHERE fecha_actualizacion >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(fecha_actualizacion)
                ORDER BY fecha DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $days);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $historial = [];
            while ($row = $result->fetch_assoc()) {
                $historial[] = $row;
            }
            
            $stmt->close();
            return $historial;
            
        } catch (Exception $e) {
            error_log("Error getting history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene estadísticas del cache
     */
    public function getCacheStats() {
        $sql = "SELECT 
                    COUNT(*) as total_registros,
                    MIN(fecha_actualizacion) as fecha_mas_antigua,
                    MAX(fecha_actualizacion) as fecha_mas_reciente,
                    COUNT(DISTINCT DATE(fecha_actualizacion)) as dias_con_registro
                FROM tasa_bcv_cache";
        
        try {
            $result = $this->db->query($sql);
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error getting cache stats: " . $e->getMessage());
            return [];
        }
    }
}
?>