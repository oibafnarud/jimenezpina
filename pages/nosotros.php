<?php
/**
 * Página Acerca de Nosotros
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

// Meta tags
$pageTitle = 'Acerca de Nosotros';
$pageDescription = 'Conozca la historia y trayectoria de Jiménez & Piña, líderes en distribución de equipos topográficos en República Dominicana.';
$pageKeywords = 'jimenez piña historia, distribuidores topografía, equipos medición república dominicana';

include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Acerca de Nosotros</h1>
                <p class="lead mb-0 opacity-90">
                    Más de 15 años siendo el socio confiable de los profesionales 
                    de la topografía en República Dominicana
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex align-items-center justify-content-lg-end gap-3">
                    <i class="bi bi-award display-4 opacity-50"></i>
                    <div class="text-start">
                        <div class="h4 mb-0">Desde 2009</div>
                        <div class="small opacity-75">Sirviendo con excelencia</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Overview -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img src="<?php echo ASSETS_URL; ?>/img/about-office.jpg" 
                     alt="Oficinas Jiménez & Piña" 
                     class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6">
                <span class="badge bg-primary-soft text-primary mb-3">Nuestra Historia</span>
                <h2 class="display-5 fw-bold mb-4">Un Legado de Precisión y Servicio</h2>
                <p class="lead mb-4">
                    Jiménez & Piña Survey Instruments nació en 2009 con la visión de transformar 
                    la forma en que los profesionales de la construcción y topografía acceden a 
                    tecnología de punta en República Dominicana.
                </p>
                <p class="mb-4">
                    Desde nuestros inicios, nos hemos comprometido a ofrecer no solo los mejores 
                    equipos del mercado, sino también el respaldo técnico y la capacitación necesaria 
                    para maximizar su inversión. Somos distribuidores autorizados de las marcas más 
                    prestigiosas a nivel mundial.
                </p>
                <p class="mb-4">
                    Hoy, después de más de una década de servicio ininterrumpido, somos reconocidos 
                    como líderes en el sector, habiendo participado en los proyectos de infraestructura 
                    más importantes del país.
                </p>
                <a href="#valores" class="btn btn-primary">
                    Conocer nuestros valores <i class="bi bi-arrow-down ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Mission, Vision, Values -->
<section id="valores" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary mb-3">Filosofía Corporativa</span>
            <h2 class="display-5 fw-bold">Misión, Visión y Valores</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <div class="feature-icon bg-primary text-white rounded-circle mb-4 mx-auto" 
                             style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-bullseye fs-2"></i>
                        </div>
                        <h4 class="mb-3">Misión</h4>
                        <p class="text-muted">
                            Proveer soluciones integrales en instrumentación topográfica, combinando 
                            tecnología de vanguardia con un servicio excepcional, para impulsar el 
                            desarrollo de la construcción y la ingeniería en República Dominicana.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <div class="feature-icon bg-success text-white rounded-circle mb-4 mx-auto" 
                             style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-eye fs-2"></i>
                        </div>
                        <h4 class="mb-3">Visión</h4>
                        <p class="text-muted">
                            Ser reconocidos como el socio estratégico preferido en el Caribe para 
                            soluciones de medición y posicionamiento, destacándonos por nuestra 
                            innovación, confiabilidad y compromiso con el éxito de nuestros clientes.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <div class="feature-icon bg-warning text-white rounded-circle mb-4 mx-auto" 
                             style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-heart fs-2"></i>
                        </div>
                        <h4 class="mb-3">Valores</h4>
                        <ul class="list-unstyled text-muted text-start">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Integridad</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Excelencia</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Innovación</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Compromiso</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i> Responsabilidad</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary mb-3">Nuestra Trayectoria</span>
            <h2 class="display-5 fw-bold">Hitos Importantes</h2>
        </div>
        
        <div class="timeline-container">
            <div class="timeline-item">
                <div class="timeline-year">2009</div>
                <div class="timeline-content">
                    <h5>Fundación</h5>
                    <p>Iniciamos operaciones como distribuidores de equipos topográficos</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-year">2012</div>
                <div class="timeline-content">
                    <h5>Primera Expansión</h5>
                    <p>Nos convertimos en distribuidores autorizados de Leica Geosystems</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-year">2015</div>
                <div class="timeline-content">
                    <h5>Centro de Servicio</h5>
                    <p>Inauguramos nuestro centro de servicio técnico certificado</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-year">2018</div>
                <div class="timeline-content">
                    <h5>Nuevas Marcas</h5>
                    <p>Sumamos Trimble y Topcon a nuestro portafolio de marcas</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-year">2021</div>
                <div class="timeline-content">
                    <h5>Transformación Digital</h5>
                    <p>Lanzamos nuestra plataforma de e-commerce y servicios en línea</p>
                </div>
            </div>
            
            <div class="timeline-item active">
                <div class="timeline-year">2024</div>
                <div class="timeline-content">
                    <h5>Liderazgo Regional</h5>
                    <p>Nos consolidamos como líderes en el Caribe en soluciones topográficas</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary mb-3">Nuestro Equipo</span>
            <h2 class="display-5 fw-bold">Profesionales Comprometidos</h2>
            <p class="lead text-muted">
                Un equipo de expertos dedicados a brindarle el mejor servicio
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="team-member text-center">
                    <div class="team-photo mb-3">
                        <img src="<?php echo ASSETS_URL; ?>/img/team-1.jpg" 
                             alt="Juan Jiménez" 
                             class="img-fluid rounded-circle"
                             style="width: 200px; height: 200px; object-fit: cover;">
                    </div>
                    <h5 class="mb-1">Juan Jiménez</h5>
                    <p class="text-muted mb-3">Director General</p>
                    <div class="social-links">
                        <a href="#" class="text-muted me-2"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-muted"><i class="bi bi-envelope"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="team-member text-center">
                    <div class="team-photo mb-3">
                        <img src="<?php echo ASSETS_URL; ?>/img/team-2.jpg" 
                             alt="María Piña" 
                             class="img-fluid rounded-circle"
                             style="width: 200px; height: 200px; object-fit: cover;">
                    </div>
                    <h5 class="mb-1">María Piña</h5>
                    <p class="text-muted mb-3">Directora Comercial</p>
                    <div class="social-links">
                        <a href="#" class="text-muted me-2"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-muted"><i class="bi bi-envelope"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="team-member text-center">
                    <div class="team-photo mb-3">
                        <img src="<?php echo ASSETS_URL; ?>/img/team-3.jpg" 
                             alt="Carlos Rodríguez" 
                             class="img-fluid rounded-circle"
                             style="width: 200px; height: 200px; object-fit: cover;">
                    </div>
                    <h5 class="mb-1">Carlos Rodríguez</h5>
                    <p class="text-muted mb-3">Gerente Técnico</p>
                    <div class="social-links">
                        <a href="#" class="text-muted me-2"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-muted"><i class="bi bi-envelope"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="team-member text-center">
                    <div class="team-photo mb-3">
                        <img src="<?php echo ASSETS_URL; ?>/img/team-4.jpg" 
                             alt="Ana Martínez" 
                             class="img-fluid rounded-circle"
                             style="width: 200px; height: 200px; object-fit: cover;">
                    </div>
                    <h5 class="mb-1">Ana Martínez</h5>
                    <p class="text-muted mb-3">Jefa de Capacitación</p>
                    <div class="social-links">
                        <a href="#" class="text-muted me-2"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-muted"><i class="bi bi-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Partners -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary mb-3">Nuestros Socios</span>
            <h2 class="display-5 fw-bold">Marcas que Representamos</h2>
            <p class="lead text-muted">
                Distribuidores autorizados de las marcas líderes a nivel mundial
            </p>
        </div>
        
        <div class="row g-4 align-items-center">
            <div class="col-6 col-md-4 col-lg-2">
                <img src="<?php echo ASSETS_URL; ?>/img/brand-leica.png" 
                     alt="Leica Geosystems" 
                     class="img-fluid grayscale">
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <img src="<?php echo ASSETS_URL; ?>/img/brand-trimble.png" 
                     alt="Trimble" 
                     class="img-fluid grayscale">
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <img src="<?php echo ASSETS_URL; ?>/img/brand-topcon.png" 
                     alt="Topcon" 
                     class="img-fluid grayscale">
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <img src="<?php echo ASSETS_URL; ?>/img/brand-sokkia.png" 
                     alt="Sokkia" 
                     class="img-fluid grayscale">
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <img src="<?php echo ASSETS_URL; ?>/img/brand-nikon.png" 
                     alt="Nikon" 
                     class="img-fluid grayscale">
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <img src="<?php echo ASSETS_URL; ?>/img/brand-geomax.png" 
                     alt="GeoMax" 
                     class="img-fluid grayscale">
            </div>
        </div>
    </div>
</section>

<!-- Certifications -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <span class="badge bg-primary-soft text-primary mb-3">Certificaciones</span>
                <h2 class="display-5 fw-bold mb-4">Calidad Garantizada</h2>
                <p class="lead mb-4">
                    Contamos con las certificaciones y acreditaciones necesarias para 
                    garantizar la calidad de nuestros productos y servicios.
                </p>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-patch-check-fill text-success me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-1">ISO 9001:2015</h6>
                                <p class="mb-0 text-muted small">Sistema de Gestión de Calidad</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-patch-check-fill text-success me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-1">Distribuidor Autorizado</h6>
                                <p class="mb-0 text-muted small">Certificado por fabricantes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-patch-check-fill text-success me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-1">Centro de Servicio</h6>
                                <p class="mb-0 text-muted small">Técnicos certificados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-patch-check-fill text-success me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-1">Calibración Trazable</h6>
                                <p class="mb-0 text-muted small">Patrones certificados</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?php echo ASSETS_URL; ?>/img/certifications.jpg" 
                     alt="Certificaciones" 
                     class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="display-6 fw-bold mb-3">¿Listo para trabajar con los mejores?</h3>
                <p class="lead mb-0 opacity-90">
                    Únase a cientos de profesionales que confían en nosotros para sus proyectos
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="<?php echo SITE_URL; ?>/contacto" class="btn btn-white btn-lg me-3">
                    <i class="bi bi-envelope me-2"></i> Contáctenos
                </a>
                <a href="<?php echo SITE_URL; ?>/productos" class="btn btn-outline-light btn-lg">
                    Ver Productos
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* Timeline Styles */
.timeline-container {
    position: relative;
    max-width: 800px;
    margin: 0 auto;
}
.timeline-container::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
    transform: translateX(-50%);
}
.timeline-item {
    position: relative;
    padding: 30px 0;
}
.timeline-item:nth-child(odd) .timeline-content {
    margin-right: 50%;
    padding-right: 40px;
    text-align: right;
}
.timeline-item:nth-child(even) .timeline-content {
    margin-left: 50%;
    padding-left: 40px;
}
.timeline-year {
    position: absolute;
    left: 50%;
    top: 30px;
    transform: translateX(-50%);
    background: var(--bs-primary);
    color: white;
    padding: 5px 20px;
    border-radius: 100px;
    font-weight: bold;
    z-index: 1;
}
.timeline-item.active .timeline-year {
    background: var(--bs-success);
}
.timeline-content {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.timeline-content h5 {
    margin-bottom: 10px;
    color: var(--bs-primary);
}

/* Grayscale effect for brand logos */
.grayscale {
    filter: grayscale(100%);
    opacity: 0.6;
    transition: all 0.3s ease;
}
.grayscale:hover {
    filter: grayscale(0%);
    opacity: 1;
}

/* Team member styling */
.team-member {
    transition: all 0.3s ease;
}
.team-member:hover {
    transform: translateY(-10px);
}
.team-photo {
    position: relative;
    overflow: hidden;
    display: inline-block;
}
.team-photo img {
    transition: all 0.3s ease;
}
.team-member:hover .team-photo img {
    transform: scale(1.1);
}

/* Responsive */
@media (max-width: 768px) {
    .timeline-container::before {
        left: 30px;
    }
    .timeline-item:nth-child(odd) .timeline-content,
    .timeline-item:nth-child(even) .timeline-content {
        margin-left: 80px;
        margin-right: 0;
        padding-left: 0;
        padding-right: 0;
        text-align: left;
    }
    .timeline-year {
        left: 30px;
        transform: translateX(-50%);
    }
}
</style>

<?php include '../includes/footer.php'; ?>