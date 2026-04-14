<?php
require_once 'config.php';

echo "<h1>Configuration Admin</h1>";

// Ajouter la colonne 'role' à la table users
try {
    // On essaie d'ajouter la colonne
    $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'client'");
    echo "<p style='color:green'>Colonne 'role' ajoutée à la base de données.</p>";
} catch (PDOException $e) {
    // Si l'erreur est que la colonne existe déjà (Code 42S21), ce n'est pas grave
    if ($e->getCode() == '42S21') {
        echo "<p style='color:orange'>ℹ️ La colonne 'role' existe déjà.</p>";
    } else {
        echo "<p style='color:red'>Erreur : " . $e->getMessage() . "</p>";
    }
}

// Créer ou mettre à jour l'admin
$email = 'admin@hotel.com';
$password = 'password'; // Mot de passe choisi
$nom = 'admin';

// Vérifier si l'utilisateur existe
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();