<?php
/**
 * Formulario de Productos - Panel Administrativo
 * Jiménez & Piña Survey Instruments
 */
require_once 'includes/header.php';

// Verificar permisos
if (!canEdit('products')) {
    redirect(ADMIN_URL . '/dashboard.php', 'No tiene permisos para acceder a esta sección', MSG_ERROR);
}

$id = $_GET['id'] ?? 0;
$duplicate = $_GET['duplicate'] ?? 0;
$producto = null;
$especificaciones = [];
$imagenes = [];
$etiquetas = [];

// Si es edición o duplicación, cargar datos
if ($id || $duplicate) {
    $productoId = $id ?: $duplicate;
    $producto = $db->fetchOne("SELECT * FROM productos WHERE id = ?", [$productoId]);
    
    if (!$producto) {
        redirect(ADMIN_URL . '/productos.php', 'Producto no encontrado', MSG_ERROR);
    }
    
    // Cargar especificaciones
    $especificaciones = $db->fetchAll("
        SELECT * FROM especificaciones 
        WHERE producto_id = ? 
        ORDER BY grupo, orden
    ", [$productoId]);
    
    // Cargar imágenes
    $imagenes = $db->fetchAll("
        SELECT * FROM producto_imagenes 
        WHERE producto_id = ? 
        ORDER BY orden
    ", [$productoId]);
    
    // Cargar etiquetas
    $etiquetas = $db->fetchAll("
        SELECT e.* FROM etiquetas e
        INNER JOIN producto_etiquetas pe ON e.id = pe.etiqueta_id
        WHERE pe.producto_id = ?
    ", [$productoId]);
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        redirect(ADMIN_URL . '/producto-form.php' . ($id ? "?id=$id" : ''), 
                'Token de seguridad inválido', MSG_ERROR);
    }
    
    // Validar campos requeridos
    $errors = [];
    if (empty($_POST['sku'])) $errors[] = 'El SKU es requerido';
    if (empty($_POST['nombre'])) $errors[] = 'El nombre es requerido';
    if (empty($_POST['categoria_id'])) $errors[] = 'La categoría es requerida';
    if (empty($_POST['precio']) || $_POST['precio'] <= 0) $errors[] = 'El precio debe ser mayor a 0';
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    } else {
        // Preparar datos
        $data = [
            'sku' => $_POST['sku'],
            'nombre' => $_POST['nombre'],
            'slug' => createSlug($_POST['nombre']),
            'descripcion' => $_POST['descripcion'],
            'descripcion_corta' => $_POST['descripcion_corta'],
            'categoria_id' => $_POST['categoria_id'],
            'marca_id' => $_POST['marca_id'] ?: null,
            'precio' => str_replace(',', '', $_POST['precio']),
            'precio_oferta' => $_POST['precio_oferta'] ? str_replace(',', '', $_POST['precio_oferta']) : null,
            'costo' => $_POST['costo'] ? str_replace(',', '', $_POST['costo']) : null,
            'stock' => (int)$_POST['stock'],
            'stock_minimo' => (int)$_POST['stock_minimo'],
            'peso' => $_POST['peso'] ?: null,
            'dimensiones' => $_POST['dimensiones'],
            'garantia_meses' => (int)$_POST['garantia_meses'],
            'video_youtube' => $_POST['video_youtube'],
            'destacado' => isset($_POST['destacado']) ? 1 : 0,
            'nuevo' => isset($_POST['nuevo']) ? 1 : 0,
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'meta_title' => $_POST['meta_title'],
            'meta_description' => $_POST['meta_description'],
            'meta_keywords' => $_POST['meta_keywords']
        ];
        
        // Verificar slug único
        $existingSlug = $db->fetchOne("
            SELECT id FROM productos 
            WHERE slug = ? AND id != ?
        ", [$data['slug'], $id]);
        
        if ($existingSlug) {
            $data['slug'] .= '-' . uniqid();
        }
        
        try {
            $db->beginTransaction();
            
            // Insertar o actualizar producto
            if ($id && !$duplicate) {
                // Actualizar
                $db->update('productos', $data, 'id = ?', [$id]);
                $productoId = $id;
                logActivity('producto_updated', "Producto actualizado: {$data['nombre']}", 'productos', $id);
            } else {
                // Insertar
                if ($duplicate) {
                    $data['sku'] .= '-COPY';
                    $data['nombre'] .= ' (Copia)';
                }
                $productoId = $db->insert('productos', $data);
                logActivity('producto_created', "Producto creado: {$data['nombre']}", 'productos', $productoId);
            }
            
            // Procesar imagen principal
            if (!empty($_FILES['imagen_principal']['name'])) {
                $upload = uploadFile($_FILES['imagen_principal'], 'productos', ALLOWED_IMAGE_TYPES);
                if ($upload['success']) {
                    // Eliminar imagen anterior si existe
                    if ($producto && $producto['imagen_principal']) {
                        deleteFile($producto['imagen_principal']);
                    }
                    
                    // Generar thumbnails
                    $sourcePath = UPLOADS_PATH . '/' . $upload['path'];
                    $thumbPath = UPLOADS_PATH . '/productos/thumb_' . $upload['filename'];
                    resizeImage($sourcePath, $thumbPath, THUMB_WIDTH, THUMB_HEIGHT);
                    
                    // Actualizar en BD
                    $db->update('productos', 
                        ['imagen_principal' => $upload['path']], 
                        'id = ?', 
                        [$productoId]
                    );
                }
            }
            
            // Procesar ficha técnica
            if (!empty($_FILES['ficha_tecnica']['name'])) {
                $upload = uploadFile($_FILES['ficha_tecnica'], 'fichas', ALLOWED_DOCUMENT_TYPES);
                if ($upload['success']) {
                    // Eliminar anterior si existe
                    if ($producto && $producto['ficha_tecnica']) {
                        deleteFile($producto['ficha_tecnica']);
                    }
                    
                    $db->update('productos', 
                        ['ficha_tecnica' => $upload['path']], 
                        'id = ?', 
                        [$productoId]
                    );
                }
            }
            
            // Procesar manual de usuario
            if (!empty($_FILES['manual_usuario']['name'])) {
                $upload = uploadFile($_FILES['manual_usuario'], 'manuales', ALLOWED_DOCUMENT_TYPES);
                if ($upload['success']) {
                    // Eliminar anterior si existe
                    if ($producto && $producto['manual_usuario']) {
                        deleteFile($producto['manual_usuario']);
                    }
                    
                    $db->update('productos', 
                        ['manual_usuario' => $upload['path']], 
                        'id = ?', 
                        [$productoId]
                    );
                }
            }
            
            // Procesar especificaciones
            if (!$duplicate) {
                // Eliminar especificaciones anteriores
                $db->delete('especificaciones', 'producto_id = ?', [$productoId]);
            }
            
            // Insertar nuevas especificaciones
            if (isset($_POST['spec_grupo']) && is_array($_POST['spec_grupo'])) {
                foreach ($_POST['spec_grupo'] as $i => $grupo) {
                    if (!empty($_POST['spec_nombre'][$i]) && !empty($_POST['spec_valor'][$i])) {
                        $db->insert('especificaciones', [
                            'producto_id' => $productoId,
                            'grupo' => $grupo,
                            'nombre' => $_POST['spec_nombre'][$i],
                            'valor' => $_POST['spec_valor'][$i],
                            'orden' => $i
                        ]);
                    }
                }
            }
            
            // Procesar etiquetas
            if (!$duplicate) {
                // Eliminar etiquetas anteriores
                $db->delete('producto_etiquetas', 'producto_id = ?', [$productoId]);
            }
            
            // Procesar nuevas etiquetas
            if (!empty($_POST['etiquetas'])) {
                $tags = explode(',', $_POST['etiquetas']);
                foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if ($tag) {
                        // Buscar o crear etiqueta
                        $etiqueta = $db->fetchOne("SELECT id FROM etiquetas WHERE nombre = ?", [$tag]);
                        if (!$etiqueta) {
                            $etiquetaId = $db->insert('etiquetas', [
                                'nombre' => $tag,
                                'slug' => createSlug($tag)
                            ]);
                        } else {
                            $etiquetaId = $etiqueta['id'];
                        }
                        
                        // Asociar al producto
                        $db->insert('producto_etiquetas', [
                            'producto_id' => $productoId,
                            'etiqueta_id' => $etiquetaId
                        ]);
                    }
                }
            }
            
            // Procesar imágenes adicionales
            if (!empty($_FILES['imagenes']['name'][0])) {
                foreach ($_FILES['imagenes']['name'] as $key => $filename) {
                    if (!empty($filename)) {
                        $file = [
                            'name' => $_FILES['imagenes']['name'][$key],
                            'type' => $_FILES['imagenes']['type'][$key],
                            'tmp_name' => $_FILES['imagenes']['tmp_name'][$key],
                            'error' => $_FILES['imagenes']['error'][$key],
                            'size' => $_FILES['imagenes']['size'][$key]
                        ];
                        
                        $upload = uploadFile($file, 'productos', ALLOWED_IMAGE_TYPES);
                        if ($upload['success']) {
                            // Generar thumbnail
                            $sourcePath = UPLOADS_PATH . '/' . $upload['path'];
                            $thumbPath = UPLOADS_PATH . '/productos/thumb_' . $upload['filename'];
                            resizeImage($sourcePath, $thumbPath, THUMB_WIDTH, THUMB_HEIGHT);
                            
                            // Guardar en BD
                            $db->insert('producto_imagenes', [
                                'producto_id' => $productoId,
                                'imagen' => $upload['path'],
                                'titulo' => pathinfo($filename, PATHINFO_FILENAME),
                                'orden' => $key
                            ]);
                        }
                    }
                }
            }
            
            $db->commit();
            
            redirect(ADMIN_URL . '/productos.php', 
                    $id && !$duplicate ? 'Producto actualizado correctamente' : 'Producto creado correctamente', 
                    MSG_SUCCESS);
            
        } catch (Exception $e) {
            $db->rollback();
            $error = 'Error al guardar el producto: ' . $e->getMessage();
        }
    }
}

// Obtener categorías y marcas
$categorias = $db->fetchAll("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");
$marcas = $db->fetchAll("SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre");

$pageTitle = $id ? 'Editar Producto' : 'Nuevo Producto';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?php echo $pageTitle; ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/productos.php">Productos</a></li>
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

<form method="POST" enctype="multipart/form-data" id="productForm">
    <?php echo csrfField(); ?>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Información General -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="sku" class="form-label">SKU *</label>
                            <input type="text" class="form-control" id="sku" name="sku" 
                                   value="<?php echo sanitize($_POST['sku'] ?? $producto['sku'] ?? ''); ?>" 
                                   required>
                        </div>
                        <div class="col-md-8">
                            <label for="nombre" class="form-label">Nombre del Producto *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo sanitize($_POST['nombre'] ?? $producto['nombre'] ?? ''); ?>" 
                                   required>
                        </div>
                        <div class="col-12">
                            <label for="descripcion_corta" class="form-label">Descripción Corta</label>
                            <textarea class="form-control" id="descripcion_corta" name="descripcion_corta" 
                                      rows="2" maxlength="500"><?php echo sanitize($_POST['descripcion_corta'] ?? $producto['descripcion_corta'] ?? ''); ?></textarea>
                            <div class="form-text">Máximo 500 caracteres. Se muestra en las listas de productos.</div>
                        </div>
                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripción Completa</label>
                            <textarea class="form-control tinymce" id="descripcion" name="descripcion" 
                                      rows="10"><?php echo $_POST['descripcion'] ?? $producto['descripcion'] ?? ''; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Especificaciones -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Especificaciones Técnicas</h5>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addSpecification()">
                        <i class="bi bi-plus-circle me-1"></i> Agregar
                    </button>
                </div>
                <div class="card-body">
                    <div id="specifications">
                        <?php if (!empty($especificaciones)): ?>
                            <?php foreach ($especificaciones as $i => $spec): ?>
                            <div class="spec-row mb-3">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Grupo</label>
                                        <input type="text" class="form-control" name="spec_grupo[]" 
                                               placeholder="Ej: General"
                                               value="<?php echo sanitize($spec['grupo']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" class="form-control" name="spec_nombre[]" 
                                               placeholder="Ej: Precisión"
                                               value="<?php echo sanitize($spec['nombre']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Valor</label>
                                        <input type="text" class="form-control" name="spec_valor[]" 
                                               placeholder="Ej: ±2mm"
                                               value="<?php echo sanitize($spec['valor']); ?>">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeSpecification(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div class="spec-row mb-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Grupo</label>
                                    <input type="text" class="form-control" name="spec_grupo[]" placeholder="Ej: General">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" class="form-control" name="spec_nombre[]" placeholder="Ej: Precisión">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Valor</label>
                                    <input type="text" class="form-control" name="spec_valor[]" placeholder="Ej: ±2mm">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSpecification(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Multimedia -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Multimedia</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="imagen_principal" class="form-label">Imagen Principal</label>
                        <input type="file" class="form-control" id="imagen_principal" name="imagen_principal" 
                               accept="image/*" onchange="previewImage(this, 'preview_principal')">
                        <?php if ($producto && $producto['imagen_principal']): ?>
                        <div class="mt-2" id="preview_principal-container">
                            <img src="<?php echo UPLOADS_URL . '/' . $producto['imagen_principal']; ?>" 
                                 id="preview_principal" 
                                 class="img-thumbnail" 
                                 style="max-height: 200px;">
                        </div>
                        <?php else: ?>
                        <div class="mt-2" id="preview_principal-container" style="display: none;">
                            <img src="" id="preview_principal" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="imagenes" class="form-label">Imágenes Adicionales</label>
                        <input type="file" class="form-control" id="imagenes" name="imagenes[]" 
                               accept="image/*" multiple>
                        <?php if (!empty($imagenes)): ?>
                        <div class="row g-2 mt-2">
                            <?php foreach ($imagenes as $img): ?>
                            <div class="col-md-3">
                                <div class="position-relative">
                                    <img src="<?php echo UPLOADS_URL . '/' . $img['imagen']; ?>" 
                                         class="img-thumbnail w-100">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                                            onclick="deleteImage(<?php echo $img['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="video_youtube" class="form-label">Video YouTube</label>
                            <input type="text" class="form-control" id="video_youtube" name="video_youtube" 
                                   placeholder="https://www.youtube.com/watch?v=..."
                                   value="<?php echo sanitize($_POST['video_youtube'] ?? $producto['video_youtube'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="ficha_tecnica" class="form-label">Ficha Técnica (PDF)</label>
                            <input type="file" class="form-control" id="ficha_tecnica" name="ficha_tecnica" 
                                   accept=".pdf">
                            <?php if ($producto && $producto['ficha_tecnica']): ?>
                            <a href="<?php echo UPLOADS_URL . '/' . $producto['ficha_tecnica']; ?>" 
                               target="_blank" class="btn btn-sm btn-info mt-2">
                                <i class="bi bi-file-pdf me-1"></i> Ver actual
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label for="manual_usuario" class="form-label">Manual Usuario (PDF)</label>
                            <input type="file" class="form-control" id="manual_usuario" name="manual_usuario" 
                                   accept=".pdf">
                            <?php if ($producto && $producto['manual_usuario']): ?>
                            <a href="<?php echo UPLOADS_URL . '/' . $producto['manual_usuario']; ?>" 
                               target="_blank" class="btn btn-sm btn-info mt-2">
                                <i class="bi bi-file-pdf me-1"></i> Ver actual
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SEO -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">SEO</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                               maxlength="70"
                               value="<?php echo sanitize($_POST['meta_title'] ?? $producto['meta_title'] ?? ''); ?>">
                        <div class="form-text">Máximo 70 caracteres. Dejar vacío para usar el nombre del producto.</div>
                    </div>
                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" 
                                  rows="2" maxlength="160"><?php echo sanitize($_POST['meta_description'] ?? $producto['meta_description'] ?? ''); ?></textarea>
                        <div class="form-text">Máximo 160 caracteres. Dejar vacío para usar la descripción corta.</div>
                    </div>
                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                               value="<?php echo sanitize($_POST['meta_keywords'] ?? $producto['meta_keywords'] ?? ''); ?>">
                        <div class="form-text">Separar con comas.</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Estado y Visibilidad -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estado y Visibilidad</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                               <?php echo (isset($_POST['activo']) || (!$_POST && (!$producto || $producto['activo']))) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">
                            Producto Activo
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="destacado" name="destacado" 
                               <?php echo (isset($_POST['destacado']) || (!$_POST && $producto && $producto['destacado'])) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="destacado">
                            Producto Destacado
                        </label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="nuevo" name="nuevo" 
                               <?php echo (isset($_POST['nuevo']) || (!$_POST && $producto && $producto['nuevo'])) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="nuevo">
                            Producto Nuevo
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Categorización -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Categorización</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="categoria_id" class="form-label">Categoría *</label>
                        <select class="form-select select2" id="categoria_id" name="categoria_id" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($_POST['categoria_id'] ?? $producto['categoria_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo sanitize($cat['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="marca_id" class="form-label">Marca</label>
                        <select class="form-select select2" id="marca_id" name="marca_id">
                            <option value="">Seleccione una marca</option>
                            <?php foreach($marcas as $marca): ?>
                            <option value="<?php echo $marca['id']; ?>" 
                                    <?php echo ($_POST['marca_id'] ?? $producto['marca_id'] ?? '') == $marca['id'] ? 'selected' : ''; ?>>
                                <?php echo sanitize($marca['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="etiquetas" class="form-label">Etiquetas</label>
                        <input type="text" class="form-control" id="etiquetas" name="etiquetas" 
                               value="<?php echo sanitize($_POST['etiquetas'] ?? implode(', ', array_column($etiquetas, 'nombre')) ?? ''); ?>">
                        <div class="form-text">Separar con comas.</div>
                    </div>
                </div>
            </div>
            
            <!-- Precios -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Precios</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio Regular *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" id="precio" name="precio" 
                                   value="<?php echo formatPrice($_POST['precio'] ?? $producto['precio'] ?? '', false); ?>" 
                                   onkeyup="formatCurrency(this)" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="precio_oferta" class="form-label">Precio Oferta</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" id="precio_oferta" name="precio_oferta" 
                                   value="<?php echo formatPrice($_POST['precio_oferta'] ?? $producto['precio_oferta'] ?? '', false); ?>" 
                                   onkeyup="formatCurrency(this)">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="costo" class="form-label">Costo</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" id="costo" name="costo" 
                                   value="<?php echo formatPrice($_POST['costo'] ?? $producto['costo'] ?? '', false); ?>" 
                                   onkeyup="formatCurrency(this)">
                        </div>
                        <div class="form-text">Solo visible para administradores.</div>
                    </div>
                </div>
            </div>
            
            <!-- Inventario -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Inventario</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock Actual</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" 
                               value="<?php echo $_POST['stock'] ?? $producto['stock'] ?? 0; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                        <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" min="0" 
                               value="<?php echo $_POST['stock_minimo'] ?? $producto['stock_minimo'] ?? 0; ?>">
                        <div class="form-text">Alerta cuando el stock llegue a este nivel.</div>
                    </div>
                </div>
            </div>
            
            <!-- Información Adicional -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información Adicional</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="peso" class="form-label">Peso (kg)</label>
                        <input type="number" class="form-control" id="peso" name="peso" step="0.001" min="0" 
                               value="<?php echo $_POST['peso'] ?? $producto['peso'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="dimensiones" class="form-label">Dimensiones</label>
                        <input type="text" class="form-control" id="dimensiones" name="dimensiones" 
                               placeholder="Largo x Ancho x Alto cm"
                               value="<?php echo sanitize($_POST['dimensiones'] ?? $producto['dimensiones'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="garantia_meses" class="form-label">Garantía (meses)</label>
                        <input type="number" class="form-control" id="garantia_meses" name="garantia_meses" min="0" 
                               value="<?php echo $_POST['garantia_meses'] ?? $producto['garantia_meses'] ?? 12; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i> <?php echo $id ? 'Actualizar' : 'Crear'; ?> Producto
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                        <i class="bi bi-file-earmark me-2"></i> Guardar Borrador
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/productos.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Agregar especificación
function addSpecification() {
    const container = document.getElementById('specifications');
    const newRow = document.createElement('div');
    newRow.className = 'spec-row mb-3';
    newRow.innerHTML = `
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Grupo</label>
                <input type="text" class="form-control" name="spec_grupo[]" placeholder="Ej: General">
            </div>
            <div class="col-md-4">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" name="spec_nombre[]" placeholder="Ej: Precisión">
            </div>
            <div class="col-md-4">
                <label class="form-label">Valor</label>
                <input type="text" class="form-control" name="spec_valor[]" placeholder="Ej: ±2mm">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeSpecification(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newRow);
}

// Eliminar especificación
function removeSpecification(btn) {
    if (confirm('¿Eliminar esta especificación?')) {
        btn.closest('.spec-row').remove();
    }
}

// Eliminar imagen
function deleteImage(id) {
    if (confirm('¿Eliminar esta imagen?')) {
        // AJAX call to delete image
        fetch('<?php echo ADMIN_URL; ?>/delete-image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id,
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al eliminar la imagen');
            }
        });
    }
}

// Guardar borrador
function saveDraft() {
    const form = document.getElementById('productForm');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'draft';
    input.value = '1';
    form.appendChild(input);
    form.submit();
}

// Auto-save draft
let changes = false;
document.querySelectorAll('input, textarea, select').forEach(element => {
    element.addEventListener('change', () => {
        changes = true;
        autoSaveDraft('productForm');
    });
});

// Advertencia al salir sin guardar
window.addEventListener('beforeunload', (e) => {
    if (changes) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>

<?php include 'includes/footer.php'; ?>