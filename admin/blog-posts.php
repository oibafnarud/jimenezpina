<?php
/**
 * Gestión de Blog Posts - Panel Administrativo
 * /admin/blog-posts.php
 */
require_once 'includes/header.php';

// Verificar permisos
if (!canEdit('blog')) {
    redirect(ADMIN_URL . '/dashboard.php', 'No tiene permisos para acceder a esta sección', MSG_ERROR);
}

// Procesar acciones
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($action == 'delete' && $id) {
    if (!verifyCSRFToken($_GET['token'] ?? '')) {
        redirect(ADMIN_URL . '/blog-posts.php', 'Token de seguridad inválido', MSG_ERROR);
    }
    
    // Obtener información del post
    $post = $db->fetchOne("SELECT titulo, imagen_destacada FROM blog_posts WHERE id = ?", [$id]);
    
    if ($post) {
        // Eliminar imagen destacada
        if ($post['imagen_destacada']) {
            deleteFile($post['imagen_destacada']);
        }
        
        // Eliminar post (los comentarios se eliminan en cascada)
        $db->delete('blog_posts', 'id = ?', [$id]);
        
        logActivity('blog_deleted', "Post eliminado: {$post['titulo']}", 'blog_posts', $id);
        redirect(ADMIN_URL . '/blog-posts.php', 'Artículo eliminado correctamente', MSG_SUCCESS);
    }
}

if ($action == 'publish' && $id) {
    $db->update('blog_posts', 
        ['estado' => 'publicado', 'fecha_publicacion' => date('Y-m-d H:i:s')], 
        'id = ?', 
        [$id]
    );
    redirect(ADMIN_URL . '/blog-posts.php', 'Artículo publicado correctamente', MSG_SUCCESS);
}

// Obtener filtros
$search = $_GET['search'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$estado = $_GET['estado'] ?? '';

// Construir consulta
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(titulo LIKE ? OR contenido LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($categoria) {
    $where[] = "categoria = ?";
    $params[] = $categoria;
}

if ($estado) {
    $where[] = "estado = ?";
    $params[] = $estado;
}

$whereClause = implode(' AND ', $where);

// Obtener posts
$posts = $db->fetchAll("
    SELECT p.*, u.nombre as autor_nombre,
           (SELECT COUNT(*) FROM blog_comentarios WHERE post_id = p.id) as total_comentarios,
           (SELECT COUNT(*) FROM blog_comentarios WHERE post_id = p.id AND aprobado = 0) as comentarios_pendientes
    FROM blog_posts p
    LEFT JOIN usuarios u ON p.autor_id = u.id
    WHERE $whereClause
    ORDER BY p.created_at DESC
", $params);

// Obtener categorías únicas
$categoriasBlog = $db->fetchAll("
    SELECT DISTINCT categoria 
    FROM blog_posts 
    WHERE categoria IS NOT NULL 
    ORDER BY categoria
");

$pageTitle = 'Gestión del Blog';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Blog</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Blog</li>
            </ol>
        </nav>
    </div>
    <a href="<?php echo ADMIN_URL; ?>/blog-form.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nuevo Artículo
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" 
                       placeholder="Buscar por título o contenido..." 
                       value="<?php echo sanitize($search); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php foreach($categoriasBlog as $cat): ?>
                    <option value="<?php echo $cat['categoria']; ?>" 
                            <?php echo $categoria == $cat['categoria'] ? 'selected' : ''; ?>>
                        <?php echo sanitize($cat['categoria']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="estado">
                    <option value="">Todos los estados</option>
                    <option value="borrador" <?php echo $estado == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="publicado" <?php echo $estado == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                    <option value="programado" <?php echo $estado == 'programado' ? 'selected' : ''; ?>>Programado</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Posts -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="80">Imagen</th>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Autor</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Vistas</th>
                        <th>Comentarios</th>
                        <th width="150">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($posts as $post): ?>
                    <tr>
                        <td>
                            <?php if($post['imagen_destacada']): ?>
                            <img src="<?php echo UPLOADS_URL . '/' . $post['imagen_destacada']; ?>" 
                                 class="img-thumbnail" 
                                 style="width: 60px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo sanitize($post['titulo']); ?></strong><br>
                            <small class="text-muted">/blog/<?php echo $post['slug']; ?></small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo sanitize($post['categoria']); ?>
                            </span>
                        </td>
                        <td><?php echo sanitize($post['autor_nombre']); ?></td>
                        <td>
                            <?php
                            $estadoBadge = match($post['estado']) {
                                'borrador' => 'bg-secondary',
                                'publicado' => 'bg-success',
                                'programado' => 'bg-warning',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?php echo $estadoBadge; ?>">
                                <?php echo ucfirst($post['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo formatDate($post['fecha_publicacion'] ?: $post['created_at']); ?>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo number_format($post['vistas']); ?></span>
                        </td>
                        <td>
                            <?php echo $post['total_comentarios']; ?>
                            <?php if($post['comentarios_pendientes'] > 0): ?>
                            <span class="badge bg-danger"><?php echo $post['comentarios_pendientes']; ?> pendientes</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="<?php echo SITE_URL; ?>/blog/<?php echo $post['slug']; ?>" 
                                   target="_blank" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?php echo ADMIN_URL; ?>/blog-form.php?id=<?php echo $post['id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if($post['estado'] == 'borrador'): ?>
                                <button onclick="publishPost(<?php echo $post['id']; ?>)" 
                                        class="btn btn-sm btn-success">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                <?php endif; ?>
                                <button onclick="confirmDelete('<?php echo ADMIN_URL; ?>/blog-posts.php?action=delete&id=<?php echo $post['id']; ?>&token=<?php echo generateCSRFToken(); ?>', '¿Está seguro de eliminar este artículo?')" 
                                        class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function publishPost(id) {
    if (confirm('¿Publicar este artículo ahora?')) {
        window.location.href = '<?php echo ADMIN_URL; ?>/blog-posts.php?action=publish&id=' + id;
    }
}
</script>

<?php include 'includes/footer.php'; ?>