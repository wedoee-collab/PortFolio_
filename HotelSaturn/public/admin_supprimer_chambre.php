<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { header("Location: login.php"); exit(); }

if (!empty($_GET['id'])) {
    $pdo->prepare("DELETE FROM reservations WHERE chambre_id = ?")->execute([$_GET['id']]);
    $pdo->prepare("DELETE FROM chambres WHERE id = ?")->execute([$_GET['id']]);
}
header("Location: admin_chambres.php");
exit();
?>