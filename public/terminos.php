<?php
// public/terminos.php
session_start();
require_once '../includes/header.php';
?>

<main class="legal-container">
    <div class="legal-header">
        <h1>Términos y Condiciones</h1>
        <p>Última actualización: <?= date('d / m / Y') ?></p>
    </div>

    <div class="legal-content">
        <section>
            <h2>1. Aspectos Generales</h2>
            <p>
                El acceso, navegación y uso del sitio web Glow & Beauty están regulados por los presentes Términos y Condiciones, así como por la legislación vigente aplicable en la República de Chile, en particular la Ley N° 19.496 sobre Protección de los Derechos de los Consumidores y la Ley N° 19.628 sobre Protección de la Vida Privada.
            </p>
        </section>

        <section>
            <h2>2. Registro del Usuario</h2>
            <p>
                Para realizar compras en nuestro sitio no es obligatorio estar registrado, sin embargo, el registro facilita el proceso de compra futura. Los datos proporcionados serán tratados de manera confidencial y utilizados exclusivamente para procesar la compra y el despacho de los productos.
            </p>
        </section>

        <section>
            <h2>3. Medios de Pago</h2>
            <p>
                Los productos ofrecidos en Glow & Beauty podrán ser pagados a través de tarjetas de crédito y débito emitidas en Chile o en el extranjero, utilizando plataformas de pago seguras y certificadas. No almacenamos los datos de tus tarjetas.
            </p>
        </section>

        <section>
            <h2>4. Despachos y Entregas</h2>
            <p>
                Las condiciones de despacho y entrega de los productos son de exclusiva responsabilidad de la empresa de transporte. Los plazos de entrega comienzan a regir a partir de la confirmación del pago. Es responsabilidad del cliente proveer una dirección exacta y un número de contacto válido.
            </p>
        </section>

        <section>
            <h2>5. Cambios y Devoluciones (Garantía Legal)</h2>
            <p>
                <strong>Derecho a retracto:</strong> Tienes un plazo de 10 días desde la recepción del producto para devolverlo si te arrepientes de la compra, siempre y cuando el producto esté sellado, sin uso, con sus etiquetas y empaque original intacto. Por motivos de higiene, no se aceptan devoluciones de maquillaje abierto o probado.
            </p>
            <p>
                <strong>Garantía Legal:</strong> Si el producto presenta fallas de fábrica, tienes derecho a la garantía legal de 6 meses. Puedes elegir entre el cambio del producto, la reparación gratuita o la devolución de tu dinero, presentando tu boleta o comprobante de compra.
            </p>
        </section>

        <section>
            <h2>6. Propiedad Intelectual</h2>
            <p>
                Todos los contenidos incluidos en este sitio, como textos, material gráfico, logotipos, íconos de botones, códigos fuente, imágenes y recopilaciones de datos, son propiedad exclusiva de Glow & Beauty o de sus proveedores de contenido.
            </p>
        </section>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>