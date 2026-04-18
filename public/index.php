<?php
session_start();
require_once '../config/database.php';

$filtro_marca = filter_input(INPUT_GET, 'brand_id', FILTER_VALIDATE_INT);
$filtro_categoria = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);

$query = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.available = 1";
$params = [];

if ($filtro_marca) {
    $query .= " AND p.brand_id = ?";
    $params[] = $filtro_marca;
}
if ($filtro_categoria) {
    $query .= " AND p.category_id = ?";
    $params[] = $filtro_categoria;
}

$query .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);

$products = $stmt->fetchAll();
$stmtBrands = $pdo->query("SELECT * FROM brands");
$categorias = $pdo->query("SELECT * FROM categories")->fetchAll();
$brands = $stmtBrands->fetchAll();


require_once '../includes/header.php';
?>
<?php if (isset($_GET['compra']) && $_GET['compra'] === 'exitosa'): ?>
    <div style="
        background-color: #d4edda; 
        color: #155724; 
        padding: 20px; 
        text-align: center; 
        border: 1px solid #c3e6cb; 
        border-radius: 8px; 
        margin: 20px auto; 
        max-width: 900px;
        font-family: 'Montserrat', sans-serif;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    ">
        <h2 style="margin-bottom: 10px;">¡Pedido Recibido! 💖</h2>
        <p>Tu orden ha sido registrada con éxito. Muy pronto nos pondremos en contacto contigo vía WhatsApp para coordinar el pago y el envío.</p>
        <a href="index.php" style="color: #155724; font-size: 0.9rem; text-decoration: underline;">Cerrar aviso</a>
    </div>
<?php endif; ?>
<header class="hero-slider">
    <div class="slide active" style="background-image: url('https://tahecosmetics.com/trends/wp-content/uploads/2022/06/historia-del-maquillaje.jpg');"></div>
    <div class="slide" style="background-image: url('https://www.loreal-paris.com.mx/-/media/project/loreal/brand-sites/oap/americas/mx/articles/blog-de-belleza/ideas-de-maquillaje/orden-correcto-maquillaje/banner-texto-03.jpg?cx=0.45&cy=0.42&cw=2000&ch=815&hash=BD8D8EF1E91C141E8E89792E85E3CD3B');"></div>
    <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1616683693504-3ea7e9ad6fec?q=80&w=1920&auto=format&fit=crop');"></div>

    <button class="slider-btn prev">&#10094;</button>
    <button class="slider-btn next">&#10095;</button>

    <div class="hero-text-box">
        <h1>Tu Belleza, Tus Reglas</h1>
        <p>Descubre nuestra nueva línea de básicos imprescindibles.</p>
        <a href="#catalogo" class="btn-shop">COMPRAR AHORA</a>
    </div>

    <div class="slider-dots">
        <span class="dot active" onclick="currentSlide(0)"></span>
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
    </div>
</header>

<?php if (count($brands) > 0): ?>
<section class="brands-section">
    <div class="brands-container">
        <p class="brands-title" style="text-align: center; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px; color: #666; margin-bottom: 30px;">Trabajamos con las mejores marcas</p>
        <div class="carousel-wrapper">
            <div class="brands-track">
                <?php foreach ($brands as $brand): ?>
                    <a href="index.php?brand_id=<?= $brand['id'] ?>#catalogo">
                        <img src="<?= htmlspecialchars($brand['image_path']) ?>" alt="<?= htmlspecialchars($brand['name']) ?>" title="<?= htmlspecialchars($brand['name']) ?>">
                    </a>
                <?php endforeach; ?>
                
                <?php foreach ($brands as $brand): ?>
                    <a href="index.php?brand_id=<?= $brand['id'] ?>#catalogo">
                        <img src="<?= htmlspecialchars($brand['image_path']) ?>" alt="<?= htmlspecialchars($brand['name']) ?>" title="<?= htmlspecialchars($brand['name']) ?>">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<main class="product-grid" id="catalogo">
    
    <div class="catalog-header" style="text-align: center; margin-bottom: 40px; margin-top: 20px;">
        <h2>
            <?php 
            if ($filtro_marca) echo "Filtrado por Marca";
            elseif ($filtro_categoria) echo "Filtrado por Categoría";
            else echo "Nuestros Favoritos"; 
            ?>
        </h2>
        
        <div class="category-filters">
            <a href="index.php#catalogo" class="filter-btn <?= (!$filtro_categoria && !$filtro_marca) ? 'active' : '' ?>">Ver Todo</a>
            
            <?php foreach ($categorias as $cat): ?>
                <a href="index.php?category_id=<?= $cat['id'] ?>#catalogo" 
                   class="filter-btn <?= ($filtro_categoria == $cat['id']) ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($filtro_marca || $filtro_categoria): ?>
            <a href="index.php#catalogo" style="font-size: 0.8rem; display: inline-block; margin-top: 15px; color: #888; text-decoration: underline;">Quitar todos los filtros</a>
        <?php endif; ?>
    </div>
    <div class="grid-container">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="producto.php?id=<?= $product['id'] ?>">
                        <?php $img_src = strpos($product['image_path'], 'http') === 0 ? $product['image_path'] : $product['image_path']; ?>
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
                        <a href="producto.php?id=<?= $product['id'] ?>" class="btn-add" style="display:block; text-align:center; text-decoration:none;">Ver Detalles</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; width: 100%; color: #666; font-size: 1.1rem; padding: 40px 0;">No hay productos disponibles en esta selección todavía.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

<script>
    let currentSlideIndex = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let slideInterval;

    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        if (index >= slides.length) currentSlideIndex = 0;
        if (index < 0) currentSlideIndex = slides.length - 1;
        slides[currentSlideIndex].classList.add('active');
        dots[currentSlideIndex].classList.add('active');
    }

    function nextSlide() {
        currentSlideIndex++;
        showSlide(currentSlideIndex);
    }

    function prevSlide() {
        currentSlideIndex--;
        showSlide(currentSlideIndex);
    }

    document.querySelector('.next').addEventListener('click', () => {
        nextSlide();
        resetInterval();
    });
    
    document.querySelector('.prev').addEventListener('click', () => {
        prevSlide();
        resetInterval();
    });

    window.currentSlide = function(index) {
        currentSlideIndex = index;
        showSlide(currentSlideIndex);
        resetInterval();
    };

    function startInterval() {
        slideInterval = setInterval(nextSlide, 5000); 
    }

    function resetInterval() {
        clearInterval(slideInterval);
        startInterval();
    }

    startInterval();
</script>