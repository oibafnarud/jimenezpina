<?php
/**
 * Página de Búsqueda
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

// Obtener término de búsqueda
$query = trim($_GET['q'] ?? '');

if (empty($query)) {
    redirect(SITE_URL, 'Por favor ingrese un término de búsqueda', MSG_WARNING);
}

// Sanitizar búsqueda
$searchTerm = '%' . $query . '%';

// Buscar en productos
$productos = $db->fetchAll("
    SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug,
           m.nombre as marca_nombre, 'producto' as tipo
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN marcas m ON p.marca_id = m.id
    WHERE p.activo = 1 
    AND (p.nombre LIKE ? OR p.descripcion LIKE ? OR p.descripcion_corta LIKE ? 
         OR p.sku LIKE ? OR c.nombre LIKE ? OR m.nombre LIKE ?)
    ORDER BY 
        CASE 
            WHEN p.nombre LIKE ? THEN 1
            WHEN p.sku LIKE ? THEN 2
            WHEN m.nombre LIKE ? THEN 3
            ELSE 4
        END,
        p.nombre
    LIMIT 20
", [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, 
    $searchTerm, $searchTerm, $searchTerm]);

// Buscar en blog
$posts = $db->fetchAll("
    SELECT id, titulo, slug, extracto, imagen_destacada, fecha_publicacion, 
           categoria, 'blog' as tipo
    FROM blog_posts
    WHERE estado = 'publicado' 
    AND fecha_publicacion <= NOW()
    AND (titulo LIKE ? OR contenido LIKE ? OR extracto LIKE ? OR tags LIKE ?)
    ORDER BY 
        CASE 
            WHEN titulo LIKE ? THEN 1
            ELSE 2
        END,
        fecha_publicacion DESC
    LIMIT 10
", [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);

// Buscar en categorías
$categorias = $db->fetchAll("
    SELECT id, nombre, slug, descripcion, 'categoria' as tipo
    FROM categorias
    WHERE activo = 1
    AND (nombre LIKE ? OR descripcion LIKE ?)
    ORDER BY nombre
    LIMIT 5
", [$searchTerm, $searchTerm]);

// Buscar en páginas estáticas
$paginas = [];
$paginasEstaticas = [
    ['titulo' => 'Servicios', 'url' => '/servicios', 'descripcion' => 'Servicios profesionales de topografía'],
    ['titulo' => 'Capacitación', 'url' => '/servicios#capacitacion', 'descripcion' => 'Programas de formación profesional'],
    ['titulo' => 'Alquiler de Equipos', 'url' => '/servicios#alquiler', 'descripcion' => 'Alquiler de equipos topográficos'],
    ['titulo' => 'Soporte Técnico', 'url' => '/servicios#soporte', 'descripcion' => 'Soporte técnico 24/7'],
    ['titulo' => 'Contacto', 'url' => '/contacto', 'descripcion' => 'Información de contacto'],
    ['titulo' => 'Nosotros', 'url' => '/nosotros', 'descripcion' => 'Acerca de Jiménez & Piña'],
];

foreach ($paginasEstaticas as $pagina) {
    if (stripos($pagina['titulo'], $query) !== false || 
        stripos($pagina['descripcion'], $query) !== false) {
        $paginas[] = array_merge($pagina, ['tipo' => 'pagina']);
    }
}

// Total de resultados
$totalResultados = count($productos) + count($posts) + count($categorias) + count($paginas);

// Meta tags
$pageTitle = 'Búsqueda: ' . $query;
$pageDescription = 'Resultados de búsqueda para: ' . $query;

include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3">Resultados de Búsqueda</h1>
                <p class="lead text-muted mb-0">
                    <?php echo $totalResultados; ?> resultado<?php echo $totalResultados != 1 ? 's' : ''; ?> 
                    para: <strong>"<?php echo sanitize($query); ?>"</strong>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <form action="<?php echo SITE_URL; ?>/buscar" method="GET" class="mt-4 mt-lg-0">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" 
                               placeholder="Nueva búsqueda..." 
                               value="<?php echo sanitize($query); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Search Results -->
<section class="py-5">
    <div class="container">
        <?php if ($totalResultados == 0): ?>
        <!-- No Results -->
        <div class="text-center py-5">
            <i class="bi bi-search display-1 text-muted"></i>
            <h3 class="mt-4 mb-3">No se encontraron resultados</h3>
            <p class="text-muted mb-4">
                No encontramos contenido que coincida con su búsqueda.
            </p>
            <div class="suggestions mb-4">
                <h5 class="mb-3">Sugerencias:</h5>
                <ul class="list-unstyled text-muted">
                    <li><i class="bi bi-check2 me-2"></i>Verifique la ortografía de las palabras</li>
                    <li><i class="bi bi-check2 me-2"></i>Intente usar términos más generales</li>
                    <li><i class="bi bi-check2 me-2"></i>Use menos palabras en su búsqueda</li>
                </ul>
            </div>
            <a href="<?php echo SITE_URL; ?>/productos" class="btn btn-primary">
                Ver todos los productos
            </a>
        </div>
        <?php else: ?>
        
        <div class="row">
            <div class="col-lg-3">
                <!-- Filter Sidebar -->
                <div class="card border-0 shadow-sm mb-4 sticky-lg-top" style="top: 100px;">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Filtrar por tipo</h5>
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center active filter-type" data-type="all">
                                Todos los resultados
                                <span class="badge bg-primary rounded-pill"><?php echo $totalResultados; ?></span>
                            </a>
                            <?php if (!empty($productos)): ?>
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center filter-type" data-type="producto">
                                Productos
                                <span class="badge bg-primary rounded-pill"><?php echo count($productos); ?></span>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($posts)): ?>
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center filter-type" data-type="blog">
                                Artículos del Blog
                                <span class="badge bg-primary rounded-pill"><?php echo count($posts); ?></span>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($categorias)): ?>
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center filter-type" data-type="categoria">
                                Categorías
                                <span class="badge bg-primary rounded-pill"><?php echo count($categorias); ?></span>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($paginas)): ?>
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center filter-type" data-type="pagina">
                                Páginas
                                <span class="badge bg-primary rounded-pill"><?php echo count($paginas); ?></span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9">
                <!-- Products Results -->
                <?php if (!empty($productos)): ?>
                <div class="result-section" data-type="producto">
                    <h4 class="mb-4">
                        <i class="bi bi-box-seam text-primary me-2"></i> 
                        Productos (<?php echo count($productos); ?>)
                    </h4>
                    <div class="row g-4 mb-5">
                        <?php foreach($productos as $producto): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="row g-0">
                                    <div class="col-4">
                                        <div class="p-3 bg-light h-100 d-flex align-items-center justify-content-center">
                                            <?php if($producto['imagen_principal']): ?>
                                            <img src="<?php echo UPLOADS_URL . '/' . $producto['imagen_principal']; ?>" 
                                                 class="img-fluid" 
                                                 alt="<?php echo sanitize($producto['nombre']); ?>">
                                            <?php else: ?>
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body">
                                            <small class="text-primary"><?php echo sanitize($producto['marca_nombre']); ?></small>
                                            <h5 class="card-title">
                                                <a href="<?php echo SITE_URL; ?>/producto/<?php echo $producto['slug']; ?>" 
                                                   class="text-dark text-decoration-none">
                                                    <?php echo highlightSearchTerm($producto['nombre'], $query); ?>
                                                </a>
                                            </h5>
                                            <p class="card-text small text-muted">
                                                <?php echo highlightSearchTerm($producto['descripcion_corta'], $query); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="h6 mb-0 text-primary">
                                                    <?php echo formatPrice($producto['precio_oferta'] ?: $producto['precio']); ?>
                                                </span>
                                                <a href="<?php echo SITE_URL; ?>/producto/<?php echo $producto['slug']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    Ver detalle
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Blog Results -->
                <?php if (!empty($posts)): ?>
                <div class="result-section" data-type="blog">
                    <h4 class="mb-4">
                        <i class="bi bi-newspaper text-primary me-2"></i> 
                        Artículos del Blog (<?php echo count($posts); ?>)
                    </h4>
                    <div class="mb-5">
                        <?php foreach($posts as $post): ?>
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <div class="d-flex">
                                    <?php if($post['imagen_destacada']): ?>
                                    <img src="<?php echo UPLOADS_URL . '/' . $post['imagen_destacada']; ?>" 
                                         class="me-3 rounded" 
                                         style="width: 120px; height: 80px; object-fit: cover;"
                                         alt="<?php echo sanitize($post['titulo']); ?>">
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <div class="mb-2">
                                            <span class="badge bg-primary-soft text-primary me-2">
                                                <?php echo sanitize($post['categoria']); ?>
                                            </span>
                                            <small class="text-muted">
                                                <?php echo formatDate($post['fecha_publicacion']); ?>
                                            </small>
                                        </div>
                                        <h5 class="card-title">
                                            <a href="<?php echo SITE_URL; ?>/blog/<?php echo $post['slug']; ?>" 
                                               class="text-dark text-decoration-none">
                                                <?php echo highlightSearchTerm($post['titulo'], $query); ?>
                                            </a>
                                        </h5>
                                        <p class="card-text text-muted">
                                            <?php echo highlightSearchTerm($post['extracto'], $query); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Categories Results -->
                <?php if (!empty($categorias)): ?>
                <div class="result-section" data-type="categoria">
                    <h4 class="mb-4">
                        <i class="bi bi-folder text-primary me-2"></i> 
                        Categorías (<?php echo count($categorias); ?>)
                    </h4>
                    <div class="row g-3 mb-5">
                        <?php foreach($categorias as $categoria): ?>
                        <div class="col-md-6">
                            <a href="<?php echo SITE_URL; ?>/categoria/<?php echo $categoria['slug']; ?>" 
                               class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">
                                            <?php echo highlightSearchTerm($categoria['nombre'], $query); ?>
                                        </h5>
                                        <p class="card-text text-muted">
                                            <?php echo highlightSearchTerm($categoria['descripcion'], $query); ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Pages Results -->
                <?php if (!empty($paginas)): ?>
                <div class="result-section" data-type="pagina">
                    <h4 class="mb-4">
                        <i class="bi bi-file-text text-primary me-2"></i> 
                        Páginas (<?php echo count($paginas); ?>)
                    </h4>
                    <div class="list-group mb-5">
                        <?php foreach($paginas as $pagina): ?>
                        <a href="<?php echo SITE_URL . $pagina['url']; ?>" 
                           class="list-group-item list-group-item-action">
                            <h5 class="mb-1"><?php echo highlightSearchTerm($pagina['titulo'], $query); ?></h5>
                            <p class="mb-1 text-muted">
                                <?php echo highlightSearchTerm($pagina['descripcion'], $query); ?>
                            </p>
                            <small class="text-primary"><?php echo SITE_URL . $pagina['url']; ?></small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Function to highlight search terms
function highlightSearchTerm($text, $term) {
    if (empty($term)) return $text;
    
    $pattern = '/(' . preg_quote($term, '/') . ')/i';
    return preg_replace($pattern, '<mark>$1</mark>', $text);
}
?>

<script>
// Filter results by type
document.querySelectorAll('.filter-type').forEach(filter => {
    filter.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Update active state
        document.querySelectorAll('.filter-type').forEach(f => f.classList.remove('active'));
        this.classList.add('active');
        
        // Show/hide sections
        const type = this.dataset.type;
        document.querySelectorAll('.result-section').forEach(section => {
            if (type === 'all' || section.dataset.type === type) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    });
});
</script>

<style>
mark {
    background-color: #fff3cd;
    padding: 0.1em 0.2em;
    border-radius: 3px;
}
</style>

<?php include '../includes/footer.php'; ?>