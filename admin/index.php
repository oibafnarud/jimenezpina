<?php
/**
 * Sistema de Login - Panel Administrativo
 * Jiménez & Piña Survey Instruments
 */
session_start();
require_once '../config/config.php';

// Si ya está autenticado, redirigir al dashboard
if (isAuthenticated()) {
    redirect(ADMIN_URL . '/dashboard.php');
}

// Procesar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Verificar CSRF
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token de seguridad inválido. Por favor, recargue la página.';
    } else {
        // Validar campos
        if (empty($email) || empty($password)) {
            $error = 'Por favor complete todos los campos.';
        } elseif (!validateEmail($email)) {
            $error = 'El email ingresado no es válido.';
        } else {
            // Verificar intentos de login
            $ip = getClientIP();
            $attempts = $db->fetchColumn("
                SELECT COUNT(*) FROM actividad_log 
                WHERE tipo = 'login_failed' 
                AND ip_address = ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            ", [$ip]);
            
            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $error = 'Demasiados intentos fallidos. Por favor, espere 15 minutos.';
            } else {
                // Buscar usuario
                $user = $db->fetchOne("
                    SELECT * FROM usuarios 
                    WHERE email = ? AND activo = 1
                ", [$email]);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Login exitoso
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['rol'];
                    $_SESSION['login_time'] = time();
                    
                    // Actualizar último acceso
                    $db->update('usuarios', 
                        ['ultimo_acceso' => date('Y-m-d H:i:s')], 
                        'id = ?', 
                        [$user['id']]
                    );
                    
                    // Registrar actividad
                    logActivity('login_success', 'Inicio de sesión exitoso', 'usuarios', $user['id']);
                    
                    // Remember me
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + REMEMBER_ME_LIFETIME, '/', '', true, true);
                        // Aquí podrías guardar el token en la BD para validación
                    }
                    
                    // Redirigir
                    $redirect = $_GET['redirect'] ?? ADMIN_URL . '/dashboard.php';
                    redirect($redirect);
                } else {
                    // Login fallido
                    $error = 'Email o contraseña incorrectos.';
                    logActivity('login_failed', "Intento de login fallido: $email");
                }
            }
        }
    }
}

// Verificar si viene de logout
$logout = $_GET['logout'] ?? false;
if ($logout) {
    $success = 'Ha cerrado sesión correctamente.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jiménez & Piña Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/admin.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .login-image {
            background: url('<?php echo ASSETS_URL; ?>/img/login-bg.jpg') center/cover;
            min-height: 500px;
            position: relative;
        }
        .login-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 102, 204, 0.8);
        }
        .login-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            z-index: 1;
        }
        .login-form {
            padding: 60px 50px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="login-container">
                    <div class="row g-0">
                        <!-- Left Side - Image -->
                        <div class="col-lg-6 d-none d-lg-block">
                            <div class="login-image">
                                <div class="login-content">
                                    <img src="<?php echo ASSETS_URL; ?>/img/logo-white.png" 
                                         alt="Jiménez & Piña" 
                                         class="mb-4" 
                                         style="height: 60px;">
                                    <h3 class="mb-3">Bienvenido de vuelta</h3>
                                    <p class="mb-0">Sistema de Gestión de Contenidos</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side - Form -->
                        <div class="col-lg-6">
                            <div class="login-form">
                                <div class="text-center mb-5">
                                    <img src="<?php echo ASSETS_URL; ?>/img/logo.png" 
                                         alt="Jiménez & Piña" 
                                         class="mb-4 d-lg-none" 
                                         style="height: 50px;">
                                    <h2 class="fw-bold">Iniciar Sesión</h2>
                                    <p class="text-muted">Acceda al panel administrativo</p>
                                </div>
                                
                                <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($success)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <?php echo $success; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="" class="needs-validation" novalidate>
                                    <?php echo csrfField(); ?>
                                    
                                    <div class="mb-4">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-envelope"></i>
                                            </span>
                                            <input type="email" 
                                                   class="form-control form-control-lg" 
                                                   id="email" 
                                                   name="email" 
                                                   placeholder="admin@jimenezpina.com"
                                                   value="<?php echo sanitize($_POST['email'] ?? ''); ?>"
                                                   required 
                                                   autofocus>
                                            <div class="invalid-feedback">
                                                Por favor ingrese un email válido
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-lock"></i>
                                            </span>
                                            <input type="password" 
                                                   class="form-control form-control-lg" 
                                                   id="password" 
                                                   name="password" 
                                                   placeholder="••••••••"
                                                   required>
                                            <button class="btn btn-outline-secondary" 
                                                    type="button" 
                                                    id="togglePassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <div class="invalid-feedback">
                                                Por favor ingrese su contraseña
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="remember" 
                                                   name="remember">
                                            <label class="form-check-label" for="remember">
                                                Recordarme
                                            </label>
                                        </div>
                                        <a href="forgot-password.php" class="text-decoration-none">
                                            ¿Olvidó su contraseña?
                                        </a>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-lg btn-login w-100">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        Iniciar Sesión
                                    </button>
                                </form>
                                
                                <hr class="my-4">
                                
                                <div class="text-center">
                                    <a href="<?php echo SITE_URL; ?>" class="text-decoration-none">
                                        <i class="bi bi-arrow-left me-2"></i>
                                        Volver al sitio web
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center text-white mt-4">
                    <small>&copy; <?php echo date('Y'); ?> Jiménez & Piña Survey Instruments. Todos los derechos reservados.</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        // Form validation
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            });
        })();
    </script>
</body>
</html>