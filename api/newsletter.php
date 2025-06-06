<?php
/**
 * API para suscripción al newsletter
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$email = sanitize($_POST['email'] ?? '');

if (empty($email) || !validateEmail($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Por favor ingrese un email válido']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Verificar si ya está suscrito
    $existe = $db->fetchOne("SELECT * FROM newsletter_suscriptores WHERE email = ?", [$email]);
    
    if ($existe) {
        if ($existe['activo']) {
            echo json_encode([
                'success' => true,
                'message' => 'Este email ya está suscrito a nuestro newsletter'
            ]);
        } else {
            // Reactivar suscripción
            $db->update('newsletter_suscriptores', 
                ['activo' => 1, 'fecha_reactivacion' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$existe['id']]
            );
            echo json_encode([
                'success' => true,
                'message' => 'Su suscripción ha sido reactivada'
            ]);
        }
    } else {
        // Nueva suscripción
        $db->insert('newsletter_suscriptores', [
            'email' => $email,
            'activo' => 1,
            'fecha_suscripcion' => date('Y-m-d H:i:s'),
            'ip_address' => getClientIP(),
            'source' => 'website'
        ]);
        
        // Enviar email de bienvenida
        sendWelcomeEmail($email);
        
        echo json_encode([
            'success' => true,
            'message' => '¡Gracias por suscribirse! Pronto recibirá nuestras novedades.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en newsletter: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al procesar la suscripción']);
}

function sendWelcomeEmail($email) {
    // Implementar envío de email de bienvenida
    error_log("Email de bienvenida enviado a: $email");
}