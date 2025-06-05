-- Base de datos para Jiménez & Piña Survey Instruments
-- Versión: 1.0
-- Fecha: 2025

CREATE DATABASE IF NOT EXISTS jimenez_pina_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jimenez_pina_db;

-- Tabla de usuarios del sistema
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'editor', 'ventas') DEFAULT 'editor',
    activo BOOLEAN DEFAULT 1,
    ultimo_acceso DATETIME,
    token_recuperacion VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de categorías de productos
CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255),
    parent_id INT DEFAULT NULL,
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT 1,
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de marcas
CREATE TABLE marcas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    logo VARCHAR(255),
    website VARCHAR(255),
    descripcion TEXT,
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de productos
CREATE TABLE productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sku VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    descripcion TEXT,
    descripcion_corta VARCHAR(500),
    categoria_id INT,
    marca_id INT,
    precio DECIMAL(10,2),
    precio_oferta DECIMAL(10,2),
    costo DECIMAL(10,2),
    stock INT DEFAULT 0,
    stock_minimo INT DEFAULT 0,
    imagen_principal VARCHAR(255),
    ficha_tecnica VARCHAR(255),
    manual_usuario VARCHAR(255),
    video_youtube VARCHAR(255),
    peso DECIMAL(10,3),
    dimensiones VARCHAR(100),
    garantia_meses INT DEFAULT 12,
    destacado BOOLEAN DEFAULT 0,
    nuevo BOOLEAN DEFAULT 0,
    activo BOOLEAN DEFAULT 1,
    vistas INT DEFAULT 0,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_sku (sku),
    INDEX idx_categoria (categoria_id),
    INDEX idx_marca (marca_id),
    INDEX idx_precio (precio),
    INDEX idx_destacado (destacado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de especificaciones de productos
CREATE TABLE especificaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    producto_id INT NOT NULL,
    grupo VARCHAR(100),
    nombre VARCHAR(100) NOT NULL,
    valor VARCHAR(255) NOT NULL,
    orden INT DEFAULT 0,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de imágenes de productos
CREATE TABLE producto_imagenes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    producto_id INT NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    titulo VARCHAR(255),
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de etiquetas
CREATE TABLE etiquetas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla relación productos-etiquetas
CREATE TABLE producto_etiquetas (
    producto_id INT NOT NULL,
    etiqueta_id INT NOT NULL,
    PRIMARY KEY (producto_id, etiqueta_id),
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de blog posts
CREATE TABLE blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    contenido TEXT NOT NULL,
    extracto VARCHAR(500),
    imagen_destacada VARCHAR(255),
    autor_id INT,
    categoria VARCHAR(50),
    tags VARCHAR(255),
    estado ENUM('borrador', 'publicado', 'programado') DEFAULT 'borrador',
    fecha_publicacion DATETIME,
    permitir_comentarios BOOLEAN DEFAULT 1,
    vistas INT DEFAULT 0,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_publicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de comentarios del blog
CREATE TABLE blog_comentarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    comentario TEXT NOT NULL,
    aprobado BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    INDEX idx_post (post_id),
    INDEX idx_aprobado (aprobado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de clientes
CREATE TABLE clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo ENUM('empresa', 'persona') DEFAULT 'empresa',
    empresa VARCHAR(200),
    nombre_contacto VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    celular VARCHAR(20),
    direccion TEXT,
    ciudad VARCHAR(100),
    provincia VARCHAR(100),
    rnc_cedula VARCHAR(20),
    notas TEXT,
    activo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_empresa (empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de cotizaciones
CREATE TABLE cotizaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero VARCHAR(20) UNIQUE NOT NULL,
    cliente_id INT,
    cliente_nombre VARCHAR(200),
    cliente_email VARCHAR(100),
    cliente_telefono VARCHAR(20),
    fecha DATE NOT NULL,
    fecha_vencimiento DATE,
    condiciones_pago VARCHAR(200),
    tiempo_entrega VARCHAR(200),
    lugar_entrega VARCHAR(200),
    estado ENUM('borrador', 'enviada', 'aprobada', 'rechazada', 'expirada') DEFAULT 'borrador',
    subtotal DECIMAL(10,2) DEFAULT 0,
    descuento_porcentaje DECIMAL(5,2) DEFAULT 0,
    descuento_monto DECIMAL(10,2) DEFAULT 0,
    itbis DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    notas TEXT,
    terminos_condiciones TEXT,
    created_by INT,
    aprobada_por VARCHAR(100),
    fecha_aprobacion DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_numero (numero),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de items de cotización
CREATE TABLE cotizacion_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cotizacion_id INT NOT NULL,
    producto_id INT,
    codigo VARCHAR(50),
    descripcion TEXT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    descuento_porcentaje DECIMAL(5,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    orden INT DEFAULT 0,
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL,
    INDEX idx_cotizacion (cotizacion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de consultas/mensajes de contacto
CREATE TABLE consultas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    empresa VARCHAR(200),
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    asunto VARCHAR(200),
    mensaje TEXT NOT NULL,
    producto_id INT,
    tipo ENUM('general', 'producto', 'servicio', 'cotizacion') DEFAULT 'general',
    estado ENUM('nueva', 'leida', 'respondida') DEFAULT 'nueva',
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL,
    INDEX idx_estado (estado),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de videos
CREATE TABLE videos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    youtube_id VARCHAR(20) NOT NULL,
    categoria VARCHAR(50),
    duracion VARCHAR(10),
    orden INT DEFAULT 0,
    destacado BOOLEAN DEFAULT 0,
    activo BOOLEAN DEFAULT 1,
    vistas INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla relación productos-videos
CREATE TABLE producto_videos (
    producto_id INT NOT NULL,
    video_id INT NOT NULL,
    PRIMARY KEY (producto_id, video_id),
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de configuración del sitio
CREATE TABLE configuracion (
    clave VARCHAR(50) PRIMARY KEY,
    valor TEXT,
    tipo ENUM('text', 'number', 'boolean', 'json', 'html') DEFAULT 'text',
    grupo VARCHAR(50) DEFAULT 'general',
    descripcion VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de log de actividades
CREATE TABLE actividad_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    tipo VARCHAR(50),
    descripcion VARCHAR(255),
    tabla_afectada VARCHAR(50),
    registro_id INT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar datos iniciales

-- Usuario administrador por defecto (password: admin123)
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@jimenezpina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Configuración inicial del sitio
INSERT INTO configuracion (clave, valor, tipo, grupo, descripcion) VALUES
('site_name', 'Jiménez & Piña Survey Instruments', 'text', 'general', 'Nombre del sitio'),
('site_email', 'info@jimenezpina.com', 'text', 'general', 'Email principal'),
('site_phone', '+1 (809) 555-0123', 'text', 'general', 'Teléfono principal'),
('site_address', 'Av. Winston Churchill #123, Santo Domingo, RD', 'text', 'general', 'Dirección física'),
('currency', 'USD', 'text', 'general', 'Moneda'),
('tax_rate', '18', 'number', 'general', 'Tasa de impuesto (%)'),
('quotation_validity', '30', 'number', 'quotations', 'Validez de cotizaciones (días)'),
('google_analytics', '', 'text', 'seo', 'ID de Google Analytics'),
('facebook_pixel', '', 'text', 'seo', 'ID de Facebook Pixel'),
('whatsapp_number', '18095550123', 'text', 'contact', 'Número de WhatsApp'),
('maintenance_mode', '0', 'boolean', 'general', 'Modo mantenimiento');

-- Categorías iniciales
INSERT INTO categorias (nombre, slug, descripcion, orden, activo) VALUES
('Estaciones Totales', 'estaciones-totales', 'Estaciones totales robóticas y manuales de alta precisión', 1, 1),
('GPS/GNSS', 'gps-gnss', 'Sistemas de posicionamiento global y navegación por satélite', 2, 1),
('Niveles', 'niveles', 'Niveles ópticos, digitales y láser', 3, 1),
('Drones', 'drones', 'Drones y UAV para fotogrametría y mapeo aéreo', 4, 1),
('Accesorios', 'accesorios', 'Trípodes, prismas, baterías y accesorios', 5, 1),
('Software', 'software', 'Software de topografía y procesamiento de datos', 6, 1);

-- Marcas iniciales
INSERT INTO marcas (nombre, slug, orden, activo) VALUES
('Leica Geosystems', 'leica', 1, 1),
('Trimble', 'trimble', 2, 1),
('Topcon', 'topcon', 3, 1),
('Sokkia', 'sokkia', 4, 1),
('Nikon', 'nikon', 5, 1),
('South', 'south', 6, 1),
('GeoMax', 'geomax', 7, 1);

-- Crear índices adicionales para optimización
CREATE INDEX idx_productos_busqueda ON productos(nombre, descripcion_corta);
CREATE INDEX idx_blog_busqueda ON blog_posts(titulo, extracto);
CREATE INDEX idx_clientes_busqueda ON clientes(empresa, nombre_contacto, email);