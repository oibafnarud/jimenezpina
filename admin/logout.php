<?php
/**
 * Logout - Cerrar Sesión
 * Jiménez & Piña Survey Instruments
 */
session_start();
require_once '../config/config.php';

// Registrar actividad antes de cerrar sesión
if (isset($_SESSION['user_id'])) {
    logActivity('logout', 'Cierre de sesión', 'usuarios', $_SESSION['user_id']);
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la cookie remember me si existe
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir al login con mensaje
header("Location: " . ADMIN_URL . "/index.php?logout=1");
exit;