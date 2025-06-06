<?php
/**
 * Página de inicio
 * /index.php
 */
require_once 'config/config.php';

$pageTitle = 'Inicio';
$metaDescription = 'Jiménez & Piña Survey Instruments - Líderes en instrumentos de topografía en República Dominicana';

// Obtener productos destacados
$productosDestacados = $db->fetchAll("
    SELECT p.*, c.nombre as categoria_nombre, m.nombre as marca_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN marcas m ON p.marca_id = m.id
    WHERE p.activo = 1 AND p.destacado = 1
    ORDER BY p.orden ASC, p.created_at DESC
    LIMIT 8
");

// Obtener categorías principales
$categorias = $db->fetchAll("
    SELECT * FROM categorias 
    WHERE activo = 1 AND parent_id IS NULL 
    ORDER BY orden ASC, nombre ASC
");

// Obtener últimos posts del blog
$ultimosPosts = $db->fetchAll("
    SELECT * FROM blog_posts 
    WHERE estado = 'publicado' 
    ORDER BY fecha_publicacion DESC 
    LIMIT 3
");

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="hero-slide" style="background-image: url('assets/img/hero-1.jpg');">
                    <div class="hero-overlay"></div>
                    <div class="container">
                        <div class="row align-items-center min-vh-100">
                            <div class="col-lg-8">
                                <h1 class="display-3 fw-bold text-white mb-4">
                                    Instrumentos de Precisión para Topografía
                                </h1>
                                <p class="lead text-white mb-4">
                                    Líderes en venta y servicio de equipos topográficos en República Dominicana
                                </p>
                                <div class="d-flex gap-3">
                                    <a href="productos" class="btn btn-primary btn-lg">
                                        <i class="bi bi-grid me-2"></i>Ver Catálogo
                                    </a>
                                    <a href="contacto" class="btn btn-outline-light btn-lg">
                                        <i class="bi bi-telephone me-2"></i>Contáctanos
                                    </a>
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
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Nuestras Categorías</h2>
            <p class="lead text-muted">Encuentra el equipo perfecto para tu proyecto</p>
        </div>
        
        <div class="row g-4">
            <?php foreach($categorias as $categoria): ?>
            <div class="col-md-4 col-lg-3">
                <div class="category-card">
                    <a href="categoria/<?php echo $categoria['slug']; ?>" class="text-decoration-none">
                        <div class="category-icon">
                            <i class="bi bi-geo-alt fs-1"></i>
                        </div>
                        <h4 class="h5"><?php echo sanitize($categoria['nombre']); ?></h4>
                        <p class="text-muted small">
                            <?php echo sanitize($categoria['descripcion']); ?>
                        </p>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Productos Destacados -->
<?php if (!empty($productosDestacados)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Productos Destacados</h2>
            <p class="lead text-muted">Los equipos más solicitados por nuestros clientes</p>
        </div>
        
        <div class="row g-4">
            <?php foreach($productosDestacados as $producto): ?>
            <div class="col-md-6 col-lg-3">
                <div class="product-card h-100">
                    <div class="product-image">
                        <?php if($producto['imagen_principal']): ?>
                        <img src="<?php echo UPLOADS_URL . '/' . $producto['imagen_principal']; ?>" 
                             alt="<?php echo sanitize($producto['nombre']); ?>" 
                             class="img-fluid">
                        <?php else: ?>
                        <img src="assets/img/placeholder.jpg" 
                             alt="<?php echo sanitize($producto['nombre']); ?>" 
                             class="img-fluid">
                        <?php endif; ?>
                        <?php if($producto['nuevo']): ?>
                        <span class="badge bg-success position-absolute top-0 end-0 m-2">Nuevo</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-body">
                        <h5 class="product-title">
                            <a href="productos/<?php echo $producto['slug']; ?>" class="text-decoration-none">
                                <?php echo sanitize($producto['nombre']); ?>
                            </a>
                        </h5>
                        <p class="text-muted small">
                            <?php echo sanitize($producto['marca_nombre']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="productos/<?php echo $producto['slug']; ?>" class="btn btn-sm btn-primary">
                                Ver detalles
                            </a>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="agregarCotizacion(<?php echo $producto['id']; ?>)">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="productos" class="btn btn-primary btn-lg">
                Ver todos los productos <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Servicios -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Nuestros Servicios</h2>
            <p class="lead text-muted">Soluciones integrales para tus equipos</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="service-card text-center">
                    <div class="service-icon">
                        <i class="bi bi-tools"></i>
                    </div>
                    <h4>Mantenimiento</h4>
                    <p>Servicio técnico especializado para mantener tus equipos en óptimas condiciones</p>
                    <a href="servicios#mantenimiento" class="btn btn-outline-primary">Más información</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card text-center">
                    <div class="service-icon">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <h4>Capacitación</h4>
                    <p>Entrenamientos profesionales para el uso eficiente de equipos topográficos</p>
                    <a href="servicios#capacitacion" class="btn btn-outline-primary">Más información</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card text-center">
                    <div class="service-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h4>Alquiler</h4>
                    <p>Equipos disponibles para proyectos temporales con las mejores tarifas</p>
                    <a href="servicios#alquiler" class="btn btn-outline-primary">Más información</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog -->
<?php if (!empty($ultimosPosts)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Últimas Noticias</h2>
            <p class="lead text-muted">Mantente actualizado con las novedades del sector</p>
        </div>
        
        <div class="row g-4">
            <?php foreach($ultimosPosts as $post): ?>
            <div class="col-md-6 col-lg-4">
                <article class="blog-card h-100">
                    <?php if($post['imagen_destacada']): ?>
                    <img src="<?php echo UPLOADS_URL . '/' . $post['imagen_destacada']; ?>" 
                         alt="<?php echo sanitize($post['titulo']); ?>" 
                         class="blog-image">
                    <?php endif; ?>
                    <div class="blog-body">
                        <div class="text-muted small mb-2">
                            <i class="bi bi-calendar3"></i> 
                            <?php echo formatDate($post['fecha_publicacion']); ?>
                        </div>
                        <h5 class="blog-title">
                            <a href="blog/<?php echo $post['slug']; ?>" class="text-decoration-none">
                                <?php echo sanitize($post['titulo']); ?>
                            </a>
                        </h5>
                        <p class="text-muted">
                            <?php echo sanitize($post['extracto']); ?>
                        </p>
                        <a href="blog/<?php echo $post['slug']; ?>" class="btn btn-sm btn-outline-primary">
                            Leer más <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="blog" class="btn btn-primary">Ver todas las noticias</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="cta-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="h3 mb-3">¿Necesitas asesoría personalizada?</h2>
                <p class="mb-lg-0">
                    Nuestro equipo de expertos está listo para ayudarte a encontrar 
                    la solución perfecta para tu proyecto
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="contacto" class="btn btn-light btn-lg">
                    <i class="bi bi-headset me-2"></i>Contáctanos Ahora
                </a>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
}

.hero-slide {
    min-height: 100vh;
    background-size: cover;
    background-position: center;
    position: relative;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
}

.category-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    text-align: center;
    transition: all 0.3s;
    height: 100%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.category-icon {
    width: 80px;
    height: 80px;
    background: var(--bs-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.product-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.product-image {
    position: relative;
    overflow: hidden;
    height: 200px;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-body {
    padding: 1.5rem;
}

.service-card {
    padding: 2rem;
    height: 100%;
}

.service-icon {
    width: 100px;
    height: 100px;
    background: var(--bs-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 3rem;
}

.blog-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.blog-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.blog-body {
    padding: 1.5rem;
}

.cta-section {
    background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-dark) 100%);
}
</style>

<?php include 'includes/footer.php'; ?>