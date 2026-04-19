<?php
session_start();
require_once '../config/database.php';

// 1. OBTENER LAS OFERTAS (Solo productos con is_on_sale = 1)
$stmtOfertas = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.available = 1 AND p.is_on_sale = 1 ORDER BY p.id DESC LIMIT 8");
$ofertas = $stmtOfertas->fetchAll();

// 2. OBTENER MARCAS (Para el carrusel)
$stmtBrands = $pdo->query("SELECT * FROM brands");
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
        <a href="catalogo.php" class="btn-shop">COMPRAR AHORA</a>
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
                    <a href="catalogo.php?brand_id=<?= $brand['id'] ?>">
                        <img src="<?= htmlspecialchars($brand['image_path']) ?>" alt="<?= htmlspecialchars($brand['name']) ?>" title="<?= htmlspecialchars($brand['name']) ?>">
                    </a>
                <?php endforeach; ?>
                
                <?php foreach ($brands as $brand): ?>
                    <a href="catalogo.php?brand_id=<?= $brand['id'] ?>">
                        <img src="<?= htmlspecialchars($brand['image_path']) ?>" alt="<?= htmlspecialchars($brand['name']) ?>" title="<?= htmlspecialchars($brand['name']) ?>">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<main class="home-container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
    
    <div style="
        position: relative;
        text-align: center; 
        padding: 80px 20px; 
        background: linear-gradient(135deg, #fdf5f6 0%, #f9eef0 100%); 
        border-radius: 16px; 
        margin: 40px 0 60px 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        overflow: hidden;
    ">
        <div style="position: absolute; top: -50px; left: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.6); border-radius: 50%; filter: blur(30px);"></div>
        <div style="position: absolute; bottom: -50px; right: -50px; width: 250px; height: 250px; background: rgba(231, 76, 60, 0.04); border-radius: 50%; filter: blur(40px);"></div>

        <h1 style="
            position: relative;
            font-family: 'Montserrat', sans-serif; 
            font-size: 2.8rem; 
            font-weight: 300; 
            color: #1a1a1a; 
            margin: 0 0 15px 0;
            letter-spacing: -0.5px;
            display: block;
        ">Descubre tu <span style="font-weight: 600; font-style: italic;">brillo interior</span></h1>
        
        <p style="
            position: relative;
            font-size: 1.1rem; 
            color: #666; 
            margin: 0 0 35px 0; 
            font-weight: 400;
            display: block;
        ">Los mejores cosméticos de tus marcas favoritas, ahora en oferta.</p>
        
        <a href="catalogo.php" style="
            position: relative;
            background: #1a1a1a; 
            color: white; 
            padding: 16px 45px; 
            text-decoration: none; 
            border-radius: 30px; 
            display: inline-block; 
            font-weight: 600; 
            letter-spacing: 1px; 
            text-transform: uppercase;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(26,26,26,0.2);
        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(26,26,26,0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(26,26,26,0.2)';">
            Ver todo el catálogo
        </a>
    </div>

    <div style="text-align: center; margin-bottom: 40px; display: flex; flex-direction: column; align-items: center;">
        <h2 style="display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 2rem; color: #1a1a1a; margin-bottom: 10px; font-weight: 600;">
            Ofertas Especiales
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-top: -3px; transform: rotate(15deg);">
                <path d="M12 3c0 4.97-4.03 9-9 9 4.97 0 9 4.03 9 9 0-4.97 4.03-9 9-9-4.97 0-9-4.03-9-9z"></path>
            </svg>
        </h2>
        <div style="width: 60px; height: 3px; background: #1a1a1a; margin: 0 auto; border-radius: 2px;"></div>
    </div>
    
    <div class="grid-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 25px; margin-bottom: 60px;">
        <?php if(count($ofertas) > 0): ?>
            <?php foreach ($ofertas as $product): ?>
                <div class="product-card" style="position: relative; background: #fff; border-radius: 8px; border: 1px solid #f0f0f0; padding: 15px; text-align: center; transition: box-shadow 0.3s;" onmouseover="this.style.boxShadow='0 8px 25px rgba(0,0,0,0.05)';" onmouseout="this.style.boxShadow='none';">
                    <span style="position: absolute; top: 15px; left: 15px; background: #e74c3c; color: white; font-weight: 700; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; letter-spacing: 1px; z-index: 2;">OFERTA</span>
                    
                    <a href="producto.php?id=<?= $product['id'] ?>" style="display: block; margin-bottom: 15px;">
                        <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 100%; height: 200px; object-fit: contain;">
                    </a>
                    <div class="product-info">
                        <p class="category" style="font-size: 0.8rem; color: #999; margin-bottom: 5px; text-transform: uppercase; font-weight: 500;"><?= htmlspecialchars($product['category_name']) ?></p>
                        <h3 style="margin: 0 0 10px 0; font-size: 1.1rem; font-weight: 600;"><a href="producto.php?id=<?= $product['id'] ?>" style="text-decoration: none; color: #1a1a1a;"><?= htmlspecialchars($product['name']) ?></a></h3>
                        
                        <div style="margin: 0 0 15px 0; display: flex; align-items: baseline; justify-content: center; gap: 8px;">
                            <?php if ($product['is_on_sale'] && !empty($product['old_price'])): ?>
                                <span style="text-decoration: line-through; color: #a0a0a0; font-size: 0.95rem; font-weight: 500;">
                                    $<?= number_format($product['old_price'], 0, ',', '.') ?>
                                </span>
                            <?php endif; ?>
                            <span class="price" style="color: <?= $product['is_on_sale'] ? '#e74c3c' : '#1a1a1a' ?>; font-weight: 700; font-size: 1.2rem;">
                                $<?= number_format($product['price'], 0, ',', '.') ?>
                            </span>
                        </div>

                        <div style="display: flex; gap: 10px; justify-content: center; margin-top: auto;">
                            <a href="producto.php?id=<?= $product['id'] ?>" style="flex: 1; padding: 10px 0; border: 1px solid #1a1a1a; color: #1a1a1a; text-decoration: none; border-radius: 4px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; transition: all 0.3s;" onmouseover="this.style.background='#f9f9f9';" onmouseout="this.style.background='transparent';">
                                Detalles
                            </a>
                            
                            <form class="form-add-cart" method="POST" action="add_to_cart.php" style="flex: 1; margin: 0;">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" style="width: 100%; height: 100%; padding: 10px 0; background: #1a1a1a; color: white; border: 1px solid #1a1a1a; border-radius: 4px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='#333';" onmouseout="this.style.background='#1a1a1a';">
                                    Comprar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: #fff; border: 1px dashed #e0e0e0; border-radius: 12px;">
                <i class="fas fa-gift" style="font-size: 3rem; color: #f9eef0; margin-bottom: 15px;"></i>
                <h3 style="color: #1a1a1a; margin-bottom: 10px; font-weight: 500;">Estamos preparando sorpresas</h3>
                <p style="color: #888; max-width: 400px; margin: 0 auto;">Actualmente estamos actualizando nuestras ofertas. ¡Vuelve pronto para descubrir tus nuevos favoritos a precios increíbles!</p>
            </div>
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

    document.addEventListener('DOMContentLoaded', () => {
    const formsAddCart = document.querySelectorAll('.form-add-cart');
    
    formsAddCart.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Evita que la página recargue
            
            const formData = new FormData(this);
            formData.append('ajax', '1');

            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Muestra el mensaje verde
                    const toastMsg = document.getElementById('toast-msg');
                    const toast = document.getElementById('toast');
                    if(toastMsg && toast) {
                        toastMsg.textContent = data.message;
                        toast.classList.add('show');
                        setTimeout(() => toast.classList.remove('show'), 3000);
                    }

                    // Actualiza el HTML del carrito
                    const cartBody = document.querySelector('.cart-body');
                    if(cartBody && data.cart_html) {
                        cartBody.innerHTML = data.cart_html;
                    }

                    // Abre el carrito lateral automáticamente
                    setTimeout(() => {
                        const offcanvasCart = document.getElementById('offcanvasCart');
                        const cartOverlay = document.getElementById('cartOverlay');
                        if(offcanvasCart && cartOverlay) {
                            offcanvasCart.classList.add('active');
                            cartOverlay.classList.add('active');
                        }
                    }, 400);
                }
            })
            .catch(error => console.error('Error al agregar al carrito:', error));
        });
    });
});
</script>