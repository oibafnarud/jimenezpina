<?php
/**
 * Sistema de upload de imágenes
 * /admin/upload.php
 */
require_once 'includes/header.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Verificar permisos
if (!isAuthenticated()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir el archivo');
    }
    
    $file = $_FILES['file'];
    $directory = $_POST['directory'] ?? 'general';
    
    // Validar tipo de archivo
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedTypes = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOCUMENT_TYPES);
    
    if (!in_array($extension, $allowedTypes)) {
        throw new Exception('Tipo de archivo no permitido');
    }
    
    // Validar tamaño
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('El archivo excede el tamaño máximo permitido');
    }
    
    // Subir archivo
    $result = uploadFile($file, $directory, $allowedTypes);
    
    if (!$result['success']) {
        throw new Exception($result['message']);
    }
    
    // Si es imagen, crear thumbnails
    if (in_array($extension, ALLOWED_IMAGE_TYPES)) {
        $sourcePath = UPLOADS_PATH . '/' . $result['path'];
        
        // Thumbnail pequeño
        $thumbPath = UPLOADS_PATH . '/' . $directory . '/thumb_' . $result['filename'];
        resizeImage($sourcePath, $thumbPath, THUMB_WIDTH, THUMB_HEIGHT);
        
        // Imagen mediana
        $mediumPath = UPLOADS_PATH . '/' . $directory . '/medium_' . $result['filename'];
        resizeImage($sourcePath, $mediumPath, MEDIUM_WIDTH, MEDIUM_HEIGHT);
    }
    
    // Registrar en base de datos si es necesario
    logActivity('file_uploaded', "Archivo subido: {$result['filename']}", 'uploads');
    
    echo json_encode([
        'success' => true,
        'location' => $result['url'],
        'filename' => $result['filename'],
        'path' => $result['path']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}