<?php
session_start();
require_once '../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ? AND p.available = 1
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("Producto no encontrado.");
}

require_once '../includes/header.php';
?>

<main class="product-detail-container">
    <div class="product-gallery">
        <?php $img_src = strpos($product['image_path'], 'http') === 0 ? $product['image_path'] : $product['image_path']; ?>
        <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="main-product-img">
    </div>

    <div class="product-info-detail">
        <nav class="breadcrumb">
            <a href="index.php">Catálogo</a> / <?= htmlspecialchars($product['category_name']) ?>
        </nav>
        
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <p class="price-large">$<?= number_format($product['price'], 0, ',', '.') ?></p>
        
        <div class="product-description">
            <h3>Descripción</h3>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>

        <?php if ($product['is_cruelty_free']): ?>
            <div class="badges-detail">
                <span class="badge-gold">🐰 Certificado Cruelty Free</span>
                <span class="badge-gold">✨ Fórmula Premium</span>
            </div>
        <?php endif; ?>

        <form action="add_to_cart.php" method="POST" class="purchase-form">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            
            <div class="option-group">
                <label for="tone">Elige tu tono:</label>
                <select name="tone" id="tone" class="custom-select" required>
                    <?php 
                    if (!empty($product['tones'])): 
                        $tonos_array = explode(',', $product['tones']);
                        foreach ($tonos_array as $tono): 
                            $tono_limpio = trim($tono); 
                    ?>
                        <option value="<?= htmlspecialchars($tono_limpio) ?>">
                            <?= htmlspecialchars($tono_limpio) ?>
                        </option>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                        <option value="Único">Tono Único</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="quantity-row">
                <div class="qty-input">
                    <label>Cant.</label>
                    <input type="number" name="quantity" value="1" min="1" max="10">
                </div>
                <button type="submit" class="btn-buy-now">Añadir al Bolso</button>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>