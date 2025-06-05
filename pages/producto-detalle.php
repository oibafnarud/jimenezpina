<?php
/**
 * Página de Detalle de Producto
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

// Obtener slug del producto
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect(SITE_URL . '/productos', 'Producto no encontrado', MSG_ERROR);
}

// Obtener información del producto
$producto = $db->fetchOne("
    SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug,
           m.nombre as marca_nombre, m.slug as marca_slug, m.logo as marca_logo
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN marcas m ON p.marca_id = m.id
    WHERE p.slug = ? AND p.activo = 1
", [$slug]);

if (!$producto) {
    redirect(SITE_URL . '/productos', 'Producto no encontrado', MSG_ERROR);
}

// Incrementar vistas
$db->query("UPDATE productos SET vistas = vistas + 1 WHERE id = ?", [$producto['id']]);

// Obtener especificaciones
$especificaciones = $db->fetchAll("
    SELECT * FROM especificaciones 
    WHERE producto_id = ? 
    ORDER BY grupo, orden
", [$producto['id']]);

// Agrupar especificaciones
$especsAgrupadas = [];
foreach ($especificaciones as $spec) {
    $grupo = $spec['grupo'] ?: 'General';
    $especsAgrupadas[$grupo][] = $spec;
}

// Obtener imágenes adicionales
$imagenes = $db->fetchAll("
    SELECT * FROM producto_imagenes 
    WHERE producto_id = ? 
    ORDER BY orden
", [$producto['id']]);

// Obtener etiquetas
$etiquetas = $db->fetchAll("
    SELECT e.* FROM etiquetas e
    INNER JOIN producto_etiquetas pe ON e.id = pe.etiqueta_id
    WHERE pe.producto_id = ?
", [$producto['id']]);

// Obtener videos relacionados
$videos = $db->fetchAll("
    SELECT v.* FROM videos v
    INNER JOIN producto_videos pv ON v.id = pv.video_id
    WHERE pv.producto_id = ? AND v.activo = 1
    ORDER BY v.orden
", [$producto['id']]);

// Obtener productos relacionados
$productosRelacionados = $db->fetchAll("
    SELECT p.*, m.nombre as marca_nombre
    FROM productos p
    LEFT JOIN marcas m ON p.marca_id = m.id
    WHERE p.categoria_id = ? AND p.id != ? AND p.activo = 1
    ORDER BY RAND()
    LIMIT 4
", [$producto['categoria_id'], $producto['id']]);

// Meta tags
$pageTitle = $producto['meta_title'] ?: $producto['nombre'];
$pageDescription = $producto['meta_description'] ?: $producto['descripcion_corta'];
$pageKeywords = $producto['meta_keywords'];
$pageImage = $producto['imagen_principal'] ? SITE_URL . UPLOADS_URL . '/' . $producto['imagen_principal'] : '';

include '../includes/header.php';
?>

<!-- Schema.org Markup -->
<script type="application/ld+json">
{
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "<?php echo sanitize($producto['nombre']); ?>",
    "image": "<?php echo $pageImage; ?>",
    "description": "<?php echo sanitize($producto['descripcion_corta']); ?>",
    "sku": "<?php echo sanitize($producto['sku']); ?>",
    "brand": {
        "@type": "Brand",
        "name": "<?php echo sanitize($producto['marca_nombre']); ?>"
    },
    "offers": {
        "@type": "Offer",
        "url": "<?php echo getCurrentUrl(); ?>",
        "priceCurrency": "USD",
        "price": "<?php echo $producto['precio_oferta'] ?: $producto['precio']; ?>",
        "availability": "<?php echo $producto['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>"
    }
}
</script>

<!-- Breadcrumb -->
<section class="py-3 bg-light">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/productos">Productos</a></li>
                <li class="breadcrumb-item">
                    <a href="<?php echo SITE_URL; ?>/categoria/<?php echo $producto['categoria_slug']; ?>">
                        <?php echo sanitize($producto['categoria_nombre']); ?>
                    </a>
                </li>
                <li class="breadcrumb-item active"><?php echo sanitize($producto['nombre']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Detail -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Product Images -->
            <div class="col-lg-6 mb-4">
                <div class="product-gallery">
                    <!-- Main Image -->
                    <div class="main-image mb-3">
                        <div class="position-relative">
                            <?php if($producto['nuevo']): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-3 z-1">Nuevo</span>
                            <?php endif; ?>
                            <?php if($producto['precio_oferta']): ?>
                            <span class="badge bg-warning position-absolute top-0 end-0 m-3 z-1">
                                -<?php echo round((($producto['precio'] - $producto['precio_oferta']) / $producto['precio']) * 100); ?>% OFF
                            </span>
                            <?php endif; ?>
                            <img id="mainImage" 
                                 src="<?php echo $producto['imagen_principal'] ? UPLOADS_URL . '/' . $producto['imagen_principal'] : ASSETS_URL . '/img/no-image.jpg'; ?>" 
                                 class="img-fluid rounded shadow" 
                                 alt="<?php echo sanitize($producto['nombre']); ?>">
                        </div>
                    </div>
                    
                    <!-- Thumbnails -->
                    <?php if (!empty($imagenes)): ?>
                    <div class="thumbnails">
                        <div class="row g-2">
                            <div class="col-3">
                                <img src="<?php echo UPLOADS_URL . '/' . $producto['imagen_principal']; ?>" 
                                     class="img-fluid rounded cursor-pointer gallery-thumb active" 
                                     onclick="changeMainImage(this.src)"
                                     alt="<?php echo sanitize($producto['nombre']); ?>">
                            </div>
                            <?php foreach($imagenes as $img): ?>
                            <div class="col-3">
                                <img src="<?php echo UPLOADS_URL . '/' . $img['imagen']; ?>" 
                                     class="img-fluid rounded cursor-pointer gallery-thumb" 
                                     onclick="changeMainImage(this.src)"
                                     alt="<?php echo sanitize($img['titulo'] ?: $producto['nombre']); ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="product-info">
                    <!-- Brand -->
                    <div class="mb-3">
                        <a href="<?php echo SITE_URL; ?>/marca/<?php echo $producto['marca_slug']; ?>" 
                           class="text-decoration-none">
                            <?php if($producto['marca_logo']): ?>
                            <img src="<?php echo UPLOADS_URL . '/' . $producto['marca_logo']; ?>" 
                                 alt="<?php echo sanitize($producto['marca_nombre']); ?>" 
                                 style="height: 40px;">
                            <?php else: ?>
                            <span class="text-primary fw-semibold"><?php echo sanitize($producto['marca_nombre']); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <!-- Title -->
                    <h1 class="h2 mb-3"><?php echo sanitize($producto['nombre']); ?></h1>
                    
                    <!-- SKU -->
                    <p class="text-muted mb-3">SKU: <?php echo sanitize($producto['sku']); ?></p>
                    
                    <!-- Short Description -->
                    <p class="lead mb-4"><?php echo sanitize($producto['descripcion_corta']); ?></p>
                    
                    <!-- Price -->
                    <div class="price-section bg-light p-4 rounded mb-4">
                        <?php if($producto['precio_oferta']): ?>
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-muted text-decoration-line-through h5">
                                <?php echo formatPrice($producto['precio']); ?>
                            </span>
                            <span class="h2 text-danger mb-0">
                                <?php echo formatPrice($producto['precio_oferta']); ?>
                            </span>
                            <span class="badge bg-danger">
                                Ahorra <?php echo formatPrice($producto['precio'] - $producto['precio_oferta']); ?>
                            </span>
                        </div>
                        <?php else: ?>
                        <div class="h2 text-primary mb-0">
                            <?php echo $producto['precio'] > 0 ? formatPrice($producto['precio']) : 'Precio a consultar'; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($producto['precio'] > 0): ?>
                        <small class="text-muted">*Precio no incluye ITBIS</small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="mb-4">
                        <?php if($producto['stock'] > 0): ?>
                        <div class="d-flex align-items-center text-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <span class="fw-semibold">Disponible en stock</span>
                            <?php if($producto['stock'] <= 5): ?>
                            <span class="ms-2 text-warning">(Últimas <?php echo $producto['stock']; ?> unidades)</span>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="d-flex align-items-center text-danger">
                            <i class="bi bi-x-circle-fill me-2"></i>
                            <span class="fw-semibold">Temporalmente agotado</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Tags -->
                    <?php if (!empty($etiquetas)): ?>
                    <div class="mb-4">
                        <?php foreach($etiquetas as $tag): ?>
                        <span class="badge bg-secondary me-2"><?php echo sanitize($tag['nombre']); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Actions -->
                    <div class="d-grid gap-3 mb-4">
                        <button class="btn btn-primary btn-lg" 
                                onclick="addToQuote(<?php echo $producto['id']; ?>, '<?php echo sanitize($producto['nombre']); ?>')">
                            <i class="bi bi-cart-plus me-2"></i> Agregar a Cotización
                        </button>
                        <a href="<?php echo SITE_URL; ?>/cotizar?producto=<?php echo $producto['id']; ?>" 
                           class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-calculator me-2"></i> Solicitar Cotización Directa
                        </a>
                    </div>
                    
                    <!-- Share -->
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted">Compartir:</span>
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="shareOnWhatsApp('<?php echo getCurrentUrl(); ?>', '<?php echo sanitize($producto['nombre']); ?>')">
                            <i class="bi bi-whatsapp"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="shareOnFacebook('<?php echo getCurrentUrl(); ?>')">
                            <i class="bi bi-facebook"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" 
                                onclick="copyToClipboard('<?php echo getCurrentUrl(); ?>')">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Information Tabs -->
        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                                data-bs-target="#description" type="button">
                            <i class="bi bi-info-circle me-2"></i> Descripción
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="specs-tab" data-bs-toggle="tab" 
                                data-bs-target="#specs" type="button">
                            <i class="bi bi-list-check me-2"></i> Especificaciones
                        </button>
                    </li>
                    <?php if (!empty($videos)): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="videos-tab" data-bs-toggle="tab" 
                                data-bs-target="#videos" type="button">
                            <i class="bi bi-play-circle me-2"></i> Videos
                        </button>
                    </li>
                    <?php endif; ?>
                    <?php if ($producto['ficha_tecnica'] || $producto['manual_usuario']): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="downloads-tab" data-bs-toggle="tab" 
                                data-bs-target="#downloads" type="button">
                            <i class="bi bi-download me-2"></i> Descargas
                        </button>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="warranty-tab" data-bs-toggle="tab" 
                                data-bs-target="#warranty" type="button">
                            <i class="bi bi-shield-check me-2"></i> Garantía
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content mt-4" id="productTabsContent">
                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <div class="product-description">
                            <?php echo nl2br($producto['descripcion']); ?>
                        </div>
                    </div>
                    
                    <!-- Specifications Tab -->
                    <div class="tab-pane fade" id="specs" role="tabpanel">
                        <?php if (!empty($especsAgrupadas)): ?>
                            <?php foreach($especsAgrupadas as $grupo => $specs): ?>
                            <h5 class="mb-3"><?php echo sanitize($grupo); ?></h5>
                            <table class="table table-striped mb-4">
                                <tbody>
                                    <?php foreach($specs as $spec): ?>
                                    <tr>
                                        <td class="fw-semibold" style="width: 40%;">
                                            <?php echo sanitize($spec['nombre']); ?>
                                        </td>
                                        <td><?php echo sanitize($spec['valor']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <p class="text-muted">No hay especificaciones disponibles para este producto.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Videos Tab -->
                    <?php if (!empty($videos)): ?>
                    <div class="tab-pane fade" id="videos" role="tabpanel">
                        <div class="row g-4">
                            <?php foreach($videos as $video): ?>
                            <div class="col-md-6">
                                <div class="ratio ratio-16x9">
                                    <iframe src="https://www.youtube.com/embed/<?php echo $video['youtube_id']; ?>" 
                                            title="<?php echo sanitize($video['titulo']); ?>" 
                                            allowfullscreen></iframe>
                                </div>
                                <h6 class="mt-2"><?php echo sanitize($video['titulo']); ?></h6>
                                <?php if($video['descripcion']): ?>
                                <p class="text-muted small"><?php echo sanitize($video['descripcion']); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Downloads Tab -->
                    <?php if ($producto['ficha_tecnica'] || $producto['manual_usuario']): ?>
                    <div class="tab-pane fade" id="downloads" role="tabpanel">
                        <div class="list-group">
                            <?php if ($producto['ficha_tecnica']): ?>
                            <a href="<?php echo UPLOADS_URL . '/' . $producto['ficha_tecnica']; ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                               download>
                                <div>
                                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                    <strong>Ficha Técnica</strong>
                                    <small class="text-muted ms-2">PDF</small>
                                </div>
                                <i class="bi bi-download"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($producto['manual_usuario']): ?>
                            <a href="<?php echo UPLOADS_URL . '/' . $producto['manual_usuario']; ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                               download>
                                <div>
                                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                    <strong>Manual de Usuario</strong>
                                    <small class="text-muted ms-2">PDF</small>
                                </div>
                                <i class="bi bi-download"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Warranty Tab -->
                    <div class="tab-pane fade" id="warranty" role="tabpanel">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-3">Información de Garantía</h5>
                                <div class="alert alert-success">
                                    <i class="bi bi-shield-check me-2"></i>
                                    Este producto cuenta con <strong><?php echo $producto['garantia_meses']; ?> meses</strong> 
                                    de garantía oficial del fabricante.
                                </div>
                                <h6>La garantía incluye:</h6>
                                <ul>
                                    <li>Defectos de fabricación</li>
                                    <li>Mal funcionamiento en condiciones normales de uso</li>
                                    <li>Soporte técnico especializado</li>
                                    <li>Repuestos originales</li>
                                </ul>
                                <h6>La garantía NO incluye:</h6>
                                <ul>
                                    <li>Daños por mal uso o negligencia</li>
                                    <li>Daños por caídas o golpes</li>
                                    <li>Modificaciones no autorizadas</li>
                                    <li>Desgaste normal por uso</li>
                                </ul>
                                <p class="mt-3">
                                    <strong>Importante:</strong> Para hacer válida la garantía, conserve su factura de compra 
                                    y registre su producto en el sitio web del fabricante.
                                </p>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center">
                                        <i class="bi bi-headset display-4 text-primary mb-3"></i>
                                        <h5>¿Necesita soporte?</h5>
                                        <p>Nuestro equipo técnico está disponible para ayudarle</p>
                                        <a href="<?php echo SITE_URL; ?>/contacto" class="btn btn-primary">
                                            Contactar Soporte
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($productosRelacionados)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">Productos Relacionados</h3>
                <div class="row g-4">
                    <?php foreach($productosRelacionados as $relacionado): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 product-card border-0 shadow-sm">
                            <a href="<?php echo SITE_URL; ?>/producto/<?php echo $relacionado['slug']; ?>">
                                <div class="product-image p-4 bg-light">
                                    <img src="<?php echo $relacionado['imagen_principal'] ? UPLOADS_URL . '/' . $relacionado['imagen_principal'] : ASSETS_URL . '/img/no-image.jpg'; ?>" 
                                         class="img-fluid" 
                                         alt="<?php echo sanitize($relacionado['nombre']); ?>">
                                </div>
                            </a>
                            <div class="card-body">
                                <small class="text-primary"><?php echo sanitize($relacionado['marca_nombre']); ?></small>
                                <h5 class="card-title mt-1">
                                    <a href="<?php echo SITE_URL; ?>/producto/<?php echo $relacionado['slug']; ?>" 
                                       class="text-dark text-decoration-none">
                                        <?php echo sanitize($relacionado['nombre']); ?>
                                    </a>
                                </h5>
                                <div class="h5 text-primary">
                                    <?php echo formatPrice($relacionado['precio_oferta'] ?: $relacionado['precio']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Custom Styles -->
<style>
.gallery-thumb {
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.3s;
}
.gallery-thumb:hover,
.gallery-thumb.active {
    opacity: 1;
}
.product-description {
    font-size: 1.1rem;
    line-height: 1.8;
}
.product-description h3,
.product-description h4,
.product-description h5 {
    margin-top: 2rem;
    margin-bottom: 1rem;
}
.product-description ul,
.product-description ol {
    margin-bottom: 1rem;
}
.product-description img {
    max-width: 100%;
    height: auto;
    margin: 1rem 0;
}
</style>

<script>
// Change main image
function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
    
    // Update active thumbnail
    document.querySelectorAll('.gallery-thumb').forEach(thumb => {
        thumb.classList.remove('active');
        if (thumb.src === src) {
            thumb.classList.add('active');
        }
    });
}

// Add to quote function
function addToQuote(productId, productName) {
    // Aquí iría la lógica para agregar a la cotización
    // Por ahora solo mostramos un mensaje
    showAlert(`${productName} agregado a la cotización`, 'success');
}

// Initialize lightbox for images (if using a library)
document.addEventListener('DOMContentLoaded', function() {
    // Image zoom on click
    const mainImage = document.getElementById('mainImage');
    if (mainImage) {
        mainImage.style.cursor = 'zoom-in';
        mainImage.addEventListener('click', function() {
            // Aquí podrías implementar un lightbox o modal para zoom
            window.open(this.src, '_blank');
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>