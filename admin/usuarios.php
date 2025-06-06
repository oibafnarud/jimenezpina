<?php
/**
 * Gestión de Usuarios - Panel Administrativo
 * /admin/usuarios.php
 */
require_once 'includes/header.php';

// Solo super administradores pueden gestionar usuarios
if ($_SESSION['user_role'] !== 'superadmin') {
    redirect(ADMIN_URL . '/dashboard.php', 'No tiene permisos para acceder a esta sección', MSG_ERROR);
}

// Procesar acciones
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($action == 'toggle' && $id && $id != $_SESSION['user_id']) {
    if (!verifyCSRFToken($_GET['token'] ?? '')) {
        redirect(ADMIN_URL . '/usuarios.php', 'Token de seguridad inválido', MSG_ERROR);
    }
    
    $db->query("UPDATE usuarios SET activo = NOT activo WHERE id = ?", [$id]);
    redirect(ADMIN_URL . '/usuarios.php', 'Estado actualizado correctamente', MSG_SUCCESS);
}

if ($action == 'delete' && $id && $id != $_SESSION['user_id']) {
    if (!verifyCSRFToken($_GET['token'] ?? '')) {
        redirect(ADMIN_URL . '/usuarios.php', 'Token de seguridad inválido', MSG_ERROR);
    }
    
    // Verificar que no sea el único superadmin
    $superadminCount = $db->count('usuarios', 'rol = ? AND id != ?', ['superadmin', $id]);
    if ($superadminCount == 0) {
        redirect(ADMIN_URL . '/usuarios.php', 'No se puede eliminar el único superadministrador', MSG_ERROR);
    }
    
    // Reasignar contenido a otro usuario
    $newUserId = $_SESSION['user_id'];
    $db->update('blog_posts', ['autor_id' => $newUserId], 'autor_id = ?', [$id]);
    $db->update('productos', ['created_by' => $newUserId], 'created_by = ?', [$id]);
    
    // Eliminar usuario
    $db->delete('usuarios', 'id = ?', [$id]);
    logActivity('usuario_deleted', "Usuario eliminado ID: $id", 'usuarios', $id);
    redirect(ADMIN_URL . '/usuarios.php', 'Usuario eliminado correctamente', MSG_SUCCESS);
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido';
    } else {
        $errors = [];
        $userId = $_POST['usuario_id'] ?? 0;
        
        // Validar datos
        if (empty($_POST['nombre'])) $errors[] = 'El nombre es requerido';
        if (empty($_POST['email']) || !validateEmail($_POST['email'])) {
            $errors[] = 'Email válido requerido';
        }
        
        // Verificar email único
        $emailExists = $db->fetchOne(
            "SELECT id FROM usuarios WHERE email = ? AND id != ?", 
            [$_POST['email'], $userId]
        );
        if ($emailExists) $errors[] = 'Este email ya está registrado';
        
        // Validar contraseña (solo si es nuevo o se está cambiando)
        if (!$userId || !empty($_POST['password'])) {
            if (empty($_POST['password'])) {
                $errors[] = 'La contraseña es requerida';
            } elseif (strlen($_POST['password']) < 8) {
                $errors[] = 'La contraseña debe tener al menos 8 caracteres';
            }
        }
        
        if (empty($errors)) {
            $data = [
                'nombre' => sanitize($_POST['nombre']),
                'email' => sanitize($_POST['email']),
                'rol' => $_POST['rol'],
                'telefono' => sanitize($_POST['telefono']),
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'permisos' => json_encode($_POST['permisos'] ?? [])
            ];
            
            // Solo actualizar contraseña si se proporcionó
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            // Procesar avatar
            if (!empty($_FILES['avatar']['name'])) {
                $upload = uploadFile($_FILES['avatar'], 'avatars', ALLOWED_IMAGE_TYPES);
                if ($upload['success']) {
                    // Eliminar avatar anterior
                    if ($userId) {
                        $oldAvatar = $db->fetchColumn("SELECT avatar FROM usuarios WHERE id = ?", [$userId]);
                        if ($oldAvatar) deleteFile($oldAvatar);
                    }
                    $data['avatar'] = $upload['path'];
                }
            }
            
            try {
                if ($userId) {
                    // No permitir cambiar su propio rol si es el único superadmin
                    if ($userId == $_SESSION['user_id'] && $_POST['rol'] != 'superadmin') {
                        $superadminCount = $db->count('usuarios', 'rol = ? AND id != ?', ['superadmin', $userId]);
                        if ($superadminCount == 0) {
                            throw new Exception('No puede cambiar su rol siendo el único superadministrador');
                        }
                    }
                    
                    $db->update('usuarios', $data, 'id = ?', [$userId]);
                    $message = 'Usuario actualizado correctamente';
                } else {
                    $db->insert('usuarios', $data);
                    $message = 'Usuario creado correctamente';
                }
                
                redirect(ADMIN_URL . '/usuarios.php', $message, MSG_SUCCESS);
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
}

// Obtener usuarios
$usuarios = $db->fetchAll("
    SELECT u.*, 
           (SELECT COUNT(*) FROM blog_posts WHERE autor_id = u.id) as total_posts,
           (SELECT MAX(fecha) FROM login_attempts WHERE usuario_id = u.id AND exitoso = 1) as ultimo_login
    FROM usuarios u
    ORDER BY u.created_at DESC
");

// Definir permisos disponibles
$permisosDisponibles = [
    'products' => 'Gestionar Productos',
    'blog' => 'Gestionar Blog',
    'cotizaciones' => 'Gestionar Cotizaciones',
    'clientes' => 'Gestionar Clientes',
    'configuracion' => 'Configuración del Sistema',
    'reportes' => 'Ver Reportes',
    'usuarios' => 'Gestionar Usuarios'
];

$pageTitle = 'Gestión de Usuarios';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Usuarios</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Usuarios</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioModal">
        <i class="bi bi-person-plus me-2"></i>Nuevo Usuario
    </button>
</div>

<!-- Lista de Usuarios -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="60">Avatar</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Último Login</th>
                        <th>Posts</th>
                        <th>Estado</th>
                        <th width="150">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $usuario): ?>
                    <tr <?php echo $usuario['id'] == $_SESSION['user_id'] ? 'class="table-info"' : ''; ?>>
                        <td>
                            <?php if($usuario['avatar']): ?>
                            <img src="<?php echo UPLOADS_URL . '/' . $usuario['avatar']; ?>" 
                                 class="rounded-circle" 
                                 style="width: 40px; height: 40px; object-fit: cover;">
                            <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo sanitize($usuario['nombre']); ?></strong>
                            <?php if($usuario['id'] == $_SESSION['user_id']): ?>
                            <span class="badge bg-primary ms-1">Tú</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo sanitize($usuario['email']); ?></td>
                        <td>
                            <?php
                            $rolBadge = match($usuario['rol']) {
                                'superadmin' => 'bg-danger',
                                'admin' => 'bg-warning',
                                'editor' => 'bg-info',
                                default => 'bg-secondary'
                            };
                            $rolNombre = match($usuario['rol']) {
                                'superadmin' => 'Super Admin',
                                'admin' => 'Administrador',
                                'editor' => 'Editor',
                                default => $usuario['rol']
                            };
                            ?>
                            <span class="badge <?php echo $rolBadge; ?>"><?php echo $rolNombre; ?></span>
                        </td>
                        <td>
                            <?php echo $usuario['ultimo_login'] ? formatDate($usuario['ultimo_login'], 'd/m/Y H:i') : 'Nunca'; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo $usuario['total_posts']; ?></span>
                        </td>
                        <td>
                            <?php if($usuario['id'] != $_SESSION['user_id']): ?>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       onchange="toggleStatus(<?php echo $usuario['id']; ?>)"
                                       <?php echo $usuario['activo'] ? 'checked' : ''; ?>>
                            </div>
                            <?php else: ?>
                            <span class="badge bg-success">Activo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-primary" 
                                        onclick="editUsuario(<?php echo htmlspecialchars(json_encode($usuario)); ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if($usuario['id'] != $_SESSION['user_id']): ?>
                                <button onclick="confirmDelete('<?php echo ADMIN_URL; ?>/usuarios.php?action=delete&id=<?php echo $usuario['id']; ?>&token=<?php echo generateCSRFToken(); ?>', '¿Está seguro de eliminar este usuario?')" 
                                        class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Usuario -->
<div class="modal fade" id="usuarioModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="usuario_id" id="usuario_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contraseña <span id="password-required">*</span></label>
                            <input type="password" class="form-control" name="password" id="password">
                            <div class="form-text">Mínimo 8 caracteres. Dejar vacío para mantener la actual.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" id="telefono">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rol *</label>
                            <select class="form-select" name="rol" id="rol" required onchange="togglePermisos()">
                                <option value="editor">Editor</option>
                                <option value="admin">Administrador</option>
                                <option value="superadmin">Super Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Avatar</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                        </div>
                        
                        <!-- Permisos personalizados -->
                        <div class="col-12" id="permisos-container">
                            <label class="form-label">Permisos Personalizados</label>
                            <div class="row">
                                <?php foreach($permisosDisponibles as $key => $label): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permiso-check" type="checkbox" 
                                               name="permisos[]" value="<?php echo $key; ?>" 
                                               id="permiso_<?php echo $key; ?>">
                                        <label class="form-check-label" for="permiso_<?php echo $key; ?>">
                                            <?php echo $label; ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="activo" id="activo" checked>
                                <label class="form-check-label" for="activo">
                                    Usuario activo
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
    window.location.href = '<?php echo ADMIN_URL; ?>/usuarios.php?action=toggle&id=' + id + '&token=<?php echo generateCSRFToken(); ?>';
}

function togglePermisos() {
    const rol = document.getElementById('rol').value;
    const permisosContainer = document.getElementById('permisos-container');
    
    if (rol === 'superadmin') {
        permisosContainer.style.display = 'none';
        // Marcar todos los permisos
        document.querySelectorAll('.permiso-check').forEach(check => check.checked = true);
    } else {
        permisosContainer.style.display = 'block';
        if (rol === 'admin') {
            // Administrador tiene algunos permisos por defecto
            document.getElementById('permiso_products').checked = true;
            document.getElementById('permiso_blog').checked = true;
            document.getElementById('permiso_cotizaciones').checked = true;
            document.getElementById('permiso_clientes').checked = true;
        }
    }
}

function editUsuario(usuario) {
    document.getElementById('usuario_id').value = usuario.id;
    document.getElementById('nombre').value = usuario.nombre;
    document.getElementById('email').value = usuario.email;
    document.getElementById('telefono').value = usuario.telefono || '';
    document.getElementById('rol').value = usuario.rol;
    document.getElementById('activo').checked = usuario.activo == 1;
    
    // Contraseña no requerida en edición
    document.getElementById('password').removeAttribute('required');
    document.getElementById('password-required').style.display = 'none';
    
    // Cargar permisos
    const permisos = JSON.parse(usuario.permisos || '[]');
    document.querySelectorAll('.permiso-check').forEach(check => {
        check.checked = permisos.includes(check.value);
    });
    
    togglePermisos();
    
    document.querySelector('#usuarioModal .modal-title').textContent = 'Editar Usuario';
    new bootstrap.Modal(document.getElementById('usuarioModal')).show();
}

// Limpiar modal al cerrarse
document.getElementById('usuarioModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('usuario_id').value = '';
    document.getElementById('password').setAttribute('required', 'required');
    document.getElementById('password-required').style.display = 'inline';
    document.querySelector('#usuarioModal .modal-title').textContent = 'Nuevo Usuario';
    this.querySelector('form').reset();
});
</script>

<?php include 'includes/footer.php'; ?>