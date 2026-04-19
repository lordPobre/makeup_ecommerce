<?php
// admin/orden_detalle.php
session_start();
require_once '../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

// --- PROCESAR CAMBIO DE ESTADO E INVENTARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_estado'])) {
    $nuevo_estado = htmlspecialchars($_POST['nuevo_estado'], ENT_QUOTES, 'UTF-8');
    // Capturamos el número de seguimiento
    $nuevo_tracking = htmlspecialchars($_POST['tracking_number'] ?? '', ENT_QUOTES, 'UTF-8');
    
    $stmtCheck = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
    $stmtCheck->execute([$id]);
    $estado_anterior = $stmtCheck->fetchColumn();

    $pdo->beginTransaction();

    try {
        // Reintegrar stock si se cancela
        if ($estado_anterior !== 'cancelado' && $nuevo_estado === 'cancelado') {
            $stmtItemsCancel = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmtItemsCancel->execute([$id]);
            $itemsCancel = $stmtItemsCancel->fetchAll();

            $stmtRestore = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            foreach ($itemsCancel as $item) {
                $stmtRestore->execute([(int)$item['quantity'], (int)$item['product_id']]);
            }
        }
        // Descontar stock si sale de cancelado
        elseif ($estado_anterior === 'cancelado' && $nuevo_estado !== 'cancelado') {
            $stmtItemsDeduct = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmtItemsDeduct->execute([$id]);
            $itemsDeduct = $stmtItemsDeduct->fetchAll();

            $stmtDeduct = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            foreach ($itemsDeduct as $item) {
                $stmtDeduct->execute([(int)$item['quantity'], (int)$item['product_id']]);
            }
        }

        // Actualizamos el estado Y el tracking number
        $stmtUpdate = $pdo->prepare("UPDATE orders SET status = ?, tracking_number = ? WHERE id = ?");
        $stmtUpdate->execute([$nuevo_estado, $nuevo_tracking, $id]);

        $pdo->commit();
        header("Location: orden_detalle.php?id=$id&msg=actualizado");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error al actualizar estado y stock: " . $e->getMessage());
    }
}

// 1. Consultar la orden principal
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) { die("Orden no encontrada."); }

// 2. Consultar los productos
$stmtItems = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image_path 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

// --- LÓGICA DE WHATSAPP DEFINITIVA ---
$telefono_limpio = preg_replace('/[^0-9]/', '', $order['phone'] ?? '');
if (strlen($telefono_limpio) == 9) { $telefono_limpio = '56' . $telefono_limpio; }

$nombre_cliente = htmlspecialchars($order['customer_name'] ?? 'Cliente');
$id_pedido = $order['id'];
$estado_actual = $order['status'];
$num_tracking = $order['tracking_number'] ?? '';

// TRUCO: Definimos los emojis usando sus bytes exactos.
// Así es imposible que el editor de texto los corrompa.
$e_corazon = "\xE2\x9D\xA4\xEF\xB8\x8F"; // ❤️
$e_camion  = "\xF0\x9F\x9A\x9A";         // 🚚
$e_brillos = "\xE2\x9C\xA8";             // ✨

$texto_wa = "";

switch ($estado_actual) {
    case 'pagado':
        $texto_wa = "¡Hola " . $nombre_cliente . "! " . $e_corazon . " Te contactamos de Glow & Beauty respecto a tu pedido #" . $id_pedido . " para informarte que el pago de tu compra fue recibido con éxito. " . $e_brillos;
        break;

    case 'enviado':
        $info_tracking = !empty($num_tracking) ? " El número de seguimiento es: " . $num_tracking : "";
        $texto_wa = "¡Hola " . $nombre_cliente . "! " . $e_corazon . " Te contactamos de Glow & Beauty para avisarte que tu pedido #" . $id_pedido . " ya fue enviado. " . $e_camion . $info_tracking;
        break;

    case 'entregado':
        $texto_wa = "¡Hola " . $nombre_cliente . "! " . $e_corazon . " Te contactamos de Glow & Beauty respecto a tu pedido #" . $id_pedido . " para que nos confirmes que fue bien recibido. ¡Disfruta tus productos! " . $e_brillos;
        break;

    case 'cancelado':
        $texto_wa = "¡Hola " . $nombre_cliente . "! " . $e_corazon . " Te contactamos de Glow & Beauty respecto a tu pedido #" . $id_pedido . " para informarte sobre la cancelación de tu orden.";
        break;

    default: // Pendiente
        $texto_wa = "¡Hola " . $nombre_cliente . "! " . $e_corazon . " Te contactamos de Glow & Beauty respecto a tu pedido #" . $id_pedido . ". Estamos atentos para ayudarte a completar tu compra.";
        break;
}

// Usamos rawurlencode, que es el formato más seguro para enlaces de WhatsApp
$mensaje_url = rawurlencode($texto_wa);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Orden #<?= $order['id'] ?></title>
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
                <h1>Detalle de Orden #<?= $order['id'] ?></h1>
                <a href="index.php" class="btn-action" style="background:#7f8c8d; text-decoration: none;">&larr; Volver</a>
            </header>

            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                
                <div class="card" style="flex: 1; min-width: 300px; border-left: 5px solid #f39c12;">
                    <h3>Estado del Pedido</h3>
                    <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
                    
                    <form method="POST" style="margin-bottom: 20px;">
                        <label>Cambiar Estado:</label>
                        <select name="nuevo_estado" style="width: 100%; padding: 10px; margin: 10px 0; border-radius: 4px; border: 1px solid #ddd;">
                            <option value="pendiente" <?= $order['status'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="pagado" <?= $order['status'] == 'pagado' ? 'selected' : '' ?>>Pagado</option>
                            <option value="enviado" <?= $order['status'] == 'enviado' ? 'selected' : '' ?>>Enviado / En camino</option>
                            <option value="entregado" <?= $order['status'] == 'entregado' ? 'selected' : '' ?>>Entregado</option>
                            <option value="cancelado" <?= $order['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>

                        <label style="margin-top: 10px; display: block;">Número de Seguimiento (Opcional):</label>
                        <input type="text" name="tracking_number" placeholder="Ej: 123456789" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>" style="width: 100%; padding: 10px; margin: 10px 0 15px; border-radius: 4px; border: 1px solid #ddd; box-sizing: border-box;">

                        <button type="submit" class="btn-action" style="width: 100%; background: #2c3e50; border: none; cursor: pointer;">Actualizar Estado y Tracking</button>
                    </form>

                    <?php if (!empty($telefono_limpio)): ?>
                        <a href="https://wa.me/<?= $telefono_limpio ?>?text=<?= $mensaje_url ?>" target="_blank" 
                           style="display: block; text-align: center; background: #25D366; color: white; padding: 12px; border-radius: 4px; text-decoration: none; font-weight: bold;">
                            <i class="fab fa-whatsapp"></i> Notificar Cliente
                        </a>
                        <p style="font-size: 0.8rem; color: #666; margin-top: 5px; text-align: center;">El mensaje cambiará según el estado seleccionado.</p>
                    <?php endif; ?>
                </div>

                <div class="card" style="flex: 1; min-width: 300px;">
                    <h3>Datos del Cliente</h3>
                    <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                    <p><strong>RUT:</strong> <?= htmlspecialchars($order['rut'] ?: 'No registrado') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                    <p><strong>Dirección:</strong> <?= htmlspecialchars($order['address']) ?></p>
                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($order['phone'] ?: 'No registrado') ?></p>
                </div>

                <div class="card" style="flex: 2; min-width: 100%;">
                    <h3>Productos Comprados</h3>
                    <table class="data-table" style="width: 100%; margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cant.</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>$<?= number_format($item['price'], 0, ',', '.') ?></td>
                                <td>$<?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                           <tr>
                                <td colspan="3" style="text-align: right; padding: 10px; border-top: 2px solid #eee;"><strong>Subtotal Productos:</strong></td>
                                <td style="text-align: right; padding: 10px; border-top: 2px solid #eee;">
                                    $<?= number_format($order['total'] - ($order['shipping_cost'] ?? 0), 0, ',', '.') ?>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="3" style="text-align: right; padding: 10px; color: #666;"><strong>Costo de Envío:</strong></td>
                                <td style="text-align: right; padding: 10px; color: #666;">
                                    $<?= number_format($order['shipping_cost'] ?? 0, 0, ',', '.') ?>
                                </td>
                            </tr>

                            <tr style="font-size: 1.2rem; background: #f9f9f9;">
                                <td colspan="3" style="text-align: right; padding: 15px;">
                                    <strong style="text-transform: uppercase; letter-spacing: 1px;">Total Final:</strong>
                                </td>
                                <td style="text-align: right; padding: 15px;">
                                    <strong style="color: #1a1a1a;">$<?= number_format($order['total'], 0, ',', '.') ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>