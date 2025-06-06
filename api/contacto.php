<?php
/**
 * API para procesar formulario de contacto
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
$errors = [];
$nombre = sanitize($_POST['nombre'] ?? '');
$empresa = sanitize($_POST['empresa'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$telefono = sanitize($_POST['telefono'] ?? '');
$asunto = sanitize($_POST['asunto'] ?? '');
$mensaje = sanitize($_POST['mensaje'] ?? '');
$newsletter = isset($_POST['newsletter']) ? 1 : 0;

// Validaciones
if (empty($nombre)) $errors[] = 'El nombre es requerido';
if (empty($email) || !validateEmail($email)) $errors[] = 'Email válido requerido';
if (empty($telefono)) $errors[] = 'El teléfono es requerido';
if (empty($asunto)) $errors[] = 'El asunto es requerido';
if (empty($mensaje)) $errors[] = 'El mensaje es requerido';

// Verificar reCAPTCHA si está configurado
if (RECAPTCHA_SECRET_KEY) {
    $recaptcha = $_POST['g-recaptcha-response'] ?? '';
    if (empty($recaptcha)) {
        $errors[] = 'Por favor complete el captcha';
    } else {
        $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . 
                  RECAPTCHA_SECRET_KEY . '&response=' . $recaptcha);
        $captcha_success = json_decode($verify);
        if (!$captcha_success->success) {
            $errors[] = 'Verificación de captcha falló';
        }
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => 'Errores de validación', 'errors' => $errors]);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Guardar consulta
    $consultaId = $db->insert('consultas', [
        'nombre' => $nombre,
        'empresa' => $empresa,
        'email' => $email,
        'telefono' => $telefono,
        'asunto' => $asunto,
        'mensaje' => $mensaje,
        'estado' => 'nueva',
        'ip_address' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    // Si se suscribió al newsletter
    if ($newsletter) {
        $existeEmail = $db->exists('newsletter_suscriptores', 'email = ?', [$email]);
        if (!$existeEmail) {
            $db->insert('newsletter_suscriptores', [
                'email' => $email,
                'nombre' => $nombre,
                'activo' => 1,
                'fecha_suscripcion' => date('Y-m-d H:i:s'),
                'ip_address' => getClientIP()
            ]);
        }
    }
    
    // Enviar email de notificación
    sendContactNotification($consultaId);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Mensaje enviado correctamente. Nos pondremos en contacto pronto.'
    ]);
    
} catch (Exception $e) {
    error_log("Error en contacto: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al enviar el mensaje']);
}

function sendContactNotification($consultaId) {
    // Implementar envío de email
    error_log("Notificación de contacto ID: $consultaId");
}