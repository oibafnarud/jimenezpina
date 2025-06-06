<?php
/**
 * API para exportar reportes a Excel
 * /admin/api/export-report.php
 */
require_once '../../config/config.php';
require_once '../../vendor/autoload.php'; // Si usas PhpSpreadsheet vía Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verificar permisos
if (!isAuthenticated() || !canEdit('reportes')) {
    http_response_code(403);
    exit('No autorizado');
}

// Obtener parámetros
$tipo = $_GET['tipo'] ?? 'general';
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');

// Crear spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar encabezados
$sheet->setCellValue('A1', 'Reporte de ' . ucfirst($tipo));
$sheet->setCellValue('A2', 'Período: ' . formatDate($fechaInicio) . ' - ' . formatDate($fechaFin));
$sheet->setCellValue('A3', 'Generado: ' . date('d/m/Y H:i'));

// Obtener datos según el tipo
$db = Database::getInstance();
$row = 5;

switch($tipo) {
    case 'ventas':
        // Encabezados
        $sheet->setCellValue('A' . $row, 'Número');
        $sheet->setCellValue('B' . $row, 'Fecha');
        $sheet->setCellValue('C' . $row, 'Cliente');
        $sheet->setCellValue('D' . $row, 'Estado');
        $sheet->setCellValue('E' . $row, 'Subtotal');
        $sheet->setCellValue('F' . $row, 'ITBIS');
        $sheet->setCellValue('G' . $row, 'Total');
        
        // Datos
        $cotizaciones = $db->fetchAll("
            SELECT c.*, cl.empresa
            FROM cotizaciones c
            LEFT JOIN clientes cl ON c.cliente_id = cl.id
            WHERE c.fecha BETWEEN ? AND ?
            ORDER BY c.fecha DESC
        ", [$fechaInicio, $fechaFin]);
        
        $row++;
        foreach($cotizaciones as $cot) {
            $sheet->setCellValue('A' . $row, $cot['numero']);
            $sheet->setCellValue('B' . $row, $cot['fecha']);
            $sheet->setCellValue('C' . $row, $cot['empresa']);
            $sheet->setCellValue('D' . $row, ucfirst($cot['estado']));
            $sheet->setCellValue('E' . $row, $cot['subtotal']);
            $sheet->setCellValue('F' . $row, $cot['itbis']);
            $sheet->setCellValue('G' . $row, $cot['total']);
            $row++;
        }
        break;
        
    case 'productos':
        // Encabezados
        $sheet->setCellValue('A' . $row, 'Código');
        $sheet->setCellValue('B' . $row, 'Producto');
        $sheet->setCellValue('C' . $row, 'Categoría');
        $sheet->setCellValue('D' . $row, 'Marca');
        $sheet->setCellValue('E' . $row, 'Veces Cotizado');
        $sheet->setCellValue('F' . $row, 'Cantidad Total');
        
        // Datos
        $productos = $db->fetchAll("
            SELECT p.*, cat.nombre as categoria, m.nombre as marca,
                   COUNT(ci.id) as veces_cotizado, 
                   SUM(ci.cantidad) as cantidad_total
            FROM productos p
            LEFT JOIN categorias cat ON p.categoria_id = cat.id
            LEFT JOIN marcas m ON p.marca_id = m.id
            LEFT JOIN cotizacion_items ci ON p.id = ci.producto_id
            LEFT JOIN cotizaciones c ON ci.cotizacion_id = c.id
            WHERE c.fecha BETWEEN ? AND ?
            GROUP BY p.id
            ORDER BY veces_cotizado DESC
        ", [$fechaInicio, $fechaFin]);
        
        $row++;
        foreach($productos as $prod) {
            $sheet->setCellValue('A' . $row, $prod['sku']);
            $sheet->setCellValue('B' . $row, $prod['nombre']);
            $sheet->setCellValue('C' . $row, $prod['categoria']);
            $sheet->setCellValue('D' . $row, $prod['marca']);
            $sheet->setCellValue('E' . $row, $prod['veces_cotizado']);
            $sheet->setCellValue('F' . $row, $prod['cantidad_total']);
            $row++;
        }
        break;
}

// Aplicar estilos
$sheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A5:G5')->getFont()->setBold(true);
$sheet->getStyle('A5:G5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      ->getStartColor()->setARGB('FFE0E0E0');

// Auto-ajustar columnas
foreach(range('A','G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Descargar archivo
$filename = 'reporte_' . $tipo . '_' . date('Y-m-d') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;