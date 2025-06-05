<?php
/**
 * Header - Encabezado del sitio
 * Jiménez & Piña Survey Instruments
 */

// Asegurar que config.php esté cargado
if (!defined('SITE_URL')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

// Obtener categorías para el menú
$db = Database::getInstance();
$menuCategorias = $db->fetchAll("
    SELECT id, nombre, slug 
    FROM categorias 
    WHERE activo = 1 AND parent_id IS NULL 
    ORDER BY orden ASC 
    LIMIT 6
");

// Página actual para menú activo
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php echo generateMetaTags($pageTitle ?? '', $pageDescription ?? '', $pageKeywords ?? ''); ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_URL; ?>/img/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo ASSETS_URL; ?>/img/apple-touch-icon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <?php if($googleAnalyticsId = getSetting('google_analytics')): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $googleAnalyticsId; ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo $googleAnalyticsId; ?>');
    </script>
    <?php endif; ?>
    
    <?php if($facebookPixelId = getSetting('facebook_pixel')): ?>
    <!-- Facebook Pixel -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?php echo $facebookPixelId; ?>');
        fbq('track', 'PageView');
    </script>
    <?php endif; ?>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar bg-dark text-white py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3 small">
                        <a href="tel:<?php echo cleanPhone(getSetting('site_phone')); ?>" class="text-white text-decoration-none">
                            <i class="bi bi-telephone me-1"></i> <?php echo getSetting('site_phone'); ?>
                        </a>
                        <a href="mailto:<?php echo getSetting('site_email'); ?>" class="text-white text-decoration-none">
                            <i class="bi bi-envelope me-1"></i> <?php echo getSetting('site_email'); ?>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex align-items-center justify-content-md-end gap-3">
                        <div class="social-links">
                            <a href="#" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="text-white me-2"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="text-white"><i class="bi bi-youtube"></i></a>
                        </div>
                        <a href="<?php echo ADMIN_URL; ?>" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-person-circle me-1"></i> Portal Cliente
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo SITE_URL; ?>">
                <img src="<?php echo ASSETS_URL; ?>/img/logo.png" alt="Jiménez & Piña" height="50" class="me-2">
                <div class="d-none d-sm-block">
                    <div class="fw-bold text-primary">Jiménez & Piña</div>
                    <div class="small text-muted">Survey Instruments</div>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'index' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">
                            Inicio
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            Productos
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/productos">
                                <i class="bi bi-grid me-2"></i> Ver Todos
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php foreach($menuCategorias as $categoria): ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo SITE_URL; ?>/categoria/<?php echo $categoria['slug']; ?>">
                                    <?php echo sanitize($categoria['nombre']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'servicios' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/servicios">
                            Servicios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'blog' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/blog">
                            Blog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'nosotros' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/nosotros">
                            Nosotros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'contacto' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/contacto">
                            Contacto
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#searchModal">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="<?php echo SITE_URL; ?>/cotizar" class="btn btn-primary">
                        <i class="bi bi-calculator me-2"></i> Cotizar
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buscar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo SITE_URL; ?>/buscar" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" name="q" 
                                   placeholder="Buscar productos, servicios, artículos..." required>
                            <button class="btn btn-primary btn-lg" type="submit">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                    <div class="mt-3">
                        <small class="text-muted">Búsquedas populares:</small>
                        <div class="mt-2">
                            <a href="<?php echo SITE_URL; ?>/buscar?q=estacion+total" class="badge bg-light text-dark me-2">Estación Total</a>
                            <a href="<?php echo SITE_URL; ?>/buscar?q=gps" class="badge bg-light text-dark me-2">GPS</a>
                            <a href="<?php echo SITE_URL; ?>/buscar?q=nivel" class="badge bg-light text-dark me-2">Nivel</a>
                            <a href="<?php echo SITE_URL; ?>/buscar?q=leica" class="badge bg-light text-dark me-2">Leica</a>
                            <a href="<?php echo SITE_URL; ?>/buscar?q=trimble" class="badge bg-light text-dark">Trimble</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- WhatsApp Button -->
    <a href="https://wa.me/<?php echo cleanPhone(getSetting('whatsapp_number')); ?>" 
       target="_blank" 
       class="whatsapp-float">
        <i class="bi bi-whatsapp"></i>
    </a>
    
    <!-- Flash Messages -->
    <div class="container mt-3">
        <?php echo flashMessage(); ?>
    </div>