<?php
/**
 * Configuración del Sistema - Panel Administrativo
 * /admin/configuracion.php
 */
require_once 'includes/header.php';

// Verificar permisos
if (!canEdit('configuracion')) {
    redirect(ADMIN_URL . '/dashboard.php', 'No tiene permisos para acceder a esta sección', MSG_ERROR);
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido';
    } else {
        $tab = $_POST['tab'] ?? 'general';
        
        try {
            switch($tab) {
                case 'general':
                    updateConfig([
                        'site_name' => sanitize($_POST['site_name']),
                        'site_tagline' => sanitize($_POST['site_tagline']),
                        'site_description' => sanitize($_POST['site_description']),
                        'admin_email' => sanitize($_POST['admin_email']),
                        'support_email' => sanitize($_POST['support_email']),
                        'phone' => sanitize($_POST['phone']),
                        'whatsapp' => sanitize($_POST['whatsapp']),
                        'address' => sanitize($_POST['address']),
                        'google_maps_api' => sanitize($_POST['google_maps_api']),
                        'google_analytics' => sanitize($_POST['google_analytics'])
                    ]);
                    
                    // Logo
                    if (!empty($_FILES['logo']['name'])) {
                        $upload = uploadFile($_FILES['logo'], 'branding', ALLOWED_IMAGE_TYPES);
                        if ($upload['success']) {
                            updateConfig(['logo' => $upload['path']]);
                        }
                    }
                    
                    // Favicon
                    if (!empty($_FILES['favicon']['name'])) {
                        $upload = uploadFile($_FILES['favicon'], 'branding', ['ico', 'png']);
                        if ($upload['success']) {
                            updateConfig(['favicon' => $upload['path']]);
                        }
                    }
                    break;
                    
                case 'email':
                    updateConfig([
                        'smtp_host' => sanitize($_POST['smtp_host']),
                        'smtp_port' => (int)$_POST['smtp_port'],
                        'smtp_secure' => $_POST['smtp_secure'],
                        'smtp_username' => sanitize($_POST['smtp_username']),
                        'smtp_password' => $_POST['smtp_password'] ?: getConfig('smtp_password'),
                        'email_from' => sanitize($_POST['email_from']),
                        'email_from_name' => sanitize($_POST['email_from_name'])
                    ]);
                    break;
                    
                case 'social':
                    updateConfig([
                        'facebook' => sanitize($_POST['facebook']),
                        'twitter' => sanitize($_POST['twitter']),
                        'instagram' => sanitize($_POST['instagram']),
                        'linkedin' => sanitize($_POST['linkedin']),
                        'youtube' => sanitize($_POST['youtube'])
                    ]);
                    break;
                    
                case 'seo':
                    updateConfig([
                        'meta_title' => sanitize($_POST['meta_title']),
                        'meta_description' => sanitize($_POST['meta_description']),
                        'meta_keywords' => sanitize($_POST['meta_keywords']),
                        'robots_txt' => $_POST['robots_txt']
                    ]);
                    break;
                    
                case 'maintenance':
                    updateConfig([
                        'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
                        'maintenance_message' => sanitize($_POST['maintenance_message']),
                        'backup_frequency' => $_POST['backup_frequency'],
                        'backup_retention' => (int)$_POST['backup_retention']
                    ]);
                    break;
            }
            
            // Limpiar caché de configuración
            clearConfigCache();
            
            $success = 'Configuración actualizada correctamente';
            
        } catch (Exception $e) {
            $error = 'Error al actualizar la configuración: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Configuración del Sistema';
$currentTab = $_GET['tab'] ?? 'general';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Configuración</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Configuración</li>
            </ol>
        </nav>
    </div>
</div>

<?php if (isset($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>
    <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Tabs de configuración -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo $currentTab == 'general' ? 'active' : ''; ?>" 
           href="?tab=general">
            <i class="bi bi-gear me-2"></i>General
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $currentTab == 'email' ? 'active' : ''; ?>" 
           href="?tab=email">
            <i class="bi bi-envelope me-2"></i>Email
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $currentTab == 'social' ? 'active' : ''; ?>" 
           href="?tab=social">
            <i class="bi bi-share me-2"></i>Redes Sociales
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $currentTab == 'seo' ? 'active' : ''; ?>" 
           href="?tab=seo">
            <i class="bi bi-search me-2"></i>SEO
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $currentTab == 'maintenance' ? 'active' : ''; ?>" 
           href="?tab=maintenance">
            <i class="bi bi-tools me-2"></i>Mantenimiento
        </a>
    </li>
</ul>

<form method="POST" enctype="multipart/form-data">
    <?php echo csrfField(); ?>
    <input type="hidden" name="tab" value="<?php echo $currentTab; ?>">
    
    <?php if ($currentTab == 'general'): ?>
    <!-- Configuración General -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Configuración General</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre del Sitio</label>
                    <input type="text" class="form-control" name="site_name" 
                           value="<?php echo sanitize(getConfig('site_name')); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slogan</label>
                    <input type="text" class="form-control" name="site_tagline" 
                           value="<?php echo sanitize(getConfig('site_tagline')); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción del Sitio</label>
                    <textarea class="form-control" name="site_description" rows="3"><?php echo sanitize(getConfig('site_description')); ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Logo</label>
                    <input type="file" class="form-control" name="logo" accept="image/*">
                    <?php if ($logo = getConfig('logo')): ?>
                    <div class="mt-2">
                        <img src="<?php echo UPLOADS_URL . '/' . $logo; ?>" alt="Logo" style="max-height: 60px;">
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Favicon</label>
                    <input type="file" class="form-control" name="favicon" accept=".ico,.png">
                    <?php if ($favicon = getConfig('favicon')): ?>
                    <div class="mt-2">
                        <img src="<?php echo UPLOADS_URL . '/' . $favicon; ?>" alt="Favicon" style="max-height: 32px;">
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Email Administrativo</label>
                    <input type="email" class="form-control" name="admin_email" 
                           value="<?php echo sanitize(getConfig('admin_email')); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email de Soporte</label>
                    <input type="email" class="form-control" name="support_email" 
                           value="<?php echo sanitize(getConfig('support_email')); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" name="phone" 
                           value="<?php echo sanitize(getConfig('phone')); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">WhatsApp</label>
                    <input type="tel" class="form-control" name="whatsapp" 
                           value="<?php echo sanitize(getConfig('whatsapp')); ?>"
                           placeholder="18095551234">
                </div>
                
                <div class="col-12">
                    <label class="form-label">Dirección</label>
                    <textarea class="form-control" name="address" rows="2"><?php echo sanitize(getConfig('address')); ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Google Maps API Key</label>
                    <input type="text" class="form-control" name="google_maps_api" 
                           value="<?php echo sanitize(getConfig('google_maps_api')); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Google Analytics ID</label>
                    <input type="text" class="form-control" name="google_analytics" 
                           value="<?php echo sanitize(getConfig('google_analytics')); ?>"
                           placeholder="G-XXXXXXXXXX">
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($currentTab == 'email'): ?>
    <!-- Configuración de Email -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Configuración de Email SMTP</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Servidor SMTP</label>
                    <input type="text" class="form-control" name="smtp_host" 
                           value="<?php echo sanitize(getConfig('smtp_host')); ?>"
                           placeholder="smtp.gmail.com">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Puerto</label>
                    <input type="number" class="form-control" name="smtp_port" 
                           value="<?php echo getConfig('smtp_port') ?: 587; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Seguridad</label>
                    <select class="form-select" name="smtp_secure">
                        <option value="">Ninguna</option>
                        <option value="tls" <?php echo getConfig('smtp_secure') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                        <option value="ssl" <?php echo getConfig('smtp_secure') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Usuario SMTP</label>
                    <input type="text" class="form-control" name="smtp_username" 
                           value="<?php echo sanitize(getConfig('smtp_username')); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Contraseña SMTP</label>
                    <input type="password" class="form-control" name="smtp_password" 
                           placeholder="••••••••">
                    <div class="form-text">Dejar vacío para mantener la actual</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Remitente</label>
                    <input type="email" class="form-control" name="email_from" 
                           value="<?php echo sanitize(getConfig('email_from')); ?>">
                </div>
                
                <div class="col-12">
                    <label class="form-label">Nombre del Remitente</label>
                    <input type="text" class="form-control" name="email_from_name" 
                           value="<?php echo sanitize(getConfig('email_from_name')); ?>">
                </div>
                
                <div class="col-12">
                    <button type="button" class="btn btn-secondary" onclick="testEmail()">
                        <i class="bi bi-send me-2"></i>Enviar Email de Prueba
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($currentTab == 'social'): ?>
    <!-- Redes Sociales -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Redes Sociales</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-facebook me-1"></i> Facebook
                    </label>
                    <input type="url" class="form-control" name="facebook" 
                           value="<?php echo sanitize(getConfig('facebook')); ?>"
                           placeholder="https://facebook.com/tupagina">
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-twitter me-1"></i> Twitter/X
                    </label>
                    <input type="url" class="form-control" name="twitter" 
                           value="<?php echo sanitize(getConfig('twitter')); ?>"
                           placeholder="https://twitter.com/tuusuario">
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-instagram me-1"></i> Instagram
                    </label>
                    <input type="url" class="form-control" name="instagram" 
                           value="<?php echo sanitize(getConfig('instagram')); ?>"
                           placeholder="https://instagram.com/tuusuario">
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-linkedin me-1"></i> LinkedIn
                    </label>
                    <input type="url" class="form-control" name="linkedin" 
                           value="<?php echo sanitize(getConfig('linkedin')); ?>"
                           placeholder="https://linkedin.com/company/tuempresa">
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-youtube me-1"></i> YouTube
                    </label>
                    <input type="url" class="form-control" name="youtube" 
                           value="<?php echo sanitize(getConfig('youtube')); ?>"
                           placeholder="https://youtube.com/@tucanal">
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($currentTab == 'seo'): ?>
    <!-- SEO -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Configuración SEO</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Meta Title por Defecto</label>
                    <input type="text" class="form-control" name="meta_title" 
                           value="<?php echo sanitize(getConfig('meta_title')); ?>"
                           maxlength="70">
                    <div class="form-text">Máximo 70 caracteres</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Meta Description por Defecto</label>
                    <textarea class="form-control" name="meta_description" rows="3" 
                              maxlength="160"><?php echo sanitize(getConfig('meta_description')); ?></textarea>
                    <div class="form-text">Máximo 160 caracteres</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Meta Keywords por Defecto</label>
                    <input type="text" class="form-control" name="meta_keywords" 
                           value="<?php echo sanitize(getConfig('meta_keywords')); ?>">
                    <div class="form-text">Separar con comas</div>
                </div>
                <div class="col-12">
                    <label class="form-label">robots.txt</label>
                    <textarea class="form-control" name="robots_txt" rows="10" 
                              style="font-family: monospace;"><?php echo getConfig('robots_txt'); ?></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($currentTab == 'maintenance'): ?>
    <!-- Mantenimiento -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Mantenimiento y Respaldos</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="maintenance_mode" 
                               id="maintenance_mode" 
                               <?php echo getConfig('maintenance_mode') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="maintenance_mode">
                            Modo de Mantenimiento
                        </label>
                    </div>
                    <div class="form-text">Los visitantes verán una página de mantenimiento</div>
                </div>
                
                <div class="col-12">
                    <label class="form-label">Mensaje de Mantenimiento</label>
                    <textarea class="form-control" name="maintenance_message" rows="3"><?php echo sanitize(getConfig('maintenance_message') ?: 'Estamos realizando tareas de mantenimiento. Volveremos pronto.'); ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Frecuencia de Respaldos</label>
                    <select class="form-select" name="backup_frequency">
                        <option value="daily" <?php echo getConfig('backup_frequency') == 'daily' ? 'selected' : ''; ?>>Diario</option>
                        <option value="weekly" <?php echo getConfig('backup_frequency') == 'weekly' ? 'selected' : ''; ?>>Semanal</option>
                        <option value="monthly" <?php echo getConfig('backup_frequency') == 'monthly' ? 'selected' : ''; ?>>Mensual</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Retención de Respaldos (días)</label>
                    <input type="number" class="form-control" name="backup_retention" 
                           value="<?php echo getConfig('backup_retention') ?: 30; ?>" min="7" max="365">
                </div>
                
                <div class="col-12">
                    <h6>Acciones de Mantenimiento</h6>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary" onclick="clearCache()">
                            <i class="bi bi-trash me-2"></i>Limpiar Caché
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="optimizeDatabase()">
                            <i class="bi bi-speedometer2 me-2"></i>Optimizar Base de Datos
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="createBackup()">
                            <i class="bi bi-download me-2"></i>Crear Respaldo Manual
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-2"></i>Guardar Cambios
        </button>
        <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle me-2"></i>Cancelar
        </a>
    </div>
</form>

<script>
function testEmail() {
    if (confirm('¿Enviar email de prueba a <?php echo getConfig('admin_email'); ?>?')) {
        fetch('<?php echo ADMIN_URL; ?>/api/test-email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Email enviado');
        })
        .catch(error => {
            alert('Error al enviar email: ' + error);
        });
    }
}

function clearCache() {
    if (confirm('¿Limpiar toda la caché del sistema?')) {
        window.location.href = '<?php echo ADMIN_URL; ?>/api/maintenance.php?action=clear-cache&token=<?php echo generateCSRFToken(); ?>';
    }
}

function optimizeDatabase() {
    if (confirm('¿Optimizar todas las tablas de la base de datos?')) {
        window.location.href = '<?php echo ADMIN_URL; ?>/api/maintenance.php?action=optimize-db&token=<?php echo generateCSRFToken(); ?>';
    }
}

function createBackup() {
    if (confirm('¿Crear respaldo manual del sistema?')) {
        window.location.href = '<?php echo ADMIN_URL; ?>/api/maintenance.php?action=backup&token=<?php echo generateCSRFToken(); ?>';
    }
}
</script>

<?php include 'includes/footer.php'; ?>