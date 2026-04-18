<?php
// public/process_checkout.php
session_start();
require_once '../config/database.php';

if (empty($_SESSION['cart']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: exito.php');
    exit;
}

// 1. Capturar y limpiar datos del formulario
$customer_name = htmlspecialchars(trim($_POST['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');

$address_input = htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES, 'UTF-8');
$city_input = htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8');
$full_address = $address_input . ', ' . $city_input;

// 2. Calcular TOTAL y preparar items consultando la BD
$total = 0;
$items_para_guardar = [];

// Preparamos la consulta de precio una sola vez fuera del bucle (más eficiente)
$stmtPrice = $pdo->prepare("SELECT id, price FROM products WHERE id = ?");

foreach ($_SESSION['cart'] as $id_carrito => $item) {
    // Forzamos que el ID sea un entero para evitar el error de "Data truncated"
    $product_id_clean = (int)$id_carrito;
    
    if ($product_id_clean <= 0) continue; // Saltamos si el ID no es válido

    $stmtPrice->execute([$product_id_clean]);
    $productoBD = $stmtPrice->fetch();

    if ($productoBD) {
        $precio_real = (int)$productoBD['price']; // Forzamos a entero (pesos chilenos)
        $cantidad = (int)$item['quantity'];
        
        $total += ($precio_real * $cantidad);

        $items_para_guardar[] = [
            'product_id' => $productoBD['id'], // Usamos el ID que viene de la BD para mayor seguridad
            'quantity' => $cantidad,
            'price' => $precio_real
        ];
    }
}

// 3. Validar y Guardar
if ($customer_name && $email && $full_address && $total > 0) {
    try {
        $pdo->beginTransaction();

        // Guardar Orden Principal
        $stmtOrder = $pdo->prepare("
            INSERT INTO orders (customer_name, email, address, phone, total, status) 
            VALUES (?, ?, ?, ?, ?, 'pendiente')
        ");
        $stmtOrder->execute([$customer_name, $email, $full_address, $phone, $total]);
        
        $order_id = (int)$pdo->lastInsertId();

        // Guardar Detalle de la Orden
        $stmtItem = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($items_para_guardar as $itemSeguro) {
            $stmtItem->execute([
                $order_id, 
                (int)$itemSeguro['product_id'], 
                (int)$itemSeguro['quantity'], 
                (int)$itemSeguro['price']
            ]);
        }

        $pdo->commit();
        unset($_SESSION['cart']);

        header('Location: index.php?compra=exitosa');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        // Esto te dará una pista más clara si algo falla
        die("Error crítico de base de datos: " . $e->getMessage());
    }
} else {
    die("Error: Datos incompletos o carrito vacío. Por favor, revisa tus datos de envío.");
}