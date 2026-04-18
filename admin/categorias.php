<?php
session_start();
require_once '../config/database.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        $mensaje = "<div style='background: #2ecc71; color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;'>Categoría agregada con éxito.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div style='background: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;'>Error: " . $e->getMessage() . "</div>";
    }
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías - Admin Glow & Beauty</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="brand">G&B Admin</div>
            <nav>
                <ul>
                    <li><a href="index.php">Órdenes</a></li>
                    <li><a href="productos.php">Productos</a></li>
                    <li><a href="categorias.php">Categorías</a></li>
                    <li><a href="marcas.php">Marcas</a></li>
                    <li><a href="../public/index.php" target="_blank">Ver Tienda</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1>Gestión de Categorías</h1>
            </header>

            <?= $mensaje ?>

            <div style="display: flex; gap: 30px; align-items: flex-start;">
                <div class="card" style="flex: 1; min-width: 250px;">
                    <h3>Nueva Categoría</h3>
                    <form action="categorias.php" method="POST" style="margin-top: 15px;">
                        <label style="display: block; margin-bottom: 8px;">Nombre (Ej: Skincare, Brochas)</label>
                        <input type="text" name="name" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; box-sizing: border-box;">
                        
                        <button type="submit" class="btn-action" style="background-color: #3498db; width: 100%; padding: 10px; border: none; cursor: pointer;">Guardar Categoría</button>
                    </form>
                </div>

                <div class="card" style="flex: 2; min-width: 300px;">
                    <h3>Categorías Activas</h3>
                    <table class="data-table" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>#<?= $cat['id'] ?></td>
                                <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>