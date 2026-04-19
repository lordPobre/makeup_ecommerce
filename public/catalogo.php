<?php
// public/catalogo.php
session_start();
require_once '../config/database.php';

// Filtros
$cat_id = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$brand_id = filter_input(INPUT_GET, 'brand_id', FILTER_VALIDATE_INT);
$cf = isset($_GET['cf']) ? 1 : 0;

// Construir consulta (eliminamos la referencia a is_vegan)
$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.available = 1";
$params = [];

if ($cat_id) { $query .= " AND p.category_id = ?"; $params[] = $cat_id; }
if ($brand_id) { $query .= " AND p.brand_id = ?"; $params[] = $brand_id; }
if ($cf) { $query .= " AND p.is_cruelty_free = 1"; }

$query .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Obtener datos para los filtros laterales
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$brands = $pdo->query("SELECT * FROM brands ORDER BY name")->fetchAll();

require_once '../includes/header.php';
?>

<main class="catalog-container">
    <div class="catalog-header" style="text-align: center; margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; color: #1a1a1a;">Catálogo Completo</h1>
        <p style="color: #666; font-size: 1.1rem;">Encuentra todo lo que necesitas para tu rutina perfecta.</p>
    </div>

    <div class="search-container" style="max-width: 600px; margin: 0 auto 40px; position: relative;">
        <i class="fas fa-search" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: #999;"></i>
        <input type="text" id="liveSearch" placeholder="Busca bases, labiales, marcas..." style="width: 100%; padding: 15px 20px 15px 50px; border: 1px solid #ddd; border-radius: 30px; font-size: 1rem; outline: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
    </div>

    <div class="catalog-layout" style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
        <aside class="filters-sidebar" style="flex: 1; min-width: 250px; max-width: 300px; background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #eee; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <form action="catalogo.php" method="GET" id="filterForm">
                
                <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 1.1rem; color: #1a1a1a; border-bottom: 2px solid #f9f9f9; padding-bottom: 10px;">Categorías</h3>
                <ul style="list-style: none; padding: 0; margin-bottom: 25px;">
                    <li style="margin-bottom: 8px;"><a href="catalogo.php" style="color: <?= !$cat_id ? '#1a1a1a; font-weight:700;' : '#666' ?>; text-decoration: none; transition: color 0.3s;">Todas las categorías</a></li>
                    <?php foreach ($categories as $c): ?>
                        <li style="margin-bottom: 8px;"><a href="catalogo.php?category_id=<?= $c['id'] ?>" style="color: <?= $cat_id == $c['id'] ? '#1a1a1a; font-weight:700;' : '#666' ?>; text-decoration: none; transition: color 0.3s;"><?= htmlspecialchars($c['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>

                <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 1.1rem; color: #1a1a1a; border-bottom: 2px solid #f9f9f9; padding-bottom: 10px;">Marcas</h3>
                <select name="brand_id" style="width: 100%; padding: 10px; margin-bottom: 25px; border-radius: 6px; border: 1px solid #ddd; background: #fdfdfd; outline: none; cursor: pointer;" onchange="document.getElementById('filterForm').submit();">
                    <option value="">Todas las marcas</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $brand_id == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 1.1rem; color: #1a1a1a; border-bottom: 2px solid #f9f9f9; padding-bottom: 10px;">Atributos Especiales</h3>
                <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; color: #666; cursor: pointer;">
                    <input type="checkbox" name="cf" value="1" <?= $cf ? 'checked' : '' ?> onchange="document.getElementById('filterForm').submit();" style="cursor: pointer; width: 18px; height: 18px; accent-color: #1a1a1a;"> 
                    <span>Cruelty Free 🐰</span>
                </label>
            </form>
        </aside>

        <div class="grid-container" style="flex: 3; display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 25px;">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card" style="position: relative;">
                        <?php if ($product['is_on_sale']): ?>
                            <span style="position: absolute; top: 10px; left: 10px; background: #e74c3c; color: white; font-weight: bold; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; z-index: 2;">¡OFERTA!</span>
                        <?php endif; ?>

                        <a href="producto.php?id=<?= $product['id'] ?>">
                            <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        </a>
                        <div class="product-info">
                            <p class="category" style="font-size: 0.8rem; color: #999; margin-bottom: 5px; text-transform: uppercase;"><?= htmlspecialchars($product['category_name']) ?></p>
                            <h3><a href="producto.php?id=<?= $product['id'] ?>" style="text-decoration: none; color: inherit;"><?= htmlspecialchars($product['name']) ?></a></h3>
                            
                            <div style="margin: 0; display: flex; align-items: baseline; justify-content: center; gap: 8px;">
                                <?php if ($product['is_on_sale'] && !empty($product['old_price'])): ?>
                                    <span style="text-decoration: line-through; color: #a0a0a0; font-size: 0.95rem; font-weight: 500;">
                                        $<?= number_format($product['old_price'], 0, ',', '.') ?>
                                    </span>
                                <?php endif; ?>
                                <span class="price" style="color: <?= $product['is_on_sale'] ? '#e74c3c' : '#1a1a1a' ?>; font-weight: 700; font-size: 1.2rem;">
                                    $<?= number_format($product['price'], 0, ',', '.') ?>
                                </span>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <a href="producto.php?id=<?= $product['id'] ?>" class="btn-action" style="display: block; text-align: center; background: #1a1a1a; color: white; padding: 12px; border-radius: 6px; text-decoration: none; font-weight: 600; transition: background 0.3s;">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <i class="fas fa-box-open" style="font-size: 3rem; color: #e0e0e0; margin-bottom: 15px;"></i>
                    <h3 style="color: #1a1a1a; margin-bottom: 10px;">No encontramos productos</h3>
                    <p style="color: #666;">Intenta quitando algunos filtros para ver más opciones.</p>
                    <a href="catalogo.php" class="btn-action" style="display: inline-block; background: #1a1a1a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px;">Limpiar Filtros</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('liveSearch');
    const gridContainer = document.querySelector('.grid-container');
    const urlParams = new URLSearchParams(window.location.search);
    const catId = urlParams.get('category_id') || '';
    const brandId = urlParams.get('brand_id') || '';

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value;
        gridContainer.style.opacity = '0.5';

        debounceTimer = setTimeout(() => {
            fetch(`buscar_productos.php?q=${encodeURIComponent(query)}&category_id=${catId}&brand_id=${brandId}`)
                .then(response => response.text())
                .then(html => {
                    gridContainer.innerHTML = html;
                    gridContainer.style.opacity = '1';
                });
        }, 300);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>