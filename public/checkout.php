<?php
// public/checkout.php
session_start();
require_once '../config/database.php';

if (empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

require_once '../includes/header.php';
?>

<main class="checkout-container">
    <div class="checkout-grid">
        <section class="billing-details">
            <h2>Detalles de Envío</h2>
            <form action="process_checkout.php" method="POST" id="checkout-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="customer_name" required placeholder="Ej. Javiera Paz">
                    </div>
                </div>
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" required placeholder="tu@email.com">
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Teléfono (WhatsApp)</label><br>
                    <input type="tel" name="phone" placeholder="Ej: 9 1234 5678" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-top: 5px;">
                </div>
                <div class="form-group">
                    <label>Dirección de Despacho</label>
                    <input type="text" name="address" required placeholder="Calle, número, departamento">
                </div>
                <div class="form-group">
                    <label>Ciudad / Comuna</label>
                    <input type="text" name="city" required placeholder="Viña del Mar">
                </div>
                
                <button type="submit" class="btn-checkout">Confirmar y Realizar Pedido</button>
            </form>
        </section>

        <section class="checkout-summary-mini">
            <h3>Tu Pedido</h3>
            <p>Al confirmar, tu pedido quedará registrado y nos pondremos en contacto para el pago.</p>
        </section>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>