<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-section brand-section">
            <h4>Glow & Beauty</h4>
            <p>Tu belleza, tus reglas. Cosmética de alta gama para resaltar tu luz natural todos los días.</p>
        </div>
        
        <div class="footer-section">
            <h4>Enlaces Útiles</h4>
            <ul>
                <li><a href="nosotros.php">Sobre Nosotros</a></li>
                <li><a href="preguntas.php">Preguntas Frecuentes</a></li>
                <li><a href="terminos.php">Términos y Condiciones</a></li>
                <li><a href="envios.php">Envíos y Devoluciones</a></li>
            </ul>
        </div>

        <div id="toast" class="toast">
            <i class="fas fa-check-circle" style="color: #2ecc71;"></i>
            <span id="toast-msg">Producto agregado</span>
        </div>

        <div class="cart-overlay" id="cartOverlay"></div>
        <div class="offcanvas-cart" id="offcanvasCart">
            <div class="cart-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #f0f0f0;">
                <h2 style="color: #1a1a1a; font-size: 1.3rem; margin: 0; font-weight: 600; letter-spacing: 0.5px; font-family: 'Montserrat', sans-serif;">Mi Bolso</h2>
                <button class="close-cart" id="closeCart" style="background: none; border: none; font-size: 2rem; cursor: pointer; color: #999; line-height: 1; outline: none;">&times;</button>
            </div>
            <div class="cart-body">
                <div style="text-align: center; margin-top: 60px; font-family: 'Montserrat', sans-serif;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#1a1a1a" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 20px; opacity: 0.8;">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <p style="font-size: 1rem; color: #1a1a1a; font-weight: 600; margin-bottom: 5px;">Tu bolso está vacío</p>
                    <p style="font-size: 0.85rem; color: #888;">Los productos que elijas aparecerán aquí.</p>
                </div>
            </div>
            <div class="cart-footer" style="padding: 20px; border-top: 1px solid #f0f0f0; background: #fafafa;">
                <a href="carrito.php" class="btn-action" style="background: #1a1a1a; width: 100%; display: block; text-align: center; text-decoration: none; padding: 15px; border-radius: 4px; color: white; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; font-family: 'Montserrat', sans-serif;">Ir al Checkout</a>
            </div>
        </div>
        <div class="footer-section">
            <h4>Síguenos</h4>
            <div class="social-icons">
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" title="TikTok"><i class="fab fa-tiktok"></i></a>
                <a href="#" title="Pinterest"><i class="fab fa-pinterest-p"></i></a>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Glow & Beauty. Todos los derechos reservados.</p>
    </div>
</footer>

<style>
    /* El Toast (Notificación) */
    .toast { position: fixed; bottom: -100px; right: 20px; background: #fff; color: #1a1a1a; padding: 15px 25px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 10px; font-family: 'Montserrat', sans-serif; font-weight: 600; transition: bottom 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55); z-index: 9999; }
    .toast.show { bottom: 30px; }

    /* El Overlay (Fondo oscuro) */
    .cart-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); opacity: 0; visibility: hidden; transition: all 0.3s ease; z-index: 1000; }
    .cart-overlay.active { opacity: 1; visibility: visible; }

    /* El Carrito Lateral */
    .offcanvas-cart { position: fixed; top: 0; right: -400px; width: 100%; max-width: 380px; height: 100vh; background: #fff; box-shadow: -5px 0 30px rgba(0,0,0,0.1); transition: right 0.4s ease; z-index: 1001; display: flex; flex-direction: column; font-family: 'Montserrat', sans-serif; }
    .offcanvas-cart.active { right: 0; }
    .cart-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #f0f0f0; }
    .close-cart { background: none; border: none; font-size: 2rem; cursor: pointer; color: #999; transition: color 0.2s; }
    .close-cart:hover { color: #1a1a1a; }
    .cart-body { padding: 20px; flex-grow: 1; overflow-y: auto; }
    .cart-footer { padding: 20px; border-top: 1px solid #f0f0f0; background: #fafafa; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('.form-add-cart');
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-msg');
    
    const offcanvas = document.getElementById('offcanvasCart');
    const overlay = document.getElementById('cartOverlay');
    const closeBtn = document.getElementById('closeCart');

    const openCart = () => {
        offcanvas.classList.add('active');
        overlay.classList.add('active');
    }

    const closeCart = () => {
        offcanvas.classList.remove('active');
        overlay.classList.remove('active');
    }

    closeBtn.addEventListener('click', closeCart);
    overlay.addEventListener('click', closeCart);

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');

            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    // 1. Mostrar Notificación Toast
                    toastMsg.textContent = data.message;
                    toast.classList.add('show');
                    
                    // 2. ACTUALIZAR EL INTERIOR DEL CARRITO LATERAL
                    if(data.cart_html) {
                        document.querySelector('.cart-body').innerHTML = data.cart_html;
                    }
                    
                    setTimeout(() => {
                        toast.classList.remove('show');
                    }, 3000); 

                    // 3. Abrir el carrito
                    setTimeout(openCart, 500);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
// --- LÓGICA PARA LOS BOTONES + y - DEL CARRITO LATERAL ---
document.querySelector('.cart-body').addEventListener('click', function(e) {
    // Detectamos si se hizo clic en un botón de actualizar cantidad (o dentro de él)
    const btn = e.target.closest('.update-qty-btn');
    
    if (btn) {
        const cartKey = btn.getAttribute('data-key');
        const change = btn.getAttribute('data-change');

        const formData = new FormData();
        formData.append('ajax', '1');
        formData.append('action', 'update'); // Le decimos al PHP que queremos modificar
        formData.append('cart_key', cartKey);
        formData.append('change', change);

        // Deshabilitar temporalmente el botón para evitar doble clic rápido
        btn.style.opacity = '0.5';
        btn.style.pointerEvents = 'none';

        fetch('add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success' && data.cart_html) {
                // Actualizamos el interior del carrito con la nueva cantidad o el SVG vacío
                document.querySelector('.cart-body').innerHTML = data.cart_html;
            }
        })
        .catch(error => console.error('Error:', error));
    }
});
});
</script>

</body>
</html>