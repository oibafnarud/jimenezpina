<?php
/**
 * API para actualizar orden de elementos
 * /admin/api/update-orden.php
 */
require_once '../../config/config.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isAuthenticated()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Obtener datos JSON
$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!isset($data['tabla']) || !isset($data['id']) || !isset($data['orden'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

// Validar CSRF token si está presente
if (isset($data['csrf_token']) && !verifyCSRFToken($data['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Token de seguridad inválido']);
    exit;
}

// Tablas permitidas
$tablasPermitidas = ['categorias', 'marcas', 'productos'];
if (!in_array($data['tabla'], $tablasPermitidas)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tabla no permitida']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Actualizar orden
    $db->update(
        $data['tabla'], 
        ['orden' => (int)$data['orden']], 
        'id = ?', 
        [(int)$data['id']]
    );
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Error actualizando orden: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar']);
}