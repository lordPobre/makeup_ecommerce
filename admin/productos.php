<?php
session_start();
require_once '../config/database.php';

$stmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - Admin Glow & Beauty</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="brand">G&B Admin</div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Dashboard</a></li>
                    <li><a href="productos.php">Productos</a></li>
                    <li><a href="categorias.php">Categorías</a></li>
                    <li><a href="marcas.php">Marcas</a></li>
                    <li><a href="../public/index.php" target="_blank">Ver Tienda</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar" style="display: flex; justify-content: space-between; align-items: center;">
                <h1>Catálogo de Maquillaje</h1>
                <a href="crear_producto.php" class="btn-action" style="background-color: #2ecc71;">+ Agregar Producto</a>
            </header>

            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th style="text-align: center;">Stock</th>
                            <th>Cruelty Free</th>
                            <th>Estado</th>
                            <th>Acciones</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php $img_src = strpos($product['image_path'], 'http') === 0 ? $product['image_path'] : '../public/' . $product['image_path']; ?>
                                <img src="<?= htmlspecialchars($img_src) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            </td>
                            <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                            <td>$<?= number_format($product['price'], 0, ',', '.') ?></td>
                            
                            <td style="text-align: center;">
                                <?php if ($product['stock'] > 0): ?>
                                    <span style="color: #2ecc71; font-weight: 600; background: #e8f8f5; padding: 4px 8px; border-radius: 12px; font-size: 0.85rem;">
                                        <?= $product['stock'] ?> unid.
                                    </span>
                                <?php else: ?>
                                    <span style="color: #e74c3c; font-weight: 600; background: #fdedec; padding: 4px 8px; border-radius: 12px; font-size: 0.85rem;">
                                        Agotado (0)
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td><?= $product['is_cruelty_free'] ? '🐰 Sí' : 'No' ?></td>
                            <td>
                                <span class="badge <?= $product['available'] ? 'badge-pagado' : 'badge-pendiente' ?>" style="<?= $product['available'] ? 'background-color: #2ecc71; color: white;' : 'background-color: #95a5a6; color: white;' ?> padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">
                                    <?= $product['available'] ? 'Activo' : 'Oculto' ?>
                                </span>
                            </td>
                            <td>
                                <a href="editar_producto.php?id=<?= $product['id'] ?>" class="btn-action" style="background-color: #f39c12; padding: 5px 10px; font-size: 0.8rem; text-decoration: none; border-radius: 4px; color: white;">✏️ Editar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>