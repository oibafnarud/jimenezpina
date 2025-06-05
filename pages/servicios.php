<?php
/**
 * Página de Servicios
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

// Meta tags
$pageTitle = 'Servicios';
$pageDescription = 'Servicios profesionales de topografía: calibración, mantenimiento, capacitación, alquiler de equipos y soporte técnico especializado.';
$pageKeywords = 'servicios topográficos, calibración equipos, mantenimiento, capacitación topografía, alquiler equipos';

include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Servicios Profesionales</h1>
                <p class="lead mb-0 opacity-90">
                    Más que equipos, ofrecemos soluciones integrales con respaldo técnico especializado 
                    para maximizar su inversión y productividad
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="#contacto-servicios" class="btn btn-white btn-lg">
                    <i class="bi bi-calendar-check me-2"></i> Agendar Servicio
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Services Overview -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Service 1: Mantenimiento y Calibración -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-hover">
                    <div class="card-body text-center p-4">
                        <div class="service-icon mb-3">
                            <i class="bi bi-tools display-3 text-primary"></i>
                        </div>
                        <h4 class="mb-3">Mantenimiento y Calibración</h4>
                        <p class="text-muted mb-3">
                            Servicio técnico certificado para mantener sus equipos en óptimas condiciones 
                            y garantizar mediciones precisas.
                        </p>
                        <a href="#mantenimiento" class="btn btn-outline-primary">
                            Más información <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Service 2: Capacitación -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-hover">
                    <div class="card-body text-center p-4">
                        <div class="service-icon mb-3">
                            <i class="bi bi-mortarboard display-3 text-primary"></i>
                        </div>
                        <h4 class="mb-3">Capacitación Profesional</h4>
                        <p class="text-muted mb-3">
                            Programas de formación especializados para dominar el uso de equipos 
                            y software topográfico.
                        </p>
                        <a href="#capacitacion" class="btn btn-outline-primary">
                            Más información <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Service 3: Alquiler -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-hover">
                    <div class="card-body text-center p-4">
                        <div class="service-icon mb-3">
                            <i class="bi bi-calendar3 display-3 text-primary"></i>
                        </div>
                        <h4 class="mb-3">Alquiler de Equipos</h4>
                        <p class="text-muted mb-3">
                            Opciones flexibles de alquiler para proyectos temporales. 
                            Equipos calibrados y listos para usar.
                        </p>
                        <a href="#alquiler" class="btn btn-outline-primary">
                            Más información <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Service 4: Soporte Técnico -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-hover">
                    <div class="card-body text-center p-4">
                        <div class="service-icon mb-3">
                            <i class="bi bi-headset display-3 text-primary"></i>
                        </div>
                        <h4 class="mb-3">Soporte Técnico 24/7</h4>
                        <p class="text-muted mb-3">
                            Asistencia técnica remota y en campo disponible las 24 horas 
                            para resolver cualquier inconveniente.
                        </p>
                        <a href="#soporte" class="btn btn-outline-primary">
                            Más información <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Service 5: Instalación -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-hover">
                    <div class="card-body text-center p-4">
                        <div class="service-icon mb-3">
                            <i class="bi bi-truck display-3 text-primary"></i>
                        </div>
                        <h4 class="mb-3">Entrega e Instalación</h4>
                        <p class="text-muted mb-3">
                            Servicio de entrega, instalación y configuración inicial 
                            en su proyecto con puesta en marcha garantizada.
                        </p>
                        <a href="#instalacion" class="btn btn-outline-primary">
                            Más información <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Service 6: Trade-In -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-hover">
                    <div class="card-body text-center p-4">
                        <div class="service-icon mb-3">
                            <i class="bi bi-arrow-repeat display-3 text-primary"></i>
                        </div>
                        <h4 class="mb-3">Programa Trade-In</h4>
                        <p class="text-muted mb-3">
                            Actualice sus equipos antiguos con nuestro programa de intercambio 
                            y obtenga descuentos especiales.
                        </p>
                        <a href="#tradein" class="btn btn-outline-primary">
                            Más información <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Service Details -->
<!-- Mantenimiento y Calibración -->
<section id="mantenimiento" class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <img src="<?php echo ASSETS_URL; ?>/img/service-maintenance.jpg" 
                     alt="Servicio de Mantenimiento" 
                     class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6">
                <div class="ps-lg-5">
                    <span class="badge bg-primary-soft text-primary mb-3">Servicio Técnico</span>
                    <h2 class="display-5 fw-bold mb-3">Mantenimiento y Calibración</h2>
                    <p class="lead mb-4">
                        Mantenga sus equipos topográficos funcionando con precisión milimétrica 
                        mediante nuestro servicio técnico certificado.
                    </p>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="bi bi-check-circle-fill text-success me-3"></i>
                                <div>
                                    <h6 class="mb-1">Técnicos Certificados</h6>
                                    <p class="mb-0 text-muted small">Personal capacitado por los fabricantes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="bi bi-check-circle-fill text-success me-3"></i>
                                <div>
                                    <h6 class="mb-1">Repuestos Originales</h6>
                                    <p class="mb-0 text-muted small">Garantía de calidad y durabilidad</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="bi bi-check-circle-fill text-success me-3"></i>
                                <div>
                                    <h6 class="mb-1">Certificado de Calibración</h6>
                                    <p class="mb-0 text-muted small">Documentación oficial con trazabilidad</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="bi bi-check-circle-fill text-success me-3"></i>
                                <div>
                                    <h6 class="mb-1">Servicio Express</h6>
                                    <p class="mb-0 text-muted small">Atención prioritaria para urgencias</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Servicios incluidos:</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-arrow-right-circle text-primary me-2"></i> Diagnóstico completo del equipo</li>
                        <li class="mb-2"><i class="bi bi-arrow-right-circle text-primary me-2"></i> Limpieza y ajuste de componentes</li>
                        <li class="mb-2"><i class="bi bi-arrow-right-circle text-primary me-2"></i> Calibración con patrones certificados</li>
                        <li class="mb-2"><i class="bi bi-arrow-right-circle text-primary me-2"></i> Actualización de firmware</li>
                        <li class="mb-2"><i class="bi bi-arrow-right-circle text-primary me-2"></i> Pruebas de funcionamiento</li>
                    </ul>
                    
                    <div class="d-flex gap-3">
                        <a href="#contacto-servicios" class="btn btn-primary">
                            Solicitar Servicio
                        </a>
                        <a href="<?php echo SITE_URL; ?>/contacto" class="btn btn-outline-primary">
                            Consultar Precios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Capacitación -->
<section id="capacitacion" class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2">
                <img src="<?php echo ASSETS_URL; ?>/img/service-training.jpg" 
                     alt="Capacitación Profesional" 
                     class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="pe-lg-5">
                    <span class="badge bg-primary-soft text-primary mb-3">Formación</span>
                    <h2 class="display-5 fw-bold mb-3">Capacitación Profesional</h2>
                    <p class="lead mb-4">
                        Maximice el rendimiento de su inversión con nuestros programas de 
                        capacitación diseñados para todos los niveles.
                    </p>
                    
                    <div class="accordion" id="trainingAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#basicTraining">
                                    <i class="bi bi-1-circle me-2"></i> Nivel Básico
                                </button>
                            </h2>
                            <div id="basicTraining" class="accordion-collapse collapse show" 
                                 data-bs-parent="#trainingAccordion">
                                <div class="accordion-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="bi bi-check text-success me-2"></i> Introducción a la topografía</li>
                                        <li><i class="bi bi-check text-success me-2"></i> Operación básica de equipos</li>
                                        <li><i class="bi bi-check text-success me-2"></i> Mediciones y nivelación</li>
                                        <li><i class="bi bi-check text-success me-2"></i> Seguridad en campo</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#intermediateTraining">
                                    <i class="bi bi-2-circle me-2"></i> Nivel Intermedio
                                </button>
                            </h2>
                            <div id="intermediateTraining" class="accordion-collapse collapse" 
                                 data-bs-parent="#trainingAccordion">
                                <div class="accordion-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="bi bi-check text-success me-2"></i> Configuración avanzada</li>
                                        <li><i class="bi bi-check text-success me-2"></i> Replanteo y trazado</li>
                                        <li><i class="bi bi-check text-success me-2"></i> Procesamiento de datos</li>
                                        <li><i class="bi bi-check text-success me-2"></i> Software especializado</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#advancedTraining">
                                    <i class="bi bi-3-circle me-2"></i> Nivel Avanzado
                                </button>
                            </h2>
                            <div id="advancedTraining" class="accordion-collapse collapse" 
                                 data-bs-parent="#trainingAccordion">
                                <div class="accordion-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="bi bi-check text-success me-2"></i> Metodologías BIM</li>
                                        <li><i class="bi bi-check text-success me-2"></i> Fotogrametría con drones</li>
                                        <li><i class="bi bi-check text-success me-2"></i> Modelado 3D</li>
                                        <li><i class="bi bi-check text-success me-2"></i> Integración de sistemas</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="#contacto-servicios" class="btn btn-primary">
                            Inscribirse
                        </a>
                        <a href="<?php echo ASSETS_URL; ?>/downloads/calendario-capacitaciones.pdf" 
                           class="btn btn-outline-primary" download>
                            <i class="bi bi-download me-2"></i> Descargar Calendario
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Alquiler -->
<section id="alquiler" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary mb-3">Alquiler</span>
            <h2 class="display-5 fw-bold">Alquiler de Equipos</h2>
            <p class="lead text-muted">
                Soluciones flexibles para proyectos temporales o pruebas de equipos
            </p>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon bg-primary-soft text-primary rounded-circle mb-3 mx-auto" 
                         style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-calendar-week fs-2"></i>
                    </div>
                    <h5>Planes Flexibles</h5>
                    <p class="text-muted">Alquiler por días, semanas o meses según su necesidad</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon bg-primary-soft text-primary rounded-circle mb-3 mx-auto" 
                         style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-check fs-2"></i>
                    </div>
                    <h5>Equipos Calibrados</h5>
                    <p class="text-muted">Todos los equipos con certificación vigente</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon bg-primary-soft text-primary rounded-circle mb-3 mx-auto" 
                         style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-headset fs-2"></i>
                    </div>
                    <h5>Soporte Incluido</h5>
                    <p class="text-muted">Asistencia técnica durante todo el período</p>
                </div>
            </div>
        </div>
        
        <!-- Pricing Table -->
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow">
                    <div class="card-header bg-white text-center py-4">
                        <h4 class="mb-0">Plan Diario</h4>
                        <p class="text-muted mb-0">Ideal para proyectos cortos</p>
                    </div>
                    <div class="card-body text-center">
                        <div class="display-4 text-primary mb-3">
                            Desde <strong>$150</strong>
                        </div>
                        <p class="text-muted">por día</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Mínimo 3 días</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Equipo calibrado</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Accesorios incluidos</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Soporte básico</li>
                        </ul>
                        <a href="#contacto-servicios" class="btn btn-outline-primary w-100">
                            Solicitar
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card h-100 border-primary shadow">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h4 class="mb-0">Plan Semanal</h4>
                        <p class="mb-0">Más popular</p>
                    </div>
                    <div class="card-body text-center">
                        <div class="display-4 text-primary mb-3">
                            Desde <strong>$800</strong>
                        </div>
                        <p class="text-muted">por semana</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> 7 días continuos</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Equipo de respaldo</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Capacitación incluida</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Soporte prioritario</li>
                        </ul>
                        <a href="#contacto-servicios" class="btn btn-primary w-100">
                            Solicitar
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow">
                    <div class="card-header bg-white text-center py-4">
                        <h4 class="mb-0">Plan Mensual</h4>
                        <p class="text-muted mb-0">Proyectos extensos</p>
                    </div>
                    <div class="card-body text-center">
                        <div class="display-4 text-primary mb-3">
                            Desde <strong>$2,500</strong>
                        </div>
                        <p class="text-muted">por mes</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> 30 días</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Mantenimiento incluido</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Opción de compra</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Soporte 24/7</li>
                        </ul>
                        <a href="#contacto-servicios" class="btn btn-outline-primary w-100">
                            Solicitar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <p class="text-muted">
                <i class="bi bi-info-circle me-2"></i>
                Los precios varían según el tipo de equipo. Consulte disponibilidad y tarifas específicas.
            </p>
        </div>
    </div>
</section>

<!-- Support & Other Services -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Soporte 24/7 -->
            <div class="col-lg-6" id="soporte">
                <div class="card h-100 border-0 bg-primary text-white">
                    <div class="card-body p-5">
                        <i class="bi bi-headset display-3 mb-4"></i>
                        <h3 class="mb-3">Soporte Técnico 24/7</h3>
                        <p class="mb-4 opacity-90">
                            Nuestro equipo de expertos está disponible las 24 horas del día, 
                            los 7 días de la semana para resolver cualquier inconveniente.
                        </p>
                        <h5 class="mb-3">Canales de soporte:</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-telephone-fill me-2"></i> 
                                Línea directa: <?php echo getSetting('site_phone'); ?>
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-whatsapp me-2"></i> 
                                WhatsApp: +1 (809) 555-0123
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-envelope-fill me-2"></i> 
                                Email: soporte@jimenezpina.com
                            </li>
                            <li class="mb-4">
                                <i class="bi bi-camera-video-fill me-2"></i> 
                                Asistencia remota por video
                            </li>
                        </ul>
                        <a href="https://wa.me/18095550123" target="_blank" class="btn btn-white">
                            <i class="bi bi-whatsapp me-2"></i> Contactar Ahora
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Trade-In Program -->
            <div class="col-lg-6" id="tradein">
                <div class="card h-100 border-0 bg-light">
                    <div class="card-body p-5">
                        <i class="bi bi-arrow-repeat display-3 text-primary mb-4"></i>
                        <h3 class="mb-3">Programa Trade-In</h3>
                        <p class="mb-4">
                            Actualice su equipo antiguo y obtenga un descuento significativo 
                            en la compra de equipos nuevos.
                        </p>
                        <h5 class="mb-3">¿Cómo funciona?</h5>
                        <ol class="mb-4">
                            <li class="mb-2">Evaluamos su equipo actual</li>
                            <li class="mb-2">Le ofrecemos un valor de intercambio justo</li>
                            <li class="mb-2">Aplicamos el descuento en su nueva compra</li>
                            <li class="mb-2">Nos encargamos del retiro del equipo antiguo</li>
                        </ol>
                        <div class="alert alert-success mb-4">
                            <i class="bi bi-tag-fill me-2"></i>
                            <strong>Hasta 40% de descuento</strong> en equipos nuevos al entregar su equipo usado
                        </div>
                        <a href="#contacto-servicios" class="btn btn-primary">
                            Evaluar mi Equipo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form -->
<section id="contacto-servicios" class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold">Solicite Nuestros Servicios</h2>
                    <p class="lead text-muted">
                        Complete el formulario y nos pondremos en contacto en menos de 24 horas
                    </p>
                </div>
                
                <div class="card border-0 shadow">
                    <div class="card-body p-5">
                        <form action="<?php echo SITE_URL; ?>/api/servicios.php" method="POST" class="needs-validation" novalidate>
                            <?php echo csrfField(); ?>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre completo *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese su nombre
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="empresa" class="form-label">Empresa</label>
                                    <input type="text" class="form-control" id="empresa" name="empresa">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese un email válido
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label">Teléfono *</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese su teléfono
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="servicio" class="form-label">Servicio requerido *</label>
                                    <select class="form-select" id="servicio" name="servicio" required>
                                        <option value="">Seleccione un servicio</option>
                                        <option value="mantenimiento">Mantenimiento y Calibración</option>
                                        <option value="capacitacion">Capacitación</option>
                                        <option value="alquiler">Alquiler de Equipos</option>
                                        <option value="soporte">Soporte Técnico</option>
                                        <option value="instalacion">Instalación</option>
                                        <option value="tradein">Programa Trade-In</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor seleccione un servicio
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="equipo" class="form-label">Modelo de equipo (si aplica)</label>
                                    <input type="text" class="form-control" id="equipo" name="equipo" 
                                           placeholder="Ej: Leica TS16">
                                </div>
                                
                                <div class="col-12">
                                    <label for="mensaje" class="form-label">Detalles adicionales</label>
                                    <textarea class="form-control" id="mensaje" name="mensaje" rows="4" 
                                              placeholder="Describa brevemente su necesidad..."></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="urgente" name="urgente">
                                        <label class="form-check-label" for="urgente">
                                            <i class="bi bi-lightning-fill text-warning"></i> 
                                            Este es un servicio urgente
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="bi bi-send me-2"></i> Enviar Solicitud
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h3 class="text-center mb-5">Preguntas Frecuentes</h3>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq1">
                                ¿Con qué frecuencia debo calibrar mis equipos?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Recomendamos calibrar los equipos topográficos al menos una vez al año o 
                                después de 200 horas de uso intensivo. También es necesario calibrar después 
                                de cualquier golpe o caída del equipo.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq2">
                                ¿Incluyen transporte en el servicio de alquiler?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Sí, incluimos servicio de entrega y recogida sin costo adicional dentro del 
                                Gran Santo Domingo. Para otras provincias, consulte las tarifas de envío.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq3">
                                ¿Las capacitaciones otorgan certificado?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Sí, todos nuestros programas de capacitación incluyen un certificado de 
                                participación avalado por Jiménez & Piña y los fabricantes de los equipos.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq4">
                                ¿Qué garantía tienen las reparaciones?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Todas nuestras reparaciones tienen una garantía de 90 días en mano de obra 
                                y 6 meses en repuestos, siempre que se utilicen en condiciones normales.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.shadow-hover {
    transition: all 0.3s ease;
}
.shadow-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}
.service-icon {
    transition: all 0.3s ease;
}
.card:hover .service-icon {
    transform: scale(1.1);
}
</style>

<?php include '../includes/footer.php'; ?>
                