<?php
/**
 * Dashboard Principal - Panel Administrativo
 * Jiménez & Piña Survey Instruments
 */
require_once 'includes/header.php';

// Obtener estadísticas generales
$stats = [
    'productos' => $db->count('productos', 'activo = 1'),
    'clientes' => $db->count('clientes', 'activo = 1'),
    'cotizaciones' => $db->count('cotizaciones', "MONTH(fecha) = MONTH(CURRENT_DATE())"),
    'ventas_mes' => $db->fetchColumn("
        SELECT SUM(total) FROM cotizaciones 
        WHERE estado = 'aprobada' 
        AND MONTH(fecha) = MONTH(CURRENT_DATE())
    ") ?: 0
];

// Productos más vistos
$productosPopulares = $db->fetchAll("
    SELECT p.nombre, p.vistas, c.nombre as categoria
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    WHERE p.activo = 1
    ORDER BY p.vistas DESC
    LIMIT 5
");

// Últimas cotizaciones
$ultimasCotizaciones = $db->fetchAll("
    SELECT c.*, cl.empresa, cl.nombre_contacto
    FROM cotizaciones c
    LEFT JOIN clientes cl ON c.cliente_id = cl.id
    ORDER BY c.created_at DESC
    LIMIT 10
");

// Consultas pendientes
$consultasPendientes = $db->fetchAll("
    SELECT * FROM consultas
    WHERE estado = 'nueva'
    ORDER BY created_at DESC
    LIMIT 5
");

// Actividad reciente
$actividadReciente = $db->fetchAll("
    SELECT a.*, u.nombre as usuario_nombre
    FROM actividad_log a
    LEFT JOIN usuarios u ON a.usuario_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 10
");

// Datos para gráficos
$ventasPorMes = $db->fetchAll("
    SELECT 
        MONTH(fecha) as mes,
        COUNT(*) as cantidad,
        SUM(total) as total
    FROM cotizaciones
    WHERE estado = 'aprobada'
    AND YEAR(fecha) = YEAR(CURRENT_DATE())
    GROUP BY MONTH(fecha)
    ORDER BY mes
");

$productosPorCategoria = $db->fetchAll("
    SELECT c.nombre, COUNT(p.id) as cantidad
    FROM categorias c
    LEFT JOIN productos p ON c.id = p.categoria_id AND p.activo = 1
    WHERE c.activo = 1
    GROUP BY c.id
    ORDER BY cantidad DESC
");

$pageTitle = 'Dashboard';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-3">Dashboard</h1>
        <p class="text-muted">Bienvenido, <?php echo sanitize($adminUser['name']); ?>. 
        Aquí está el resumen de su sistema.</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Productos Activos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['productos']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-box-seam fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Ventas del Mes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo formatPrice($stats['ventas_mes']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-dollar fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Cotizaciones del Mes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['cotizaciones']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calculator fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Clientes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['clientes']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Ventas por Mes -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Ventas por Mes</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                        <a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/reportes.php">Ver Reportes</a>
                        <a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/exportar.php?tipo=ventas">Exportar</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="myAreaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Productos por Categoría -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Productos por Categoría</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="myPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Últimas Cotizaciones -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Últimas Cotizaciones</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($ultimasCotizaciones as $cotizacion): ?>
                            <tr>
                                <td><?php echo sanitize($cotizacion['numero']); ?></td>
                                <td><?php echo sanitize($cotizacion['empresa'] ?: $cotizacion['nombre_contacto']); ?></td>
                                <td><?php echo formatPrice($cotizacion['total']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = match($cotizacion['estado']) {
                                        'borrador' => 'bg-secondary',
                                        'enviada' => 'bg-info',
                                        'aprobada' => 'bg-success',
                                        'rechazada' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo ucfirst($cotizacion['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo ADMIN_URL; ?>/cotizacion-detalle.php?id=<?php echo $cotizacion['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Consultas Pendientes -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Consultas Pendientes</h6>
            </div>
            <div class="card-body">
                <?php if(empty($consultasPendientes)): ?>
                <p class="text-center text-muted">No hay consultas pendientes</p>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach($consultasPendientes as $consulta): ?>
                    <a href="<?php echo ADMIN_URL; ?>/consulta-detalle.php?id=<?php echo $consulta['id']; ?>" 
                       class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo sanitize($consulta['nombre']); ?></h6>
                            <small><?php echo formatDateHuman($consulta['created_at']); ?></small>
                        </div>
                        <p class="mb-1"><?php echo sanitize($consulta['asunto']); ?></p>
                        <small class="text-muted"><?php echo sanitize($consulta['email']); ?></small>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Productos Populares & Actividad -->
<div class="row">
    <!-- Productos Más Vistos -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Productos Más Vistos</h6>
            </div>
            <div class="card-body">
                <?php foreach($productosPopulares as $producto): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0"><?php echo sanitize($producto['nombre']); ?></h6>
                        <small class="text-muted"><?php echo sanitize($producto['categoria']); ?></small>
                    </div>
                    <span class="badge bg-primary rounded-pill">
                        <?php echo number_format($producto['vistas']); ?> vistas
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Actividad Reciente -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Actividad Reciente</h6>
            </div>
            <div class="card-body">
                <div class="activity-feed">
                    <?php foreach($actividadReciente as $actividad): ?>
                    <div class="feed-item mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-primary p-2 text-white">
                                    <i class="bi bi-<?php echo getActivityIcon($actividad['tipo']); ?> small"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">
                                    <?php echo sanitize($actividad['usuario_nombre'] ?: 'Sistema'); ?>
                                    <small class="text-muted float-end">
                                        <?php echo formatDateHuman($actividad['created_at']); ?>
                                    </small>
                                </h6>
                                <p class="text-muted mb-0 small">
                                    <?php echo sanitize($actividad['descripcion']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Función para obtener icono de actividad
function getActivityIcon($tipo) {
    return match($tipo) {
        'login_success' => 'box-arrow-in-right',
        'producto_created' => 'plus-circle',
        'producto_updated' => 'pencil',
        'producto_deleted' => 'trash',
        'cotizacion_created' => 'calculator',
        default => 'clock-history'
    };
}

// Scripts específicos de la página
$pageScripts = '
<script>
// Datos para gráficos
const ventasPorMes = ' . json_encode($ventasPorMes) . ';
const productosPorCategoria = ' . json_encode($productosPorCategoria) . ';

// Gráfico de Área - Ventas por Mes
const ctx = document.getElementById("myAreaChart");
const myLineChart = new Chart(ctx, {
    type: "line",
    data: {
        labels: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
        datasets: [{
            label: "Ventas",
            lineTension: 0.3,
            backgroundColor: "rgba(78, 115, 223, 0.05)",
            borderColor: "rgba(78, 115, 223, 1)",
            pointRadius: 3,
            pointBackgroundColor: "rgba(78, 115, 223, 1)",
            pointBorderColor: "rgba(78, 115, 223, 1)",
            pointHoverRadius: 3,
            pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
            pointHoverBorderColor: "rgba(78, 115, 223, 1)",
            pointHitRadius: 10,
            pointBorderWidth: 2,
            data: Array(12).fill(0).map((_, i) => {
                const mes = ventasPorMes.find(v => v.mes == i + 1);
                return mes ? mes.total : 0;
            })
        }]
    },
    options: {
        maintainAspectRatio: false,
        layout: {
            padding: {
                left: 10,
                right: 25,
                top: 25,
                bottom: 0
            }
        },
        scales: {
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                }
            },
            y: {
                ticks: {
                    callback: function(value) {
                        return "$" + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: "rgb(255,255,255)",
                bodyColor: "#858796",
                titleMarginBottom: 10,
                titleColor: "#6e707e",
                titleFont: {
                    size: 14
                },
                borderColor: "#dddfeb",
                borderWidth: 1,
                padding: 15,
                displayColors: false,
                intersect: false,
                mode: "index",
                caretPadding: 10,
                callbacks: {
                    label: function(context) {
                        return "Ventas: $" + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});

// Gráfico de Pie - Productos por Categoría
const ctx2 = document.getElementById("myPieChart");
const myPieChart = new Chart(ctx2, {
    type: "doughnut",
    data: {
        labels: productosPorCategoria.map(c => c.nombre),
        datasets: [{
            data: productosPorCategoria.map(c => c.cantidad),
            backgroundColor: ["#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b", "#858796"],
            hoverBackgroundColor: ["#2e59d9", "#17a673", "#2c9faf", "#dda20a", "#e02d1b", "#60616f"],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                backgroundColor: "rgb(255,255,255)",
                bodyColor: "#858796",
                borderColor: "#dddfeb",
                borderWidth: 1,
                padding: 15,
                displayColors: false,
                caretPadding: 10
            },
            legend: {
                display: true,
                position: "bottom"
            }
        },
        cutout: 80,
    }
});
</script>
';

include 'includes/footer.php';
?>