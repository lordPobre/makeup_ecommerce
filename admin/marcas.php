<?php
// admin/marcas.php
session_start();
require_once '../config/database.php';

$mensaje = '';

// 1. PROCESAR EL FORMULARIO DE CREACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    // CORRECCIÓN: El campo en el form es 'name', no 'nombre'
    $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../public/img/';
        
        // Crear el directorio si no existe por seguridad
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

        $file_name = time() . '_brand_' . basename($_FILES['logo']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
            $image_path = 'img/' . $file_name; 
            
            try {
                $stmt = $pdo->prepare("INSERT INTO brands (name, image_path) VALUES (?, ?)");
                $stmt->execute([$name, $image_path]);
                
                // PRG Pattern: Redirigimos a la misma página para limpiar el POST y evitar duplicados
                header("Location: marcas.php?success=1");
                exit;
            } catch (PDOException $e) {
                $mensaje = "<div style='background: #e74c3c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>Error de BD: " . $e->getMessage() . "</div>";
            }
        } else {
            $mensaje = "<div style='background: #e74c3c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>Error al mover la imagen al servidor.</div>";
        }
    }
}

// 2. MENSAJES DE ÉXITO (Vía GET tras el redirect)
if (isset($_GET['success'])) {
    $mensaje = "<div style='background: #2ecc71; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;'><i class='fas fa-check-circle'></i> Marca guardada con éxito.</div>";
}
if (isset($_GET['deleted'])) {
    $mensaje = "<div style='background: #f39c12; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;'><i class='fas fa-trash-alt'></i> Marca eliminada correctamente.</div>";
}

// 3. CONSULTAR LAS MARCAS ACTUALIZADAS
$stmt = $pdo->query("SELECT * FROM brands ORDER BY id DESC");
$brands = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Marcas - Admin Glow & Beauty</title>
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
                <h1>Gestión de Marcas</h1>
            </header>

            <?= $mensaje ?>

            <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
                
                <div class="card" style="flex: 1; min-width: 320px;">
                    <h3 style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Nueva Marca</h3>
                    <form action="marcas.php" method="POST" enctype="multipart/form-data">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nombre de la Marca</label>
                            <input type="text" name="name" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Logo de Marca</label>
                            <input type="file" name="logo" accept="image/*" required style="display: block; width: 100%;">
                            <small style="color: #666; display: block; margin-top: 5px;">Formatos: PNG, JPG (ideal fondo transparente).</small>
                        </div>
                        
                        <button type="submit" class="btn-action" style="background-color: #3498db; width: 100%; padding: 12px; border: none; color: white; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                            <i class="fas fa-save"></i> Guardar Marca
                        </button>
                    </form>
                </div>

                <div class="card" style="flex: 2; min-width: 400px;">
                    <h3 style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Marcas Activas</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Logo</th>
                                <th>Nombre</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($brands) > 0): ?>
                                <?php foreach ($brands as $brand): ?>
                                <tr>
                                    <td>#<?= $brand['id'] ?></td>
                                    <td>
                                        <?php 
                                            // Corregimos la ruta para mostrar en el admin
                                            $img_path = '../public/' . $brand['image_path'];
                                        ?>
                                        <img src="<?= htmlspecialchars($img_path) ?>" style="width: 50px; height: 50px; object-fit: contain; border-radius: 4px; background: #fff; border: 1px solid #eee;">
                                    </td>
                                    <td><strong><?= htmlspecialchars($brand['name']) ?></strong></td>
                                    <td style="text-align: center;">
                                        <a href="eliminar_marca.php?id=<?= $brand['id'] ?>" 
                                           onclick="return confirm('¿Eliminar esta marca? Los productos asociados podrían quedar sin logo.');" 
                                           style="background-color: #e74c3c; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; display: inline-block;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 30px; color: #888;">No hay marcas registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>