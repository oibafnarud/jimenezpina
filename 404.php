<?php
/**
 * Página de Error 404
 * /error.php o /404.php
 */
require_once 'config/config.php';

$pageTitle = 'Página no encontrada';
$metaDescription = 'La página que busca no existe o ha sido movida.';

// Registrar el error 404
$requestedUrl = $_SERVER['REQUEST_URI'] ?? '';
error_log("404 Error: " . $requestedUrl . " - IP: " . getClientIP());

include 'includes/header.php';
?>

<section class="error-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="error-content">
                    <h1 class="display-1 fw-bold text-primary">404</h1>
                    <h2 class="mb-4">¡Oops! Página no encontrada</h2>
                    <p class="lead mb-5">
                        Lo sentimos, la página que está buscando no existe, 
                        ha sido movida o temporalmente no está disponible.
                    </p>
                    
                    <div class="error-actions">
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-primary btn-lg me-3">
                            <i class="bi bi-house-door me-2"></i>Ir al Inicio
                        </a>
                        <a href="<?php echo SITE_URL; ?>/productos" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-grid me-2"></i>Ver Productos
                        </a>
                    </div>
                    
                    <div class="mt-5">
                        <p class="text-muted">¿Necesita ayuda? Contáctenos:</p>
                        <div class="d-flex justify-content-center gap-4">
                            <a href="tel:<?php echo getConfig('phone'); ?>" class="text-decoration-none">
                                <i class="bi bi-telephone me-1"></i>
                                <?php echo getConfig('phone'); ?>
                            </a>
                            <a href="mailto:<?php echo getConfig('support_email'); ?>" class="text-decoration-none">
                                <i class="bi bi-envelope me-1"></i>
                                <?php echo getConfig('support_email'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sugerencias -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="text-center mb-4">Enlaces útiles</h3>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-box-seam fs-1 text-primary mb-3 d-block"></i>
                                <h5>Productos</h5>
                                <p class="text-muted">Explore nuestro catálogo completo</p>
                                <a href="<?php echo SITE_URL; ?>/productos" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-wrench fs-1 text-primary mb-3 d-block"></i>
                                <h5>Servicios</h5>
                                <p class="text-muted">Conozca nuestros servicios</p>
                                <a href="<?php echo SITE_URL; ?>/servicios" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-newspaper fs-1 text-primary mb-3 d-block"></i>
                                <h5>Blog</h5>
                                <p class="text-muted">Últimas noticias y artículos</p>
                                <a href="<?php echo SITE_URL; ?>/blog" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="bi bi-headset fs-1 text-primary mb-3 d-block"></i>
                                <h5>Contacto</h5>
                                <p class="text-muted">¿Necesita ayuda? Contáctenos</p>
                                <a href="<?php echo SITE_URL; ?>/contacto" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.error-section {
    min-height: 60vh;
    display: flex;
    align-items: center;
}
.error-content {
    padding: 3rem 0;
}
</style>

<?php include 'includes/footer.php'; ?>