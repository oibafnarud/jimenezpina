<?php
/**
 * Header del sitio
 * /includes/header.php
 */
if (!defined('ROOT_PATH')) {
    die('Acceso directo no permitido');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Jiménez & Piña Survey Instruments'; ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo $metaDescription ?? getConfig('site_description'); ?>">
    
    <!-- Favicon -->
    <?php if($favicon = getConfig('favicon')): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo UPLOADS_URL . '/' . $favicon; ?>">
    <?php endif; ?>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <!-- Google Analytics -->
    <?php if($ga = getConfig('google_analytics')): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $ga; ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo $ga; ?>');
    </script>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <?php if($logo = getConfig('logo')): ?>
                <img src="<?php echo UPLOADS_URL . '/' . $logo; ?>" alt="<?php echo SITE_NAME; ?>" height="40">
                <?php else: ?>
                <strong><?php echo SITE_NAME; ?></strong>
                <?php endif; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>">Inicio</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            Productos
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/productos">Todos los Productos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php
                            $categorias = $db->fetchAll("SELECT * FROM categorias WHERE activo = 1 AND parent_id IS NULL ORDER BY orden");
                            foreach($categorias as $cat):
                            ?>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/categoria/<?php echo $cat['slug']; ?>">
                                <?php echo sanitize($cat['nombre']); ?>
                            </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/servicios">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/blog">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/nosotros">Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/contacto">Contacto</a>
                    </li>
                </ul>
                
                <div class="ms-3">
                    <button class="btn btn-primary" onclick="verCotizacion()">
                        <i class="bi bi-cart"></i>
                        <span class="badge bg-danger" id="cart-count">0</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Espaciador para navbar fixed -->
    <div style="height: 76px;"></div>