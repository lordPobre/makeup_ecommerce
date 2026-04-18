<?php
session_start();
require_once '../config/database.php';

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$order_id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

require_once '../includes/header.php';
?>

<main class="success-container">
    <div class="success-message">
        <div class="icon-check">✨</div>
        <h1>¡Gracias por tu compra, <?= htmlspecialchars($order['customer_name']) ?>!</h1>
        <p>Tu pedido ha sido registrado con éxito y ya estamos preparándolo.</p>
        
        <div class="order-details-box">
            <h2>Detalles de tu Orden</h2>
            <p><strong>Número de Orden:</strong> #<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></p>
            <p><strong>Total a Pagar:</strong> $<?= number_format($order['total_amount'], 0, ',', '.') ?></p>
            <p><strong>Estado:</strong> <span class="status-badge"><?= ucfirst($order['status']) ?></span></p>
            <p><strong>Dirección de Envío:</strong> <?= htmlspecialchars($order['address']) ?>, <?= htmlspecialchars($order['city']) ?></p>
        </div>

        <p class="next-steps">Te hemos enviado un correo a <strong><?= htmlspecialchars($order['email']) ?></strong> con los pasos para realizar el pago.</p>
        
        <a href="index.php" class="btn-shop">Volver a la tienda</a>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>