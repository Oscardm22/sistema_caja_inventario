<?php
// modules/inventario/cache_tasa.php

class TasaBCVCache {
    private $db;
    private $cache_time = 3600; // 1 hora en segundos
    private $bcv_url = 'http://www.bcv.org.ve/';
    private $user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    ];
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtiene la tasa BCV actual (desde cache o scraping)
     */
    public function getTasa() {
        // Primero intentar obtener del cache
        $cached_rate = $this->getLatestCache();
        
        if ($cached_rate && !$this->isCacheExpired($cached_rate)) {
            error_log("Usando tasa desde cache: " . $cached_rate['tasa_usd']);
            return (float) $cached_rate['tasa_usd'];
        }
        
        // Si el cache está expirado o no existe, hacer scraping
        error_log("Cache expirado o no existe, haciendo scraping del BCV...");
        return $this->scrapeAndUpdate();
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
     * Realiza el scraping de la página del BCV
     */
    private function scrapeBCV() {
        // Seleccionar User-Agent aleatorio
        $user_agent = $this->user_agents[array_rand($this->user_agents)];
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->bcv_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $user_agent,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'
            ]
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error al conectar con BCV: " . $error_msg);
        }
        
        curl_close($ch);
        
        if ($http_code !== 200) {
            throw new Exception("BCV responded with code: " . $http_code);
        }
        
        return $response;
    }
    
    /**
     * Extrae la tasa del dolar del HTML del BCV
     */
    private function extractTasaFromHTML($html) {
        if (!$html) {
            throw new Exception("HTML vacío recibido del BCV");
        }
        
        // Crear un DOM document para parsear el HTML
        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // Suprimir warnings de HTML mal formado
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Intentar diferentes selectores XPath que podrían contener la tasa
        
        // Selector 1: Buscar por el ID específico (basado en la estructura del BCV)
        $elements = $xpath->query("//div[contains(@id, 'dolar')]//strong");
        if ($elements->length > 0) {
            $tasa_text = trim($elements->item(0)->nodeValue);
            return $this->parseTasaText($tasa_text);
        }
        
        // Selector 2: Buscar por clase específica
        $elements = $xpath->query("//div[contains(@class, 'dolar')]//strong");
        if ($elements->length > 0) {
            $tasa_text = trim($elements->item(0)->nodeValue);
            return $this->parseTasaText($tasa_text);
        }
        
        // Selector 3: Buscar en la tabla de tasas (estructura común del BCV)
        $elements = $xpath->query("//table//tr[td[contains(text(), 'USD')]]/td[2]");
        if ($elements->length > 0) {
            $tasa_text = trim($elements->item(0)->nodeValue);
            return $this->parseTasaText($tasa_text);
        }
        
        // Selector 4: Buscar usando expresiones regulares como fallback
        $patterns = [
            '/<strong>([0-9]+\.[0-9]+)<\/strong>/',
            '/dolar.*?([0-9]+\.[0-9]+)/i',
            '/USD.*?([0-9]+\.[0-9]+)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                if (isset($matches[1])) {
                    return (float) $matches[1];
                }
            }
        }
        
        throw new Exception("No se pudo extraer la tasa del HTML del BCV");
    }
    
    /**
     * Parsea el texto de la tasa a número
     */
    private function parseTasaText($text) {
        // Limpiar el texto: eliminar espacios, convertir coma a punto si es necesario
        $text = preg_replace('/[^0-9.,]/', '', $text);
        
        // Manejar formato con coma decimal (ej: 36,50)
        if (strpos($text, ',') !== false && strpos($text, '.') === false) {
            $text = str_replace(',', '.', $text);
        }
        
        // Manejar formato con punto de miles y coma decimal (ej: 1.234,56)
        if (strpos($text, '.') !== false && strpos($text, ',') !== false) {
            // Asumimos que el último punto o coma es el decimal
            if (strrpos($text, ',') > strrpos($text, '.')) {
                // Formato europeo: punto como separador de miles, coma decimal
                $text = str_replace('.', '', $text);
                $text = str_replace(',', '.', $text);
            } else {
                // Formato US: coma como separador de miles, punto decimal
                $text = str_replace(',', '', $text);
            }
        }
        
        return (float) $text;
    }
    
    /**
     * Realiza el scraping y actualiza el cache
     */
    private function scrapeAndUpdate() {
        try {
            // Intentar scraping hasta 3 veces
            $max_attempts = 3;
            $attempt = 1;
            $tasa_usd = null;
            
            while ($attempt <= $max_attempts && $tasa_usd === null) {
                try {
                    error_log("Intento $attempt de scraping al BCV");
                    $html = $this->scrapeBCV();
                    $tasa_usd = $this->extractTasaFromHTML($html);
                } catch (Exception $e) {
                    error_log("Intento $attempt falló: " . $e->getMessage());
                    if ($attempt < $max_attempts) {
                        sleep(2); // Esperar 2 segundos antes de reintentar
                    }
                }
                $attempt++;
            }
            
            if ($tasa_usd === null || $tasa_usd <= 0) {
                error_log("No se pudo obtener tasa del BCV después de $max_attempts intentos, usando fallback");
                return $this->getFallbackRate();
            }
            
            // Actualizar o crear registro en cache
            $this->updateOrCreateCache($tasa_usd);
            
            error_log("Tasa obtenida del BCV mediante scraping: " . $tasa_usd . " Bs/USD");
            
            return $tasa_usd;
            
        } catch (Exception $e) {
            error_log("Error en scraping del BCV: " . $e->getMessage());
            return $this->getFallbackRate();
        }
    }
    
    /**
     * Actualiza el registro existente o crea uno nuevo
     */
    private function updateOrCreateCache($tasa_usd) {
        // Verificar si existe un registro para hoy
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
            $fuente = 'bcv.gob.ve (scraping)';
            $stmt->bind_param("ds", $tasa_usd, $fuente);
            
            if ($stmt->execute()) {
                error_log("Registro actualizado en cache para hoy");
            } else {
                error_log("Error al actualizar cache: " . $stmt->error);
            }
            $stmt->close();
            
        } else {
            // Crear nuevo registro
            $sql = "INSERT INTO tasa_bcv_cache 
                    (tasa_usd, tasa_eur, fecha_consulta, fecha_actualizacion, fuente) 
                    VALUES (?, ?, NOW(), CURDATE(), ?)";
            
            $stmt = $this->db->prepare($sql);
            
            $tasa_eur = 0.0;
            $fuente = 'bcv.gob.ve (scraping)';
            
            $stmt->bind_param("dds", $tasa_usd, $tasa_eur, $fuente);
            
            if ($stmt->execute()) {
                error_log("Nuevo registro creado en cache para hoy");
            } else {
                error_log("Error al crear cache: " . $stmt->error);
            }
            $stmt->close();
        }
        
        // Limpiar registros antiguos
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
     * Tasa de respaldo si el scraping falla
     */
    private function getFallbackRate() {
        $last_rate = $this->getLatestCache();
        if ($last_rate) {
            error_log("Usando tasa de cache como fallback: " . $last_rate['tasa_usd']);
            return (float) $last_rate['tasa_usd'];
        }
        
        // Intentar obtener de fuente alternativa (dolarapi.com como último recurso)
        try {
            error_log("Intentando dolarapi.com como fallback...");
            $tasa_alternativa = $this->fetchFromDolarAPI();
            if ($tasa_alternativa > 0) {
                return $tasa_alternativa;
            }
        } catch (Exception $e) {
            error_log("Fallback de dolarapi también falló: " . $e->getMessage());
        }
        
        // Si todo falla, usar tasa por defecto
        $fallback_rate = 36.50;
        error_log("Usando tasa por defecto como fallback: " . $fallback_rate);
        return $fallback_rate;
    }
    
    /**
     * Fallback a dolarapi.com si BCV no funciona
     */
    private function fetchFromDolarAPI() {
        $api_url = 'https://ve.dolarapi.com/v1/dolares/oficial';
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            if (isset($data['promedio'])) {
                return (float) $data['promedio'];
            }
        }
        
        return 0;
    }
    
    /**
     * Fuerza la actualización mediante scraping
     */
    public function forceUpdate() {
        try {
            $tasa_usd = null;
            $html = $this->scrapeBCV();
            $tasa_usd = $this->extractTasaFromHTML($html);
            
            if ($tasa_usd === null || $tasa_usd <= 0) {
                throw new Exception("Scraping devolvió tasa inválida");
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
     * Obtiene el historial de tasas
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