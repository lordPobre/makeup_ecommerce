<?php
// public/add_to_cart.php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Averiguar si estamos agregando un producto nuevo o actualizando la cantidad
    $action = $_POST['action'] ?? 'add';

    if ($action === 'add') {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        $tone = htmlspecialchars(trim($_POST['tone'] ?? ''), ENT_QUOTES, 'UTF-8');

        if ($product_id && $quantity) {
            if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

            $cart_key = $product_id . '_' . ($tone ?: 'default');

            if (isset($_SESSION['cart'][$cart_key])) {
                $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$cart_key] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'tone' => $tone
                ];
            }
        }
    } elseif ($action === 'update') {
        // Lógica para modificar (+1 o -1) desde el carrito lateral
        $cart_key = $_POST['cart_key'] ?? '';
        $change = (int)($_POST['change'] ?? 0);

        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]['quantity'] += $change;
            
            // Si la cantidad llega a 0, eliminamos el producto del bolso
            if ($_SESSION['cart'][$cart_key]['quantity'] <= 0) {
                unset($_SESSION['cart'][$cart_key]);
            }
        }
    }
    
    // --- LÓGICA AJAX ---
    if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
        header('Content-Type: application/json');
        
        $total_items = 0;
        $total_price = 0;
        $cart_html = '';

        if (!empty($_SESSION['cart'])) {
            $stmtCart = $pdo->prepare("SELECT id, name, price, image_path FROM products WHERE id = ?");
            
            foreach ($_SESSION['cart'] as $key => $item) {
                $total_items += $item['quantity'];
                
                $stmtCart->execute([$item['product_id']]);
                $prod = $stmtCart->fetch();
                
                if ($prod) {
                    $subtotal = $prod['price'] * $item['quantity'];
                    $total_price += $subtotal;
                    
                    $img = strpos($prod['image_path'], 'http') === 0 ? $prod['image_path'] : $prod['image_path'];
                    
                    // Diseño del producto CON los controles de cantidad
                    $cart_html .= '
                    <div style="display: flex; gap: 15px; margin-bottom: 15px; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px;">
                        <img src="'.htmlspecialchars($img).'" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #eee;">
                        <div style="flex: 1;">
                            <h4 style="margin: 0; font-size: 0.85rem; color: #1a1a1a;">'.htmlspecialchars($prod['name']).'</h4>
                            '.($item['tone'] ? '<p style="margin: 3px 0 0; color: #888; font-size: 0.75rem;">Tono: '.$item['tone'].'</p>' : '').'
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                <div style="display: flex; align-items: center; border: 1px solid #e0e0e0; border-radius: 4px; overflow: hidden;">
                                    <button class="update-qty-btn" data-key="'.htmlspecialchars($key).'" data-change="-1" style="background: #fff; border: none; padding: 4px 10px; cursor: pointer; color: #666; font-weight: bold; border-right: 1px solid #e0e0e0;">-</button>
                                    <span style="padding: 0 10px; font-size: 0.85rem; font-weight: 600; min-width: 25px; text-align: center; color: #1a1a1a;">'.$item['quantity'].'</span>
                                    <button class="update-qty-btn" data-key="'.htmlspecialchars($key).'" data-change="1" style="background: #fff; border: none; padding: 4px 10px; cursor: pointer; color: #666; font-weight: bold; border-left: 1px solid #e0e0e0;">+</button>
                                </div>
                                <p style="margin: 0; font-weight: 600; font-size: 0.9rem; color: #1a1a1a;">$'.number_format($subtotal, 0, ',', '.').'</p>
                            </div>
                        </div>
                    </div>';
                }
            }
            
            // Total a pagar
            $cart_html .= '
            <div style="margin-top: 20px; display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 600; color: #1a1a1a; font-family: \'Montserrat\', sans-serif;">
                <span>Total:</span>
                <span>$'.number_format($total_price, 0, ',', '.').'</span>
            </div>';
        } else {
            // Si el carrito queda vacío (porque restó todos los productos)
            $cart_html = '
            <div style="text-align: center; margin-top: 60px; font-family: \'Montserrat\', sans-serif;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#1a1a1a" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 20px; opacity: 0.8;">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <p style="font-size: 1rem; color: #1a1a1a; font-weight: 600; margin-bottom: 5px;">Tu bolso está vacío</p>
                <p style="font-size: 0.85rem; color: #888;">Los productos que elijas aparecerán aquí.</p>
            </div>';
        }

        echo json_encode([
            'status' => 'success',
            'message' => ($action === 'add' ? 'Producto agregado ✨' : 'Bolso actualizado'),
            'cart_count' => $total_items,
            'cart_html' => $cart_html
        ]);
        exit;
    }
    
    header('Location: index.php?added=success');
    exit;
}
?>