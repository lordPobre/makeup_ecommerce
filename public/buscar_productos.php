<?php
// public/buscar_productos.php
require_once '../config/database.php';

// Limpieza para PHP 8.1+
$q = htmlspecialchars(trim($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8');
$cat = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$brand = filter_input(INPUT_GET, 'brand_id', FILTER_VALIDATE_INT);

// Construimos la consulta dinámica
$query = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.available = 1";
$params = [];

if ($q) {
    // Busca en el nombre del producto, la categoría o la descripción
    $query .= " AND (p.name LIKE ? OR c.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($cat) {
    $query .= " AND p.category_id = ?";
    $params[] = $cat;
}
if ($brand) {
    $query .= " AND p.brand_id = ?";
    $params[] = $brand;
}

$query .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Si encontramos productos, dibujamos las tarjetas
if (count($products) > 0) {
    foreach ($products as $product) {
        $img_src = strpos($product['image_path'], 'http') === 0 ? $product['image_path'] : $product['image_path'];
        ?>
        <div class="product-card">
            <a href="producto.php?id=<?= $product['id'] ?>">
                <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </a>
            
            <div class="product-info">
                <p class="category"><?= htmlspecialchars($product['category_name']) ?></p>
                <a href="producto.php?id=<?= $product['id'] ?>" style="text-decoration: none; color: inherit;">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                </a>
                <p class="price">$<?= number_format($product['price'], 0, ',', '.') ?></p>
                
                <?php if ($product['is_cruelty_free']): ?>
                    <span class="badge">Cruelty Free 🐰</span>
                <?php endif; ?>
                
                <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                    <div style="display: flex; gap: 8px; margin-top: 15px;">
                        <a href="producto.php?id=<?= $product['id'] ?>" style="flex: 1; text-align: center; text-decoration: none; background: #f8f9fa; color: #333; border: 1px solid #ddd; padding: 10px; border-radius: 4px; font-size: 0.85rem; font-weight: 500; transition: background 0.2s;">
                            Detalles
                        </a>
                        <form action="add_to_cart.php" method="POST" class="form-add-cart" style="flex: 1; margin: 0;">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" style="width: 100%; border: none; cursor: pointer; padding: 10px; border-radius: 4px; background: #1a1a1a; color: white; font-size: 0.85rem; font-weight: 600; transition: transform 0.1s;">
                                Agregar
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 15px;">
                        <span style="display: block; width: 100%; text-align: center; padding: 10px; background: #fdedec; color: #e74c3c; border-radius: 4px; font-weight: 600; font-size: 0.85rem; border: 1px solid #fadbd8;">
                            Agotado temporalmente
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
} else {
    // Si no hay resultados
    echo '<div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-search" style="font-size: 3rem; color: #e0e0e0; margin-bottom: 15px;"></i>
            <h3 style="color: #1a1a1a; margin-bottom: 10px;">No encontramos lo que buscas</h3>
            <p style="color: #666;">Intenta con otras palabras clave o revisa nuestras categorías.</p>
          </div>';
}
?>