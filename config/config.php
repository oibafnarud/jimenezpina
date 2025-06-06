<?php
/**
 * Archivo de configuración
 * Generado automáticamente por el instalador
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'jpsurvey_db');
define('DB_USER', 'root');
define('DB_PASS', 'jr010101@');

// Configuración del sitio
define('SITE_URL', 'http://localhost/jimenezpina');
define('SITE_NAME', 'Jiménez & Piña Survey Instruments');
define('ADMIN_EMAIL', 'oibafnarud@gmail.com');

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('UPLOADS_URL', SITE_URL . '/uploads');
define('ADMIN_URL', SITE_URL . '/admin');

// Configuración de seguridad
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_NAME', 'jpsurvey_session');
define('SECURE_COOKIES', false);

// Configuración de archivos
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Configuración de imágenes
define('THUMB_WIDTH', 150);
define('THUMB_HEIGHT', 150);
define('MEDIUM_WIDTH', 500);
define('MEDIUM_HEIGHT', 500);

// Configuración regional
define('TIMEZONE', 'America/Santo_Domingo');
define('LOCALE', 'es_DO');
define('CURRENCY', 'RD$');
define('TAX_RATE', 0.18);

// Configuración de email
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_SECURE', 'tls');

// APIs externas
define('GOOGLE_MAPS_API_KEY', '');
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

// Modo de desarrollo
define('DEBUG_MODE', false);
define('SHOW_ERRORS', false);

// Configurar zona horaria
date_default_timezone_set(TIMEZONE);

// Configurar manejo de errores
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Incluir archivos necesarios
require_once ROOT_PATH . '/includes/database.php';
require_once ROOT_PATH . '/includes/functions.php';

// Inicializar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
