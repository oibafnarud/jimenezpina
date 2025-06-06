<?php
/**
 * Gestión de Marcas - Panel Administrativo
 * /admin/marcas.php
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
        redirect(ADMIN_URL . '/marcas.php', 'Token de seguridad inválido', MSG_ERROR);
    }
    
    // Verificar si tiene productos asociados
    $productCount = $db->count('productos', 'marca_id = ?', [$id]);
    
    if ($productCount > 0) {
        redirect(ADMIN_URL . '/marcas.php', 
                'No se puede eliminar. Hay ' . $productCount . ' productos asociados', MSG_ERROR);
    }
    
    // Eliminar logo si existe
    $marca = $db->fetchOne("SELECT logo FROM marcas WHERE id = ?", [$id]);
    if ($marca && $marca['logo']) {
        deleteFile($marca['logo']);
    }
    
    // Eliminar marca
    $db->delete('marcas', 'id = ?', [$id]);
    logActivity('marca_deleted', "Marca eliminada ID: $id", 'marcas', $id);
    redirect(ADMIN_URL . '/marcas.php', 'Marca eliminada correctamente', MSG_SUCCESS);
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
            'website' => sanitize($_POST['website']),
            'orden' => (int)$_POST['orden'],
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        // Verificar slug único
        $existingSlug = $db->fetchOne(
            "SELECT id FROM marcas WHERE slug = ? AND id != ?", 
            [$data['slug'], $_POST['marca_id'] ?? 0]
        );
        
        if ($existingSlug) {
            $data['slug'] .= '-' . uniqid();
        }
        
        // Procesar logo
        if (!empty($_FILES['logo']['name'])) {
            $upload = uploadFile($_FILES['logo'], 'marcas', ALLOWED_IMAGE_TYPES);
            if ($upload['success']) {
                // Eliminar logo anterior si existe
                if (isset($_POST['marca_id']) && $_POST['marca_id']) {
                    $oldLogo = $db->fetchColumn("SELECT logo FROM marcas WHERE id = ?", [$_POST['marca_id']]);
                    if ($oldLogo) {
                        deleteFile($oldLogo);
                    }
                }
                $data['logo'] = $upload['path'];
            }
        }
        
        if (isset($_POST['marca_id']) && $_POST['marca_id']) {
            // Actualizar
            $db->update('marcas', $data, 'id = ?', [$_POST['marca_id']]);
            $message = 'Marca actualizada correctamente';
        } else {
            // Crear
            $db->insert('marcas', $data);
            $message = 'Marca creada correctamente';
        }
        
        redirect(ADMIN_URL . '/marcas.php', $message, MSG_SUCCESS);
    }
}

// Obtener marcas
$marcas = $db->fetchAll("
    SELECT m.*, 
           (SELECT COUNT(*) FROM productos WHERE marca_id = m.id) as total_productos
    FROM marcas m
    ORDER BY m.orden ASC, m.nombre ASC
");

$pageTitle = 'Gestión de Marcas';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Marcas</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Marcas</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#marcaModal">
        <i class="bi bi-plus-circle me-2"></i>Nueva Marca
    </button>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Total Marcas</h6>
                        <h3 class="mb-0"><?php echo count($marcas); ?></h3>
                    </div>
                    <i class="bi bi-tags-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Marcas Activas</h6>
                        <h3 class="mb-0"><?php echo count(array_filter($marcas, fn($m) => $m['activo'])); ?></h3>
                    </div>
                    <i class="bi bi-check-circle-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Total Productos</h6>
                        <h3 class="mb-0"><?php echo array_sum(array_column($marcas, 'total_productos')); ?></h3>
                    </div>
                    <i class="bi bi-box-seam-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Promedio Productos</h6>
                        <h3 class="mb-0">
                            <?php 
                            $avg = count($marcas) > 0 ? 
                                   round(array_sum(array_column($marcas, 'total_productos')) / count($marcas)) : 0;
                            echo $avg;
                            ?>
                        </h3>
                    </div>
                    <i class="bi bi-graph-up fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Marcas -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">Orden</th>
                        <th width="100">Logo</th>
                        <th>Nombre</th>
                        <th>Website</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th width="150">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($marcas as $marca): ?>
                    <tr>
                        <td>
                            <input type="number" class="form-control form-control-sm orden-input" 
                                   value="<?php echo $marca['orden']; ?>" 
                                   data-id="<?php echo $marca['id']; ?>" 
                                   style="width: 60px;">
                        </td>
                        <td>
                            <?php if($marca['logo']): ?>
                            <img src="<?php echo UPLOADS_URL . '/' . $marca['logo']; ?>" 
                                 class="img-thumbnail bg-light" 
                                 style="max-height: 40px; max-width: 80px;">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 40px;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo sanitize($marca['nombre']); ?></strong><br>
                            <small class="text-muted">/<?php echo $marca['slug']; ?></small>
                        </td>
                        <td>
                            <?php if($marca['website']): ?>
                            <a href="<?php echo $marca['website']; ?>" target="_blank" class="text-decoration-none">
                                <?php echo sanitize($marca['website']); ?>
                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?php echo $marca['total_productos']; ?></span>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       onchange="toggleStatus(<?php echo $marca['id']; ?>)"
                                       <?php echo $marca['activo'] ? 'checked' : ''; ?>>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-primary" 
                                        onclick="editMarca(<?php echo htmlspecialchars(json_encode($marca)); ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="confirmDelete('<?php echo ADMIN_URL; ?>/marcas.php?action=delete&id=<?php echo $marca['id']; ?>&token=<?php echo generateCSRFToken(); ?>', '¿Está seguro de eliminar esta marca?')" 
                                        class="btn btn-sm btn-danger"
                                        <?php echo $marca['total_productos'] > 0 ? 'disabled' : ''; ?>>
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

<!-- Modal Marca -->
<div class="modal fade" id="marcaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Marca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="marca_id" id="marca_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" class="form-control" name="website" id="website" 
                               placeholder="https://www.ejemplo.com">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                        <div class="form-text">Recomendado: PNG con fondo transparente, máx 500x200px</div>
                        <div id="currentLogo" class="mt-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Orden</label>
                        <input type="number" class="form-control" name="orden" id="orden" value="0">
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" checked>
                        <label class="form-check-label" for="activo">
                            Marca activa
                        </label>
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
    window.location.href = '<?php echo ADMIN_URL; ?>/marcas.php?action=toggle&id=' + id;
}

function editMarca(marca) {
    document.getElementById('marca_id').value = marca.id;
    document.getElementById('nombre').value = marca.nombre;
    document.getElementById('website').value = marca.website || '';
    document.getElementById('descripcion').value = marca.descripcion || '';
    document.getElementById('orden').value = marca.orden;
    document.getElementById('activo').checked = marca.activo == 1;
    
    // Mostrar logo actual
    const logoDiv = document.getElementById('currentLogo');
    if (marca.logo) {
        logoDiv.innerHTML = `<img src="<?php echo UPLOADS_URL; ?>/${marca.logo}" class="img-thumbnail" style="max-height: 100px;">`;
    } else {
        logoDiv.innerHTML = '';
    }
    
    document.querySelector('#marcaModal .modal-title').textContent = 'Editar Marca';
    new bootstrap.Modal(document.getElementById('marcaModal')).show();
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
                tabla: 'marcas',
                id: id,
                orden: orden,
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            })
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>