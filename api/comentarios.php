<?php
/**
 * API para sistema de comentarios del blog
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Verificar CSRF
if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Token de seguridad inválido']);
    exit;
}

// Validar datos
$post_id = (int)($_POST['post_id'] ?? 0);
$nombre = sanitize($_POST['nombre'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$comentario = sanitize($_POST['comentario'] ?? '');

$errors = [];
if (!$post_id) $errors[] = 'Post no válido';
if (empty($nombre)) $errors[] = 'El nombre es requerido';
if (empty($email) || !validateEmail($email)) $errors[] = 'Email válido requerido';
if (empty($comentario)) $errors[] = 'El comentario es requerido';
if (strlen($comentario) < 10) $errors[] = 'El comentario debe tener al menos 10 caracteres';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => 'Errores de validación', 'errors' => $errors]);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Verificar que el post existe y permite comentarios
    $post = $db->fetchOne("
        SELECT id, titulo, permitir_comentarios 
        FROM blog_posts 
        WHERE id = ? AND estado = 'publicado'
    ", [$post_id]);
    
    if (!$post) {
        throw new Exception('Artículo no encontrado');
    }
    
    if (!$post['permitir_comentarios']) {
        throw new Exception('Los comentarios están deshabilitados para este artículo');
    }
    
    // Verificar límite de comentarios por IP (anti-spam)
    $ip = getClientIP();
    $comentariosRecientes = $db->count(
        'blog_comentarios', 
        'ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)', 
        [$ip]
    );
    
    if ($comentariosRecientes >= 5) {
        throw new Exception('Ha excedido el límite de comentarios. Intente más tarde.');
    }
    
    // Insertar comentario
    $comentarioId = $db->insert('blog_comentarios', [
        'post_id' => $post_id,
        'nombre' => $nombre,
        'email' => $email,
        'comentario' => $comentario,
        'aprobado' => 0, // Requiere moderación
        'ip_address' => $ip,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    // Notificar al administrador
    notifyNewComment($comentarioId, $post['titulo']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Comentario enviado. Será publicado después de ser revisado.'
    ]);
    
} catch (Exception $e) {
    error_log("Error en comentario: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function notifyNewComment($comentarioId, $postTitulo) {
    // Notificar nuevo comentario para moderación
    error_log("Nuevo comentario ID: $comentarioId en post: $postTitulo");
}