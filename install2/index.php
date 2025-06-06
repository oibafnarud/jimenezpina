<?php
/**
 * Instalador del Sistema
 * /install/index.php
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('INSTALLER_VERSION', '1.0');
define('MIN_PHP_VERSION', '7.4');
define('MIN_MYSQL_VERSION', '5.7');

// Verificar si ya está instalado
if (file_exists('../config/installed.lock')) {
    die('El sistema ya está instalado. Por seguridad, elimine la carpeta /install/');
}

$step = $_GET['step'] ?? 1;
$errors = [];
$success = false;

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? 1;
    
    switch($step) {
        case 2: // Configuración de base de datos
            testDatabaseConnection();
            break;
        case 3: // Configuración del sitio
            setupDatabase();
            break;
        case 4: // Crear administrador
            createAdminUser();
            break;
    }
}

function testDatabaseConnection() {
    global $errors, $step;
    
    $host = $_POST['db_host'];
    $name = $_POST['db_name'];
    $user = $_POST['db_user'];
    $pass = $_POST['db_pass'];
    
    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crear base de datos si no existe
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Guardar en sesión
        $_SESSION['db_config'] = [
            'host' => $host,
            'name' => $name,
            'user' => $user,
            'pass' => $pass
        ];
        
        $step = 3;
    } catch(PDOException $e) {
        $errors[] = "Error de conexión: " . $e->getMessage();
    }
}

function setupDatabase() {
    global $errors, $step;
    
    if (!isset($_SESSION['db_config'])) {
        $errors[] = "Configuración de base de datos no encontrada";
        return;
    }
    
    $config = $_SESSION['db_config'];
    
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['name']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Leer y limpiar el archivo SQL
        $sql = file_get_contents(__DIR__ . '/database.sql');
        
        // Remover comentarios de múltiples líneas
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Remover el USE statement
        $sql = preg_replace('/USE\s+`?\w+`?;/i', '', $sql);
        
        // Remover líneas vacías y comentarios de línea
        $lines = explode("\n", $sql);
        $cleanLines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !preg_match('/^--/', $line)) {
                $cleanLines[] = $line;
            }
        }
        $sql = implode("\n", $cleanLines);
        
        // Dividir por punto y coma, pero no dentro de strings
        $statements = preg_split('/;(?=([^\'"]*(\'|")[^\'"]*(\'|"))*[^\'"]*$)/', $sql);
        
        $successCount = 0;
        $totalStatements = count($statements);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                    $successCount++;
                } catch (PDOException $e) {
                    // Ignorar errores de "IF NOT EXISTS" para vistas
                    if (strpos($e->getMessage(), '1050') === false) { // Table already exists
                        error_log("SQL Error: " . $e->getMessage() . " in statement: " . substr($statement, 0, 100));
                    }
                }
            }
        }
        
        // Guardar configuración del sitio
        $_SESSION['site_config'] = [
            'site_name' => $_POST['site_name'],
            'site_url' => $_POST['site_url'],
            'admin_email' => $_POST['admin_email']
        ];
        
        $step = 4;
        
        // Log del progreso
        error_log("Base de datos configurada: $successCount de $totalStatements statements ejecutados");
        
    } catch(PDOException $e) {
        $errors[] = "Error al configurar la base de datos: " . $e->getMessage();
        error_log("Error crítico en BD: " . $e->getMessage());
    }
}

function createAdminUser() {
    global $errors, $success;
    
    if (!isset($_SESSION['db_config']) || !isset($_SESSION['site_config'])) {
        $errors[] = "Configuración incompleta";
        return;
    }
    
    $dbConfig = $_SESSION['db_config'];
    $siteConfig = $_SESSION['site_config'];
    
    // Validar datos
    if (empty($_POST['admin_name']) || empty($_POST['admin_email']) || empty($_POST['admin_password'])) {
        $errors[] = "Todos los campos son requeridos";
        return;
    }
    
    if (strlen($_POST['admin_password']) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres";
        return;
    }
    
    if ($_POST['admin_password'] !== $_POST['admin_password_confirm']) {
        $errors[] = "Las contraseñas no coinciden";
        return;
    }
    
    try {
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8mb4",
            $dbConfig['user'],
            $dbConfig['pass']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Actualizar usuario admin
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET nombre = ?, email = ?, password = ? 
            WHERE id = 1
        ");
        
        $hashedPassword = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
        $stmt->execute([
            $_POST['admin_name'],
            $_POST['admin_email'],
            $hashedPassword
        ]);
        
        // Actualizar configuración
        $updates = [
            'site_name' => $siteConfig['site_name'],
            'site_url' => $siteConfig['site_url'],
            'admin_email' => $siteConfig['admin_email']
        ];
        
        foreach ($updates as $key => $value) {
            $stmt = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
            $stmt->execute([$value, $key]);
        }
        
        // Crear archivo de configuración
        createConfigFile();
        
        // Crear archivo de bloqueo
        file_put_contents('../config/installed.lock', date('Y-m-d H:i:s'));
        
        $success = true;
        
    } catch(Exception $e) {
        $errors[] = "Error al crear usuario: " . $e->getMessage();
    }
}

function createConfigFile() {
    $dbConfig = $_SESSION['db_config'];
    $siteConfig = $_SESSION['site_config'];
    
    $configContent = "<?php
/**
 * Archivo de configuración
 * Generado automáticamente por el instalador
 */

// Configuración de la base de datos
define('DB_HOST', '{$dbConfig['host']}');
define('DB_NAME', '{$dbConfig['name']}');
define('DB_USER', '{$dbConfig['user']}');
define('DB_PASS', '{$dbConfig['pass']}');

// Configuración del sitio
define('SITE_URL', '{$siteConfig['site_url']}');
define('SITE_NAME', '{$siteConfig['site_name']}');
define('ADMIN_EMAIL', '{$siteConfig['admin_email']}');

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('UPLOADS_URL', SITE_URL . '/uploads');
define('ADMIN_URL', SITE_URL . '/admin');

// Configuración de seguridad
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_NAME', 'jpsurvey_session');
define('SECURE_COOKIES', " . (strpos($siteConfig['site_url'], 'https://') === 0 ? 'true' : 'false') . ");

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
";

    file_put_contents('../config/config.php', $configContent);
}

// Verificaciones del sistema
function checkRequirements() {
    $requirements = [];
    
    // PHP Version
    $requirements['php'] = [
        'name' => 'PHP ' . MIN_PHP_VERSION . '+',
        'current' => PHP_VERSION,
        'passed' => version_compare(PHP_VERSION, MIN_PHP_VERSION, '>=')
    ];
    
    // Extensiones PHP
    $extensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'json', 'curl'];
    foreach ($extensions as $ext) {
        $requirements[$ext] = [
            'name' => "PHP $ext",
            'current' => extension_loaded($ext) ? 'Instalado' : 'No instalado',
            'passed' => extension_loaded($ext)
        ];
    }
    
    // Permisos de escritura
    $writableDirs = ['../config', '../uploads', '../cache', '../backups'];
    foreach ($writableDirs as $dir) {
        if (!file_exists($dir)) {
            @mkdir($dir, 0755, true);
        }
        $requirements['write_' . basename($dir)] = [
            'name' => "Escritura en /$dir",
            'current' => is_writable($dir) ? 'Escribible' : 'No escribible',
            'passed' => is_writable($dir)
        ];
    }
    
    return $requirements;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Jiménez & Piña Survey Instruments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .installer-container {
            max-width: 800px;
            margin: 50px auto;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            position: relative;
        }
        .step.active {
            background: #0d6efd;
            color: white;
        }
        .step.completed {
            background: #198754;
            color: white;
        }
        .requirement-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .requirement-item.failed {
            background: #f8d7da;
        }
        .requirement-item.passed {
            background: #d1e7dd;
        }
    </style>
</head>
<body>
    <div class="container installer-container">
        <h1 class="text-center mb-4">
            <i class="bi bi-gear-fill"></i> Instalación del Sistema
        </h1>
        
        <!-- Indicador de pasos -->
        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                <i class="bi bi-check-circle"></i> Verificación
            </div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                <i class="bi bi-database"></i> Base de Datos
            </div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">
                <i class="bi bi-gear"></i> Configuración
            </div>
            <div class="step <?php echo $step >= 4 ? 'active' : ''; ?> <?php echo $step > 4 ? 'completed' : ''; ?>">
                <i class="bi bi-person"></i> Administrador
            </div>
            <div class="step <?php echo $success ? 'completed' : ''; ?>">
                <i class="bi bi-check-all"></i> Finalizar
            </div>
        </div>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <h5>Se encontraron errores:</h5>
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <!-- Paso 5: Instalación completada -->
        <div class="card">
            <div class="card-body text-center">
                <h2 class="text-success">
                    <i class="bi bi-check-circle-fill"></i> ¡Instalación Completada!
                </h2>
                <p class="lead">El sistema se ha instalado correctamente.</p>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Importante:</strong> Por seguridad, elimine la carpeta <code>/install/</code> del servidor.
                </div>
                
                <div class="mt-4">
                    <a href="../" class="btn btn-primary btn-lg me-2">
                        <i class="bi bi-house"></i> Ir al Sitio
                    </a>
                    <a href="../admin" class="btn btn-secondary btn-lg">
                        <i class="bi bi-speedometer2"></i> Panel Admin
                    </a>
                </div>
                
                <hr>
                
                <h5>Credenciales de Acceso:</h5>
                <p>
                    <strong>Email:</strong> <?php echo $_POST['admin_email']; ?><br>
                    <strong>Contraseña:</strong> La que configuró
                </p>
            </div>
        </div>
        
        <?php elseif ($step == 1): ?>
        <!-- Paso 1: Verificación de requisitos -->
        <div class="card">
            <div class="card-header">
                <h3>Verificación de Requisitos</h3>
            </div>
            <div class="card-body">
                <?php 
                $requirements = checkRequirements();
                $allPassed = true;
                ?>
                
                <?php foreach ($requirements as $req): ?>
                <div class="requirement-item <?php echo $req['passed'] ? 'passed' : 'failed'; ?>">
                    <span>
                        <?php echo $req['name']; ?>
                        <?php if (!$req['passed']): $allPassed = false; endif; ?>
                    </span>
                    <span>
                        <?php echo $req['current']; ?>
                        <?php if ($req['passed']): ?>
                        <i class="bi bi-check-circle text-success"></i>
                        <?php else: ?>
                        <i class="bi bi-x-circle text-danger"></i>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endforeach; ?>
                
                <div class="mt-4">
                    <?php if ($allPassed): ?>
                    <form method="GET">
                        <input type="hidden" name="step" value="2">
                        <button type="submit" class="btn btn-primary">
                            Continuar <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-danger">
                        Corrija los requisitos faltantes antes de continuar.
                    </div>
                    <button class="btn btn-secondary" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Verificar Nuevamente
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php elseif ($step == 2): ?>
        <!-- Paso 2: Configuración de base de datos -->
        <div class="card">
            <div class="card-header">
                <h3>Configuración de Base de Datos</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="step" value="2">
                    
                    <div class="mb-3">
                        <label class="form-label">Servidor MySQL</label>
                        <input type="text" class="form-control" name="db_host" 
                               value="<?php echo $_POST['db_host'] ?? 'localhost'; ?>" required>
                        <div class="form-text">Generalmente es 'localhost'</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Base de Datos</label>
                        <input type="text" class="form-control" name="db_name" 
                               value="<?php echo $_POST['db_name'] ?? 'jpsurvey_db'; ?>" required>
                        <div class="form-text">Se creará si no existe</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Usuario MySQL</label>
                        <input type="text" class="form-control" name="db_user" 
                               value="<?php echo $_POST['db_user'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contraseña MySQL</label>
                        <input type="password" class="form-control" name="db_pass">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Probar Conexión <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <?php elseif ($step == 3): ?>
        <!-- Paso 3: Configuración del sitio -->
        <div class="card">
            <div class="card-header">
                <h3>Configuración del Sitio</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="step" value="3">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre del Sitio</label>
                        <input type="text" class="form-control" name="site_name" 
                               value="<?php echo $_POST['site_name'] ?? 'Jiménez & Piña Survey Instruments'; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL del Sitio</label>
                        <input type="url" class="form-control" name="site_url" 
                               value="<?php echo $_POST['site_url'] ?? 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI'])); ?>" required>
                        <div class="form-text">Sin barra diagonal al final</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email Administrativo</label>
                        <input type="email" class="form-control" name="admin_email" 
                               value="<?php echo $_POST['admin_email'] ?? ''; ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Configurar Base de Datos <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <?php elseif ($step == 4): ?>
        <!-- Paso 4: Crear administrador -->
        <div class="card">
            <div class="card-header">
                <h3>Crear Usuario Administrador</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="step" value="4">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="admin_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="admin_email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="admin_password" required minlength="8">
                        <div class="form-text">Mínimo 8 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" name="admin_password_confirm" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Finalizar Instalación <i class="bi bi-check-circle"></i>
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>