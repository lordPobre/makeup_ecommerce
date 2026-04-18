<?php
// public/preguntas.php
session_start();
require_once '../includes/header.php';
?>

<main class="faq-container">
    <div class="faq-header">
        <h1>Preguntas Frecuentes</h1>
        <p>Resolvemos tus dudas para que disfrutes tu experiencia Glow & Beauty.</p>
    </div>

    <div class="faq-accordion">
        
        <div class="faq-item">
            <button class="faq-question">
                ¿Hacen envíos a todo el país?
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="faq-answer">
                <p>Sí, realizamos despachos a todas las regiones de Chile. Trabajamos con los principales couriers para asegurar que tu maquillaje llegue en perfectas condiciones hasta la puerta de tu casa.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                ¿Cuánto demora en llegar mi pedido?
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="faq-answer">
                <p>Para la Región Metropolitana los tiempos de entrega son de 24 a 48 horas hábiles. Para el resto de las regiones, el tiempo estimado es de 3 a 5 días hábiles dependiendo de la lejanía.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                ¿Son sus productos Cruelty Free?
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="faq-answer">
                <p>¡Absolutamente! Nos tomamos la ética muy en serio. Todo nuestro catálogo está certificado como libre de crueldad animal. Ninguno de los ingredientes ni el producto final ha sido testeado en animales.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                ¿Qué hago si me equivoco de tono de base?
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="faq-answer">
                <p>Entendemos que elegir el tono online puede ser un desafío. Si el producto está sellado y sin uso, puedes solicitar un cambio dentro de los primeros 10 días desde que lo recibiste. Por motivos de higiene, no podemos cambiar bases o correctores que ya hayan sido abiertos.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                ¿Qué medios de pago aceptan?
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="faq-answer">
                <p>Aceptamos todas las tarjetas de crédito y débito a través de nuestra pasarela de pago segura. También puedes pagar mediante transferencia bancaria directa (en este caso, tu pedido se procesará una vez confirmado el depósito).</p>
            </div>
        </div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

<script>
    document.querySelectorAll('.faq-question').forEach(button => {
        button.addEventListener('click', () => {
            const faqItem = button.parentElement;
            
            // Cerrar las otras preguntas si están abiertas (opcional, pero elegante)
            document.querySelectorAll('.faq-item').forEach(item => {
                if (item !== faqItem) {
                    item.classList.remove('active');
                }
            });

            // Abrir o cerrar la que clickeamos
            faqItem.classList.toggle('active');
        });
    });
</script>