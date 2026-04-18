<?php
// admin/orden_detalle.php
session_start();
require_once '../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

// 1. Consultar la orden principal
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    echo "Orden no encontrada.";
    exit;
}

// 2. Consultar los productos de esta orden (Agregamos p.price por si oi.price no existe)
$items = [];
try {
    $stmtItems = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.image_path, p.price as current_product_price 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmtItems->execute([$id]);
    $items = $stmtItems->fetchAll();
} catch (PDOException $e) {
    $error_items = "Nota: Asegúrate de tener creada la tabla 'order_items' para ver los productos de la compra.";
}

// Lógica de WhatsApp
$telefono_limpio = preg_replace('/[^0-9]/', '', $order['phone'] ?? '');
if (strlen($telefono_limpio) == 9) {
    $telefono_limpio = '56' . $telefono_limpio;
}
$mensaje = urlencode("¡Hola " . ($order['customer_name'] ?? '') . "! 💖 Te escribimos de Glow & Beauty respecto a tu pedido #" . $order['id'] . ".");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Orden #<?= $order['id'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="brand">G&B Admin</div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Órdenes</a></li>
                    <li><a href="productos.php">Productos</a></li>
                    <li><a href="categorias.php">Categorías</a></li>
                    <li><a href="marcas.php">Marcas</a></li>
                    <li><a href="../public/index.php" target="_blank">Ver Tienda</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1>Orden #<?= $order['id'] ?></h1>
                <a href="index.php" class="btn-action" style="background:#7f8c8d; text-decoration: none;">&larr; Volver</a>
            </header>

            <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
                
                <div class="card" style="flex: 1; min-width: 300px;">
                    <h3>Datos del Cliente</h3>
                    <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
                    
                    <p style="margin-bottom: 10px;"><strong>Nombre:</strong> <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
                    <p style="margin-bottom: 10px;"><strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? 'N/A') ?></p>
                    
                    <p style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                        <strong>Teléfono:</strong> <?= !empty($order['phone']) ? htmlspecialchars($order['phone']) : 'No registrado' ?> 
                        <?php if (!empty($telefono_limpio)): ?>
                            <a href="https://wa.me/<?= $telefono_limpio ?>?text=<?= $mensaje ?>" target="_blank" style="color: #25D366; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-size: 0.9rem;">
                                <i class="fab fa-whatsapp"></i> Enviar Mensaje
                            </a>
                        <?php endif; ?>
                    </p>
                    
                    <p style="margin-bottom: 10px;"><strong>Dirección:</strong> <?= htmlspecialchars($order['address'] ?? 'N/A') ?></p>
                    <p style="margin-bottom: 10px;"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')) ?></p>
                    
                    <div style="margin-top: 25px;">
                        <span class="badge" style="background-color: #f39c12; color: white; padding: 6px 12px; font-size: 1rem; border-radius: 4px;">
                            Estado: <?= htmlspecialchars($order['status'] ?? 'Pendiente') ?>
                        </span>
                    </div>
                </div>

                <div class="card" style="flex: 2; min-width: 400px;">
                    <h3>Productos en esta Orden</h3>
                    <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

                    <?php if (isset($error_items)): ?>
                        <div style="background: #f39c12; color: white; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                            <?= $error_items ?>
                        </div>
                    <?php endif; ?>

                    <table class="data-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #f4efe9;">
                                <th style="text-align: left; padding: 10px;">Producto</th>
                                <th style="text-align: center; padding: 10px;">Cant.</th>
                                <th style="text-align: right; padding: 10px;">Precio Unit.</th>
                                <th style="text-align: right; padding: 10px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): 
                                // Captura el precio de manera segura
                                $precio_unitario = $item['price'] ?? $item['current_product_price'] ?? 0;
                                $cantidad = $item['quantity'] ?? 1;
                                $subtotal = $precio_unitario * $cantidad;
                            ?>
                                <tr style="border-bottom: 1px solid #f4efe9;">
                                    <td style="padding: 15px 10px;">
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <?php if (!empty($item['image_path'])): ?>
                                                <img src="../public/<?= htmlspecialchars($item['image_path']) ?>" alt="img" style="width: 50px; height: 50px; object-fit: contain; border-radius: 4px; background: #fff;">
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($item['product_name'] ?? 'Producto Desconocido') ?></span>
                                        </div>
                                    </td>
                                    <td style="text-align: center; padding: 10px;"><?= $cantidad ?></td>
                                    <td style="text-align: right; padding: 10px;">$<?= number_format($precio_unitario, 0, ',', '.') ?></td>
                                    <td style="text-align: right; padding: 10px;"><strong>$<?= number_format($subtotal, 0, ',', '.') ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right; padding-top: 25px; font-size: 1.1rem;"><strong>Total Pagado:</strong></td>
                                <td style="text-align: right; padding-top: 25px; font-size: 1.3rem; color: #1a1a1a;"><strong>$<?= number_format($order['total'] ?? 0, 0, ',', '.') ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>