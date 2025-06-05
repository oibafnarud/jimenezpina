<?php
/**
 * Archivo de configuración principal
 * Jiménez & Piña Survey Instruments
 * 
 * Este archivo contiene todas las constantes y configuraciones globales del sistema
 */

// Prevenir acceso directo
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Modo de desarrollo (cambiar a false en producción)
define('DEVELOPMENT_MODE', true);

// Configuración de errores
if (DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuración de zona horaria
date_default_timezone_set('America/Santo_Domingo');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'jimenez_pina_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// URLs del sitio
define('SITE_URL', 'http://localhost/jimenez-pina');
define('ADMIN_URL', SITE_URL . '/admin');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/assets/uploads');

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PAGES_PATH', ROOT_PATH . '/pages');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ASSETS_PATH . '/uploads');

// Configuración de sesiones
define('SESSION_NAME', 'jp_session');
define('SESSION_LIFETIME', 7200); // 2 horas
define('REMEMBER_ME_LIFETIME', 2592000); // 30 días

// Configuración de seguridad
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos
define('PASSWORD_MIN_LENGTH', 8);

// Configuración de uploads
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);
define('IMAGE_QUALITY', 85);

// Tamaños de imágenes
define('THUMB_WIDTH', 300);
define('THUMB_HEIGHT', 300);
define('MEDIUM_WIDTH', 600);
define('MEDIUM_HEIGHT', 600);
define('LARGE_WIDTH', 1200);
define('LARGE_HEIGHT', 1200);

// Configuración de paginación
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Configuración de email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_AUTH', true);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-password');
define('FROM_EMAIL', 'info@jimenezpina.com');
define('FROM_NAME', 'Jiménez & Piña');

// Configuración de API keys (cambiar en producción)
define('GOOGLE_MAPS_API_KEY', '');
define('GOOGLE_ANALYTICS_ID', '');
define('FACEBOOK_PIXEL_ID', '');
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

// Configuración de moneda
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'USD');
define('DECIMAL_PLACES', 2);
define('THOUSANDS_SEPARATOR', ',');
define('DECIMAL_SEPARATOR', '.');

// Configuración de impuestos
define('TAX_RATE', 0.18); // 18% ITBIS
define('TAX_NAME', 'ITBIS');

// Mensajes del sistema
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'error');
define('MSG_WARNING', 'warning');
define('MSG_INFO', 'info');

// Estados de productos
define('PRODUCT_ACTIVE', 1);
define('PRODUCT_INACTIVE', 0);

// Estados de cotizaciones
define('QUOTE_DRAFT', 'borrador');
define('QUOTE_SENT', 'enviada');
define('QUOTE_APPROVED', 'aprobada');
define('QUOTE_REJECTED', 'rechazada');
define('QUOTE_EXPIRED', 'expirada');

// Estados de blog posts
define('POST_DRAFT', 'borrador');
define('POST_PUBLISHED', 'publicado');
define('POST_SCHEDULED', 'programado');

// Roles de usuario
define('ROLE_ADMIN', 'admin');
define('ROLE_EDITOR', 'editor');
define('ROLE_SALES', 'ventas');

// Configuración de caché
define('CACHE_ENABLED', !DEVELOPMENT_MODE);
define('CACHE_TIME', 3600); // 1 hora

// Configuración de logs
define('LOG_ERRORS', true);
define('LOG_PATH', ROOT_PATH . '/logs');

// Versión del sistema
define('SYSTEM_VERSION', '1.0.0');

// Cargar funciones auxiliares
require_once CONFIG_PATH . '/functions.php';
require_once CONFIG_PATH . '/database.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Establecer configuración de caracteres
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Configurar el manejo de errores personalizado
if (!DEVELOPMENT_MODE) {
    set_error_handler('custom_error_handler');
    set_exception_handler('custom_exception_handler');
}