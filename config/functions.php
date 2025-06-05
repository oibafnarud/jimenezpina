<?php
/**
 * Funciones auxiliares globales
 * Jiménez & Piña Survey Instruments
 */

/**
 * Sanitizar entrada de datos
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generar slug desde string
 */
function createSlug($string) {
    $slug = mb_strtolower($string, 'UTF-8');
    $slug = preg_replace('/[áàäâã]/u', 'a', $slug);
    $slug = preg_replace('/[éèëê]/u', 'e', $slug);
    $slug = preg_replace('/[íìïî]/u', 'i', $slug);
    $slug = preg_replace('/[óòöôõ]/u', 'o', $slug);
    $slug = preg_replace('/[úùüû]/u', 'u', $slug);
    $slug = preg_replace('/[ñ]/u', 'n', $slug);
    $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Formatear precio
 */
function formatPrice($price, $withSymbol = true) {
    $formatted = number_format($price, DECIMAL_PLACES, DECIMAL_SEPARATOR, THOUSANDS_SEPARATOR);
    return $withSymbol ? CURRENCY_SYMBOL . $formatted : $formatted;
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formatear fecha para humanos
 */
function formatDateHuman($date) {
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'hace un momento';
    if ($diff < 3600) return 'hace ' . floor($diff / 60) . ' minutos';
    if ($diff < 86400) return 'hace ' . floor($diff / 3600) . ' horas';
    if ($diff < 604800) return 'hace ' . floor($diff / 86400) . ' días';
    
    return formatDate($timestamp);
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    if (empty($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Campo CSRF para formularios
 */
function csrfField() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCSRFToken() . '">';
}

/**
 * Verificar si el usuario está autenticado
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verificar rol de usuario
 */
function hasRole($role) {
    return isAuthenticated() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Verificar permisos
 */
function canAccess($permission) {
    if (!isAuthenticated()) return false;
    
    $permissions = [
        'admin' => ['all'],
        'editor' => ['products', 'blog', 'pages', 'media'],
        'ventas' => ['quotes', 'customers', 'reports']
    ];
    
    $userRole = $_SESSION['user_role'] ?? '';
    
    if ($userRole === 'admin' || in_array('all', $permissions[$userRole] ?? [])) {
        return true;
    }
    
    return in_array($permission, $permissions[$userRole] ?? []);
}

/**
 * Redirigir con mensaje
 */
function redirect($url, $message = null, $type = MSG_INFO) {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}

/**
 * Mostrar mensaje flash
 */
function flashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? MSG_INFO;
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        $alertClass = [
            MSG_SUCCESS => 'success',
            MSG_ERROR => 'danger',
            MSG_WARNING => 'warning',
            MSG_INFO => 'info'
        ][$type] ?? 'info';
        
        return '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show" role="alert">
                    ' . $message . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    }
    
    return '';
}

/**
 * Subir archivo
 */
function uploadFile($file, $directory, $allowedTypes = null) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error al subir el archivo'];
    }
    
    // Verificar tamaño
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'El archivo excede el tamaño máximo permitido'];
    }
    
    // Verificar tipo de archivo
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($allowedTypes && !in_array($extension, $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
    }
    
    // Generar nombre único
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = UPLOADS_PATH . '/' . $directory;
    
    // Crear directorio si no existe
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    $fullPath = $uploadPath . '/' . $filename;
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $directory . '/' . $filename,
            'url' => UPLOADS_URL . '/' . $directory . '/' . $filename
        ];
    }
    
    return ['success' => false, 'message' => 'Error al mover el archivo'];
}

/**
 * Redimensionar imagen
 */
function resizeImage($sourcePath, $targetPath, $maxWidth, $maxHeight, $quality = IMAGE_QUALITY) {
    list($width, $height, $type) = getimagesize($sourcePath);
    
    // Calcular nuevas dimensiones
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);
    
    // Crear imagen desde el archivo
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }
    
    // Crear nueva imagen
    $target = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preservar transparencia para PNG
    if ($type == IMAGETYPE_PNG) {
        imagealphablending($target, false);
        imagesavealpha($target, true);
    }
    
    // Redimensionar
    imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Guardar imagen
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($target, $targetPath, $quality);
            break;
        case IMAGETYPE_PNG:
            imagepng($target, $targetPath, 9 - round($quality / 10));
            break;
        case IMAGETYPE_GIF:
            imagegif($target, $targetPath);
            break;
    }
    
    // Liberar memoria
    imagedestroy($source);
    imagedestroy($target);
    
    return true;
}

/**
 * Eliminar archivo
 */
function deleteFile($path) {
    $fullPath = UPLOADS_PATH . '/' . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Obtener IP del cliente
 */
function getClientIP() {
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, 
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Registrar actividad
 */
function logActivity($type, $description, $table = null, $recordId = null) {
    $db = Database::getInstance();
    
    $data = [
        'usuario_id' => $_SESSION['user_id'] ?? null,
        'tipo' => $type,
        'descripcion' => $description,
        'tabla_afectada' => $table,
        'registro_id' => $recordId,
        'ip_address' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];
    
    $db->insert('actividad_log', $data);
}

/**
 * Paginar resultados
 */
function paginate($totalItems, $currentPage = 1, $itemsPerPage = ITEMS_PER_PAGE, $urlPattern = '?page=(:num)') {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    
    $pagination = [
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => ($currentPage - 1) * $itemsPerPage,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'previous_page' => $currentPage - 1,
        'next_page' => $currentPage + 1,
        'pages' => []
    ];
    
    // Generar array de páginas para mostrar
    $range = 2; // Páginas a mostrar antes y después de la actual
    for ($i = max(1, $currentPage - $range); $i <= min($totalPages, $currentPage + $range); $i++) {
        $pagination['pages'][] = [
            'number' => $i,
            'url' => str_replace('(:num)', $i, $urlPattern),
            'is_current' => $i == $currentPage
        ];
    }
    
    return $pagination;
}

/**
 * Generar meta tags SEO
 */
function generateMetaTags($title = '', $description = '', $keywords = '', $image = '') {
    $siteName = getSetting('site_name', 'Jiménez & Piña');
    $title = $title ? "$title | $siteName" : $siteName;
    $description = $description ?: getSetting('site_description', '');
    
    $tags = [
        '<meta charset="UTF-8">',
        '<meta name="viewport" content="width=device-width, initial-scale=1.0">',
        '<title>' . sanitize($title) . '</title>',
        '<meta name="description" content="' . sanitize($description) . '">',
    ];
    
    if ($keywords) {
        $tags[] = '<meta name="keywords" content="' . sanitize($keywords) . '">';
    }
    
    // Open Graph tags
    $tags[] = '<meta property="og:title" content="' . sanitize($title) . '">';
    $tags[] = '<meta property="og:description" content="' . sanitize($description) . '">';
    $tags[] = '<meta property="og:type" content="website">';
    $tags[] = '<meta property="og:url" content="' . getCurrentUrl() . '">';
    
    if ($image) {
        $tags[] = '<meta property="og:image" content="' . $image . '">';
    }
    
    // Twitter Card tags
    $tags[] = '<meta name="twitter:card" content="summary_large_image">';
    $tags[] = '<meta name="twitter:title" content="' . sanitize($title) . '">';
    $tags[] = '<meta name="twitter:description" content="' . sanitize($description) . '">';
    
    if ($image) {
        $tags[] = '<meta name="twitter:image" content="' . $image . '">';
    }
    
    return implode("\n    ", $tags);
}

/**
 * Obtener URL actual
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Obtener configuración del sitio
 */
function getSetting($key, $default = '') {
    static $settings = null;
    
    if ($settings === null) {
        $db = Database::getInstance();
        $results = $db->fetchAll("SELECT clave, valor FROM configuracion");
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['clave']] = $row['valor'];
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * Validar y limpiar número de teléfono
 */
function cleanPhone($phone) {
    return preg_replace('/[^0-9+]/', '', $phone);
}

/**
 * Generar número de cotización
 */
function generateQuoteNumber() {
    $db = Database::getInstance();
    $year = date('Y');
    $month = date('m');
    
    // Obtener último número del mes actual
    $lastNumber = $db->fetchColumn(
        "SELECT MAX(CAST(SUBSTRING(numero, -4) AS UNSIGNED)) 
         FROM cotizaciones 
         WHERE numero LIKE ?",
        ["COT-$year$month-%"]
    );
    
    $nextNumber = ($lastNumber ?: 0) + 1;
    
    return sprintf("COT-%s%s-%04d", $year, $month, $nextNumber);
}

/**
 * Manejador de errores personalizado
 */
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    $error = date('Y-m-d H:i:s') . " - Error [$errno]: $errstr in $errfile on line $errline\n";
    
    if (LOG_ERRORS) {
        error_log($error, 3, LOG_PATH . '/error.log');
    }
    
    if (DEVELOPMENT_MODE) {
        echo "<div style='background:#f8d7da;color:#721c24;padding:1rem;margin:1rem;border:1px solid #f5c6cb;border-radius:4px;'>
                <strong>Error:</strong> $errstr<br>
                <strong>File:</strong> $errfile<br>
                <strong>Line:</strong> $errline
              </div>";
    }
    
    return true;
}

/**
 * Manejador de excepciones personalizado
 */
function custom_exception_handler($exception) {
    $error = date('Y-m-d H:i:s') . " - Exception: " . $exception->getMessage() . 
             " in " . $exception->getFile() . " on line " . $exception->getLine() . 
             "\nStack trace:\n" . $exception->getTraceAsString() . "\n";
    
    if (LOG_ERRORS) {
        error_log($error, 3, LOG_PATH . '/error.log');
    }
    
    if (DEVELOPMENT_MODE) {
        echo "<div style='background:#f8d7da;color:#721c24;padding:1rem;margin:1rem;border:1px solid #f5c6cb;border-radius:4px;'>
                <strong>Exception:</strong> " . $exception->getMessage() . "<br>
                <strong>File:</strong> " . $exception->getFile() . "<br>
                <strong>Line:</strong> " . $exception->getLine() . "<br>
                <strong>Trace:</strong><pre>" . $exception->getTraceAsString() . "</pre>
              </div>";
    } else {
        redirect(SITE_URL . '/error.php', 'Ha ocurrido un error inesperado', MSG_ERROR);
    }
}