<?php
/**
 * Gestión de Categorías - Panel Administrativo
 * /admin/categorias.php
 */
require_once 'includes/header.php';

// Verificar permisos
if (!canEdit('products')) {
    redirect(ADMIN_URL . '/dashboard.php', 'No tiene permisos para acceder a esta sección', MSG_ERROR);
}

// Procesar acciones
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($action == 'delete' && $id) {
    if (!verifyCSRFToken($_GET['token'] ?? '')) {
        redirect(ADMIN_URL . '/categorias.php', 'Token de seguridad inválido', MSG_ERROR);
    }
    
    // Verificar si tiene productos asociados
    $productCount = $db->count('productos', 'categoria_id = ?', [$id]);
    
    if ($productCount > 0) {
        redirect(ADMIN_URL . '/categorias.php', 
                'No se puede eliminar. Hay ' . $productCount . ' productos asociados', MSG_ERROR);
    }
    
    // Eliminar categoría
    $db->delete('categorias', 'id = ?', [$id]);
    logActivity('categoria_deleted', "Categoría eliminada ID: $id", 'categorias', $id);
    redirect(ADMIN_URL . '/categorias.php', 'Categoría eliminada correctamente', MSG_SUCCESS);
}

if ($action == 'toggle' && $id) {
    $db->query("UPDATE categorias SET activo = NOT activo WHERE id = ?", [$id]);
    redirect(ADMIN_URL . '/categorias.php', 'Estado actualizado correctamente', MSG_SUCCESS);
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido';
    } else {
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'slug' => createSlug($_POST['nombre']),
            'descripcion' => sanitize($_POST['descripcion']),
            'orden' => (int)$_POST['orden'],
            'parent_id' => $_POST['parent_id'] ?: null,
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'meta_title' => sanitize($_POST['meta_title']),
            'meta_description' => sanitize($_POST['meta_description'])
        ];
        
        // Verificar slug único
        $existingSlug = $db->fetchOne(
            "SELECT id FROM categorias WHERE slug = ? AND id != ?", 
            [$data['slug'], $_POST['categoria_id'] ?? 0]
        );
        
        if ($existingSlug) {
            $data['slug'] .= '-' . uniqid();
        }
        
        // Procesar imagen
        if (!empty($_FILES['imagen']['name'])) {
            $upload = uploadFile($_FILES['imagen'], 'categorias', ALLOWED_IMAGE_TYPES);
            if ($upload['success']) {
                $data['imagen'] = $upload['path'];
            }
        }
        
        if (isset($_POST['categoria_id']) && $_POST['categoria_id']) {
            // Actualizar
            $db->update('categorias', $data, 'id = ?', [$_POST['categoria_id']]);
            $message = 'Categoría actualizada correctamente';
        } else {
            // Crear
            $db->insert('categorias', $data);
            $message = 'Categoría creada correctamente';
        }
        
        redirect(ADMIN_URL . '/categorias.php', $message, MSG_SUCCESS);
    }
}

// Obtener categorías
$categorias = $db->fetchAll("
    SELECT c.*, 
           p.nombre as parent_nombre,
           (SELECT COUNT(*) FROM productos WHERE categoria_id = c.id) as total_productos
    FROM categorias c
    LEFT JOIN categorias p ON c.parent_id = p.id
    ORDER BY c.orden ASC, c.nombre ASC
");

$pageTitle = 'Gestión de Categorías';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Categorías</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Categorías</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoriaModal">
        <i class="bi bi-plus-circle me-2"></i>Nueva Categoría
    </button>
</div>

<!-- Lista de Categorías -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">Orden</th>
                        <th width="80">Imagen</th>
                        <th>Nombre</th>
                        <th>Categoría Padre</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th width="150">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categorias as $categoria): ?>
                    <tr>
                        <td>
                            <input type="number" class="form-control form-control-sm orden-input" 
                                   value="<?php echo $categoria['orden']; ?>" 
                                   data-id="<?php echo $categoria['id']; ?>" 
                                   style="width: 60px;">
                        </td>
                        <td>
                            <?php if($categoria['imagen']): ?>
                            <img src="<?php echo UPLOADS_URL . '/' . $categoria['imagen']; ?>" 
                                 class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="width: 50px; height: 50px;">
                                <i class="bi bi-folder text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo sanitize($categoria['nombre']); ?></strong><br>
                            <small class="text-muted">/<?php echo $categoria['slug']; ?></small>
                        </td>
                        <td><?php echo sanitize($categoria['parent_nombre'] ?: '-'); ?></td>
                        <td>
                            <span class="badge bg-primary"><?php echo $categoria['total_productos']; ?></span>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       onchange="toggleStatus(<?php echo $categoria['id']; ?>)"
                                       <?php echo $categoria['activo'] ? 'checked' : ''; ?>>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-primary" 
                                        onclick="editCategoria(<?php echo htmlspecialchars(json_encode($categoria)); ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="confirmDelete('<?php echo ADMIN_URL; ?>/categorias.php?action=delete&id=<?php echo $categoria['id']; ?>&token=<?php echo generateCSRFToken(); ?>', '¿Está seguro de eliminar esta categoría?')" 
                                        class="btn btn-sm btn-danger"
                                        <?php echo $categoria['total_productos'] > 0 ? 'disabled' : ''; ?>>
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

<!-- Modal Categoría -->
<div class="modal fade" id="categoriaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="categoria_id" id="categoria_id">
                    
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Orden</label>
                            <input type="number" class="form-control" name="orden" id="orden" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría Padre</label>
                            <select class="form-select" name="parent_id" id="parent_id">
                                <option value="">Ninguna (Categoría principal)</option>
                                <?php foreach($categorias as $cat): ?>
                                <?php if(!$cat['parent_id']): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo sanitize($cat['nombre']); ?>
                                </option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Imagen</label>
                            <input type="file" class="form-control" name="imagen" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="descripcion" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Meta Title (SEO)</label>
                            <input type="text" class="form-control" name="meta_title" id="meta_title">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Meta Description (SEO)</label>
                            <textarea class="form-control" name="meta_description" id="meta_description" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="activo" id="activo" checked>
                                <label class="form-check-label" for="activo">
                                    Categoría activa
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleStatus(id) {
    window.location.href = '<?php echo ADMIN_URL; ?>/categorias.php?action=toggle&id=' + id;
}

function editCategoria(categoria) {
    document.getElementById('categoria_id').value = categoria.id;
    document.getElementById('nombre').value = categoria.nombre;
    document.getElementById('descripcion').value = categoria.descripcion || '';
    document.getElementById('orden').value = categoria.orden;
    document.getElementById('parent_id').value = categoria.parent_id || '';
    document.getElementById('meta_title').value = categoria.meta_title || '';
    document.getElementById('meta_description').value = categoria.meta_description || '';
    document.getElementById('activo').checked = categoria.activo == 1;
    
    document.querySelector('#categoriaModal .modal-title').textContent = 'Editar Categoría';
    new bootstrap.Modal(document.getElementById('categoriaModal')).show();
}

// Auto-guardar orden
document.querySelectorAll('.orden-input').forEach(input => {
    input.addEventListener('change', function() {
        const id = this.dataset.id;
        const orden = this.value;
        
        fetch('<?php echo ADMIN_URL; ?>/api/update-orden.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tabla: 'categorias',
                id: id,
                orden: orden
            })
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>