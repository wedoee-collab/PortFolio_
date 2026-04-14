<?php
session_start();
require_once 'config.php';

// Vérification de l'ID et de la session
if (!empty($_GET['id']) && !empty($_SESSION['user_id'])) {
    // Suppression de la réservation
    $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}
header("Location: mes_reservations.php");
exit();
?>