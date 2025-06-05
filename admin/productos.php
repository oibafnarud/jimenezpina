<?php
/**
 * Gestión de Productos - Panel Administrativo
 * Jiménez & Piña Survey Instruments
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
    // Verificar CSRF
    if (!verifyCSRFToken($_GET['token'] ?? '')) {
        redirect(ADMIN_URL . '/productos.php', 'Token de seguridad inválido', MSG_ERROR);
    }
    
    // Obtener información del producto antes de eliminar
    $producto = $db->fetchOne("SELECT nombre, imagen_principal FROM productos WHERE id = ?", [$id]);
    
    if ($producto) {
        // Eliminar imágenes asociadas
        $imagenes = $db->fetchAll("SELECT imagen FROM producto_imagenes WHERE producto_id = ?", [$id]);
        foreach($imagenes as $img) {
            deleteFile($img['imagen']);
        }
        
        // Eliminar imagen principal
        if ($producto['imagen_principal']) {
            deleteFile($producto['imagen_principal']);
        }
        
        // Eliminar producto
        $db->delete('productos', 'id = ?', [$id]);
        
        // Registrar actividad
        logActivity('producto_deleted', "Producto eliminado: {$producto['nombre']}", 'productos', $id);
        
        redirect(ADMIN_URL . '/productos.php', 'Producto eliminado correctamente', MSG_SUCCESS);
    }
}

if ($action == 'toggle' && $id) {
    // Toggle estado activo/inactivo
    $db->query("UPDATE productos SET activo = NOT activo WHERE id = ?", [$id]);
    redirect(ADMIN_URL . '/productos.php', 'Estado actualizado correctamente', MSG_SUCCESS);
}

// Obtener filtros
$search = $_GET['search'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$marca = $_GET['marca'] ?? '';
$estado = $_GET['estado'] ?? '';

// Construir consulta
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(p.nombre LIKE ? OR p.sku LIKE ? OR p.descripcion_corta LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($categoria) {
    $where[] = "p.categoria_id = ?";
    $params[] = $categoria;
}

if ($marca) {
    $where[] = "p.marca_id = ?";
    $params[] = $marca;
}

if ($estado !== '') {
    $where[] = "p.activo = ?";
    $params[] = $estado;
}

$whereClause = implode(' AND ', $where);

// Obtener productos
$productos = $db->fetchAll("
    SELECT p.*, c.nombre as categoria_nombre, m.nombre as marca_nombre,
           (SELECT COUNT(*) FROM producto_imagenes WHERE producto_id = p.id) as total_imagenes
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN marcas m ON p.marca_id = m.id
    WHERE $whereClause
    ORDER BY p.created_at DESC
", $params);

// Obtener categorías y marcas para filtros
$categorias = $db->fetchAll("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");
$marcas = $db->fetchAll("SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre");

$pageTitle = 'Gestión de Productos';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Productos</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Productos</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="<?php echo ADMIN_URL; ?>/producto-form.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nuevo Producto
        </a>
        <button class="btn btn-secondary" onclick="exportProducts()">
            <i class="bi bi-download me-2"></i>Exportar
        </button>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search" 
                       placeholder="Nombre, SKU, descripción..." 
                       value="<?php echo sanitize($search); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select class="form-select" name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php foreach($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo sanitize($cat['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Marca</label>
                <select class="form-select" name="marca">
                    <option value="">Todas las marcas</option>
                    <?php foreach($marcas as $mar): ?>
                    <option value="<?php echo $mar['id']; ?>" <?php echo $marca == $mar['id'] ? 'selected' : ''; ?>>
                        <?php echo sanitize($mar['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Todos</option>
                    <option value="1" <?php echo $estado === '1' ? 'selected' : ''; ?>>Activos</option>
                    <option value="0" <?php echo $estado === '0' ? 'selected' : ''; ?>>Inactivos</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="<?php echo ADMIN_URL; ?>/productos.php" class="btn btn-secondary">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de productos -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="80">Imagen</th>
                        <th>SKU</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th width="150">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($productos as $producto): ?>
                    <tr>
                        <td><?php echo $producto['id']; ?></td>
                        <td>
                            <?php if($producto['imagen_principal']): ?>
                            <img src="<?php echo UPLOADS_URL . '/' . $producto['imagen_principal']; ?>" 
                                 alt="<?php echo sanitize($producto['nombre']); ?>"
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
                            <code><?php echo sanitize($producto['sku']); ?></code>
                        </td>
                        <td>
                            <strong><?php echo sanitize($producto['nombre']); ?></strong>
                            <?php if($producto['nuevo']): ?>
                            <span class="badge bg-danger ms-1">Nuevo</span>
                            <?php endif; ?>
                            <?php if($producto['destacado']): ?>
                            <span class="badge bg-warning ms-1">Destacado</span>
                            <?php endif; ?>
                            <br>
                            <small class="text-muted">
                                <?php echo sanitize(substr($producto['descripcion_corta'], 0, 60)) . '...'; ?>
                            </small>
                        </td>
                        <td><?php echo sanitize($producto['categoria_nombre']); ?></td>
                        <td><?php echo sanitize($producto['marca_nombre']); ?></td>
                        <td>
                            <?php if($producto['precio_oferta']): ?>
                            <span class="text-decoration-line-through text-muted">
                                <?php echo formatPrice($producto['precio']); ?>
                            </span><br>
                            <span class="text-danger">
                                <?php echo formatPrice($producto['precio_oferta']); ?>
                            </span>
                            <?php else: ?>
                            <?php echo formatPrice($producto['precio']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($producto['stock'] <= 0): ?>
                            <span class="badge bg-danger">Agotado</span>
                            <?php elseif($producto['stock'] <= $producto['stock_minimo']): ?>
                            <span class="badge bg-warning"><?php echo $producto['stock']; ?></span>
                            <?php else: ?>
                            <span class="badge bg-success"><?php echo $producto['stock']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       onchange="toggleStatus(<?php echo $producto['id']; ?>)"
                                       <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="<?php echo SITE_URL; ?>/producto/<?php echo $producto['slug']; ?>" 
                                   target="_blank"
                                   class="btn btn-sm btn-info"
                                   data-bs-toggle="tooltip" title="Ver en el sitio">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?php echo ADMIN_URL; ?>/producto-form.php?id=<?php echo $producto['id']; ?>" 
                                   class="btn btn-sm btn-primary"
                                   data-bs-toggle="tooltip" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button onclick="duplicateProduct(<?php echo $producto['id']; ?>)" 
                                        class="btn btn-sm btn-secondary"
                                        data-bs-toggle="tooltip" title="Duplicar">
                                    <i class="bi bi-files"></i>
                                </button>
                                <button onclick="confirmDelete('<?php echo ADMIN_URL; ?>/productos.php?action=delete&id=<?php echo $producto['id']; ?>&token=<?php echo generateCSRFToken(); ?>', '¿Está seguro de eliminar este producto?')" 
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="tooltip" title="Eliminar">
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

<!-- Modal de importación masiva -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar Productos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo ADMIN_URL; ?>/importar-productos.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label">Archivo CSV</label>
                        <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                        <div class="form-text">
                            El archivo debe tener las columnas: SKU, Nombre, Categoría, Marca, Precio, Stock
                        </div>
                    </div>
                    <div class="mb-3">
                        <a href="<?php echo ADMIN_URL; ?>/plantilla-productos.csv" download class="btn btn-sm btn-secondary">
                            <i class="bi bi-download me-2"></i>Descargar plantilla
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleStatus(id) {
    window.location.href = '<?php echo ADMIN_URL; ?>/productos.php?action=toggle&id=' + id;
}

function duplicateProduct(id) {
    if (confirm('¿Desea duplicar este producto?')) {
        window.location.href = '<?php echo ADMIN_URL; ?>/producto-form.php?duplicate=' + id;
    }
}

function exportProducts() {
    window.location.href = '<?php echo ADMIN_URL; ?>/exportar.php?tipo=productos';
}
</script>

<?php include 'includes/footer.php'; ?>