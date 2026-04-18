<?php
// admin/eliminar_marca.php
session_start();
require_once '../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        // Opcional: Podrías verificar si hay productos asociados antes de borrar
        $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: marcas.php?deleted=1');
        exit;
    } catch (PDOException $e) {
        die("Error al eliminar la marca: " . $e->getMessage());
    }
}

header('Location: marcas.php');
exit;