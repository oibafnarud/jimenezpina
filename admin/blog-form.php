<?php
/**
 * Formulario de Blog Post - Panel Administrativo
 * /admin/blog-form.php
 */
require_once 'includes/header.php';

// Verificar permisos
if (!canEdit('blog')) {
    redirect(ADMIN_URL . '/dashboard.php', 'No tiene permisos para acceder a esta sección', MSG_ERROR);
}

$id = $_GET['id'] ?? 0;
$post = null;

// Si es edición, cargar datos
if ($id) {
    $post = $db->fetchOne("SELECT * FROM blog_posts WHERE id = ?", [$id]);
    if (!$post) {
        redirect(ADMIN_URL . '/blog-posts.php', 'Artículo no encontrado', MSG_ERROR);
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        redirect($_SERVER['REQUEST_URI'], 'Token de seguridad inválido', MSG_ERROR);
    }
    
    // Validar campos
    $errors = [];
    if (empty($_POST['titulo'])) $errors[] = 'El título es requerido';
    if (empty($_POST['contenido'])) $errors[] = 'El contenido es requerido';
    if (empty($_POST['categoria'])) $errors[] = 'La categoría es requerida';
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    } else {
        // Preparar datos
        $data = [
            'titulo' => sanitize($_POST['titulo']),
            'slug' => createSlug($_POST['titulo']),
            'contenido' => $_POST['contenido'], // No sanitizar el HTML del editor
            'extracto' => sanitize($_POST['extracto']),
            'categoria' => sanitize($_POST['categoria']),
            'estado' => $_POST['estado'],
            'autor_id' => $_SESSION['user_id'],
            'permitir_comentarios' => isset($_POST['permitir_comentarios']) ? 1 : 0,
            'tags' => sanitize($_POST['tags']),
            'meta_title' => sanitize($_POST['meta_title']),
            'meta_description' => sanitize($_POST['meta_description']),
            'meta_keywords' => sanitize($_POST['meta_keywords'])
        ];
        
        // Fecha de publicación
        if ($data['estado'] == 'publicado' && empty($post['fecha_publicacion'])) {
            $data['fecha_publicacion'] = date('Y-m-d H:i:s');
        } elseif ($data['estado'] == 'programado' && !empty($_POST['fecha_publicacion'])) {
            $data['fecha_publicacion'] = $_POST['fecha_publicacion'];
        }
        
        // Verificar slug único
        $existingSlug = $db->fetchOne(
            "SELECT id FROM blog_posts WHERE slug = ? AND id != ?", 
            [$data['slug'], $id]
        );
        
        if ($existingSlug) {
            $data['slug'] .= '-' . uniqid();
        }
        
        // Procesar imagen destacada
        if (!empty($_FILES['imagen_destacada']['name'])) {
            $upload = uploadFile($_FILES['imagen_destacada'], 'blog', ALLOWED_IMAGE_TYPES);
            if ($upload['success']) {
                // Eliminar imagen anterior si existe
                if ($post && $post['imagen_destacada']) {
                    deleteFile($post['imagen_destacada']);
                }
                $data['imagen_destacada'] = $upload['path'];
            }
        }
        
        try {
            if ($id) {
                // Actualizar
                $db->update('blog_posts', $data, 'id = ?', [$id]);
                logActivity('blog_updated', "Post actualizado: {$data['titulo']}", 'blog_posts', $id);
                $message = 'Artículo actualizado correctamente';
            } else {
                // Crear
                $postId = $db->insert('blog_posts', $data);
                logActivity('blog_created', "Post creado: {$data['titulo']}", 'blog_posts', $postId);
                $message = 'Artículo creado correctamente';
            }
            
            redirect(ADMIN_URL . '/blog-posts.php', $message, MSG_SUCCESS);
            
        } catch (Exception $e) {
            $error = 'Error al guardar el artículo: ' . $e->getMessage();
        }
    }
}

// Obtener categorías existentes para sugerencias
$categoriasSugeridas = $db->fetchAll("
    SELECT DISTINCT categoria 
    FROM blog_posts 
    WHERE categoria IS NOT NULL 
    ORDER BY categoria
");

$pageTitle = $id ? 'Editar Artículo' : 'Nuevo Artículo';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?php echo $pageTitle; ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/blog-posts.php">Blog</a></li>
                <li class="breadcrumb-item active"><?php echo $pageTitle; ?></li>
            </ol>
        </nav>
    </div>
</div>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="blogForm">
    <?php echo csrfField(); ?>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Contenido Principal -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título *</label>
                        <input type="text" class="form-control form-control-lg" id="titulo" name="titulo" 
                               value="<?php echo sanitize($_POST['titulo'] ?? $post['titulo'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="extracto" class="form-label">Extracto</label>
                        <textarea class="form-control" id="extracto" name="extracto" rows="3" 
                                  maxlength="500"><?php echo sanitize($_POST['extracto'] ?? $post['extracto'] ?? ''); ?></textarea>
                        <div class="form-text">Breve descripción del artículo (máx. 500 caracteres)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contenido" class="form-label">Contenido *</label>
                        <textarea class="form-control tinymce" id="contenido" name="contenido" 
                                  rows="20"><?php echo $_POST['contenido'] ?? $post['contenido'] ?? ''; ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- SEO -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Optimización SEO</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                               maxlength="70"
                               value="<?php echo sanitize($_POST['meta_title'] ?? $post['meta_title'] ?? ''); ?>">
                        <div class="form-text">Máximo 70 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" 
                                  rows="2" maxlength="160"><?php echo sanitize($_POST['meta_description'] ?? $post['meta_description'] ?? ''); ?></textarea>
                        <div class="form-text">Máximo 160 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                               value="<?php echo sanitize($_POST['meta_keywords'] ?? $post['meta_keywords'] ?? ''); ?>">
                        <div class="form-text">Separar con comas</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Publicación -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Publicación</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="borrador" <?php echo ($_POST['estado'] ?? $post['estado'] ?? '') == 'borrador' ? 'selected' : ''; ?>>
                                Borrador
                            </option>
                            <option value="publicado" <?php echo ($_POST['estado'] ?? $post['estado'] ?? '') == 'publicado' ? 'selected' : ''; ?>>
                                Publicado
                            </option>
                            <option value="programado" <?php echo ($_POST['estado'] ?? $post['estado'] ?? '') == 'programado' ? 'selected' : ''; ?>>
                                Programado
                            </option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="fecha-publicacion-group" style="display: none;">
                        <label for="fecha_publicacion" class="form-label">Fecha de Publicación</label>
                        <input type="datetime-local" class="form-control" id="fecha_publicacion" 
                               name="fecha_publicacion" 
                               value="<?php echo isset($post['fecha_publicacion']) ? date('Y-m-d\TH:i', strtotime($post['fecha_publicacion'])) : ''; ?>">
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="permitir_comentarios" 
                               name="permitir_comentarios" 
                               <?php echo (isset($_POST['permitir_comentarios']) || (!$_POST && (!$post || $post['permitir_comentarios']))) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="permitir_comentarios">
                            Permitir comentarios
                        </label>
                    </div>
                    
                    <?php if ($post): ?>
                    <div class="text-muted small">
                        <p class="mb-1">Creado: <?php echo formatDate($post['created_at'], 'd/m/Y H:i'); ?></p>
                        <p class="mb-1">Actualizado: <?php echo formatDate($post['updated_at'], 'd/m/Y H:i'); ?></p>
                        <p class="mb-0">Vistas: <?php echo number_format($post['vistas']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Categorización -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Categorización</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría *</label>
                        <input type="text" class="form-control" id="categoria" name="categoria" 
                               list="categorias-list"
                               value="<?php echo sanitize($_POST['categoria'] ?? $post['categoria'] ?? ''); ?>" 
                               required>
                        <datalist id="categorias-list">
                            <?php foreach($categoriasSugeridas as $cat): ?>
                            <option value="<?php echo sanitize($cat['categoria']); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tags" class="form-label">Etiquetas</label>
                        <input type="text" class="form-control" id="tags" name="tags" 
                               value="<?php echo sanitize($_POST['tags'] ?? $post['tags'] ?? ''); ?>">
                        <div class="form-text">Separar con comas</div>
                    </div>
                </div>
            </div>
            
            <!-- Imagen Destacada -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Imagen Destacada</h5>
                </div>
                <div class="card-body">
                    <input type="file" class="form-control mb-3" id="imagen_destacada" 
                           name="imagen_destacada" accept="image/*" 
                           onchange="previewImage(this, 'preview_destacada')">
                    
                    <?php if ($post && $post['imagen_destacada']): ?>
                    <div id="preview_destacada-container">
                        <img src="<?php echo UPLOADS_URL . '/' . $post['imagen_destacada']; ?>" 
                             id="preview_destacada" 
                             class="img-fluid rounded">
                    </div>
                    <?php else: ?>
                    <div id="preview_destacada-container" style="display: none;">
                        <img src="" id="preview_destacada" class="img-fluid rounded">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i> <?php echo $id ? 'Actualizar' : 'Crear'; ?> Artículo
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                        <i class="bi bi-file-earmark me-2"></i> Guardar como Borrador
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/blog-posts.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i> Cancelar
                    </a>
                    <?php if ($post && $post['estado'] == 'publicado'): ?>
                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo $post['slug']; ?>" 
                       target="_blank" class="btn btn-info float-end">
                        <i class="bi bi-eye me-2"></i> Ver Artículo
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Mostrar/ocultar fecha de publicación según estado
document.getElementById('estado').addEventListener('change', function() {
    const fechaGroup = document.getElementById('fecha-publicacion-group');
    if (this.value === 'programado') {
        fechaGroup.style.display = 'block';
    } else {
        fechaGroup.style.display = 'none';
    }
});

// Trigger change event on load
document.getElementById('estado').dispatchEvent(new Event('change'));

// Guardar como borrador
function saveDraft() {
    document.getElementById('estado').value = 'borrador';
    document.getElementById('blogForm').submit();
}

// Preview de imagen
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById(previewId + '-container').style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>