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
    // Limpieza de datos
    $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $brand_id = filter_input(INPUT_POST, 'brand_id', FILTER_VALIDATE_INT) ?: null;
    $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
    $tones = htmlspecialchars(trim($_POST['tones'] ?? ''), ENT_QUOTES, 'UTF-8'); 
    
    // Checkboxes
    $is_cruelty_free = isset($_POST['is_cruelty_free']) ? 1 : 0;
    $is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
    $available = isset($_POST['available']) ? 1 : 0;
    
    // Números
    $price_input = $_POST['price'] ?? '';
    $price = (int) preg_replace('/[^0-9]/', '', $price_input);
    $old_price = !empty($_POST['old_price']) ? (int)$_POST['old_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);

    if (empty($name) || empty($category_id) || $price <= 0) {
        $mensaje = "Por favor, completa los campos obligatorios.";
    } else {
        try {
            // 1. Armamos la consulta base con TODAS las columnas
            $sql = "UPDATE products SET name = ?, description = ?, price = ?, old_price = ?, stock = ?, category_id = ?, brand_id = ?, tones = ?, is_cruelty_free = ?, is_on_sale = ?, available = ?";
            
            // 2. Metemos todos los valores en orden
            $params = [$name, $description, $price, $old_price, $stock, $category_id, $brand_id, $tones, $is_cruelty_free, $is_on_sale, $available];

            // 3. Verificamos si subió una foto nueva
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../public/img/';
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Si subió foto, añadimos la columna a la consulta y el valor a los parámetros
                    $sql .= ", image_path = ?";
                    $params[] = 'img/' . $file_name; 
                }
            }

            // 4. Añadimos el WHERE id = ? al final de la consulta
            $sql .= " WHERE id = ?";
            $params[] = $id; 

            // 5. Ejecutamos usando el arreglo dinámico $params
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            header('Location: productos.php?updated=1');
            exit;
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar: " . $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: productos.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
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
                    <li><a href="index.php">Dashboard</a></li>
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

            <div class="card" style="max-width: 800px;">
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
                    
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label>Categoría</label><br>
                            <select name="category_id" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $product['category_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="flex: 1;">
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
                    </div>

                    <div style="display: flex; gap: 15px; margin-bottom: 15px; align-items: flex-start;">
                        <div style="flex: 1;">
                            <label>Precio Actual (CLP)</label><br>
                            <input type="number" name="price" value="<?= $product['price'] ?>" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>

                        <div style="flex: 1;">
                            <label>Precio Anterior (Si hay oferta)</label><br>
                            <input type="number" name="old_price" placeholder="Ej: 29990" value="<?= htmlspecialchars($product['old_price'] ?? '') ?>" style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>

                        <div style="flex: 1;">
                            <label>Cantidad en Stock</label><br>
                            <input type="number" name="stock" value="<?= (int)($product['stock'] ?? 0) ?>" min="0" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; font-weight: bold; color: #2c3e50;">
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Tonos Disponibles (Separados por coma)</label><br>
                        <input type="text" name="tones" value="<?= htmlspecialchars($product['tones'] ?? '') ?>" placeholder="Ej: Natural, Sand, Honey" style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Actualizar Foto (Deja en blanco para mantener la actual)</label><br>
                        <input type="file" name="image" accept="image/*" style="margin-top: 5px; margin-bottom: 10px;">
                        <?php if(!empty($product['image_path'])): ?>
                            <br><img src="../public/<?= htmlspecialchars($product['image_path']) ?>" alt="Foto actual" style="width: 80px; border-radius: 4px; border: 1px solid #ddd;">
                        <?php endif; ?>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Descripción</label><br>
                        <textarea name="description" rows="4" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>

                    <div style="margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e74c3c; border-left: 5px solid #e74c3c;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600; color: #1a1a1a;">
                            <input type="checkbox" name="is_on_sale" value="1" <?= $product['is_on_sale'] ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #e74c3c;">
                            <span>Mantener este producto en OFERTA 🔥</span>
                        </label>
                    </div>

                    <div style="display: flex; gap: 20px; margin-bottom: 25px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_cruelty_free" value="1" <?= $product['is_cruelty_free'] ? 'checked' : '' ?>>
                            Es Cruelty Free 🐰
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="available" value="1" <?= $product['available'] ? 'checked' : '' ?>>
                            Producto Activo (Visible en tienda)
                        </label>
                    </div>

                    <button type="submit" class="btn-action" style="background-color: #f39c12; width: 100%; padding: 12px; font-size: 1rem; border: none; cursor: pointer;">Guardar Cambios</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>