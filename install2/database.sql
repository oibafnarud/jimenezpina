-- /install/database.sql
-- Base de datos para Jiménez & Piña Survey Instruments
-- Versión 1.0 - CORREGIDO SIN DELIMITER

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `jpsurvey_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `jpsurvey_db`;

-- --------------------------------------------------------
-- Estructura de tablas
-- --------------------------------------------------------

-- Tabla usuarios
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `rol` enum('superadmin','admin','editor') DEFAULT 'editor',
  `telefono` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `activo` boolean DEFAULT 1,
  `permisos` text DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla categorias
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `descripcion` text,
  `imagen` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` boolean DEFAULT 1,
  `meta_title` varchar(70) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_parent` (`parent_id`),
  FOREIGN KEY (`parent_id`) REFERENCES `categorias`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla marcas
CREATE TABLE `marcas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `descripcion` text,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` boolean DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla productos
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(50) UNIQUE,
  `nombre` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL UNIQUE,
  `descripcion` text,
  `descripcion_corta` text,
  `especificaciones` text,
  `precio` decimal(10,2) DEFAULT 0,
  `precio_oferta` decimal(10,2) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `marca_id` int(11) DEFAULT NULL,
  `imagen_principal` varchar(255) DEFAULT NULL,
  `imagenes` text,
  `disponible` boolean DEFAULT 1,
  `destacado` boolean DEFAULT 0,
  `nuevo` boolean DEFAULT 0,
  `vistas` int(11) DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  `activo` boolean DEFAULT 1,
  `meta_title` varchar(70) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_sku` (`sku`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_marca` (`marca_id`),
  KEY `idx_destacado` (`destacado`),
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`marca_id`) REFERENCES `marcas`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla clientes
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa` varchar(200) NOT NULL,
  `nombre_contacto` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20),
  `rnc` varchar(20),
  `direccion` text,
  `provincia` varchar(50),
  `notas` text,
  `activo` boolean DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_empresa` (`empresa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla cotizaciones
CREATE TABLE `cotizaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL UNIQUE,
  `cliente_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `validez_dias` int(11) DEFAULT 30,
  `estado` enum('pendiente','aprobada','rechazada','expirada') DEFAULT 'pendiente',
  `subtotal` decimal(10,2) DEFAULT 0,
  `itbis` decimal(10,2) DEFAULT 0,
  `total` decimal(10,2) DEFAULT 0,
  `notas` text,
  `proyecto_tipo` varchar(50),
  `plazo_entrega` varchar(50),
  `incluir_instalacion` boolean DEFAULT 0,
  `incluir_capacitacion` boolean DEFAULT 0,
  `urgente` boolean DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_numero` (`numero`),
  KEY `idx_cliente` (`cliente_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha`),
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`),
  FOREIGN KEY (`created_by`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla cotizacion_items
CREATE TABLE `cotizacion_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cotizacion_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `descripcion` text NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0,
  `total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cotizacion` (`cotizacion_id`),
  KEY `idx_producto` (`producto_id`),
  FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla blog_posts
CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL UNIQUE,
  `contenido` text NOT NULL,
  `extracto` text,
  `imagen_destacada` varchar(255) DEFAULT NULL,
  `categoria` varchar(50),
  `tags` text,
  `autor_id` int(11) DEFAULT NULL,
  `estado` enum('borrador','publicado','programado') DEFAULT 'borrador',
  `fecha_publicacion` datetime DEFAULT NULL,
  `vistas` int(11) DEFAULT 0,
  `permitir_comentarios` boolean DEFAULT 1,
  `meta_title` varchar(70) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha_publicacion`),
  KEY `idx_autor` (`autor_id`),
  FOREIGN KEY (`autor_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla blog_comentarios
CREATE TABLE `blog_comentarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `comentario` text NOT NULL,
  `aprobado` boolean DEFAULT 0,
  `ip_address` varchar(45),
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_post_aprobado` (`post_id`, `aprobado`),
  FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla consultas
CREATE TABLE `consultas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `empresa` varchar(200),
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `asunto` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` enum('nueva','leida','respondida','archivada') DEFAULT 'nueva',
  `ip_address` varchar(45),
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla newsletter_suscriptores
CREATE TABLE `newsletter_suscriptores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL UNIQUE,
  `nombre` varchar(100),
  `activo` boolean DEFAULT 1,
  `fecha_suscripcion` datetime NOT NULL,
  `fecha_baja` datetime,
  `fecha_reactivacion` datetime,
  `ip_address` varchar(45),
  `source` varchar(50) DEFAULT 'website',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activo` (`activo`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla solicitudes_servicio
CREATE TABLE `solicitudes_servicio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `tipo_servicio` enum('mantenimiento','capacitacion','alquiler','soporte','instalacion','tradein') NOT NULL,
  `equipo_modelo` varchar(200),
  `descripcion` text,
  `urgente` boolean DEFAULT 0,
  `estado` enum('pendiente','en_proceso','completado','cancelado') DEFAULT 'pendiente',
  `fecha_solicitud` datetime NOT NULL,
  `fecha_atencion` datetime,
  `fecha_completado` datetime,
  `tecnico_asignado` int(11),
  `notas_tecnico` text,
  `ip_address` varchar(45),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_urgente` (`urgente`),
  KEY `idx_tipo` (`tipo_servicio`),
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`),
  FOREIGN KEY (`tecnico_asignado`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla configuracion
CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL UNIQUE,
  `valor` text,
  `tipo` varchar(50) DEFAULT 'text',
  `grupo` varchar(50) DEFAULT 'general',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_clave` (`clave`),
  KEY `idx_grupo` (`grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla activity_log
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `descripcion` text,
  `tabla_afectada` varchar(50),
  `registro_id` int(11),
  `ip_address` varchar(45),
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_fecha` (`created_at`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla sesiones (para manejo de sesiones en DB si se requiere)
CREATE TABLE `sesiones` (
  `id` varchar(128) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45),
  `user_agent` text,
  `payload` text NOT NULL,
  `ultima_actividad` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_ultima_actividad` (`ultima_actividad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla login_attempts
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100),
  `usuario_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45),
  `user_agent` text,
  `exitoso` boolean DEFAULT 0,
  `fecha` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Datos iniciales
-- --------------------------------------------------------

-- Usuario administrador por defecto
INSERT INTO `usuarios` (`nombre`, `email`, `password`, `rol`, `activo`) VALUES
('Administrador', 'admin@jpsurvey.com', '$2y$10$YourHashedPasswordHere', 'superadmin', 1);

-- Configuración inicial
INSERT INTO `configuracion` (`clave`, `valor`, `grupo`) VALUES
('site_name', 'Jiménez & Piña Survey Instruments', 'general'),
('site_tagline', 'Instrumentos de Precisión para Topografía', 'general'),
('site_description', 'Líderes en venta de instrumentos de topografía en República Dominicana', 'general'),
('admin_email', 'admin@jpsurvey.com', 'general'),
('support_email', 'soporte@jpsurvey.com', 'general'),
('phone', '809-555-0123', 'general'),
('whatsapp', '18095550123', 'general'),
('address', 'Av. Principal #123\nSanto Domingo, República Dominicana', 'general'),
('tax_rate', '0.18', 'general'),
('currency', 'RD$', 'general'),
('timezone', 'America/Santo_Domingo', 'general'),
('date_format', 'd/m/Y', 'general'),
('time_format', 'h:i A', 'general'),
('maintenance_mode', '0', 'maintenance'),
('backup_frequency', 'weekly', 'maintenance'),
('backup_retention', '30', 'maintenance');

-- Categorías iniciales
INSERT INTO `categorias` (`nombre`, `slug`, `descripcion`, `activo`) VALUES
('Estaciones Totales', 'estaciones-totales', 'Instrumentos de medición angular y de distancias', 1),
('Niveles', 'niveles', 'Niveles ópticos y digitales para nivelación', 1),
('GPS/GNSS', 'gps-gnss', 'Sistemas de posicionamiento global', 1),
('Accesorios', 'accesorios', 'Accesorios y complementos para equipos', 1),
('Software', 'software', 'Software especializado para topografía', 1);

-- Marcas iniciales
INSERT INTO `marcas` (`nombre`, `slug`, `website`, `activo`) VALUES
('Topcon', 'topcon', 'https://www.topcon.com', 1),
('Sokkia', 'sokkia', 'https://www.sokkia.com', 1),
('Leica', 'leica', 'https://leica-geosystems.com', 1),
('Trimble', 'trimble', 'https://www.trimble.com', 1),
('Nikon', 'nikon', 'https://www.nikon.com', 1);

-- Índices adicionales para mejorar rendimiento
CREATE INDEX idx_activity_fecha_tipo ON activity_log(created_at, tipo);
CREATE INDEX idx_productos_precio ON productos(precio);
CREATE INDEX idx_productos_fecha ON productos(created_at);