<?php
// public/add_to_cart.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $tone = filter_input(INPUT_POST, 'tone', FILTER_SANITIZE_STRING);

    if ($product_id && $quantity) {
        // Inicializar el carrito si no existe
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Crear un identificador único para el producto + tono
        // Esto permite comprar la misma base en dos tonos distintos
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
    
    // Redirigir de vuelta al catálogo o al carrito
    header('Location: index.php?added=success');
    exit;
}
?>