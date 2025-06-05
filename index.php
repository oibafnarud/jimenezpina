<?php
/**
 * Página principal - Jiménez & Piña Survey Instruments
 */
require_once 'config/config.php';

// Obtener productos destacados
$db = Database::getInstance();
$productosDestacados = $db->fetchAll("
    SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug, 
           m.nombre as marca_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN marcas m ON p.marca_id = m.id
    WHERE p.activo = 1 AND p.destacado = 1
    ORDER BY p.created_at DESC
    LIMIT 6
");

// Obtener categorías principales
$categorias = $db->fetchAll("
    SELECT * FROM categorias 
    WHERE activo = 1 AND parent_id IS NULL 
    ORDER BY orden ASC
");

// Obtener últimos posts del blog
$blogPosts = $db->fetchAll("
    SELECT * FROM blog_posts 
    WHERE estado = 'publicado' AND fecha_publicacion <= NOW()
    ORDER BY fecha_publicacion DESC
    LIMIT 3
");

// Obtener estadísticas
$stats = [
    'productos' => $db->count('productos', 'activo = 1'),
    'marcas' => $db->count('marcas', 'activo = 1'),
    'clientes' => $db->count('clientes', 'activo = 1')
];

// Meta tags SEO
$pageTitle = '';
$pageDescription = 'Equipos topográficos de precisión. Distribuidor autorizado de Leica, Trimble, Topcon. Estaciones totales, GPS, niveles y más.';
$pageKeywords = 'instrumentos topográficos, estaciones totales, GPS topográfico, niveles, Santo Domingo, República Dominicana';

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <div class="hero-bg"></div>
    <div class="container position-relative">
        <div class="row min-vh-100 align-items-center py-5">
            <div class="col-lg-6">
                <div class="hero-content">
                    <span class="badge bg-primary-soft text-primary mb-3">
                        <i class="bi bi-star-fill me-1"></i> Líderes en Tecnología Topográfica
                    </span>
                    <h1 class="display-3 fw-bold mb-4">
                        Instrumentos de Precisión para el 
                        <span class="text-primary">Futuro de la Construcción</span>
                    </h1>
                    <p class="lead mb-4">
                        Distribuimos y servimos los equipos topográficos más avanzados del mercado. 
                        Soluciones integrales para profesionales que exigen precisión y confiabilidad.
                    </p>
                    <div class="d-flex flex-wrap gap-3 mb-5">
                        <a href="productos" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-seam me-2"></i> Ver Catálogo
                        </a>
                        <a href="cotizar" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-calculator me-2"></i> Solicitar Cotización
                        </a>
                    </div>
                    <div class="row g-4">
                        <div class="col-auto">
                            <div class="d-flex align-items-center">
                                <div class="display-4 fw-bold text-primary">500+</div>
                                <div class="ms-3">
                                    <div class="fw-semibold">Equipos</div>
                                    <div class="text-muted">Vendidos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex align-items-center">
                                <div class="display-4 fw-bold text-primary">24/7</div>
                                <div class="ms-3">
                                    <div class="fw-semibold">Soporte</div>
                                    <div class="text-muted">Técnico</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image position-relative">
                    <img src="assets/img/hero-instrument.png" alt="Estación Total" class="img-fluid">
                    <div class="floating-card position-absolute top-0 end-0">
                        <div class="card shadow-lg border-0">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-shield-check text-success fs-3 me-3"></i>
                                    <div>
                                        <h6 class="mb-0">Garantía Extendida</h6>
                                        <small class="text-muted">Hasta 3 años</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categorías -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary mb-3">Categorías</span>
            <h2 class="display-5 fw-bold">Explore Nuestros Productos</h2>
            <p class="lead text-muted">Encuentre el equipo perfecto para su proyecto</p>
        </div>
        
        <div class="row g-4">
            <?php foreach($categorias as $categoria): ?>
            <div class="col-md-6 col-lg-4">
                <a href="categoria/<?php echo $categoria['slug']; ?>" class="text-decoration-none">
                    <div class="card h-100 shadow-hover border-0">
                        <div class="card-body p-4 text-center">
                            <?php if($categoria['imagen']): ?>
                            <img src="<?php echo UPLOADS_URL . '/' . $categoria['imagen']; ?>" 
                                 alt="<?php echo sanitize($categoria['nombre']); ?>" 
                                 class="mb-3" style="height: 80px;">
                            <?php else: ?>
                            <i class="bi bi-box-seam display-1 text-primary mb-3"></i>
                            <?php endif; ?>
                            <h4 class="h5 mb-2"><?php echo sanitize($categoria['nombre']); ?></h4>
                            <p class="text-muted mb-0"><?php echo sanitize($categoria['descripcion']); ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Productos Destacados -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <span class="badge bg-primary-soft text-primary mb-3">Productos Destacados</span>
                <h2 class="display-5 fw-bold">Equipos de Última Generación</h2>
            </div>
            <a href="productos" class="btn btn-outline-primary">
                Ver todos <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php foreach($productosDestacados as $producto): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 product-card border-0 shadow-sm">
                    <div class="position-relative">
                        <?php if($producto['nuevo']): ?>
                        <span class="badge bg-danger position-absolute top-0 start-0 m-3">Nuevo</span>
                        <?php endif; ?>
                        <?php if($producto['precio_oferta']): ?>
                        <span class="badge bg-warning position-absolute top-0 end-0 m-3">Oferta</span>
                        <?php endif; ?>
                        <div class="product-image p-4 bg-light">
                            <img src="<?php echo $producto['imagen_principal'] ? UPLOADS_URL . '/' . $producto['imagen_principal'] : 'assets/img/no-image.jpg'; ?>" 
                                 class="img-fluid" 
                                 alt="<?php echo sanitize($producto['nombre']); ?>">
                        </div>
                    </div>
                    <div class="card-body">
                        <small class="text-primary fw-semibold"><?php echo sanitize($producto['marca_nombre']); ?></small>
                        <h5 class="card-title mt-1">
                            <a href="producto/<?php echo $producto['slug']; ?>" class="text-decoration-none text-dark stretched-link">
                                <?php echo sanitize($producto['nombre']); ?>
                            </a>
                        </h5>
                        <p class="card-text text-muted small">
                            <?php echo sanitize($producto['descripcion_corta']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <?php if($producto['precio_oferta']): ?>
                                <span class="text-muted text-decoration-line-through small">
                                    <?php echo formatPrice($producto['precio']); ?>
                                </span>
                                <div class="h5 mb-0 text-danger">
                                    <?php echo formatPrice($producto['precio_oferta']); ?>
                                </div>
                                <?php else: ?>
                                <div class="h5 mb-0 text-primary">
                                    <?php echo formatPrice($producto['precio']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Características -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <i class="bi bi-truck display-4 mb-3"></i>
                <h5>Envío Rápido</h5>
                <p class="mb-0 opacity-75">Entrega en 24-48 horas en Santo Domingo</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-shield-check display-4 mb-3"></i>
                <h5>Garantía Extendida</h5>
                <p class="mb-0 opacity-75">Hasta 3 años en equipos seleccionados</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-headset display-4 mb-3"></i>
                <h5>Soporte 24/7</h5>
                <p class="mb-0 opacity-75">Asistencia técnica cuando la necesite</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-award display-4 mb-3"></i>
                <h5>Calidad Certificada</h5>
                <p class="mb-0 opacity-75">Distribuidores autorizados oficiales</p>
            </div>
        </div>
    </div>
</section>

<!-- Blog -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary mb-3">Blog</span>
            <h2 class="display-5 fw-bold">Últimas Noticias y Guías</h2>
            <p class="lead text-muted">Manténgase actualizado con las últimas tendencias</p>
        </div>
        
        <div class="row g-4">
            <?php foreach($blogPosts as $post): ?>
            <div class="col-md-6 col-lg-4">
                <article class="card h-100 border-0 shadow-sm">
                    <?php if($post['imagen_destacada']): ?>
                    <img src="<?php echo UPLOADS_URL . '/' . $post['imagen_destacada']; ?>" 
                         class="card-img-top" 
                         alt="<?php echo sanitize($post['titulo']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-primary"><?php echo sanitize($post['categoria']); ?></small>
                            <small class="text-muted"><?php echo formatDate($post['fecha_publicacion']); ?></small>
                        </div>
                        <h5 class="card-title">
                            <a href="blog/<?php echo $post['slug']; ?>" class="text-decoration-none text-dark">
                                <?php echo sanitize($post['titulo']); ?>
                            </a>
                        </h5>
                        <p class="card-text text-muted">
                            <?php echo sanitize($post['extracto']); ?>
                        </p>
                        <a href="blog/<?php echo $post['slug']; ?>" class="btn btn-link text-primary p-0">
                            Leer más <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="blog" class="btn btn-primary">Ver todos los artículos</a>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="display-6 fw-bold mb-3">¿Necesita asesoría para elegir el equipo ideal?</h3>
                <p class="lead mb-0 opacity-90">
                    Nuestros expertos están listos para ayudarle a encontrar la solución perfecta para su proyecto
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="contacto" class="btn btn-white btn-lg me-3">
                    <i class="bi bi-envelope me-2"></i> Contáctenos
                </a>
                <a href="https://wa.me/18095550123" target="_blank" class="btn btn-success btn-lg">
                    <i class="bi bi-whatsapp me-2"></i> WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>items-center">
                                <div class="display-4 fw-bold text-primary">15+</div>
                                <div class="ms-3">
                                    <div class="fw-semibold">Años de</div>
                                    <div class="text-muted">Experiencia</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex align-