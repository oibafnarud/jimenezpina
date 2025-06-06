<?php
/**
 * Footer del sitio
 * /includes/footer.php
 */
if (!defined('ROOT_PATH')) {
    die('Acceso directo no permitido');
}
?>
    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>Sobre Nosotros</h5>
                    <p><?php echo sanitize(getConfig('site_description')); ?></p>
                    <div class="social-links">
                        <?php if($fb = getConfig('facebook')): ?>
                        <a href="<?php echo $fb; ?>" target="_blank" class="text-white me-3">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <?php endif; ?>
                        <?php if($ig = getConfig('instagram')): ?>
                        <a href="<?php echo $ig; ?>" target="_blank" class="text-white me-3">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <?php endif; ?>
                        <?php if($tw = getConfig('twitter')): ?>
                        <a href="<?php echo $tw; ?>" target="_blank" class="text-white me-3">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Enlaces Rápidos</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>/productos" class="text-white-50">Productos</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/servicios" class="text-white-50">Servicios</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/blog" class="text-white-50">Blog</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/nosotros" class="text-white-50">Nosotros</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contacto" class="text-white-50">Contacto</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Servicios</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>/servicios#mantenimiento" class="text-white-50">Mantenimiento</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/servicios#capacitacion" class="text-white-50">Capacitación</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/servicios#alquiler" class="text-white-50">Alquiler</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/servicios#soporte" class="text-white-50">Soporte Técnico</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Contacto</h5>
                    <p class="text-white-50">
                        <i class="bi bi-geo-alt"></i> <?php echo nl2br(sanitize(getConfig('address'))); ?>
                    </p>
                    <p class="text-white-50">
                        <i class="bi bi-telephone"></i> 
                        <a href="tel:<?php echo getConfig('phone'); ?>" class="text-white-50">
                            <?php echo getConfig('phone'); ?>
                        </a>
                    </p>
                    <p class="text-white-50">
                        <i class="bi bi-envelope"></i> 
                        <a href="mailto:<?php echo getConfig('support_email'); ?>" class="text-white-50">
                            <?php echo getConfig('support_email'); ?>
                        </a>
                    </p>
                </div>
            </div>
            
            <hr class="bg-white-50">
            
            <div class="row">
                <div class="col-md-6">
                    <p class="text-white-50 mb-0">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos los derechos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="<?php echo SITE_URL; ?>/terminos" class="text-white-50 me-3">Términos y Condiciones</a>
                    <a href="<?php echo SITE_URL; ?>/privacidad" class="text-white-50">Política de Privacidad</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <!-- WhatsApp Button -->
    <?php if($whatsapp = getConfig('whatsapp')): ?>
    <a href="https://wa.me/<?php echo $whatsapp; ?>" 
       target="_blank" 
       class="whatsapp-button"
       title="Contáctanos por WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
    
    <style>
    .whatsapp-button {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
        background-color: #25D366;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        z-index: 1000;
        transition: all 0.3s;
    }
    .whatsapp-button:hover {
        color: white;
        transform: scale(1.1);
    }
    </style>
    <?php endif; ?>
</body>
</html>