<?php
/**
 * API para solicitud de servicios
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
$servicio = sanitize($_POST['servicio'] ?? '');
$equipo = sanitize($_POST['equipo'] ?? '');
$mensaje = sanitize($_POST['mensaje'] ?? '');
$urgente = isset($_POST['urgente']) ? 1 : 0;

// Validaciones
if (empty($nombre)) $errors[] = 'El nombre es requerido';
if (empty($email) || !validateEmail($email)) $errors[] = 'Email válido requerido';
if (empty($telefono)) $errors[] = 'El teléfono es requerido';
if (empty($servicio)) $errors[] = 'Debe seleccionar un servicio';

$servicios_validos = ['mantenimiento', 'capacitacion', 'alquiler', 'soporte', 'instalacion', 'tradein'];
if (!in_array($servicio, $servicios_validos)) {
    $errors[] = 'Servicio no válido';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => 'Errores de validación', 'errors' => $errors]);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Buscar o crear cliente
    $cliente = $db->fetchOne("SELECT id FROM clientes WHERE email = ?", [$email]);
    
    if (!$cliente) {
        $clienteId = $db->insert('clientes', [
            'empresa' => $empresa ?: $nombre,
            'nombre_contacto' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'activo' => 1
        ]);
    } else {
        $clienteId = $cliente['id'];
    }
    
    // Guardar solicitud de servicio
    $solicitudId = $db->insert('solicitudes_servicio', [
        'cliente_id' => $clienteId,
        'tipo_servicio' => $servicio,
        'equipo_modelo' => $equipo,
        'descripcion' => $mensaje,
        'urgente' => $urgente,
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s'),
        'ip_address' => getClientIP()
    ]);
    
    // Prioridad según urgencia
    if ($urgente) {
        // Notificar inmediatamente al equipo técnico
        notifyUrgentService($solicitudId, $servicio);
    }
    
    // Registrar actividad
    logActivity('servicio_solicitado', "Nueva solicitud de servicio: $servicio", 'solicitudes_servicio', $solicitudId);
    
    // Enviar confirmación
    sendServiceConfirmation($email, $servicio, $urgente);
    
    echo json_encode([
        'success' => true,
        'message' => $urgente ? 
            'Solicitud urgente recibida. Le contactaremos en las próximas 2 horas.' :
            'Solicitud recibida correctamente. Le contactaremos pronto.',
        'solicitud_id' => $solicitudId
    ]);
    
} catch (Exception $e) {
    error_log("Error en servicio: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al procesar la solicitud']);
}

function notifyUrgentService($solicitudId, $servicio) {
    // Notificación urgente al equipo técnico
    error_log("URGENTE: Nueva solicitud de servicio ID: $solicitudId - Tipo: $servicio");
}

function sendServiceConfirmation($email, $servicio, $urgente) {
    // Enviar email de confirmación
    $tiempoRespuesta = $urgente ? '2 horas' : '24 horas';
    error_log("Confirmación de servicio $servicio enviada a $email - Respuesta en $tiempoRespuesta");
}