<?php
/**
 * Página de Cotización
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

// Verificar si viene de un producto específico
$producto_id = $_GET['producto'] ?? null;
$producto = null;

if ($producto_id) {
    $producto = $db->fetchOne("
        SELECT p.*, m.nombre as marca_nombre
        FROM productos p
        LEFT JOIN marcas m ON p.marca_id = m.id
        WHERE p.id = ? AND p.activo = 1
    ", [$producto_id]);
}

// Obtener productos para el selector
$productos = $db->fetchAll("
    SELECT p.id, p.nombre, p.sku, p.precio, c.nombre as categoria_nombre, m.nombre as marca_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN marcas m ON p.marca_id = m.id
    WHERE p.activo = 1
    ORDER BY c.orden, p.nombre
");

// Meta tags
$pageTitle = 'Solicitar Cotización';
$pageDescription = 'Solicite una cotización personalizada para equipos topográficos. Respuesta en menos de 24 horas.';
$pageKeywords = 'cotización equipos topográficos, presupuesto instrumentos, precio estación total';

include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Solicitar Cotización</h1>
                <p class="lead mb-0 opacity-90">
                    Obtenga un presupuesto personalizado para sus necesidades. 
                    Respuesta garantizada en menos de 24 horas.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex align-items-center justify-content-lg-end gap-3">
                    <i class="bi bi-clock display-4 opacity-50"></i>
                    <div class="text-start">
                        <div class="h4 mb-0">24 hrs</div>
                        <div class="small opacity-75">Tiempo de respuesta</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quote Form -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-5">
                        <h3 class="mb-4">Formulario de Cotización</h3>
                        
                        <form action="<?php echo SITE_URL; ?>/api/cotizacion.php" method="POST" 
                              class="needs-validation" novalidate id="quoteForm">
                            <?php echo csrfField(); ?>
                            
                            <!-- Customer Information -->
                            <div class="mb-5">
                                <h5 class="mb-3">
                                    <i class="bi bi-person-circle text-primary me-2"></i>
                                    Información del Cliente
                                </h5>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nombre" class="form-label">Nombre completo *</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                                        <div class="invalid-feedback">
                                            Por favor ingrese su nombre
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="empresa" class="form-label">Empresa *</label>
                                        <input type="text" class="form-control" id="empresa" name="empresa" required>
                                        <div class="invalid-feedback">
                                            Por favor ingrese el nombre de su empresa
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback">
                                            Por favor ingrese un email válido
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="telefono" class="form-label">Teléfono *</label>
                                        <input type="tel" class="form-control" id="telefono" name="telefono" required>
                                        <div class="invalid-feedback">
                                            Por favor ingrese su teléfono
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="rnc" class="form-label">RNC/Cédula</label>
                                        <input type="text" class="form-control" id="rnc" name="rnc">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="provincia" class="form-label">Provincia *</label>
                                        <select class="form-select" id="provincia" name="provincia" required>
                                            <option value="">Seleccione una provincia</option>
                                            <option value="Santo Domingo">Santo Domingo</option>
                                            <option value="Distrito Nacional">Distrito Nacional</option>
                                            <option value="Santiago">Santiago</option>
                                            <option value="San Cristóbal">San Cristóbal</option>
                                            <option value="La Vega">La Vega</option>
                                            <option value="Puerto Plata">Puerto Plata</option>
                                            <option value="San Pedro de Macorís">San Pedro de Macorís</option>
                                            <option value="La Romana">La Romana</option>
                                            <option value="La Altagracia">La Altagracia</option>
                                            <option value="Otro">Otra provincia</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor seleccione su provincia
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Products Selection -->
                            <div class="mb-5">
                                <h5 class="mb-3">
                                    <i class="bi bi-box-seam text-primary me-2"></i>
                                    Productos a Cotizar
                                </h5>
                                
                                <div id="productsList">
                                    <?php if ($producto): ?>
                                    <div class="product-item mb-3" data-index="0">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-6">
                                                <label class="form-label">Producto</label>
                                                <select class="form-select product-select" name="productos[0][id]" required>
                                                    <option value="<?php echo $producto['id']; ?>" selected>
                                                        <?php echo sanitize($producto['marca_nombre'] . ' - ' . $producto['nombre']); ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Cantidad</label>
                                                <input type="number" class="form-control" name="productos[0][cantidad]" 
                                                       value="1" min="1" required>
                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" class="btn btn-danger btn-remove-product" disabled>
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="product-item mb-3" data-index="0">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-6">
                                                <label class="form-label">Producto</label>
                                                <select class="form-select product-select" name="productos[0][id]" required>
                                                    <option value="">Seleccione un producto</option>
                                                    <?php 
                                                    $currentCategory = '';
                                                    foreach($productos as $prod): 
                                                        if ($currentCategory != $prod['categoria_nombre']):
                                                            if ($currentCategory != '') echo '</optgroup>';
                                                            echo '<optgroup label="' . sanitize($prod['categoria_nombre']) . '">';
                                                            $currentCategory = $prod['categoria_nombre'];
                                                        endif;
                                                    ?>
                                                    <option value="<?php echo $prod['id']; ?>" 
                                                            data-price="<?php echo $prod['precio']; ?>">
                                                        <?php echo sanitize($prod['marca_nombre'] . ' - ' . $prod['nombre']); ?>
                                                        (<?php echo $prod['sku']; ?>)
                                                    </option>
                                                    <?php endforeach; ?>
                                                    <?php if ($currentCategory != '') echo '</optgroup>'; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Cantidad</label>
                                                <input type="number" class="form-control" name="productos[0][cantidad]" 
                                                       value="1" min="1" required>
                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" class="btn btn-danger btn-remove-product" disabled>
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="button" class="btn btn-outline-primary" id="addProduct">
                                    <i class="bi bi-plus-circle me-2"></i> Agregar otro producto
                                </button>
                            </div>
                            
                            <!-- Additional Information -->
                            <div class="mb-5">
                                <h5 class="mb-3">
                                    <i class="bi bi-info-circle text-primary me-2"></i>
                                    Información Adicional
                                </h5>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="proyecto" class="form-label">Tipo de proyecto</label>
                                        <select class="form-select" id="proyecto" name="proyecto">
                                            <option value="">Seleccione el tipo</option>
                                            <option value="Construcción">Construcción</option>
                                            <option value="Vialidad">Vialidad</option>
                                            <option value="Minería">Minería</option>
                                            <option value="Agricultura">Agricultura</option>
                                            <option value="Educación">Educación</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="plazo" class="form-label">Plazo de entrega deseado</label>
                                        <select class="form-select" id="plazo" name="plazo">
                                            <option value="">Seleccione un plazo</option>
                                            <option value="Inmediato">Inmediato</option>
                                            <option value="1 semana">1 semana</option>
                                            <option value="2 semanas">2 semanas</option>
                                            <option value="1 mes">1 mes</option>
                                            <option value="Flexible">Flexible</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="notas" class="form-label">Comentarios o requisitos especiales</label>
                                        <textarea class="form-control" id="notas" name="notas" rows="4" 
                                                  placeholder="Indique cualquier información adicional que considere relevante..."></textarea>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="incluir_instalacion" 
                                                   name="incluir_instalacion" value="1">
                                            <label class="form-check-label" for="incluir_instalacion">
                                                Incluir servicio de instalación y configuración
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="incluir_capacitacion" 
                                                   name="incluir_capacitacion" value="1">
                                            <label class="form-check-label" for="incluir_capacitacion">
                                                Incluir capacitación del personal
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="urgente" 
                                                   name="urgente" value="1">
                                            <label class="form-check-label" for="urgente">
                                                <i class="bi bi-lightning-fill text-warning"></i> 
                                                Cotización urgente (respuesta en 2 horas)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="bi bi-send me-2"></i> Enviar Solicitud de Cotización
                                </button>
                                <p class="text-muted mt-3 mb-0">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Su información está segura y no será compartida con terceros
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Why Quote With Us -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">¿Por qué cotizar con nosotros?</h5>
                        
                        <div class="d-flex mb-3">
                            <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Mejores Precios</h6>
                                <p class="mb-0 text-muted small">
                                    Precios competitivos y descuentos por volumen
                                </p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-3">
                            <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Respuesta Rápida</h6>
                                <p class="mb-0 text-muted small">
                                    Cotización detallada en menos de 24 horas
                                </p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-3">
                            <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Asesoría Incluida</h6>
                                <p class="mb-0 text-muted small">
                                    Recomendaciones según su proyecto
                                </p>
                            </div>
                        </div>
                        
                        <div class="d-flex">
                            <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Sin Compromiso</h6>
                                <p class="mb-0 text-muted small">
                                    Cotización gratuita y sin obligación de compra
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-body text-center">
                        <i class="bi bi-headset display-4 text-primary mb-3"></i>
                        <h5 class="card-title">¿Necesita ayuda?</h5>
                        <p class="card-text text-muted">
                            Nuestros asesores están disponibles para ayudarle
                        </p>
                        <div class="d-grid gap-2">
                            <a href="tel:<?php echo cleanPhone(getSetting('site_phone')); ?>" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-telephone me-2"></i>
                                <?php echo getSetting('site_phone'); ?>
                            </a>
                            <a href="https://wa.me/<?php echo cleanPhone(getSetting('whatsapp_number')); ?>" 
                               target="_blank" class="btn btn-success">
                                <i class="bi bi-whatsapp me-2"></i>
                                WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Process Timeline -->
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Proceso de cotización</h5>
                        
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-icon bg-primary text-white">1</div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Envío de solicitud</h6>
                                    <p class="mb-0 text-muted small">Complete y envíe el formulario</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-icon bg-primary text-white">2</div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Análisis</h6>
                                    <p class="mb-0 text-muted small">Evaluamos sus necesidades</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-icon bg-primary text-white">3</div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Cotización</h6>
                                    <p class="mb-0 text-muted small">Enviamos propuesta detallada</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-icon bg-success text-white">4</div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Seguimiento</h6>
                                    <p class="mb-0 text-muted small">Aclaramos dudas y negociamos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.timeline {
    position: relative;
    padding-left: 40px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 20px;
    bottom: 20px;
    width: 2px;
    background: #e9ecef;
}
.timeline-item {
    position: relative;
    margin-bottom: 25px;
}
.timeline-item:last-child {
    margin-bottom: 0;
}
.timeline-icon {
    position: absolute;
    left: -40px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}
</style>

<script>
// Product management
let productIndex = 1;

document.getElementById('addProduct').addEventListener('click', function() {
    const productsList = document.getElementById('productsList');
    const newProduct = document.createElement('div');
    newProduct.className = 'product-item mb-3';
    newProduct.dataset.index = productIndex;
    
    newProduct.innerHTML = `
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Producto</label>
                <select class="form-select product-select" name="productos[${productIndex}][id]" required>
                    <option value="">Seleccione un producto</option>
                    <?php 
                    $currentCategory = '';
                    foreach($productos as $prod): 
                        if ($currentCategory != $prod['categoria_nombre']):
                            if ($currentCategory != '') echo '</optgroup>';
                            echo '<optgroup label="' . sanitize($prod['categoria_nombre']) . '">';
                            $currentCategory = $prod['categoria_nombre'];
                        endif;
                    ?>
                    <option value="<?php echo $prod['id']; ?>" data-price="<?php echo $prod['precio']; ?>">
                        <?php echo sanitize($prod['marca_nombre'] . ' - ' . $prod['nombre']); ?>
                        (<?php echo $prod['sku']; ?>)
                    </option>
                    <?php endforeach; ?>
                    <?php if ($currentCategory != '') echo '</optgroup>'; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Cantidad</label>
                <input type="number" class="form-control" name="productos[${productIndex}][cantidad]" 
                       value="1" min="1" required>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-danger btn-remove-product">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
        </div>
    `;
    
    productsList.appendChild(newProduct);
    productIndex++;
    
    // Enable remove buttons if more than one product
    updateRemoveButtons();
});

// Remove product
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-remove-product') || 
        e.target.parentElement.classList.contains('btn-remove-product')) {
        const button = e.target.classList.contains('btn-remove-product') ? 
                      e.target : e.target.parentElement;
        const productItem = button.closest('.product-item');
        productItem.remove();
        updateRemoveButtons();
    }
});

function updateRemoveButtons() {
    const products = document.querySelectorAll('.product-item');
    const removeButtons = document.querySelectorAll('.btn-remove-product');
    
    removeButtons.forEach(btn => {
        btn.disabled = products.length <= 1;
    });
}
</script>

<?php include '../includes/footer.php'; ?>