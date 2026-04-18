<?php
// public/process_checkout.php
session_start();
require_once '../config/database.php';

// Si el carrito está vacío o entran directo, los devolvemos al inicio (no a la página de éxito)
if (empty($_SESSION['cart']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// 1. Capturar y limpiar datos del formulario
$customer_name = htmlspecialchars(trim($_POST['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$rut = htmlspecialchars(trim($_POST['rut'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');

$address_input = htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES, 'UTF-8');
$city_input = htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8');
$full_address = $address_input . ', ' . $city_input;

// 2. Calcular TOTAL y preparar items consultando la BD
$total = 0;
$items_para_guardar = [];

// Preparamos la consulta de precio una sola vez fuera del bucle
$stmtPrice = $pdo->prepare("SELECT id, price FROM products WHERE id = ?");

foreach ($_SESSION['cart'] as $id_carrito => $item) {
    // Forzamos que el ID sea un entero
    $product_id_clean = (int)$id_carrito;
    
    if ($product_id_clean <= 0) continue; // Saltamos si el ID no es válido

    $stmtPrice->execute([$product_id_clean]);
    $productoBD = $stmtPrice->fetch();

    if ($productoBD) {
        $precio_real = (int)$productoBD['price'];
        $cantidad = (int)$item['quantity'];
        
        $total += ($precio_real * $cantidad);

        $items_para_guardar[] = [
            'product_id' => $productoBD['id'],
            'quantity' => $cantidad,
            'price' => $precio_real
        ];
    }
}

// 3. Validar y Guardar (Agregamos $rut a la validación)
if ($customer_name && $rut && $email && $full_address && $total > 0) {
    try {
        $pdo->beginTransaction();

        // 1. Guardar Orden Principal
        $stmtOrder = $pdo->prepare("
            INSERT INTO orders (customer_name, rut, email, address, phone, total, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pendiente')
        ");
        $stmtOrder->execute([$customer_name, $rut, $email, $full_address, $phone, $total]);
        $order_id = (int)$pdo->lastInsertId();

        // 2. Guardar Detalle y DESCONTAR STOCK
        $stmtItem = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        
        // Nueva consulta preparada para descontar el stock
        $stmtUpdateStock = $pdo->prepare("
            UPDATE products SET stock = stock - ? WHERE id = ?
        ");

        foreach ($items_para_guardar as $itemSeguro) {
            $cantidad_comprada = (int)$itemSeguro['quantity'];
            $id_producto = (int)$itemSeguro['product_id'];

            // Insertamos en el detalle
            $stmtItem->execute([
                $order_id, 
                $id_producto, 
                $cantidad_comprada, 
                (int)$itemSeguro['price']
            ]);

            // Descontamos del inventario
            $stmtUpdateStock->execute([$cantidad_comprada, $id_producto]);
        }

        $pdo->commit();
        unset($_SESSION['cart']);

        header('Location: exito.php');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error crítico de base de datos: " . $e->getMessage());
    }
} else {
    die("Error: Datos incompletos. Por favor, revisa que todos los campos, incluyendo el RUT, estén llenos.");
}
?>