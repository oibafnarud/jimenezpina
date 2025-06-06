<?php
/**
 * API para procesar cotizaciones
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

header('Content-Type: application/json');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Verificar CSRF token
if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Token de seguridad inválido']);
    exit;
}

// Validar datos requeridos
$errors = [];

// Datos del cliente
$nombre = sanitize($_POST['nombre'] ?? '');
$empresa = sanitize($_POST['empresa'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$telefono = sanitize($_POST['telefono'] ?? '');
$rnc = sanitize($_POST['rnc'] ?? '');
$provincia = sanitize($_POST['provincia'] ?? '');

// Validaciones
if (empty($nombre)) $errors[] = 'El nombre es requerido';
if (empty($empresa)) $errors[] = 'La empresa es requerida';
if (empty($email) || !validateEmail($email)) $errors[] = 'Email válido requerido';
if (empty($telefono)) $errors[] = 'El teléfono es requerido';
if (empty($provincia)) $errors[] = 'La provincia es requerida';

// Validar productos
$productos = $_POST['productos'] ?? [];
if (empty($productos)) $errors[] = 'Debe seleccionar al menos un producto';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => 'Errores de validación', 'errors' => $errors]);
    exit;
}

try {
    $db = Database::getInstance();
    $db->beginTransaction();
    
    // Buscar o crear cliente
    $cliente = $db->fetchOne("SELECT id FROM clientes WHERE email = ?", [$email]);
    
    if (!$cliente) {
        $clienteId = $db->insert('clientes', [
            'empresa' => $empresa,
            'nombre_contacto' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'rnc' => $rnc,
            'direccion' => $provincia,
            'activo' => 1
        ]);
    } else {
        $clienteId = $cliente['id'];
        // Actualizar datos del cliente
        $db->update('clientes', [
            'empresa' => $empresa,
            'nombre_contacto' => $nombre,
            'telefono' => $telefono,
            'rnc' => $rnc,
            'direccion' => $provincia
        ], 'id = ?', [$clienteId]);
    }
    
    // Calcular totales
    $subtotal = 0;
    $productosData = [];
    
    foreach ($productos as $prod) {
        if (empty($prod['id']) || empty($prod['cantidad'])) continue;
        
        $producto = $db->fetchOne("SELECT * FROM productos WHERE id = ? AND activo = 1", [$prod['id']]);
        if (!$producto) continue;
        
        $cantidad = (int)$prod['cantidad'];
        $precio = $producto['precio_oferta'] ?: $producto['precio'];
        $total = $precio * $cantidad;
        $subtotal += $total;
        
        $productosData[] = [
            'producto' => $producto,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'total' => $total
        ];
    }
    
    if (empty($productosData)) {
        throw new Exception('No se encontraron productos válidos');
    }
    
    // Calcular impuestos y total
    $itbis = $subtotal * TAX_RATE;
    $total = $subtotal + $itbis;
    
    // Crear cotización
    $numeroCotizacion = generateQuoteNumber();
    $cotizacionId = $db->insert('cotizaciones', [
        'numero' => $numeroCotizacion,
        'cliente_id' => $clienteId,
        'fecha' => date('Y-m-d'),
        'validez_dias' => 30,
        'estado' => 'pendiente',
        'subtotal' => $subtotal,
        'itbis' => $itbis,
        'total' => $total,
        'notas' => sanitize($_POST['notas'] ?? ''),
        'proyecto_tipo' => sanitize($_POST['proyecto'] ?? ''),
        'plazo_entrega' => sanitize($_POST['plazo'] ?? ''),
        'incluir_instalacion' => isset($_POST['incluir_instalacion']) ? 1 : 0,
        'incluir_capacitacion' => isset($_POST['incluir_capacitacion']) ? 1 : 0,
        'urgente' => isset($_POST['urgente']) ? 1 : 0,
        'created_by' => $_SESSION['user_id'] ?? null
    ]);
    
    // Insertar items de la cotización
    foreach ($productosData as $item) {
        $db->insert('cotizacion_items', [
            'cotizacion_id' => $cotizacionId,
            'producto_id' => $item['producto']['id'],
            'descripcion' => $item['producto']['nombre'],
            'cantidad' => $item['cantidad'],
            'precio_unitario' => $item['precio'],
            'descuento' => 0,
            'total' => $item['total']
        ]);
    }
    
    // Registrar actividad
    logActivity('cotizacion_created', "Nueva cotización: $numeroCotizacion", 'cotizaciones', $cotizacionId);
    
    $db->commit();
    
    // Enviar email de notificación
    sendQuoteNotification($cotizacionId, $email);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Cotización enviada correctamente',
        'numero' => $numeroCotizacion,
        'id' => $cotizacionId
    ]);
    
} catch (Exception $e) {
    $db->rollback();
    error_log("Error en cotización: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al procesar la cotización']);
}

/**
 * Enviar notificación por email
 */
function sendQuoteNotification($cotizacionId, $clienteEmail) {
    // Aquí iría la lógica de envío de email
    // Por ahora solo registramos
    error_log("Email de cotización $cotizacionId enviado a $clienteEmail");
}