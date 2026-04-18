<?php
// admin/index.php
session_start();
require_once '../config/database.php';

// Consultamos las órdenes
try {
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
    $error = "Asegúrate de haber creado la tabla 'orders' con la columna 'phone'.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Órdenes - Admin Glow & Beauty</title>
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
                <h1>Gestión de Órdenes</h1>
            </header>

            <div class="card">
                <?php if (isset($error)): ?>
                    <div style="background: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $order): 
                                $telefono_limpio = preg_replace('/[^0-9]/', '', $order['phone'] ?? '');
                                
                                if (strlen($telefono_limpio) == 9) {
                                    $telefono_limpio = '56' . $telefono_limpio;
                                }

                                $mensaje = urlencode("¡Hola " . ($order['customer_name'] ?? 'Hermosa') . "! 💖 Te contactamos de Glow & Beauty respecto a tu pedido #" . $order['id'] . ".");
                            ?>
                            <tr>
                                <td><strong>#<?= $order['id'] ?></strong></td>
                                <td><?= htmlspecialchars($order['customer_name'] ?? 'Sin Nombre') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')) ?></td>
                                <td>$<?= number_format($order['total'] ?? 0, 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge" style="background-color: #f39c12; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">
                                        <?= htmlspecialchars($order['status'] ?? 'Pendiente') ?>
                                    </span>
                                </td>
                                <td style="text-align: center; display: flex; justify-content: center; align-items: center; gap: 10px;">
                                    
                                    <?php if (!empty($telefono_limpio)): ?>
                                        <a href="https://wa.me/<?= $telefono_limpio ?>?text=<?= $mensaje ?>" target="_blank" title="Contactar por WhatsApp" style="background-color: #25D366; color: white; width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; text-decoration: none; transition: transform 0.2s;">
                                            <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #ccc; width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center;"><i class="fas fa-phone-slash"></i></span>
                                    <?php endif; ?>

                                    <a href="ver_orden.php?id=<?= $order['id'] ?>" class="btn-action" style="padding: 8px 12px; text-decoration: none; background-color: #3498db; color: white; border-radius: 4px;">Detalle</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px;">Aún no hay órdenes registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>