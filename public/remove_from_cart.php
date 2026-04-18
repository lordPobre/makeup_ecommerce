<?php
session_start();

$key = filter_input(INPUT_GET, 'key', FILTER_SANITIZE_STRING);

if ($key && isset($_SESSION['cart'][$key])) {
    unset($_SESSION['cart'][$key]);
}

header('Location: carrito.php');
exit;
?>