<?php
/**
 * Página de Productos - Catálogo
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

// Obtener parámetros de filtrado
$categoria_slug = $_GET['categoria'] ?? '';
$marca_slug = $_GET['marca'] ?? '';
$busqueda = $_GET['q'] ?? '';
$orden = $_GET['orden'] ?? 'recientes';
$page = (int)($_GET['page'] ?? 1);

// Construir consulta base
$where = ['p.activo = 1'];
$params = [];
$joins = '';

// Filtro por categoría
if ($categoria_slug) {
    $joins .= ' LEFT JOIN categorias c ON p.categoria_id = c.id';
    $where[] = 'c.slug = ?';
    $params[] = $categoria_slug;
    
    // Obtener información de la categoría
    $categoria = $db->fetchOne("SELECT * FROM categorias WHERE slug = ? AND activo = 1", [$categoria_slug]);
    if (!$categoria) {
        redirect(SITE_URL . '/productos', 'Categoría no encontrada', MSG_ERROR);
    }
}

// Filtro por marca
if ($marca_slug) {
    $joins .= ' LEFT JOIN marcas m ON p.marca_id = m.id';
    $where[] = 'm.slug = ?';
    $params[] = $marca_slug;
    
    // Obtener información de la marca
    $marca = $db->fetchOne("SELECT * FROM marcas WHERE slug = ? AND activo = 1", [$marca_slug]);
    if (!$marca) {
        redirect(SITE_URL . '/productos', 'Marca no encontrada', MSG_ERROR);
    }
}

// Filtro por búsqueda
if ($busqueda) {
    $where[] = '(p.nombre LIKE ? OR p.descripcion_corta LIKE ? OR p.sku LIKE ?)';
    $searchTerm = '%' . $busqueda . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Orden
$orderBy = match($orden) {
    'precio_menor' => 'p.precio ASC',
    'precio_mayor' => 'p.precio DESC',
    'nombre' => 'p.nombre ASC',
    'mas_vendidos' => 'p.vistas DESC',
    default => 'p.created_at DESC'
};

// Contar total de productos
$whereClause = implode(' AND ', $where);
$countQuery = "SELECT COUNT(*) FROM productos p $joins WHERE $whereClause";
$totalItems = $db->fetchColumn($countQuery, $params);

// Paginación
$pagination = paginate($totalItems, $page, ITEMS_PER_PAGE);

// Obtener productos
$query = "
    SELECT p.*, 
           cat.nombre as categoria_nombre, cat.slug as categoria_slug,
           mar.nombre as marca_nombre, mar.slug as marca_slug
    FROM productos p
    LEFT JOIN categorias cat ON p.categoria_id = cat.id
    LEFT JOIN marcas mar ON p.marca_id = mar.id
    $joins
    WHERE $whereClause
    ORDER BY $orderBy
    LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}
";

$productos = $db->fetchAll($query, $params);

// Obtener categorías y marcas para filtros
$categoriasFiltro = $db->fetchAll("
    SELECT c.*, COUNT(p.id) as total_productos
    FROM categorias c
    LEFT JOIN productos p ON c.id = p.categoria_id AND p.activo = 1
    WHERE c.activo = 1 AND c.parent_id IS NULL
    GROUP BY c.id
    HAVING total_productos > 0
    ORDER BY c.orden ASC
");

$marcasFiltro = $db->fetchAll("
    SELECT m.*, COUNT(p.id) as total_productos
    FROM marcas m
    LEFT JOIN productos p ON m.id = p.marca_id AND p.activo = 1
    WHERE m.activo = 1
    GROUP BY m.id
    HAVING total_productos > 0
    ORDER BY m.orden ASC
");

// Rango de precios
$precioMin = $db->fetchColumn("SELECT MIN(precio) FROM productos WHERE activo = 1");
$precioMax = $db->fetchColumn("SELECT MAX(precio) FROM productos WHERE activo = 1");

// Meta tags
$pageTitle = 'Catálogo de Productos';
if ($categoria) {
    $pageTitle = $categoria['nombre'];
    $pageDescription = $categoria['meta_description'] ?: $categoria['descripcion'];
} elseif ($marca) {
    $pageTitle = 'Productos ' . $marca['nombre'];
    $pageDescription = 'Encuentra todos los productos de la marca ' . $marca['nombre'];
} else {
    $pageDescription = 'Explore nuestro catálogo completo de instrumentos topográficos. Estaciones totales, GPS, niveles y más.';
}

include '../includes/header.php';
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/productos">Productos</a></li>
                <?php if ($categoria): ?>
                <li class="breadcrumb-item active"><?php echo sanitize($categoria['nombre']); ?></li>
                <?php elseif ($marca): ?>
                <li class="breadcrumb-item active"><?php echo sanitize($marca['nombre']); ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</section>

<!-- Page Header -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">
                    <?php 
                    if ($categoria) {
                        echo sanitize($categoria['nombre']);
                    } elseif ($marca) {
                        echo 'Productos ' . sanitize($marca['nombre']);
                    } elseif ($busqueda) {
                        echo 'Resultados para: "' . sanitize($busqueda) . '"';
                    } else {
                        echo 'Catálogo de Productos';
                    }
                    ?>
                </h1>
                <p class="lead mb-0 opacity-90">
                    <?php 
                    if ($categoria && $categoria['descripcion']) {
                        echo sanitize($categoria['descripcion']);
                    } elseif ($marca && $marca['descripcion']) {
                        echo sanitize($marca['descripcion']);
                    } else {
                        echo 'Encuentre el equipo perfecto para su proyecto';
                    }
                    ?>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="mt-4 mt-lg-0">
                    <span class="h3"><?php echo $totalItems; ?></span>
                    <span class="opacity-75">productos encontrados</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3">
                <div class="sticky-lg-top" style="top: 100px;">
                    <!-- Filtros -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">Filtros</h5>
                        </div>
                        <div class="card-body">
                            <!-- Búsqueda -->
                            <form action="<?php echo SITE_URL; ?>/productos" method="GET" class="mb-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="q" 
                                           placeholder="Buscar productos..." 
                                           value="<?php echo sanitize($busqueda); ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                            
                            <!-- Categorías -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Categorías</h6>
                                <div class="list-group list-group-flush">
                                    <a href="<?php echo SITE_URL; ?>/productos" 
                                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo !$categoria_slug ? 'active' : ''; ?>">
                                        Todas las categorías
                                        <span class="badge bg-primary rounded-pill"><?php echo $totalItems; ?></span>
                                    </a>
                                    <?php foreach($categoriasFiltro as $cat): ?>
                                    <a href="<?php echo SITE_URL; ?>/categoria/<?php echo $cat['slug']; ?>" 
                                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $categoria_slug == $cat['slug'] ? 'active' : ''; ?>">
                                        <?php echo sanitize($cat['nombre']); ?>
                                        <span class="badge bg-primary rounded-pill"><?php echo $cat['total_productos']; ?></span>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Marcas -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Marcas</h6>
                                <?php foreach($marcasFiltro as $mar): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="marca<?php echo $mar['id']; ?>"
                                           onchange="window.location.href='<?php echo SITE_URL; ?>/marca/<?php echo $mar['slug']; ?>'"
                                           <?php echo $marca_slug == $mar['slug'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="marca<?php echo $mar['id']; ?>">
                                        <?php echo sanitize($mar['nombre']); ?> 
                                        <small class="text-muted">(<?php echo $mar['total_productos']; ?>)</small>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Rango de Precio -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Precio</h6>
                                <div class="price-range">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><?php echo formatPrice($precioMin); ?></span>
                                        <span><?php echo formatPrice($precioMax); ?></span>
                                    </div>
                                    <input type="range" class="form-range" min="<?php echo $precioMin; ?>" 
                                           max="<?php echo $precioMax; ?>" step="100" id="priceRange">
                                </div>
                            </div>
                            
                            <?php if($categoria_slug || $marca_slug || $busqueda): ?>
                            <a href="<?php echo SITE_URL; ?>/productos" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-x-circle me-2"></i> Limpiar filtros
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Banner CTA -->
                    <div class="card border-0 bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-headset display-4 mb-3"></i>
                            <h5>¿Necesita ayuda?</h5>
                            <p class="mb-3">Nuestros expertos están listos para asesorarle</p>
                            <a href="<?php echo SITE_URL; ?>/contacto" class="btn btn-white btn-sm">
                                Contáctenos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Toolbar -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        Mostrando <?php echo ($pagination['offset'] + 1); ?>-<?php echo min($pagination['offset'] + $pagination['items_per_page'], $totalItems); ?> 
                        de <?php echo $totalItems; ?> productos
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" onchange="window.location.href='?orden=' + this.value">
                            <option value="recientes" <?php echo $orden == 'recientes' ? 'selected' : ''; ?>>Más recientes</option>
                            <option value="precio_menor" <?php echo $orden == 'precio_menor' ? 'selected' : ''; ?>>Menor precio</option>
                            <option value="precio_mayor" <?php echo $orden == 'precio_mayor' ? 'selected' : ''; ?>>Mayor precio</option>
                            <option value="nombre" <?php echo $orden == 'nombre' ? 'selected' : ''; ?>>Nombre A-Z</option>
                            <option value="mas_vendidos" <?php echo $orden == 'mas_vendidos' ? 'selected' : ''; ?>>Más populares</option>
                        </select>
                        
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary active" id="gridView">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="listView">
                                <i class="bi bi-list"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Products -->
                <?php if (empty($productos)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="mt-3">No se encontraron productos</h4>
                    <p class="text-muted">Intente ajustar los filtros o realizar una nueva búsqueda</p>
                    <a href="<?php echo SITE_URL; ?>/productos" class="btn btn-primary mt-3">
                        Ver todos los productos
                    </a>
                </div>
                <?php else: ?>
                <div class="row g-4" id="productsGrid">
                    <?php foreach($productos as $producto): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 product-card border-0 shadow-sm">
                            <div class="position-relative">
                                <?php if($producto['nuevo']): ?>
                                <span class="badge bg-danger position-absolute top-0 start-0 m-3 z-1">Nuevo</span>
                                <?php endif; ?>
                                <?php if($producto['precio_oferta']): ?>
                                <span class="badge bg-warning position-absolute top-0 end-0 m-3 z-1">
                                    -<?php echo round((($producto['precio'] - $producto['precio_oferta']) / $producto['precio']) * 100); ?>%
                                </span>
                                <?php endif; ?>
                                
                                <a href="<?php echo SITE_URL; ?>/producto/<?php echo $producto['slug']; ?>">
                                    <div class="product-image p-4 bg-light">
                                        <img src="<?php echo $producto['imagen_principal'] ? UPLOADS_URL . '/' . $producto['imagen_principal'] : ASSETS_URL . '/img/no-image.jpg'; ?>" 
                                             class="img-fluid" 
                                             alt="<?php echo sanitize($producto['nombre']); ?>">
                                    </div>
                                </a>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <div class="mb-2">
                                    <a href="<?php echo SITE_URL; ?>/marca/<?php echo $producto['marca_slug']; ?>" 
                                       class="text-primary text-decoration-none small fw-semibold">
                                        <?php echo sanitize($producto['marca_nombre']); ?>
                                    </a>
                                </div>
                                
                                <h5 class="card-title">
                                    <a href="<?php echo SITE_URL; ?>/producto/<?php echo $producto['slug']; ?>" 
                                       class="text-dark text-decoration-none">
                                        <?php echo sanitize($producto['nombre']); ?>
                                    </a>
                                </h5>
                                
                                <p class="card-text text-muted small flex-grow-1">
                                    <?php echo sanitize($producto['descripcion_corta']); ?>
                                </p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
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
                                                <?php echo $producto['precio'] > 0 ? formatPrice($producto['precio']) : 'Consultar'; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <?php if($producto['stock'] > 0): ?>
                                            <span class="badge bg-success-soft text-success">
                                                <i class="bi bi-check-circle"></i> En stock
                                            </span>
                                            <?php else: ?>
                                            <span class="badge bg-danger-soft text-danger">
                                                <i class="bi bi-x-circle"></i> Agotado
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="<?php echo SITE_URL; ?>/producto/<?php echo $producto['slug']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye me-2"></i> Ver detalles
                                        </a>
                                        <button class="btn btn-primary btn-sm btn-add-to-quote" 
                                                data-product-id="<?php echo $producto['id']; ?>"
                                                data-product-name="<?php echo sanitize($producto['nombre']); ?>">
                                            <i class="bi bi-cart-plus me-2"></i> Agregar a cotización
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Paginación de productos" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['has_previous']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $pagination['previous_page']; ?>&orden=<?php echo $orden; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php foreach ($pagination['pages'] as $page): ?>
                        <li class="page-item <?php echo $page['is_current'] ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $page['url']; ?>&orden=<?php echo $orden; ?>">
                                <?php echo $page['number']; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        
                        <?php if ($pagination['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&orden=<?php echo $orden; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="feature-box">
                    <i class="bi bi-truck display-4 text-primary mb-3"></i>
                    <h5>Envío Rápido</h5>
                    <p class="text-muted mb-0">Entrega en 24-48 horas en Santo Domingo</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-box">
                    <i class="bi bi-shield-check display-4 text-primary mb-3"></i>
                    <h5>Garantía Oficial</h5>
                    <p class="text-muted mb-0">Respaldo directo del fabricante</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-box">
                    <i class="bi bi-arrow-repeat display-4 text-primary mb-3"></i>
                    <h5>Devoluciones</h5>
                    <p class="text-muted mb-0">30 días para cambios</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-box">
                    <i class="bi bi-credit-card display-4 text-primary mb-3"></i>
                    <h5>Pago Seguro</h5>
                    <p class="text-muted mb-0">Múltiples formas de pago</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Cambiar vista de productos
document.getElementById('gridView')?.addEventListener('click', function() {
    document.getElementById('productsGrid').className = 'row g-4';
    this.classList.add('active');
    document.getElementById('listView').classList.remove('active');
});

document.getElementById('listView')?.addEventListener('click', function() {
    document.getElementById('productsGrid').className = 'row g-2';
    this.classList.add('active');
    document.getElementById('gridView').classList.remove('active');
});

// Agregar a cotización
document.querySelectorAll('.btn-add-to-quote').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const productName = this.dataset.productName;
        
        // Aquí iría la lógica para agregar a la cotización
        showAlert(`${productName} agregado a la cotización`, 'success');
    });
});
</script>

<?php include '../includes/footer.php'; ?>