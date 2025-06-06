<?php
/**
 * API de Mantenimiento del Sistema
 * /admin/api/maintenance.php
 */
require_once '../../config/config.php';

// Verificar autenticación y permisos
if (!isAuthenticated() || !canEdit('configuracion')) {
    redirect(ADMIN_URL . '/dashboard.php', 'No autorizado', MSG_ERROR);
}

$action = $_GET['action'] ?? '';
$token = $_GET['token'] ?? '';

// Verificar CSRF token
if (!verifyCSRFToken($token)) {
    redirect(ADMIN_URL . '/configuracion.php', 'Token de seguridad inválido', MSG_ERROR);
}

$db = Database::getInstance();

switch($action) {
    case 'clear-cache':
        // Limpiar caché de archivos
        $cacheDir = ROOT_PATH . '/cache';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*');
            foreach($files as $file) {
                if(is_file($file) && $file != $cacheDir . '/.htaccess') {
                    unlink($file);
                }
            }
        }
        
        // Limpiar caché de sesiones antiguas
        $db->query("DELETE FROM sesiones WHERE ultima_actividad < DATE_SUB(NOW(), INTERVAL 7 DAY)");
        
        // Limpiar logs antiguos
        $db->query("DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
        
        logActivity('cache_cleared', 'Caché del sistema limpiada', 'system');
        redirect(ADMIN_URL . '/configuracion.php?tab=maintenance', 'Caché limpiada correctamente', MSG_SUCCESS);
        break;
        
    case 'optimize-db':
        // Obtener todas las tablas
        $tables = $db->fetchAll("SHOW TABLES");
        $optimized = 0;
        
        foreach($tables as $table) {
            $tableName = array_values($table)[0];
            $db->query("OPTIMIZE TABLE `$tableName`");
            $optimized++;
        }
        
        logActivity('database_optimized', "Base de datos optimizada: $optimized tablas", 'system');
        redirect(ADMIN_URL . '/configuracion.php?tab=maintenance', 
                "Base de datos optimizada: $optimized tablas procesadas", MSG_SUCCESS);
        break;
        
    case 'backup':
        // Crear directorio de backups si no existe
        $backupDir = ROOT_PATH . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // Nombre del archivo
        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;
        
        // Comando mysqldump
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_NAME),
            escapeshellarg($filepath)
        );
        
        exec($command, $output, $return);
        
        if ($return === 0) {
            // Comprimir el archivo
            $zip = new ZipArchive();
            $zipname = $filepath . '.zip';
            
            if ($zip->open($zipname, ZipArchive::CREATE) === TRUE) {
                $zip->addFile($filepath, $filename);
                $zip->close();
                unlink($filepath); // Eliminar SQL sin comprimir
                
                // Limpiar backups antiguos
                $retention = (int)getConfig('backup_retention', 30);
                $oldBackups = glob($backupDir . '/backup_*.zip');
                foreach($oldBackups as $backup) {
                    if (filemtime($backup) < strtotime("-$retention days")) {
                        unlink($backup);
                    }
                }
                
                logActivity('backup_created', "Respaldo creado: $filename.zip", 'system');
                
                // Descargar el archivo
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($zipname) . '"');
                header('Content-Length: ' . filesize($zipname));
                readfile($zipname);
                exit;
            }
        }
        
        redirect(ADMIN_URL . '/configuracion.php?tab=maintenance', 
                'Error al crear el respaldo', MSG_ERROR);
        break;
        
    default:
        redirect(ADMIN_URL . '/configuracion.php', 'Acción no válida', MSG_ERROR);
}