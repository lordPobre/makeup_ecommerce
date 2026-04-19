<?php
// public/process_checkout.php
session_start();
require_once '../config/database.php';

// 1. VALIDACIONES INICIALES
if (empty($_SESSION['cart']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// 2. CAPTURA Y LIMPIEZA DE DATOS
$customer_name = htmlspecialchars(trim($_POST['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$rut           = htmlspecialchars(trim($_POST['rut'] ?? ''), ENT_QUOTES, 'UTF-8');
$email         = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone         = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
$address_input = htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES, 'UTF-8');
$city_input    = htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8');
$region        = $_POST['region'] ?? '';

$full_address  = $address_input . ', ' . $city_input . ' (' . $region . ')';

// 3. LÓGICA DE COSTOS DE ENVÍO
$costos_envio = [
    'Metropolitana' => 3500,
    'Valparaiso'    => 5500,
    'Otras'         => 7500
];
$shipping_cost = $costos_envio[$region] ?? 0;

// 4. CALCULAR TOTAL DE PRODUCTOS Y PREPARAR DATOS
$product_subtotal = 0;
$items_para_guardar = [];

// Preparamos la consulta para obtener el precio real de la BD (seguridad)
$stmtProd = $pdo->prepare("SELECT id, price, stock FROM products WHERE id = ?");

foreach ($_SESSION['cart'] as $cart_key => $item) {
    // Extraemos el ID real (recordar que la llave puede ser "12_default" o "12_rojo")
    $parts = explode('_', $cart_key);
    $product_id = (int)$parts[0];

    $stmtProd->execute([$product_id]);
    $productoBD = $stmtProd->fetch();

    if ($productoBD) {
        $cantidad = (int)$item['quantity'];
        $precio_unitario = (int)$productoBD['price'];
        
        $product_subtotal += ($precio_unitario * $cantidad);

        $items_para_guardar[] = [
            'product_id' => $productoBD['id'],
            'quantity'   => $cantidad,
            'price'      => $precio_unitario,
            'tone'       => $item['tone'] ?? ''
        ];
    }
}

// 5. CÁLCULO DEL TOTAL FINAL
$total_final = $product_subtotal + $shipping_cost;

// 6. GUARDAR TODO EN UNA SOLA TRANSACCIÓN (Súper Seguro)
if ($customer_name && $rut && $email && $product_subtotal > 0) {
    try {
        $pdo->beginTransaction();

        // A. Insertar la Orden Principal
        $stmtOrder = $pdo->prepare("
            INSERT INTO orders (customer_name, rut, email, address, phone, total, shipping_cost, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')
        ");
        $stmtOrder->execute([
            $customer_name, 
            $rut, 
            $email, 
            $full_address, 
            $phone, 
            $total_final, 
            $shipping_cost
        ]);
        
        $order_id = $pdo->lastInsertId();

        // B. Insertar los Items y Actualizar Stock uno por uno
        $stmtItem = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price, tone) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmtUpdateStock = $pdo->prepare("
            UPDATE products SET stock = stock - ? WHERE id = ?
        ");

        foreach ($items_para_guardar as $item) {
            // Guardamos el detalle
            $stmtItem->execute([
                $order_id, 
                $item['product_id'], 
                $item['quantity'], 
                $item['price'],
                $item['tone']
            ]);

            // Descontamos del inventario
            $stmtUpdateStock->execute([
                $item['quantity'], 
                $item['product_id']
            ]);
        }

        // C. Si todo salió bien, confirmamos los cambios
        $pdo->commit();

        // Limpiamos el carrito
        unset($_SESSION['cart']);

        // Redirigimos al éxito (Usamos el parámetro que configuramos en tu index)
        header('Location: index.php?compra=exitosa');
        exit;

    } catch (PDOException $e) {
        // Si algo falla, deshacemos todo para no dejar órdenes incompletas
        $pdo->rollBack();
        die("Error crítico al procesar la compra: " . $e->getMessage());
    }
} else {
    // Si faltan datos o el carrito está vacío por error
    die("Error: Datos incompletos. Por favor, asegúrate de llenar todos los campos y seleccionar tu región.");
}
?>