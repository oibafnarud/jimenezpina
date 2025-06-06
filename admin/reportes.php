<?php
/**
 * Módulo de Reportes - Panel Administrativo
 * /admin/reportes.php
 */
require_once 'includes/header.php';

// Verificar permisos
if (!canEdit('reportes')) {
    redirect(ADMIN_URL . '/dashboard.php', 'No tiene permisos para acceder a esta sección', MSG_ERROR);
}

// Obtener fechas de filtro
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t'); // Último día del mes
$tipoReporte = $_GET['tipo'] ?? 'general';

// Validar fechas
if (strtotime($fechaInicio) > strtotime($fechaFin)) {
    $temp = $fechaInicio;
    $fechaInicio = $fechaFin;
    $fechaFin = $temp;
}

// Función para obtener datos según el tipo de reporte
function getReportData($tipo, $fechaInicio, $fechaFin) {
    global $db;
    
    switch($tipo) {
        case 'ventas':
            return [
                'cotizaciones' => $db->fetchAll("
                    SELECT c.*, cl.empresa, cl.nombre_contacto
                    FROM cotizaciones c
                    LEFT JOIN clientes cl ON c.cliente_id = cl.id
                    WHERE c.fecha BETWEEN ? AND ?
                    ORDER BY c.fecha DESC
                ", [$fechaInicio, $fechaFin]),
                
                'totales' => $db->fetchOne("
                    SELECT 
                        COUNT(*) as total_cotizaciones,
                        SUM(CASE WHEN estado = 'aprobada' THEN 1 ELSE 0 END) as aprobadas,
                        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                        SUM(total) as monto_total,
                        SUM(CASE WHEN estado = 'aprobada' THEN total ELSE 0 END) as monto_aprobado
                    FROM cotizaciones
                    WHERE fecha BETWEEN ? AND ?
                ", [$fechaInicio, $fechaFin])
            ];
            
        case 'productos':
            return [
                'mas_cotizados' => $db->fetchAll("
                    SELECT p.*, COUNT(ci.id) as veces_cotizado, SUM(ci.cantidad) as cantidad_total
                    FROM productos p
                    INNER JOIN cotizacion_items ci ON p.id = ci.producto_id
                    INNER JOIN cotizaciones c ON ci.cotizacion_id = c.id
                    WHERE c.fecha BETWEEN ? AND ?
                    GROUP BY p.id
                    ORDER BY veces_cotizado DESC
                    LIMIT 20
                ", [$fechaInicio, $fechaFin]),
                
                'categorias' => $db->fetchAll("
                    SELECT cat.nombre, COUNT(DISTINCT p.id) as productos, SUM(ci.cantidad) as unidades
                    FROM categorias cat
                    INNER JOIN productos p ON cat.id = p.categoria_id
                    INNER JOIN cotizacion_items ci ON p.id = ci.producto_id
                    INNER JOIN cotizaciones c ON ci.cotizacion_id = c.id
                    WHERE c.fecha BETWEEN ? AND ?
                    GROUP BY cat.id
                    ORDER BY unidades DESC
                ", [$fechaInicio, $fechaFin])
            ];
            
        case 'clientes':
            return [
                'top_clientes' => $db->fetchAll("
                    SELECT cl.*, 
                           COUNT(c.id) as total_cotizaciones,
                           SUM(c.total) as monto_total
                    FROM clientes cl
                    INNER JOIN cotizaciones c ON cl.id = c.cliente_id
                    WHERE c.fecha BETWEEN ? AND ?
                    GROUP BY cl.id
                    ORDER BY monto_total DESC
                    LIMIT 20
                ", [$fechaInicio, $fechaFin]),
                
                'nuevos_clientes' => $db->fetchAll("
                    SELECT * FROM clientes
                    WHERE created_at BETWEEN ? AND ?
                    ORDER BY created_at DESC
                ", [$fechaInicio, $fechaFin . ' 23:59:59'])
            ];
            
        case 'blog':
            return [
                'posts_populares' => $db->fetchAll("
                    SELECT * FROM blog_posts
                    WHERE created_at BETWEEN ? AND ?
                    ORDER BY vistas DESC
                    LIMIT 10
                ", [$fechaInicio, $fechaFin . ' 23:59:59']),
                
                'estadisticas' => $db->fetchOne("
                    SELECT 
                        COUNT(*) as total_posts,
                        SUM(vistas) as total_vistas,
                        AVG(vistas) as promedio_vistas,
                        (SELECT COUNT(*) FROM blog_comentarios WHERE created_at BETWEEN ? AND ?) as total_comentarios
                    FROM blog_posts
                    WHERE created_at BETWEEN ? AND ?
                ", [$fechaInicio, $fechaFin . ' 23:59:59', $fechaInicio, $fechaFin . ' 23:59:59'])
            ];
            
        default: // general
            return [
                'resumen' => [
                    'cotizaciones' => $db->fetchColumn("
                        SELECT COUNT(*) FROM cotizaciones WHERE fecha BETWEEN ? AND ?
                    ", [$fechaInicio, $fechaFin]),
                    
                    'clientes_nuevos' => $db->fetchColumn("
                        SELECT COUNT(*) FROM clientes WHERE created_at BETWEEN ? AND ?
                    ", [$fechaInicio, $fechaFin . ' 23:59:59']),
                    
                    'productos_activos' => $db->fetchColumn("SELECT COUNT(*) FROM productos WHERE activo = 1"),
                    
                    'consultas' => $db->fetchColumn("
                        SELECT COUNT(*) FROM consultas WHERE created_at BETWEEN ? AND ?
                    ", [$fechaInicio, $fechaFin . ' 23:59:59']),
                    
                    'monto_cotizado' => $db->fetchColumn("
                        SELECT SUM(total) FROM cotizaciones WHERE fecha BETWEEN ? AND ?
                    ", [$fechaInicio, $fechaFin]) ?: 0
                ],
                
                'grafico_ventas' => $db->fetchAll("
                    SELECT DATE(fecha) as dia, COUNT(*) as cantidad, SUM(total) as monto
                    FROM cotizaciones
                    WHERE fecha BETWEEN ? AND ?
                    GROUP BY DATE(fecha)
                    ORDER BY fecha
                ", [$fechaInicio, $fechaFin])
            ];
    }
}

$reportData = getReportData($tipoReporte, $fechaInicio, $fechaFin);
$pageTitle = 'Reportes';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Reportes</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Reportes</li>
            </ol>
        </nav>
    </div>
    <div>
        <button class="btn btn-primary" onclick="exportReport()">
            <i class="bi bi-download me-2"></i>Exportar
        </button>
        <button class="btn btn-secondary" onclick="printReport()">
            <i class="bi bi-printer me-2"></i>Imprimir
        </button>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tipo de Reporte</label>
                <select class="form-select" name="tipo" onchange="this.form.submit()">
                    <option value="general" <?php echo $tipoReporte == 'general' ? 'selected' : ''; ?>>General</option>
                    <option value="ventas" <?php echo $tipoReporte == 'ventas' ? 'selected' : ''; ?>>Ventas</option>
                    <option value="productos" <?php echo $tipoReporte == 'productos' ? 'selected' : ''; ?>>Productos</option>
                    <option value="clientes" <?php echo $tipoReporte == 'clientes' ? 'selected' : ''; ?>>Clientes</option>
                    <option value="blog" <?php echo $tipoReporte == 'blog' ? 'selected' : ''; ?>>Blog</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" class="form-control" name="fecha_inicio" 
                       value="<?php echo $fechaInicio; ?>" max="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" class="form-control" name="fecha_fin" 
                       value="<?php echo $fechaFin; ?>" max="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block">
                    <i class="bi bi-funnel me-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Contenido del Reporte -->
<div id="report-content">
    <?php if ($tipoReporte == 'general'): ?>
    <!-- Reporte General -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Cotizaciones</h6>
                    <h3 class="mb-0"><?php echo number_format($reportData['resumen']['cotizaciones']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Monto Cotizado</h6>
                    <h3 class="mb-0">$<?php echo number_format($reportData['resumen']['monto_cotizado'], 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Clientes Nuevos</h6>
                    <h3 class="mb-0"><?php echo number_format($reportData['resumen']['clientes_nuevos']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Consultas</h6>
                    <h3 class="mb-0"><?php echo number_format($reportData['resumen']['consultas']); ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráfico de Ventas -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Evolución de Cotizaciones</h5>
        </div>
        <div class="card-body">
            <canvas id="salesChart" height="100"></canvas>
        </div>
    </div>
    
    <?php elseif ($tipoReporte == 'ventas'): ?>
    <!-- Reporte de Ventas -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total</h6>
                    <h4><?php echo $reportData['totales']['total_cotizaciones']; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Aprobadas</h6>
                    <h4 class="text-success"><?php echo $reportData['totales']['aprobadas']; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Pendientes</h6>
                    <h4 class="text-warning"><?php echo $reportData['totales']['pendientes']; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Rechazadas</h6>
                    <h4 class="text-danger"><?php echo $reportData['totales']['rechazadas']; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Monto Total Cotizado</h6>
                    <h4>$<?php echo number_format($reportData['totales']['monto_total'], 2); ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Detalle de Cotizaciones</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reportData['cotizaciones'] as $cot): ?>
                        <tr>
                            <td><?php echo $cot['numero']; ?></td>
                            <td><?php echo formatDate($cot['fecha']); ?></td>
                            <td><?php echo sanitize($cot['empresa']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo match($cot['estado']) {
                                    'aprobada' => 'success',
                                    'pendiente' => 'warning',
                                    'rechazada' => 'danger',
                                    default => 'secondary'
                                }; ?>"><?php echo ucfirst($cot['estado']); ?></span>
                            </td>
                            <td>$<?php echo number_format($cot['total'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php elseif ($tipoReporte == 'productos'): ?>
    <!-- Reporte de Productos -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Productos Más Cotizados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Veces Cotizado</th>
                                    <th>Cantidad Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reportData['mas_cotizados'] as $prod): ?>
                                <tr>
                                    <td><?php echo sanitize($prod['nombre']); ?></td>
                                    <td><?php echo $prod['veces_cotizado']; ?></td>
                                    <td><?php echo $prod['cantidad_total']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Por Categoría</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($tipoReporte == 'clientes'): ?>
    <!-- Reporte de Clientes -->
    <div class="row mb-4">
<div class="col-md-6">
           <div class="card">
               <div class="card-header">
                   <h5 class="card-title mb-0">Top Clientes por Monto</h5>
               </div>
               <div class="card-body">
                   <div class="table-responsive">
                       <table class="table table-sm">
                           <thead>
                               <tr>
                                   <th>Cliente</th>
                                   <th>Cotizaciones</th>
                                   <th>Monto Total</th>
                               </tr>
                           </thead>
                           <tbody>
                               <?php foreach($reportData['top_clientes'] as $cliente): ?>
                               <tr>
                                   <td><?php echo sanitize($cliente['empresa']); ?></td>
                                   <td><?php echo $cliente['total_cotizaciones']; ?></td>
                                   <td>$<?php echo number_format($cliente['monto_total'], 2); ?></td>
                               </tr>
                               <?php endforeach; ?>
                           </tbody>
                       </table>
                   </div>
               </div>
           </div>
       </div>
       <div class="col-md-6">
           <div class="card">
               <div class="card-header">
                   <h5 class="card-title mb-0">Nuevos Clientes (<?php echo count($reportData['nuevos_clientes']); ?>)</h5>
               </div>
               <div class="card-body">
                   <div class="table-responsive">
                       <table class="table table-sm">
                           <thead>
                               <tr>
                                   <th>Empresa</th>
                                   <th>Contacto</th>
                                   <th>Fecha Registro</th>
                               </tr>
                           </thead>
                           <tbody>
                               <?php foreach($reportData['nuevos_clientes'] as $cliente): ?>
                               <tr>
                                   <td><?php echo sanitize($cliente['empresa']); ?></td>
                                   <td><?php echo sanitize($cliente['nombre_contacto']); ?></td>
                                   <td><?php echo formatDate($cliente['created_at']); ?></td>
                               </tr>
                               <?php endforeach; ?>
                           </tbody>
                       </table>
                   </div>
               </div>
           </div>
       </div>
   </div>
   
   <?php elseif ($tipoReporte == 'blog'): ?>
   <!-- Reporte de Blog -->
   <div class="row mb-4">
       <div class="col-md-3">
           <div class="card">
               <div class="card-body text-center">
                   <h6 class="text-muted">Posts Publicados</h6>
                   <h3><?php echo $reportData['estadisticas']['total_posts']; ?></h3>
               </div>
           </div>
       </div>
       <div class="col-md-3">
           <div class="card">
               <div class="card-body text-center">
                   <h6 class="text-muted">Total Vistas</h6>
                   <h3><?php echo number_format($reportData['estadisticas']['total_vistas']); ?></h3>
               </div>
           </div>
       </div>
       <div class="col-md-3">
           <div class="card">
               <div class="card-body text-center">
                   <h6 class="text-muted">Promedio Vistas</h6>
                   <h3><?php echo number_format($reportData['estadisticas']['promedio_vistas']); ?></h3>
               </div>
           </div>
       </div>
       <div class="col-md-3">
           <div class="card">
               <div class="card-body text-center">
                   <h6 class="text-muted">Comentarios</h6>
                   <h3><?php echo $reportData['estadisticas']['total_comentarios']; ?></h3>
               </div>
           </div>
       </div>
   </div>
   
   <div class="card">
       <div class="card-header">
           <h5 class="card-title mb-0">Posts Más Populares</h5>
       </div>
       <div class="card-body">
           <div class="table-responsive">
               <table class="table">
                   <thead>
                       <tr>
                           <th>Título</th>
                           <th>Categoría</th>
                           <th>Fecha</th>
                           <th>Vistas</th>
                       </tr>
                   </thead>
                   <tbody>
                       <?php foreach($reportData['posts_populares'] as $post): ?>
                       <tr>
                           <td>
                               <a href="<?php echo SITE_URL; ?>/blog/<?php echo $post['slug']; ?>" target="_blank">
                                   <?php echo sanitize($post['titulo']); ?>
                               </a>
                           </td>
                           <td><?php echo sanitize($post['categoria']); ?></td>
                           <td><?php echo formatDate($post['created_at']); ?></td>
                           <td><?php echo number_format($post['vistas']); ?></td>
                       </tr>
                       <?php endforeach; ?>
                   </tbody>
               </table>
           </div>
       </div>
   </div>
   <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
<?php if ($tipoReporte == 'general' && !empty($reportData['grafico_ventas'])): ?>
// Gráfico de ventas
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesCtx, {
   type: 'line',
   data: {
       labels: <?php echo json_encode(array_column($reportData['grafico_ventas'], 'dia')); ?>,
       datasets: [{
           label: 'Monto Cotizado',
           data: <?php echo json_encode(array_column($reportData['grafico_ventas'], 'monto')); ?>,
           borderColor: 'rgb(75, 192, 192)',
           backgroundColor: 'rgba(75, 192, 192, 0.2)',
           tension: 0.1
       }, {
           label: 'Cantidad',
           data: <?php echo json_encode(array_column($reportData['grafico_ventas'], 'cantidad')); ?>,
           borderColor: 'rgb(255, 99, 132)',
           backgroundColor: 'rgba(255, 99, 132, 0.2)',
           tension: 0.1,
           yAxisID: 'y1'
       }]
   },
   options: {
       responsive: true,
       maintainAspectRatio: false,
       interaction: {
           mode: 'index',
           intersect: false,
       },
       scales: {
           y: {
               type: 'linear',
               display: true,
               position: 'left',
               ticks: {
                   callback: function(value) {
                       return '$' + value.toLocaleString();
                   }
               }
           },
           y1: {
               type: 'linear',
               display: true,
               position: 'right',
               grid: {
                   drawOnChartArea: false,
               }
           }
       }
   }
});
<?php endif; ?>

<?php if ($tipoReporte == 'productos' && !empty($reportData['categorias'])): ?>
// Gráfico de categorías
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
   type: 'doughnut',
   data: {
       labels: <?php echo json_encode(array_column($reportData['categorias'], 'nombre')); ?>,
       datasets: [{
           data: <?php echo json_encode(array_column($reportData['categorias'], 'unidades')); ?>,
           backgroundColor: [
               'rgba(255, 99, 132, 0.8)',
               'rgba(54, 162, 235, 0.8)',
               'rgba(255, 205, 86, 0.8)',
               'rgba(75, 192, 192, 0.8)',
               'rgba(153, 102, 255, 0.8)',
           ]
       }]
   },
   options: {
       responsive: true,
       plugins: {
           legend: {
               position: 'bottom',
           }
       }
   }
});
<?php endif; ?>

function exportReport() {
   const params = new URLSearchParams(window.location.search);
   params.append('export', 'excel');
   window.location.href = '<?php echo ADMIN_URL; ?>/api/export-report.php?' + params.toString();
}

function printReport() {
   window.print();
}
</script>

<!-- Estilos para impresión -->
<style>
@media print {
   .navbar, .breadcrumb, .btn, form {
       display: none !important;
   }
   .card {
       border: 1px solid #ddd !important;
       page-break-inside: avoid;
   }
}
</style>

<?php include 'includes/footer.php'; ?>