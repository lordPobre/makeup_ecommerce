<?php
// admin/index.php
session_start();
require_once '../config/database.php';

// --- 1. LÓGICA DE MÉTRICAS ACTUALIZADA ---

// Ingresos totales (excluyendo cancelados)
$stmtIngresos = $pdo->query("SELECT SUM(total) FROM orders WHERE status != 'cancelado'");
$ingresosTotales = $stmtIngresos->fetchColumn() ?: 0;

// Órdenes por enviar: Ahora cuenta 'pendiente' y 'pagado'
// Se descuentan solas cuando cambian a 'enviado', 'entregado' o 'cancelado'
$stmtPorEnviar = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pendiente', 'pagado')");
$ordenesPorEnviar = $stmtPorEnviar->fetchColumn() ?: 0;

// Productos con stock crítico (Menor a 3)
$stmtStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 3");
$stockCritico = $stmtStock->fetchColumn() ?: 0;

// --- 2. LÓGICA DE ÓRDENES Y FILTROS ---
$filtro = $_GET['status'] ?? 'todos';

try {
    if ($filtro === 'todos') {
        $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE status = ? ORDER BY id DESC");
        $stmt->execute([$filtro]);
    }
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
    $error = "Asegúrate de haber creado la tabla 'orders'. Error: " . $e->getMessage();
}

$colores_estado = [
    'pendiente' => '#f39c12',
    'pagado'    => '#9b59b6',
    'enviado'   => '#3498db',
    'entregado' => '#2ecc71',
    'cancelado' => '#e74c3c'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin Glow & Beauty</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* Variables por defecto (Modo Claro) */
        :root {
            --bg-body: #f4f7f6;
            --bg-card: #ffffff;
            --text-main: #333333;
            --text-muted: #666666;
            --border-color: #eeeeee;
        }

        /* Detección automática de Modo Oscuro del sistema */
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-body: #121212;
                --bg-card: #1e1e1e;
                --text-main: #f5f5f5;
                --text-muted: #aaaaaa;
                --border-color: #333333;
            }
        }

        /* Aplicando variables SÓLO al contenido principal (respetando tu sidebar original) */
        body { background-color: var(--bg-body); color: var(--text-main); transition: background-color 0.3s, color 0.3s; }
        .card, .filter-bar, .metric-card { background-color: var(--bg-card) !important; border-color: var(--border-color) !important; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .data-table th { border-bottom: 2px solid var(--border-color); color: var(--text-main); }
        .data-table td { border-bottom: 1px solid var(--border-color); color: var(--text-muted); }
        .filter-title { color: var(--text-main); }
        .btn-filter { background: var(--bg-body); color: var(--text-main); border-color: var(--border-color); }
        .top-bar h1 { color: var(--text-main) !important; }

        /* Estilos de las Tarjetas de Métricas */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .metric-card {
            padding: 25px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid var(--border-color);
        }
        .metric-info h3 { margin: 0; font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .metric-info p { margin: 5px 0 0 0; font-size: 1.8rem; font-weight: 700; color: var(--text-main); }
        .metric-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; }
        
        .filter-bar { display: flex; align-items: center; gap: 12px; margin-bottom: 25px; padding: 15px 20px; border-radius: 10px; flex-wrap: wrap; }
        .btn-filter { padding: 8px 18px; border-radius: 20px; text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: all 0.3s; }
        .btn-filter.active { background: #3498db; color: #fff; border-color: #3498db; }
        .badge { padding: 5px 12px; border-radius: 20px; color: white; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
    </style>
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
                <h1>Resumen de la Tienda</h1>
            </header>

            <div class="dashboard-grid">
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Ingresos Netos</h3>
                        <p>$<?= number_format($ingresosTotales, 0, ',', '.') ?></p>
                    </div>
                    <div class="metric-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Por Enviar</h3>
                        <p><?= $ordenesPorEnviar ?> <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">pedidos</span></p>
                    </div>
                    <div class="metric-icon" style="background: linear-gradient(135deg, #f39c12, #d35400);">
                        <i class="fas fa-truck-loading"></i>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Stock Crítico</h3>
                        <p style="<?= $stockCritico > 0 ? 'color: #e74c3c;' : '' ?>">
                            <?= $stockCritico ?> <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">productos</span>
                        </p>
                    </div>
                    <div class="metric-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            <div class="filter-bar">
                <span class="filter-title"><i class="fas fa-filter"></i> Filtrar por estado:</span>
                <a href="index.php?status=todos" class="btn-filter <?= $filtro === 'todos' ? 'active' : '' ?>">Todas</a>
                <a href="index.php?status=pendiente" class="btn-filter <?= $filtro === 'pendiente' ? 'active' : '' ?>">Pendientes</a>
                <a href="index.php?status=pagado" class="btn-filter <?= $filtro === 'pagado' ? 'active' : '' ?>">Pagadas</a>
                <a href="index.php?status=enviado" class="btn-filter <?= $filtro === 'enviado' ? 'active' : '' ?>">Enviadas</a>
                <a href="index.php?status=entregado" class="btn-filter <?= $filtro === 'entregado' ? 'active' : '' ?>">Entregadas</a>
            </div>

            <div class="card">
                <?php if (isset($error)): ?>
                    <div style="background: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                        <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th style="text-align: center;">Estado</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $order): 
                                $telefono_limpio = preg_replace('/[^0-9]/', '', $order['phone'] ?? '');
                                if (strlen($telefono_limpio) == 9) { $telefono_limpio = '56' . $telefono_limpio; }
                                $mensaje = urlencode("¡Hola " . ($order['customer_name'] ?? 'Hermosa') . "! 💖 Te contactamos de Glow & Beauty respecto a tu pedido #" . $order['id'] . ".");
                                
                                $estado_actual = strtolower($order['status'] ?? 'pendiente');
                                $color_fondo = $colores_estado[$estado_actual] ?? '#95a5a6';
                            ?>
                            <tr>
                                <td><strong>#<?= $order['id'] ?></strong></td>
                                <td><?= htmlspecialchars($order['customer_name'] ?? 'Sin Nombre') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')) ?></td>
                                <td><strong>$<?= number_format($order['total'] ?? 0, 0, ',', '.') ?></strong></td>
                                <td style="text-align: center;">
                                    <span class="badge" style="background-color: <?= $color_fondo ?>;">
                                        <?= htmlspecialchars($order['status'] ?? 'Pendiente') ?>
                                    </span>
                                </td>
                                <td style="text-align: center; display: flex; justify-content: center; align-items: center; gap: 10px;">
                                    <?php if (!empty($telefono_limpio)): ?>
                                        <a href="https://wa.me/<?= $telefono_limpio ?>?text=<?= $mensaje ?>" target="_blank" title="WhatsApp" style="background-color: #25D366; color: white; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; text-decoration: none;">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="ver_orden.php?id=<?= $order['id'] ?>" class="btn-action" style="padding: 6px 12px; text-decoration: none; background-color: #1a1a1a; color: white; border-radius: 4px; font-size: 0.85rem;">Ver</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 50px; color: var(--text-muted);">
                                    <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5; display: block;"></i>
                                    No se encontraron órdenes en este estado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>