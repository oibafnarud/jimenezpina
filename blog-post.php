<?php
/**
 * Página de Artículo Individual del Blog
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

// Obtener slug del post
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect(SITE_URL . '/blog', 'Artículo no encontrado', MSG_ERROR);
}

// Obtener información del post
$post = $db->fetchOne("
    SELECT p.*, u.nombre as autor_nombre
    FROM blog_posts p
    LEFT JOIN usuarios u ON p.autor_id = u.id
    WHERE p.slug = ? AND p.estado = 'publicado' AND p.fecha_publicacion <= NOW()
", [$slug]);

if (!$post) {
    redirect(SITE_URL . '/blog', 'Artículo no encontrado', MSG_ERROR);
}

// Incrementar vistas
$db->query("UPDATE blog_posts SET vistas = vistas + 1 WHERE id = ?", [$post['id']]);

// Obtener comentarios aprobados
$comentarios = $db->fetchAll("
    SELECT * FROM blog_comentarios 
    WHERE post_id = ? AND aprobado = 1
    ORDER BY created_at DESC
", [$post['id']]);

// Obtener posts relacionados
$postsRelacionados = $db->fetchAll("
    SELECT titulo, slug, imagen_destacada, extracto, fecha_publicacion
    FROM blog_posts
    WHERE categoria = ? AND id != ? AND estado = 'publicado' AND fecha_publicacion <= NOW()
    ORDER BY fecha_publicacion DESC
    LIMIT 3
", [$post['categoria'], $post['id']]);

// Meta tags
$pageTitle = $post['meta_title'] ?: $post['titulo'];
$pageDescription = $post['meta_description'] ?: $post['extracto'];
$pageKeywords = $post['meta_keywords'] ?: $post['tags'];
$pageImage = $post['imagen_destacada'] ? SITE_URL . UPLOADS_URL . '/' . $post['imagen_destacada'] : '';

include '../includes/header.php';
?>

<!-- Schema.org Article Markup -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "<?php echo sanitize($post['titulo']); ?>",
    "description": "<?php echo sanitize($post['extracto']); ?>",
    "image": "<?php echo $pageImage; ?>",
    "datePublished": "<?php echo date('c', strtotime($post['fecha_publicacion'])); ?>",
    "dateModified": "<?php echo date('c', strtotime($post['updated_at'])); ?>",
    "author": {
        "@type": "Person",
        "name": "<?php echo sanitize($post['autor_nombre']); ?>"
    },
    "publisher": {
        "@type": "Organization",
        "name": "Jiménez & Piña Survey Instruments",
        "logo": {
            "@type": "ImageObject",
            "url": "<?php echo SITE_URL . ASSETS_URL; ?>/img/logo.png"
        }
    }
}
</script>

<!-- Breadcrumb -->
<section class="py-3 bg-light">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/blog">Blog</a></li>
                <li class="breadcrumb-item">
                    <a href="<?php echo SITE_URL; ?>/blog?categoria=<?php echo urlencode($post['categoria']); ?>">
                        <?php echo sanitize($post['categoria']); ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo sanitize($post['titulo']); ?>
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Article Content -->
<article class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Article Header -->
                <header class="mb-5">
                    <div class="mb-3">
                        <a href="<?php echo SITE_URL; ?>/blog?categoria=<?php echo urlencode($post['categoria']); ?>" 
                           class="badge bg-primary text-decoration-none">
                            <?php echo sanitize($post['categoria']); ?>
                        </a>
                    </div>
                    
                    <h1 class="display-5 fw-bold mb-4"><?php echo sanitize($post['titulo']); ?></h1>
                    
                    <div class="d-flex flex-wrap align-items-center gap-4 text-muted mb-4">
                        <div>
                            <i class="bi bi-person me-2"></i>
                            Por <?php echo sanitize($post['autor_nombre']); ?>
                        </div>
                        <div>
                            <i class="bi bi-calendar3 me-2"></i>
                            <?php echo formatDate($post['fecha_publicacion'], 'd \d\e F, Y'); ?>
                        </div>
                        <div>
                            <i class="bi bi-clock me-2"></i>
                            <?php 
                            $wordCount = str_word_count(strip_tags($post['contenido']));
                            $readTime = ceil($wordCount / 200);
                            echo $readTime . ' min de lectura';
                            ?>
                        </div>
                        <div>
                            <i class="bi bi-eye me-2"></i>
                            <?php echo number_format($post['vistas']); ?> vistas
                        </div>
                    </div>
                    
                    <?php if($post['imagen_destacada']): ?>
                    <img src="<?php echo UPLOADS_URL . '/' . $post['imagen_destacada']; ?>" 
                         class="img-fluid rounded shadow" 
                         alt="<?php echo sanitize($post['titulo']); ?>">
                    <?php endif; ?>
                </header>
                
                <!-- Article Body -->
                <div class="article-content mb-5">
                    <p class="lead"><?php echo sanitize($post['extracto']); ?></p>
                    
                    <?php echo $post['contenido']; ?>
                </div>
                
                <!-- Tags -->
                <?php if($post['tags']): ?>
                <div class="mb-5">
                    <i class="bi bi-tags me-2"></i>
                    <?php 
                    $tags = explode(',', $post['tags']);
                    foreach($tags as $tag): 
                        $tag = trim($tag);
                    ?>
                    <a href="<?php echo SITE_URL; ?>/blog?q=<?php echo urlencode($tag); ?>" 
                       class="badge bg-light text-dark text-decoration-none me-2">
                        <?php echo sanitize($tag); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Share Buttons -->
                <div class="border-top border-bottom py-4 mb-5">
                    <h5 class="mb-3">Compartir este artículo</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success" 
                                onclick="shareOnWhatsApp('<?php echo getCurrentUrl(); ?>', '<?php echo sanitize($post['titulo']); ?>')">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </button>
                        <button class="btn btn-outline-primary" 
                                onclick="shareOnFacebook('<?php echo getCurrentUrl(); ?>')">
                            <i class="bi bi-facebook"></i> Facebook
                        </button>
                        <button class="btn btn-outline-info" 
                                onclick="shareOnTwitter('<?php echo getCurrentUrl(); ?>', '<?php echo sanitize($post['titulo']); ?>')">
                            <i class="bi bi-twitter"></i> Twitter
                        </button>
                        <button class="btn btn-outline-secondary" 
                                onclick="copyToClipboard('<?php echo getCurrentUrl(); ?>')">
                            <i class="bi bi-link-45deg"></i> Copiar enlace
                        </button>
                    </div>
                </div>
                
                <!-- Author Box -->
                <div class="bg-light rounded p-4 mb-5">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="author-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px; font-size: 2rem;">
                                <?php echo strtoupper(substr($post['autor_nombre'], 0, 1)); ?>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-4">
                            <h5 class="mb-1">Sobre el autor</h5>
                            <h6 class="text-primary mb-2"><?php echo sanitize($post['autor_nombre']); ?></h6>
                            <p class="mb-0 text-muted">
                                Especialista en instrumentos topográficos con amplia experiencia en el sector. 
                                Comparte conocimientos y mejores prácticas para profesionales de la topografía.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Related Posts -->
                <?php if (!empty($postsRelacionados)): ?>
                <div class="mb-5">
                    <h3 class="mb-4">Artículos Relacionados</h3>
                    <div class="row g-4">
                        <?php foreach($postsRelacionados as $relacionado): ?>
                        <div class="col-md-4">
                            <article class="card h-100 border-0 shadow-sm">
                                <?php if($relacionado['imagen_destacada']): ?>
                                <img src="<?php echo UPLOADS_URL . '/' . $relacionado['imagen_destacada']; ?>" 
                                     class="card-img-top" style="height: 150px; object-fit: cover;"
                                     alt="<?php echo sanitize($relacionado['titulo']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="<?php echo SITE_URL; ?>/blog/<?php echo $relacionado['slug']; ?>" 
                                           class="text-dark text-decoration-none">
                                            <?php echo sanitize($relacionado['titulo']); ?>
                                        </a>
                                    </h6>
                                    <p class="card-text small text-muted">
                                        <?php echo sanitize(substr($relacionado['extracto'], 0, 100)) . '...'; ?>
                                    </p>
                                </div>
                            </article>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Comments Section -->
                <div id="comments">
                    <h3 class="mb-4">
                        Comentarios 
                        <?php if(count($comentarios) > 0): ?>
                        <span class="badge bg-primary"><?php echo count($comentarios); ?></span>
                        <?php endif; ?>
                    </h3>
                    
                    <?php if($post['permitir_comentarios']): ?>
                    <!-- Comment Form -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Deja tu comentario</h5>
                            <form action="<?php echo SITE_URL; ?>/api/comentarios.php" method="POST" class="needs-validation" novalidate>
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nombre" class="form-label">Nombre *</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                                        <div class="invalid-feedback">
                                            Por favor ingrese su nombre
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback">
                                            Por favor ingrese un email válido
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="comentario" class="form-label">Comentario *</label>
                                        <textarea class="form-control" id="comentario" name="comentario" 
                                                  rows="4" required></textarea>
                                        <div class="invalid-feedback">
                                            Por favor escriba su comentario
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-send me-2"></i> Publicar Comentario
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Comments List -->
                    <?php if (!empty($comentarios)): ?>
                    <div class="comments-list">
                        <?php foreach($comentarios as $comentario): ?>
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="comment-avatar bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <?php echo strtoupper(substr($comentario['nombre'], 0, 1)); ?>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="bg-light rounded p-3">
                                    <h6 class="mb-1"><?php echo sanitize($comentario['nombre']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo formatDateHuman($comentario['created_at']); ?>
                                    </small>
                                    <p class="mb-0 mt-2">
                                        <?php echo nl2br(sanitize($comentario['comentario'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">Sé el primero en comentar este artículo.</p>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Los comentarios están deshabilitados para este artículo.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</article>

<!-- Newsletter CTA -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h3 class="mb-3">¿Te gustó este artículo?</h3>
                <p class="mb-0 opacity-90">
                    Suscríbete a nuestro newsletter y recibe contenido exclusivo sobre topografía
                </p>
            </div>
            <div class="col-lg-6">
                <form action="<?php echo SITE_URL; ?>/api/newsletter.php" method="POST" class="newsletter-form">
                    <div class="input-group input-group-lg">
                        <input type="email" class="form-control" placeholder="Tu correo electrónico" required>
                        <button class="btn btn-warning" type="submit">
                            <i class="bi bi-envelope me-2"></i> Suscribirse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.article-content {
    font-size: 1.1rem;
    line-height: 1.8;
}
.article-content h2,
.article-content h3,
.article-content h4 {
    margin-top: 2rem;
    margin-bottom: 1rem;
}
.article-content p {
    margin-bottom: 1.5rem;
}
.article-content img {
    max-width: 100%;
    height: auto;
    margin: 2rem 0;
}
.article-content ul,
.article-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}
.article-content blockquote {
    border-left: 4px solid var(--bs-primary);
    padding-left: 1rem;
    margin: 2rem 0;
    font-style: italic;
    color: #6c757d;
}
.article-content pre {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
}
.article-content code {
    background: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-family: 'Courier New', monospace;
}
</style>

<?php include '../includes/footer.php'; ?>
                