<?php
/**
 * Página de Contacto
 * Jiménez & Piña Survey Instruments
 */
require_once '../config/config.php';

// Meta tags
$pageTitle = 'Contacto';
$pageDescription = 'Contáctenos para más información sobre equipos topográficos, servicios técnicos y cotizaciones. Estamos para servirle.';
$pageKeywords = 'contacto jimenez piña, distribuidor topográfico santo domingo, servicio técnico equipos';

include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Contáctenos</h1>
                <p class="lead mb-0 opacity-90">
                    Estamos aquí para ayudarle a encontrar la solución perfecta para sus necesidades topográficas
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column align-items-lg-end gap-2">
                    <a href="tel:<?php echo cleanPhone(getSetting('site_phone')); ?>" class="btn btn-white">
                        <i class="bi bi-telephone me-2"></i> Llamar Ahora
                    </a>
                    <a href="https://wa.me/<?php echo cleanPhone(getSetting('whatsapp_number')); ?>" 
                       target="_blank" class="btn btn-success">
                        <i class="bi bi-whatsapp me-2"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info Cards -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="contact-icon bg-primary-soft text-primary rounded-circle mb-3 mx-auto" 
                             style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-geo-alt-fill fs-2"></i>
                        </div>
                        <h5 class="card-title">Ubicación</h5>
                        <p class="card-text">
                            <?php echo nl2br(getSetting('site_address')); ?>
                        </p>
                        <a href="#mapa" class="btn btn-outline-primary btn-sm">Ver en mapa</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="contact-icon bg-primary-soft text-primary rounded-circle mb-3 mx-auto" 
                             style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-telephone-fill fs-2"></i>
                        </div>
                        <h5 class="card-title">Teléfonos</h5>
                        <p class="card-text">
                            <strong>Oficina:</strong> <?php echo getSetting('site_phone'); ?><br>
                            <strong>WhatsApp:</strong> +1 (809) 555-0123<br>
                            <strong>Soporte:</strong> +1 (809) 555-0124
                        </p>
                        <a href="tel:<?php echo cleanPhone(getSetting('site_phone')); ?>" 
                           class="btn btn-outline-primary btn-sm">Llamar ahora</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="contact-icon bg-primary-soft text-primary rounded-circle mb-3 mx-auto" 
                             style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-clock-fill fs-2"></i>
                        </div>
                        <h5 class="card-title">Horario</h5>
                        <p class="card-text">
                            <strong>Lun - Vie:</strong> 8:00 AM - 6:00 PM<br>
                            <strong>Sábados:</strong> 9:00 AM - 1:00 PM<br>
                            <strong>Domingos:</strong> Cerrado
                        </p>
                        <span class="badge bg-success">Abierto ahora</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Form & Map -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow h-100">
                    <div class="card-body p-5">
                        <h3 class="mb-4">Envíenos un mensaje</h3>
                        <p class="text-muted mb-4">
                            Complete el formulario y nos pondremos en contacto con usted lo antes posible.
                        </p>
                        
                        <form action="<?php echo SITE_URL; ?>/api/contacto.php" method="POST" 
                              class="needs-validation" novalidate>
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
                                    <label for="asunto" class="form-label">Asunto *</label>
                                    <select class="form-select" id="asunto" name="asunto" required>
                                        <option value="">Seleccione un asunto</option>
                                        <option value="Información de productos">Información de productos</option>
                                        <option value="Solicitud de cotización">Solicitud de cotización</option>
                                        <option value="Servicio técnico">Servicio técnico</option>
                                        <option value="Capacitación">Capacitación</option>
                                        <option value="Soporte">Soporte</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor seleccione un asunto
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="mensaje" class="form-label">Mensaje *</label>
                                    <textarea class="form-control" id="mensaje" name="mensaje" 
                                              rows="5" required></textarea>
                                    <div class="invalid-feedback">
                                        Por favor escriba su mensaje
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="newsletter" 
                                               name="newsletter" value="1">
                                        <label class="form-check-label" for="newsletter">
                                            Deseo recibir información y ofertas por correo electrónico
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="bi bi-send me-2"></i> Enviar Mensaje
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow h-100">
                    <div class="card-body p-0">
                        <!-- Google Maps -->
                        <div id="mapa" style="height: 100%; min-height: 600px;">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3784.081951851699!2d-69.93667668455372!3d18.479835087435967!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8eaf89f1107ea5ab%3A0xd6625c3f6e2e7c8f!2sAv.%20Winston%20Churchill%2C%20Santo%20Domingo!5e0!3m2!1ses!2sdo!4v1234567890" 
                                width="100%" 
                                height="100%" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional Contact Options -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-5">Otras formas de contactarnos</h3>
        
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="contact-option">
                    <i class="bi bi-whatsapp text-success display-4 mb-3"></i>
                    <h5>WhatsApp Business</h5>
                    <p class="text-muted">Respuesta inmediata</p>
                    <a href="https://wa.me/<?php echo cleanPhone(getSetting('whatsapp_number')); ?>" 
                       target="_blank" class="btn btn-success btn-sm">
                        Iniciar chat
                    </a>
                </div>
            </div>
            
            <div class="col-md-3 text-center">
                <div class="contact-option">
                    <i class="bi bi-envelope text-primary display-4 mb-3"></i>
                    <h5>Email</h5>
                    <p class="text-muted"><?php echo getSetting('site_email'); ?></p>
                    <a href="mailto:<?php echo getSetting('site_email'); ?>" 
                       class="btn btn-primary btn-sm">
                        Enviar email
                    </a>
                </div>
            </div>
            
            <div class="col-md-3 text-center">
                <div class="contact-option">
                    <i class="bi bi-facebook text-primary display-4 mb-3"></i>
                    <h5>Facebook</h5>
                    <p class="text-muted">@jimenezpina</p>
                    <a href="https://facebook.com/jimenezpina" 
                       target="_blank" class="btn btn-primary btn-sm">
                        Visitar página
                    </a>
                </div>
            </div>
            
            <div class="col-md-3 text-center">
                <div class="contact-option">
                    <i class="bi bi-instagram text-danger display-4 mb-3"></i>
                    <h5>Instagram</h5>
                    <p class="text-muted">@jimenezpina_rd</p>
                    <a href="https://instagram.com/jimenezpina_rd" 
                       target="_blank" class="btn btn-danger btn-sm">
                        Seguir
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h3 class="text-center mb-5">Preguntas Frecuentes</h3>
                
                <div class="accordion" id="contactFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq1">
                                ¿Cuál es el tiempo de respuesta a las consultas?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#contactFAQ">
                            <div class="accordion-body">
                                Nos comprometemos a responder todas las consultas en un plazo máximo de 24 horas 
                                hábiles. Para casos urgentes, recomendamos contactarnos por teléfono o WhatsApp 
                                para una respuesta inmediata.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq2">
                                ¿Realizan visitas a proyectos?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                            <div class="accordion-body">
                                Sí, realizamos visitas técnicas a proyectos para evaluar necesidades específicas, 
                                realizar demostraciones de equipos o brindar soporte en campo. Coordine una cita 
                                con nuestro equipo técnico.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq3">
                                ¿Tienen servicio de entrega a domicilio?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                            <div class="accordion-body">
                                Sí, ofrecemos servicio de entrega en todo el territorio nacional. En Santo Domingo 
                                y Santiago las entregas son gratuitas para compras superiores a US$500. Para otras 
                                provincias, consulte los costos de envío.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq4">
                                ¿Aceptan pagos con tarjeta de crédito?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                            <div class="accordion-body">
                                Aceptamos múltiples formas de pago: efectivo, transferencia bancaria, cheque 
                                certificado y todas las tarjetas de crédito principales. También ofrecemos 
                                facilidades de financiamiento para proyectos calificados.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Dynamic business hours
function updateBusinessStatus() {
    const now = new Date();
    const day = now.getDay();
    const hour = now.getHours();
    const minute = now.getMinutes();
    const time = hour + (minute / 60);
    
    let isOpen = false;
    
    if (day >= 1 && day <= 5) { // Monday to Friday
        isOpen = time >= 8 && time < 18;
    } else if (day === 6) { // Saturday
        isOpen = time >= 9 && time < 13;
    }
    
    const badge = document.querySelector('.badge.bg-success');
    if (badge) {
        if (isOpen) {
            badge.textContent = 'Abierto ahora';
            badge.className = 'badge bg-success';
        } else {
            badge.textContent = 'Cerrado ahora';
            badge.className = 'badge bg-danger';
        }
    }
}

// Update status on page load
updateBusinessStatus();
</script>

<?php include '../includes/footer.php'; ?>