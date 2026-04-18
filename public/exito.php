<?php
// public/exito.php
session_start();
require_once '../includes/header.php'; // Para mantener el menú y estilo
?>

<main style="display: flex; align-items: center; justify-content: center; min-height: 70vh; padding: 20px;">
    <div style="text-align: center; max-width: 600px; background: white; padding: 50px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f4efe9;">
        
        <div style="font-size: 60px; color: #2ecc71; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 style="font-family: 'Montserrat', sans-serif; color: #1a1a1a; margin-bottom: 15px;">¡Pedido Confirmado!</h1>
        
        <p style="font-size: 1.1rem; color: #666; line-height: 1.6; margin-bottom: 30px;">
            Gracias por confiar en <strong>Glow & Beauty</strong>. 💖 <br>
            Hemos recibido tu pedido correctamente. En breve nos pondremos en contacto contigo a través de <strong>WhatsApp</strong> para coordinar el pago y el envío de tus productos.
        </p>

        <div style="background: #fff9f4; border: 1px dashed #e5c6a1; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <p style="margin: 0; color: #856404; font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i> Recuerda tener tu celular a mano, te escribiremos pronto.
            </p>
        </div>

        <a href="index.php" class="btn-checkout" style="text-decoration: none; display: inline-block; padding: 15px 40px; background: #1a1a1a; color: white; border-radius: 5px; font-weight: 600; transition: transform 0.2s;">
            Volver a la Tienda
        </a>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>