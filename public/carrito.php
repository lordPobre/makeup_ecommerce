<?php
// public/carrito.php
session_start();
require_once '../config/database.php';

$cart = $_SESSION['cart'] ?? [];
$cart_products = [];
$total = 0;

if (!empty($cart)) {
    // Obtener los IDs únicos de los productos en el carrito
    $product_ids = array_unique(array_column($cart, 'product_id'));
    
    // Preparar la consulta dinámica para los IN (?)
    $inQuery = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price, image_path FROM products WHERE id IN ($inQuery)");
    $stmt->execute(array_values($product_ids));
    
    // Crear un diccionario de productos para acceso rápido
    $db_products = [];
    while ($row = $stmt->fetch()) {
        $db_products[$row['id']] = $row;
    }

    // Armar la lista final con los datos de la DB y la sesión
    foreach ($cart as $key => $item) {
        if (isset($db_products[$item['product_id']])) {
            $product = $db_products[$item['product_id']];
            $subtotal = $product['price'] * $item['quantity'];
            $total += $subtotal;
            
            $cart_products[] = [
                'cart_key' => $key,
                'name' => $product['name'],
                'image' => $product['image_path'],
                'price' => $product['price'],
                'quantity' => $item['quantity'],
                'tone' => $item['tone'],
                'subtotal' => $subtotal
            ];
        }
    }
}

require_once '../includes/header.php';
?>

<main class="cart-container">
    <h1>Mi Bolso de Belleza</h1>

    <?php if (empty($cart_products)): ?>
        <div class="empty-cart" style="text-align: center; margin-top: 50px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#1a1a1a" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 20px; opacity: 0.8;">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <p style="font-size: 1.1rem; color: #666;">Tu bolso está vacío. ¡Es hora de descubrir tus nuevos favoritos!</p>
            <a href="index.php" class="btn-shop" style="display: inline-block; margin-top: 20px;">Volver al catálogo</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($cart_products as $item): ?>
                    <div class="cart-item" style="transition: opacity 0.3s ease;">
                        <?php $img_src = strpos($item['image'], 'http') === 0 ? $item['image'] : $item['image']; ?>
                        <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        
                        <div class="item-details">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <?php if ($item['tone']): ?>
                                <p class="item-tone">Tono: <?= htmlspecialchars($item['tone']) ?></p>
                            <?php endif; ?>
                            <a href="remove_from_cart.php?key=<?= urlencode($item['cart_key']) ?>" class="btn-remove" style="color: #e74c3c; margin-top: 10px; display: inline-block;">Eliminar</a>
                        </div>
                        
                        <div class="item-quantity">
                            <div style="display: flex; align-items: center; border: 1px solid #e0e0e0; border-radius: 4px; overflow: hidden; max-width: 110px; margin: 0 auto;">
                                <button class="main-qty-btn" data-key="<?= htmlspecialchars($item['cart_key']) ?>" data-change="-1" style="background: #fff; border: none; padding: 8px 15px; cursor: pointer; color: #666; font-weight: bold; border-right: 1px solid #e0e0e0; font-size: 1rem;">-</button>
                                
                                <span style="flex: 1; text-align: center; font-size: 0.95rem; font-weight: 600; color: #1a1a1a;"><?= $item['quantity'] ?></span>
                                
                                <button class="main-qty-btn" data-key="<?= htmlspecialchars($item['cart_key']) ?>" data-change="1" style="background: #fff; border: none; padding: 8px 15px; cursor: pointer; color: #666; font-weight: bold; border-left: 1px solid #e0e0e0; font-size: 1rem;">+</button>
                            </div>
                        </div>
                        
                        <div class="item-price">
                            <p style="font-weight: 600; font-size: 1.1rem; color: #1a1a1a;">$<?= number_format($item['subtotal'], 0, ',', '.') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Resumen</h2>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?= number_format($total, 0, ',', '.') ?></span>
                </div>
                <div class="summary-row">
                    <span>Envío</span>
                    <span style="font-size: 0.85rem; color: #888;">Calculado en el checkout</span>
                </div>
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
                <div class="summary-row total" style="font-size: 1.2rem; margin-bottom: 25px;">
                    <span style="font-weight: 700;">Total Estimado</span>
                    <span style="font-weight: 700;">$<?= number_format($total, 0, ',', '.') ?></span>
                </div>
                <a href="checkout.php" class="btn-checkout" style="background: #1a1a1a; color: white; text-align: center; display: block; padding: 15px; text-decoration: none; font-weight: 600; border-radius: 4px; letter-spacing: 1px; text-transform: uppercase;">Proceder al Pago</a>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const qtyButtons = document.querySelectorAll('.main-qty-btn');
    
    qtyButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const cartKey = this.getAttribute('data-key');
            const change = this.getAttribute('data-change');
            const container = this.closest('.cart-item');

            // Efecto visual: difuminamos la fila y bloqueamos clics para evitar errores
            container.style.opacity = '0.4';
            container.style.pointerEvents = 'none';

            // Preparamos los datos a enviar
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('action', 'update');
            formData.append('cart_key', cartKey);
            formData.append('change', change);

            // Enviamos la orden a tu archivo add_to_cart.php
            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    // Recargamos la página suavemente para que PHP recalcule todos los totales 
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Si algo falla, devolvemos el contenedor a la normalidad
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            });
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>