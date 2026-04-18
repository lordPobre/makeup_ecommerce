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
        <div class="empty-cart">
            <p>Tu bolso está vacío. ¡Es hora de descubrir tus nuevos favoritos!</p>
            <a href="index.php" class="btn-shop">Volver al catálogo</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($cart_products as $item): ?>
                    <div class="cart-item">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        
                        <div class="item-details">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <?php if ($item['tone']): ?>
                                <p class="item-tone">Tono: <?= htmlspecialchars($item['tone']) ?></p>
                            <?php endif; ?>
                            <a href="remove_from_cart.php?key=<?= urlencode($item['cart_key']) ?>" class="btn-remove">Eliminar</a>
                        </div>
                        
                        <div class="item-quantity">
                            <p>Cant: <?= $item['quantity'] ?></p>
                        </div>
                        
                        <div class="item-price">
                            <p>$<?= number_format($item['subtotal'], 0, ',', '.') ?></p>
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
                    <span>Calculado en el siguiente paso</span>
                </div>
                <hr>
                <div class="summary-row total">
                    <span>Total Estimado</span>
                    <span>$<?= number_format($total, 0, ',', '.') ?></span>
                </div>
                <a href="checkout.php" class="btn-checkout">Proceder al Pago</a>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>