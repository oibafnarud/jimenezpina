<?php
/**
 * Términos y Condiciones
 * /terminos.php
 */
require_once 'config/config.php';

$pageTitle = 'Términos y Condiciones';
$metaDescription = 'Términos y condiciones de uso del sitio web de Jiménez & Piña Survey Instruments.';

include 'includes/header.php';
?>

<section class="page-header bg-primary text-white py-4">
    <div class="container">
        <h1>Términos y Condiciones</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-white-50">Inicio</a></li>
                <li class="breadcrumb-item active text-white">Términos y Condiciones</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="legal-content">
                    <p class="text-muted">Última actualización: <?php echo date('d/m/Y'); ?></p>
                    
                    <h2>1. Aceptación de los Términos</h2>
                    <p>
                        Al acceder y utilizar este sitio web de Jiménez & Piña Survey Instruments, 
                        usted acepta cumplir con estos términos y condiciones de uso. Si no está 
                        de acuerdo con alguna parte de estos términos, no debe utilizar nuestro sitio.
                    </p>
                    
                    <h2>2. Uso del Sitio Web</h2>
                    <p>
                        Este sitio web está destinado para proporcionar información sobre nuestros 
                        productos y servicios de instrumentos de topografía. Usted se compromete a:
                    </p>
                    <ul>
                        <li>Utilizar el sitio solo para fines legales y autorizados</li>
                        <li>No intentar acceder a áreas restringidas del sitio</li>
                        <li>No interferir con el funcionamiento del sitio</li>
                        <li>Proporcionar información precisa al realizar consultas o cotizaciones</li>
                    </ul>
                    
                    <h2>3. Propiedad Intelectual</h2>
                    <p>
                        Todo el contenido de este sitio web, incluyendo pero no limitado a textos, 
                        gráficos, logos, imágenes, y software, es propiedad de Jiménez & Piña Survey 
                        Instruments o sus proveedores de contenido y está protegido por las leyes de 
                        propiedad intelectual.
                    </p>
                    
                    <h2>4. Información de Productos</h2>
                    <p>
                        Nos esforzamos por proporcionar información precisa sobre nuestros productos. 
                        Sin embargo:
                    </p>
                    <ul>
                        <li>Las especificaciones pueden cambiar sin previo aviso</li>
                        <li>Las imágenes son referenciales y pueden variar del producto real</li>
                        <li>Los precios están sujetos a cambios y deben confirmarse al momento de la cotización</li>
                    </ul>
                    
                    <h2>5. Cotizaciones y Precios</h2>
                    <p>
                        Las cotizaciones realizadas a través de nuestro sitio web:
                    </p>
                    <ul>
                        <li>Son válidas por el período especificado en cada cotización</li>
                        <li>Están sujetas a disponibilidad de inventario</li>
                        <li>No incluyen impuestos a menos que se especifique</li>
                        <li>Pueden requerir confirmación adicional por parte de nuestro equipo de ventas</li>
                    </ul>
                    
                    <h2>6. Limitación de Responsabilidad</h2>
                    <p>
                        Jiménez & Piña Survey Instruments no será responsable por:
                    </p>
                    <ul>
                        <li>Daños directos, indirectos, incidentales o consecuentes derivados del uso del sitio</li>
                        <li>Interrupciones o errores en el servicio del sitio web</li>
                        <li>Pérdida de datos o información</li>
                        <li>Decisiones tomadas basándose en la información del sitio</li>
                    </ul>
                    
                    <h2>7. Enlaces a Terceros</h2>
                    <p>
                        Nuestro sitio puede contener enlaces a sitios web de terceros. No somos 
                        responsables del contenido o las prácticas de privacidad de estos sitios.
                    </p>
                    
                    <h2>8. Privacidad</h2>
                    <p>
                        El uso de nuestro sitio web también se rige por nuestra 
                        <a href="<?php echo SITE_URL; ?>/privacidad">Política de Privacidad</a>, 
                        que describe cómo recopilamos, usamos y protegemos su información personal.
                    </p>
                    
                    <h2>9. Modificaciones</h2>
                    <p>
                        Nos reservamos el derecho de modificar estos términos y condiciones en 
                        cualquier momento. Los cambios entrarán en vigor inmediatamente después 
                        de su publicación en el sitio.
                    </p>
                    
                    <h2>10. Ley Aplicable</h2>
                    <p>
                        Estos términos se regirán e interpretarán de acuerdo con las leyes de la 
                        República Dominicana, sin tener en cuenta sus disposiciones sobre conflictos de leyes.
                    </p>
                    
                    <h2>11. Contacto</h2>
                    <p>
                        Si tiene preguntas sobre estos términos y condiciones, puede contactarnos en:
                    </p>
                    <address>
                        <strong>Jiménez & Piña Survey Instruments</strong><br>
                        <?php echo nl2br(getConfig('address')); ?><br>
                        Teléfono: <a href="tel:<?php echo getConfig('phone'); ?>"><?php echo getConfig('phone'); ?></a><br>
                        Email: <a href="mailto:<?php echo getConfig('support_email'); ?>"><?php echo getConfig('support_email'); ?></a>
                    </address>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.legal-content {
    font-size: 1.1rem;
    line-height: 1.8;
}
.legal-content h2 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: var(--bs-primary);
}
.legal-content ul {
    margin-bottom: 1.5rem;
}
</style>

<?php include 'includes/footer.php'; ?>