<?php
/**
 * Footer - Pie de página
 * Jiménez & Piña Survey Instruments
 */

// Obtener categorías para el footer
$footerCategorias = $db->fetchAll("
    SELECT nombre, slug FROM categorias 
    WHERE activo = 1 AND parent_id IS NULL 
    ORDER BY orden ASC 
    LIMIT 6
");

// Obtener marcas para el footer
$footerMarcas = $db->fetchAll("
    SELECT nombre, slug FROM marcas 
    WHERE activo = 1 
    ORDER BY orden ASC 
    LIMIT 6
");
?>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <!-- Columna 1 - Info de la empresa -->
                <div class="col-lg-4">
                    <div class="mb-4">
                        <img src="<?php echo ASSETS_URL; ?>/img/logo-white.png" alt="Jiménez & Piña" height="50" class="mb-3">
                        <p class="text-white-50">
                            Líderes en distribución de equipos topográficos de precisión. 
                            Más de 15 años brindando soluciones integrales para profesionales 
                            de la construcción y topografía en República Dominicana.
                        </p>
                    </div>
                    <div class="social-links mb-4">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle me-2">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle me-2">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle me-2">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle">
                            <i class="bi bi-youtube"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Columna 2 - Enlaces rápidos -->
                <div class="col-lg-2 col-md-4">
                    <h5 class="fw-bold mb-3">Enlaces Rápidos</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> Inicio
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/productos" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> Productos
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/servicios" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> Servicios
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/blog" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> Blog
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/nosotros" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> Nosotros
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/contacto" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> Contacto
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Columna 3 - Categorías -->
                <div class="col-lg-2 col-md-4">
                    <h5 class="fw-bold mb-3">Categorías</h5>
                    <ul class="list-unstyled">
                        <?php foreach($footerCategorias as $categoria): ?>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/categoria/<?php echo $categoria['slug']; ?>" 
                               class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> <?php echo sanitize($categoria['nombre']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Columna 4 - Marcas -->
                <div class="col-lg-2 col-md-4">
                    <h5 class="fw-bold mb-3">Marcas</h5>
                    <ul class="list-unstyled">
                        <?php foreach($footerMarcas as $marca): ?>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/marca/<?php echo $marca['slug']; ?>" 
                               class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right small"></i> <?php echo sanitize($marca['nombre']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Columna 5 - Contacto -->
                <div class="col-lg-2 col-md-4">
                    <h5 class="fw-bold mb-3">Contacto</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-3">
                            <i class="bi bi-geo-alt me-2"></i>
                            <?php echo nl2br(getSetting('site_address')); ?>
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-telephone me-2"></i>
                            <a href="tel:<?php echo cleanPhone(getSetting('site_phone')); ?>" 
                               class="text-white-50 text-decoration-none">
                                <?php echo getSetting('site_phone'); ?>
                            </a>
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-envelope me-2"></i>
                            <a href="mailto:<?php echo getSetting('site_email'); ?>" 
                               class="text-white-50 text-decoration-none">
                                <?php echo getSetting('site_email'); ?>
                            </a>
                        </li>
                        <li>
                            <i class="bi bi-clock me-2"></i>
                            Lun-Vie: 8:00 AM - 6:00 PM<br>
                            <span class="ms-4">Sáb: 9:00 AM - 1:00 PM</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Newsletter -->
            <div class="row mt-5 pt-5 border-top border-secondary">
                <div class="col-lg-6">
                    <h5 class="fw-bold mb-3">Suscríbase a nuestro boletín</h5>
                    <p class="text-white-50">Reciba las últimas noticias y ofertas especiales directamente en su correo</p>
                </div>
                <div class="col-lg-6">
                    <form action="<?php echo SITE_URL; ?>/api/newsletter.php" method="POST" class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Su correo electrónico" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-send me-1"></i> Suscribirse
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="row mt-5 pt-4 border-top border-secondary">
                <div class="col-md-6">
                    <p class="text-white-50 mb-0">
                        &copy; <?php echo date('Y'); ?> Jiménez & Piña Survey Instruments. Todos los derechos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="<?php echo SITE_URL; ?>/terminos" class="text-white-50 text-decoration-none me-3">
                        Términos y Condiciones
                    </a>
                    <a href="<?php echo SITE_URL; ?>/privacidad" class="text-white-50 text-decoration-none">
                        Política de Privacidad
                    </a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button id="btnBackToTop" class="btn btn-primary btn-back-to-top" style="display: none;">
        <i class="bi bi-arrow-up"></i>
    </button>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
    
    <!-- Live Chat Widget (opcional) -->
    <!--
    <script>
        window.$crisp=[];window.CRISP_WEBSITE_ID="YOUR-WEBSITE-ID";(function(){
            d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";
            s.async=1;d.getElementsByTagName("head")[0].appendChild(s);
        })();
    </script>
    -->
</body>
</html>