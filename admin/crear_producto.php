<?php
// admin/crear_producto.php
session_start();
require_once '../config/database.php';

$stmtCat = $pdo->query("SELECT * FROM categories");
$categories = $stmtCat->fetchAll();
$stmtBrands = $pdo->query("SELECT * FROM brands");
$brands = $stmtBrands->fetchAll();

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpieza de datos moderna y segura para PHP 8.1+
    $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    $is_cruelty_free = isset($_POST['is_cruelty_free']) ? 1 : 0;
    
    $price_input = $_POST['price'] ?? '';
    $price = (int) preg_replace('/[^0-9]/', '', $price_input);
    
    // Capturamos el stock que viene del formulario
    $stock = (int)($_POST['stock'] ?? 0);

    if (empty($name) || empty($category_id) || $price <= 0) {
        $mensaje = "Por favor, completa el nombre, selecciona una categoría y pon un precio válido.";
    } else {
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../public/img/';
            $file_name = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = 'img/' . $file_name; 
            }
        } else {
            $image_path = 'https://images.unsplash.com/photo-1596462502278-27bf85033e5a?q=80&w=1000&auto=format&fit=crop';
        }
        
        try {
            // Agregamos 'stock' a la consulta INSERT
            $stmt = $pdo->prepare("
                INSERT INTO products (name, category_id, price, description, is_cruelty_free, image_path, available, stock) 
                VALUES (?, ?, ?, ?, ?, ?, 1, ?)
            ");
            $stmt->execute([$name, $category_id, $price, $description, $is_cruelty_free, $image_path, $stock]);
            
            header('Location: productos.php?success=1');
            exit;
        } catch (PDOException $e) {
            $mensaje = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Producto - Admin Glow & Beauty</title>
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
            <header class="top-bar">
                <h1>Agregar Nuevo Producto</h1>
                <a href="productos.php" class="btn-action" style="background:#7f8c8d;">&larr; Volver</a>
            </header>

            <div class="card" style="max-width: 600px;">
                <?php if ($mensaje): ?>
                    <div style="background: #e74c3c; color: white; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                        <?= $mensaje ?>
                    </div>
                <?php endif; ?>

                <form action="crear_producto.php" method="POST" enctype="multipart/form-data">
                    <div style="margin-bottom: 15px;">
                        <label>Nombre del Producto</label><br>
                        <input type="text" name="name" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label>Categoría</label><br>
                            <select name="category_id" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="">Selecciona...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label>Marca (Opcional)</label><br>
                            <select name="brand_id" style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="">Sin marca...</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label>Precio (CLP)</label><br>
                            <input type="number" name="price" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        
                        <div style="flex: 1;">
                            <label>Cantidad en Stock</label><br>
                            <input type="number" name="stock" value="0" min="0" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; font-weight: bold; color: #2c3e50;">
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Foto del Producto</label><br>
                        <input type="file" name="image" accept="image/*" style="margin-top: 5px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Descripción</label><br>
                        <textarea name="description" rows="4" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_cruelty_free" value="1" checked>
                            Es Cruelty Free 🐰
                        </label>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Tonos Disponibles (Separados por comas)</label><br>
                        <input type="text" name="tones" 
                            value="<?= htmlspecialchars($_POST['tones'] ?? '') ?>" 
                            placeholder="Ej: Claro, Medio, Oscuro" 
                            style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                        <small style="color: #666;">Escribe los tonos que quieres que aparezcan en el desplegable.</small>
                    </div>

                    <button type="submit" class="btn-action" style="background-color: #2c3e50; width: 100%; padding: 12px; font-size: 1rem;">Guardar Producto</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>