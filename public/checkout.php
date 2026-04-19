<?php
// public/checkout.php
session_start();
require_once '../config/database.php';

if (empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

$total_carrito = 0;

// Calculamos el total real consultando la base de datos
if (!empty($_SESSION['cart'])) {
    $product_ids = [];
    foreach ($_SESSION['cart'] as $cart_key => $item) {
        $parts = explode('_', $cart_key);
        $product_ids[] = (int)$parts[0];
    }
    
    $product_ids = array_unique($product_ids);
    $inQuery = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($inQuery)");
    $stmt->execute(array_values($product_ids));
    
    $prices = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Crea un array [id => precio]

    foreach ($_SESSION['cart'] as $cart_key => $item) {
        $parts = explode('_', $cart_key);
        $id = (int)$parts[0];
        if (isset($prices[$id])) {
            $total_carrito += ($prices[$id] * $item['quantity']);
        }
    }
}

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
                    <label for="rut">RUT</label>
                    <input type="text" name="rut" id="rut" class="form-control" placeholder="12.345.678-9" required>
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
                <div class="form-group">
                    <label>Región de Envío</label>
                    <select name="region" id="regionSelector" class="form-control" required>
                        <option value="" data-cost="0">Selecciona tu región...</option>
                        <option value="Metropolitana" data-cost="3500">Región Metropolitana ($3.500)</option>
                        <option value="Valparaiso" data-cost="5500">Región de Valparaíso ($5.500)</option>
                        <option value="Otras" data-cost="7500">Otras Regiones ($7.500)</option>
                    </select>
                </div>
                <div class="summary-box" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-top: 20px;">
                    <p>Subtotal: <span id="subtotalDisplay">$<?= number_format($total_carrito, 0, ',', '.') ?></span></p>
                    <p>Envío: <span id="shippingDisplay">$0</span></p>
                    <hr>
                    <h3 style="margin: 0;">Total: <span id="totalDisplay">$<?= number_format($total_carrito, 0, ',', '.') ?></span></h3>
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

<script>
    document.getElementById('rut')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\./g, '').replace('-', '');
    if (value.match(/^(\d{2})(\d{3})(\d{3})([\dkK]{1})$/)) {
        e.target.value = value.replace(/^(\d{2})(\d{3})(\d{3})([\dkK]{1})$/, '$1.$2.$3-$4');
    }
});

document.getElementById('regionSelector').addEventListener('change', function() {
    // Obtener el costo del atributo data-cost del option seleccionado
    const selectedOption = this.options[this.selectedIndex];
    const shippingCost = parseInt(selectedOption.getAttribute('data-cost'));
    const subtotal = <?= $total_carrito ?>; // El total de los productos desde PHP

    // Formatear a moneda chilena
    const formatter = new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        minimumFractionDigits: 0
    });

    // Actualizar la interfaz
    document.getElementById('shippingDisplay').innerText = formatter.format(shippingCost);
    document.getElementById('totalDisplay').innerText = formatter.format(subtotal + shippingCost);
});
</script>

<?php require_once '../includes/footer.php'; ?>