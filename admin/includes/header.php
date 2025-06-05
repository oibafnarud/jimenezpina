<?php
/**
 * Sistema de Autenticación - Middleware
 * Jiménez & Piña Survey Instruments
 */

// Incluir configuración si no está cargada
if (!defined('SITE_URL')) {
    require_once dirname(dirname(__DIR__)) . '/config/config.php';
}

// Verificar si el usuario está autenticado
if (!isAuthenticated()) {
    $currentUrl = urlencode(getCurrentUrl());
    redirect(ADMIN_URL . '/index.php?redirect=' . $currentUrl, 'Debe iniciar sesión para acceder a esta página', MSG_WARNING);
    exit;
}

// Verificar timeout de sesión
if (isset($_SESSION['login_time'])) {
    $sessionLifetime = time() - $_SESSION['login_time'];
    if ($sessionLifetime > SESSION_LIFETIME) {
        // Cerrar sesión por inactividad
        session_destroy();
        redirect(ADMIN_URL . '/index.php', 'Su sesión ha expirado por inactividad', MSG_WARNING);
        exit;
    }
    // Actualizar tiempo de actividad
    $_SESSION['login_time'] = time();
}

// Verificar permisos según la página actual
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$requiredPermissions = [
    'productos' => ['admin', 'editor'],
    'categorias' => ['admin', 'editor'],
    'blog' => ['admin', 'editor'],
    'cotizaciones' => ['admin', 'ventas'],
    'clientes' => ['admin', 'ventas'],
    'usuarios' => ['admin'],
    'configuracion' => ['admin']
];

// Verificar si la página requiere permisos específicos
if (isset($requiredPermissions[$currentPage])) {
    $allowedRoles = $requiredPermissions[$currentPage];
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        redirect(ADMIN_URL . '/dashboard.php', 'No tiene permisos para acceder a esta sección', MSG_ERROR);
        exit;
    }
}

// Funciones auxiliares para el admin
function getAdminUser() {
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}

function isAdmin() {
    return $_SESSION['user_role'] === 'admin';
}

function isEditor() {
    return $_SESSION['user_role'] === 'editor';
}

function isSales() {
    return $_SESSION['user_role'] === 'ventas';
}

function canEdit($section) {
    $permissions = [
        'products' => ['admin', 'editor'],
        'blog' => ['admin', 'editor'],
        'quotes' => ['admin', 'ventas'],
        'customers' => ['admin', 'ventas'],
        'users' => ['admin'],
        'settings' => ['admin']
    ];
    
    return isset($permissions[$section]) && in_array($_SESSION['user_role'], $permissions[$section]);
}

// Logout function
function logout() {
    // Registrar actividad
    if (isset($_SESSION['user_id'])) {
        logActivity('logout', 'Cierre de sesión', 'usuarios', $_SESSION['user_id']);
    }
    
    // Destruir sesión
    $_SESSION = [];
    
    // Eliminar cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Eliminar remember me cookie
    setcookie('remember_token', '', time() - 3600, '/');
    
    // Destruir la sesión
    session_destroy();
    
    // Redirigir al login
    redirect(ADMIN_URL . '/index.php?logout=1');
}