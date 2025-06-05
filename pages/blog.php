<?php
/**
 * Página del Blog
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

// Obtener parámetros
$categoria = $_GET['categoria'] ?? '';
$busqueda = $_GET['q'] ?? '';
$page = (int)($_GET['page'] ?? 1);

// Construir consulta
$where = ["estado = 'publicado'", "fecha_publicacion <= NOW()"];
$params = [];

// Filtro por categoría
if ($categoria) {
    $where[] = "categoria = ?";
    $params[] = $categoria;
}

// Filtro por búsqueda
if ($busqueda) {
    $where[] = "(titulo LIKE ? OR contenido LIKE ? OR extracto LIKE ?)";
    $searchTerm = '%' . $busqueda . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Contar total de posts
$whereClause = implode(' AND ', $where);
$totalItems = $db->fetchColumn("SELECT COUNT(*) FROM blog_posts WHERE $whereClause", $params);

// Paginación
$pagination = paginate($totalItems, $page, 9);

// Obtener posts
$posts = $db->fetchAll("
    SELECT p.*, u.nombre as autor_nombre
    FROM blog_posts p
    LEFT JOIN usuarios u ON p.autor_id = u.id
    WHERE $whereClause
    ORDER BY fecha_publicacion DESC
    LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}
", $params);

// Obtener categorías para sidebar
$categorias = $db->fetchAll("
    SELECT categoria, COUNT(*) as total
    FROM blog_posts
    WHERE estado = 'publicado' AND fecha_publicacion <= NOW()
    GROUP BY categoria
    ORDER BY total DESC
");

// Obtener posts populares para sidebar
$postsPopulares = $db->fetchAll("
    SELECT titulo, slug, imagen_destacada, fecha_publicacion
    FROM blog_posts
    WHERE estado = 'publicado' AND fecha_publicacion <= NOW()
    ORDER BY vistas DESC
    LIMIT 5
");

// Meta tags
$pageTitle = 'Blog y Noticias';
$pageDescription = 'Últimas noticias, tutoriales y guías sobre topografía, equipos de medición y tecnología de construcción.';
$pageKeywords = 'blog topografía, noticias instrumentos, tutoriales GPS, guías estación total';

include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Blog y Recursos</h1>
                <p class="lead mb-0 opacity-90">
                    Manténgase actualizado con las últimas tendencias, tutoriales y 
                    noticias del mundo de la topografía
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <form action="<?php echo SITE_URL; ?>/blog" method="GET" class="mt-4 mt-lg-0">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" 
                               placeholder="Buscar artículos..." 
                               value="<?php echo sanitize($busqueda); ?>">
                        <button class="btn btn-white" type="submit">
                            <i class="bi bi-search text-primary"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Blog Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Blog Posts -->
            <div class="col-lg-8">
                <?php if ($categoria || $busqueda): ?>
                <div class="mb-4">
                    <h5 class="text-muted">
                        <?php if ($categoria): ?>
                            Categoría: <span class="text-primary"><?php echo sanitize($categoria); ?></span>
                        <?php elseif ($busqueda): ?>
                            Resultados para: <span class="text-primary">"<?php echo sanitize($busqueda); ?>"</span>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/blog" class="btn btn-sm btn-outline-secondary ms-2">
                            <i class="bi bi-x"></i> Limpiar
                        </a>
                    </h5>
                </div>
                <?php endif; ?>
                
                <?php if (empty($posts)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-newspaper display-1 text-muted"></i>
                    <h4 class="mt-3">No se encontraron artículos</h4>
                    <p class="text-muted">Intente con otros términos de búsqueda o categorías</p>
                    <a href="<?php echo SITE_URL; ?>/blog" class="btn btn-primary mt-3">
                        Ver todos los artículos
                    </a>
                </div>
                <?php else: ?>
                <div class="row g-4">
                    <?php foreach($posts as $post): ?>
                    <div class="col-md-6">
                        <article class="card h-100 border-0 shadow-sm blog-card">
                            <?php if($post['imagen_destacada']): ?>
                            <a href="<?php echo SITE_URL; ?>/blog/<?php echo $post['slug']; ?>">
                                <img src="<?php echo UPLOADS_URL . '/' . $post['imagen_destacada']; ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo sanitize($post['titulo']); ?>">
                            </a>
                            <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <div class="mb-2">
                                    <a href="<?php echo SITE_URL; ?>/blog?categoria=<?php echo urlencode($post['categoria']); ?>" 
                                       class="badge bg-primary-soft text-primary text-decoration-none">
                                        <?php echo sanitize($post['categoria']); ?>
                                    </a>
                                </div>
                                
                                <h5 class="card-title">
                                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo $post['slug']; ?>" 
                                       class="text-dark text-decoration-none">
                                        <?php echo sanitize($post['titulo']); ?>
                                    </a>
                                </h5>
                                
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo sanitize($post['extracto']); ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?php echo formatDate($post['fecha_publicacion']); ?>
                                    </small>
                                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo $post['slug']; ?>" 
                                       class="btn btn-link text-primary p-0">
                                        Leer más <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Paginación del blog" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['has_previous']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $pagination['previous_page']; ?>">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php foreach ($pagination['pages'] as $page): ?>
                        <li class="page-item <?php echo $page['is_current'] ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $page['url']; ?>">
                                <?php echo $page['number']; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        
                        <?php if ($pagination['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Newsletter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-envelope display-4 text-primary mb-3"></i>
                        <h5 class="card-title">Suscríbase al Newsletter</h5>
                        <p class="card-text text-muted">
                            Reciba las últimas noticias y ofertas especiales
                        </p>
                        <form action="<?php echo SITE_URL; ?>/api/newsletter.php" method="POST" class="newsletter-form">
                            <div class="input-group mb-3">
                                <input type="email" class="form-control" placeholder="Su email" required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Categories -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Categorías</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="<?php echo SITE_URL; ?>/blog" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                Todas las categorías
                                <span class="badge bg-primary rounded-pill"><?php echo $totalItems; ?></span>
                            </a>
                            <?php foreach($categorias as $cat): ?>
                            <a href="<?php echo SITE_URL; ?>/blog?categoria=<?php echo urlencode($cat['categoria']); ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $categoria == $cat['categoria'] ? 'active' : ''; ?>">
                                <?php echo sanitize($cat['categoria']); ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $cat['total']; ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Popular Posts -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Artículos Populares</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach($postsPopulares as $popular): ?>
                        <div class="d-flex mb-3">
                            <?php if($popular['imagen_destacada']): ?>
                            <img src="<?php echo UPLOADS_URL . '/' . $popular['imagen_destacada']; ?>" 
                                 class="me-3 rounded" 
                                 style="width: 80px; height: 60px; object-fit: cover;"
                                 alt="<?php echo sanitize($popular['titulo']); ?>">
                            <?php else: ?>
                            <div class="me-3 bg-light rounded d-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 60px;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                            <?php endif; ?>
                            <div>
                                <h6 class="mb-1">
                                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo $popular['slug']; ?>" 
                                       class="text-dark text-decoration-none">
                                        <?php echo sanitize($popular['titulo']); ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <?php echo formatDate($popular['fecha_publicacion']); ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Tags Cloud -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Etiquetas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="<?php echo SITE_URL; ?>/blog?q=GPS" 
                               class="badge bg-light text-dark text-decoration-none p-2">GPS</a>
                            <a href="<?php echo SITE_URL; ?>/blog?q=Estación+Total" 
                               class="badge bg-light text-dark text-decoration-none p-2">Estación Total</a>
                            <a href="<?php echo SITE_URL; ?>/blog?q=Drones" 
                               class="badge bg-light text-dark text-decoration-none p-2">Drones</a>
                            <a href="<?php echo SITE_URL; ?>/blog?q=BIM" 
                               class="badge bg-light text-dark text-decoration-none p-2">BIM</a>
                            <a href="<?php echo SITE_URL; ?>/blog?q=Fotogrametría" 
                               class="badge bg-light text-dark text-decoration-none p-2">Fotogrametría</a>
                            <a href="<?php echo SITE_URL; ?>/blog?q=RTK" 
                               class="badge bg-light text-dark text-decoration-none p-2">RTK</a>
                            <a href="<?php echo SITE_URL; ?>/blog?q=Calibración" 
                               class="badge bg-light text-dark text-decoration-none p-2">Calibración</a>
                            <a href="<?php echo SITE_URL; ?>/blog?q=Software" 
                               class="badge bg-light text-dark text-decoration-none p-2">Software</a>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Síguenos</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="#" class="btn btn-outline-primary">
                                <i class="bi bi-facebook me-2"></i> Facebook
                            </a>
                            <a href="#" class="btn btn-outline-info">
                                <i class="bi bi-linkedin me-2"></i> LinkedIn
                            </a>
                            <a href="#" class="btn btn-outline-danger">
                                <i class="bi bi-youtube me-2"></i> YouTube
                            </a>
                            <a href="#" class="btn btn-outline-secondary">
                                <i class="bi bi-instagram me-2"></i> Instagram
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.blog-card {
    transition: all 0.3s ease;
}
.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}
.blog-card img {
    height: 200px;
    object-fit: cover;
    transition: all 0.3s ease;
}
.blog-card:hover img {
    transform: scale(1.05);
}
</style>

<?php include '../includes/footer.php'; ?>