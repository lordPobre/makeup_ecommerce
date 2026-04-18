<?php
// admin/editar_producto.php
session_start();
require_once '../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: productos.php');
    exit;
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $is_cruelty_free = isset($_POST['is_cruelty_free']) ? 1 : 0;
    $available = isset($_POST['available']) ? 1 : 0;
    $tones = $_POST['tones'] ?? ''; 
    
    // CAPTURAR EL ID DE LA MARCA
    $brand_id = filter_input(INPUT_POST, 'brand_id', FILTER_VALIDATE_INT) ?: null;

    $price_input = $_POST['price'] ?? '';
    $price = (int) preg_replace('/[^0-9]/', '', $price_input);

    if (empty($name) || empty($category_id) || $price <= 0) {
        $mensaje = "Por favor, completa los campos obligatorios.";
    } else {
        $update_image_sql = "";
        
        // AGREGAR $brand_id A LA LISTA DE PARÁMETROS
        $params = [$name, $category_id, $price, $description, $is_cruelty_free, $available, $tones, $brand_id];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../public/img/';
            $file_name = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $update_image_sql = ", image_path = ?";
                $params[] = 'img/' . $file_name; 
            }
        }

        $params[] = $id; 

        try {
            // AÑADIR brand_id = ? EN LA CONSULTA SQL
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, category_id = ?, price = ?, description = ?, is_cruelty_free = ?, available = ?, tones = ?, brand_id = ? $update_image_sql
                WHERE id = ?
            ");
            $stmt->execute($params);
            
            header('Location: productos.php?updated=1');
            exit;
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar: " . $e->getMessage();
        }
    }
}

// CORREGIDO: Sintaxis correcta para el array del execute
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: productos.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
// TRAER LAS MARCAS PARA EL SELECTOR
$brands = $pdo->query("SELECT * FROM brands")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - Admin</title>
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
                    <li><a href="productos.php" class="active">Productos</a></li>
                    <li><a href="categorias.php">Categorías</a></li>
                    <li><a href="marcas.php">Marcas</a></li>
                    <li><a href="../public/index.php" target="_blank">Ver Tienda</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1>Editar: <?= htmlspecialchars($product['name']) ?></h1>
                <a href="productos.php" class="btn-action" style="background:#7f8c8d;">&larr; Volver</a>
            </header>

            <div class="card" style="max-width: 600px;">
                <?php if ($mensaje): ?>
                    <div style="background: #e74c3c; color: white; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                        <?= $mensaje ?>
                    </div>
                <?php endif; ?>

                <form action="editar_producto.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">

                    <div style="margin-bottom: 15px;">
                        <label>Nombre del Producto</label><br>
                        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label>Categoría</label><br>
                        <select name="category_id" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $product['category_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Marca</label><br>
                        <select name="brand_id" style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">Sin marca específica...</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($b['id'] == $product['brand_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Precio (CLP)</label><br>
                        <input type="number" name="price" value="<?= $product['price'] ?>" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Tonos Disponibles</label><br>
                        <input type="text" name="tones" value="<?= htmlspecialchars($product['tones'] ?? '') ?>" placeholder="Ej: Natural, Sand, Honey" style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Foto del Producto</label><br>
                        <input type="file" name="image" accept="image/*" style="margin-top: 5px; margin-bottom: 10px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Descripción</label><br>
                        <textarea name="description" rows="4" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_cruelty_free" value="1" <?= $product['is_cruelty_free'] ? 'checked' : '' ?>>
                            Es Cruelty Free 🐰
                        </label>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="available" value="1" <?= $product['available'] ? 'checked' : '' ?>>
                            Producto Activo
                        </label>
                    </div>

                    <button type="submit" class="btn-action" style="background-color: #f39c12; width: 100%; padding: 12px; font-size: 1rem; border: none; cursor: pointer;">Actualizar Producto</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>