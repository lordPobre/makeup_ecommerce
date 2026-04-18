<?php
// admin/marcas.php
session_start();
require_once '../config/database.php';

$mensaje = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    // CÓDIGO MODERNO (Seguro para PHP 8.1+)
    $name = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    // Lógica para subir el logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../public/img/';
        $file_name = time() . '_brand_' . basename($_FILES['logo']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
            $image_path = 'img/' . $file_name; 
            
            try {
                $stmt = $pdo->prepare("INSERT INTO brands (name, image_path) VALUES (?, ?)");
                $stmt->execute([$name, $image_path]);
                $mensaje = "<div style='background: #2ecc71; color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;'>Marca guardada con éxito.</div>";
            } catch (PDOException $e) {
                $mensaje = "<div style='background: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;'>Error: " . $e->getMessage() . "</div>";
            }
        } else {
            $mensaje = "<div style='background: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;'>Error al mover la imagen.</div>";
        }
    } else {
        $mensaje = "<div style='background: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;'>Debes subir un archivo de imagen.</div>";
    }
}

// Consultar las marcas para la tabla
$stmt = $pdo->query("SELECT * FROM brands ORDER BY id DESC");
$brands = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Marcas - Admin Glow & Beauty</title>
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
                <h1>Gestión de Marcas</h1>
            </header>

            <?= $mensaje ?>

            <div style="display: flex; gap: 30px; align-items: flex-start;">
                <div class="card" style="flex: 1; min-width: 250px;">
                    <h3>Agregar Marca</h3>
                    <form action="marcas.php" method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
                        <label style="display: block; margin-bottom: 8px;">Nombre de la Marca</label>
                        <input type="text" name="name" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px;">
                        
                        <label style="display: block; margin-bottom: 8px;">Logo (Fondo transparente idealmente)</label>
                        <input type="file" name="logo" accept="image/*" required style="margin-bottom: 20px;">
                        
                        <button type="submit" class="btn-action" style="background-color: #3498db; width: 100%; padding: 10px; border: none; cursor: pointer;">Guardar Marca</button>
                    </form>
                </div>

                <div class="card" style="flex: 2; min-width: 300px;">
                    <h3>Marcas Activas</h3>
                    <table class="data-table" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>Logo</th>
                                <th>Nombre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($brands as $brand): ?>
                            <tr>
                                <td><img src="../public/<?= htmlspecialchars($brand['image_path']) ?>" alt="logo" style="height: 30px; object-fit: contain;"></td>
                                <td><strong><?= htmlspecialchars($brand['name']) ?></strong></td>
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