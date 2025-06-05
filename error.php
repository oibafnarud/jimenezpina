<?php
/**
 * Página de Error
 * Jiménez & Piña Survey Instruments
 */
require_once 'config/config.php';

// Obtener código de error
$errorCode = $_GET['code'] ?? 404;

// Configurar mensajes según el código de error
$errorMessages = [
    400 => [
        'title' => 'Solicitud Incorrecta',
        'message' => 'La solicitud no pudo ser procesada debido a un error en los datos enviados.',
        'icon' => 'bi-exclamation-triangle'
    ],
    401 => [
        'title' => 'No Autorizado',
        'message' => 'Necesita iniciar sesión para acceder a esta página.',
        'icon' => 'bi-lock'
    ],
    403 => [
        'title' => 'Acceso Prohibido',
        'message' => 'No tiene permisos para acceder a este recurso.',
        'icon' => 'bi-shield-x'
    ],
    404 => [
        'title' => 'Página No Encontrada',
        'message' => 'Lo sentimos, la página que busca no existe o ha sido movida.',
        'icon' => 'bi-search'
    ],
    500 => [
        'title' => 'Error del Servidor',
        'message' => 'Ha ocurrido un error inesperado. Por favor, intente más tarde.',
        'icon' => 'bi-server'
    ]
];

// Obtener información del error
$error = $errorMessages[$errorCode] ?? $errorMessages[404];

// Meta tags
$pageTitle = 'Error ' . $errorCode;
$pageDescription = $error['title'];

// Header HTTP correcto
http_response_code($errorCode);

include 'includes/header.php';
?>

<!-- Error Page -->
<section class="min-vh-100 d-flex align-items-center py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mx-auto text-center">
                <div class="error-content">
                    <!-- Error Icon -->
                    <div class="error-icon mb-4">
                        <i class="<?php echo $error['icon']; ?> display-1 text-primary"></i>
                    </div>
                    
                    <!-- Error Code -->
                    <h1 class="display-1 fw-bold text-primary mb-4"><?php echo $errorCode; ?></h1>
                    
                    <!-- Error Title -->
                    <h2 class="display-5 fw-bold mb-3"><?php echo $error['title']; ?></h2>
                    
                    <!-- Error Message -->
                    <p class="lead text-muted mb-5"><?php echo $error['message']; ?></p>
                    
                    <!-- Actions -->
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-house me-2"></i> Ir al Inicio
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-arrow-left me-2"></i> Volver Atrás
                        </button>
                    </div>
                    
                    <!-- Search Box -->
                    <div class="mt-5">
                        <p class="text-muted mb-3">¿Busca algo específico?</p>
                        <form action="<?php echo SITE_URL; ?>/buscar" method="GET" class="mx-auto" style="max-width: 400px;">
                            <div class="input-group">
                                <input type="text" class="form-control" name="q" 
                                       placeholder="Buscar productos, servicios..." required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Popular Links -->
                    <div class="mt-5">
                        <h5 class="mb-3">Enlaces Populares</h5>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="<?php echo SITE_URL; ?>/productos" class="btn btn-outline-secondary">
                                Productos
                            </a>
                            <a href="<?php echo SITE_URL; ?>/servicios" class="btn btn-outline-secondary">
                                Servicios
                            </a>
                            <a href="<?php echo SITE_URL; ?>/blog" class="btn btn-outline-secondary">
                                Blog
                            </a>
                            <a href="<?php echo SITE_URL; ?>/contacto" class="btn btn-outline-secondary">
                                Contacto
                            </a>
                        </div>
                    </div>
                    
                    <!-- Contact Support -->
                    <?php if ($errorCode >= 500): ?>
                    <div class="mt-5 p-4 bg-light rounded">
                        <h5 class="mb-3">¿Necesita ayuda inmediata?</h5>
                        <p class="text-muted mb-3">
                            Si el problema persiste, contáctenos:
                        </p>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="tel:<?php echo cleanPhone(getSetting('site_phone')); ?>" 
                               class="btn btn-success">
                                <i class="bi bi-telephone me-2"></i> 
                                <?php echo getSetting('site_phone'); ?>
                            </a>
                            <a href="mailto:<?php echo getSetting('site_email'); ?>" 
                               class="btn btn-info text-white">
                                <i class="bi bi-envelope me-2"></i> 
                                Enviar Email
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.error-icon {
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-20px);
    }
    60% {
        transform: translateY(-10px);
    }
}

.min-vh-100 {
    min-height: calc(100vh - 200px) !important;
}
</style>

<?php include 'includes/footer.php'; ?>