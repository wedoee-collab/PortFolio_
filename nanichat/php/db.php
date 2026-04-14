<?php
// Connexion PDO.

// Infos de connexion (a changer si besoin).
$dsnConnexion = "mysql:host=localhost;dbname=chat_project;charset=utf8mb4";
$utilisateurBdd = "chat_user";
$motDePasseBdd = "ChatPass123!";

// Options PDO (erreurs claires + fetch en assoc).
$optionsPdo = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// Connexion PDO, si ca rate on stoppe.
// $pdo sert partout dans les modeles.
try {
    $pdo = new PDO($dsnConnexion, $utilisateurBdd, $motDePasseBdd, $optionsPdo);
} catch (PDOException $e) {
    // Si ca plante, on stoppe direct.
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>


