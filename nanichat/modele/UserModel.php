<?php
require_once __DIR__ . '/../php/db.php';

// Gere tout ce qui concerne les comptes utilisateurs.
// Modele utilisateurs : on parle a la table users.
// On trouve un utilisateur par son nom d'utilisateur.
function utilisateurTrouverParNomUtilisateur($nomUtilisateur)
{
    global $pdo;
    // Recherche par username.
    $sql = 'SELECT id, username AS nomUtilisateur, email AS courriel, password AS motDePasse, role AS role FROM users WHERE username = :nomUtilisateur LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':nomUtilisateur' => $nomUtilisateur));
    $ligne = $stmt->fetch();
    // On renvoie la ligne si on a trouve.
    if ($ligne) {
        return $ligne;
    }
    return null;
}

// On trouve un utilisateur par courriel.
function utilisateurTrouverParCourriel($courriel)
{
    global $pdo;
    // Recherche par email.
    $sql = 'SELECT id, username AS nomUtilisateur, email AS courriel, password AS motDePasse, role AS role FROM users WHERE email = :courriel LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':courriel' => $courriel));
    $ligne = $stmt->fetch();
    // On renvoie la ligne si existe.
    if ($ligne) {
        return $ligne;
    }
    return null;
}
// Stmt stocke la requête préparée et envoie les données à la base de données.
// On trouve par identifiant.
function utilisateurTrouverParIdentifiant($identifiant)
{
    global $pdo;
    // Recherche sur username ou email.
    $sql = 'SELECT id, username AS nomUtilisateur, email AS courriel, password AS motDePasse, role AS role FROM users WHERE username = :identifiant OR email = :identifiant LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':identifiant' => $identifiant));
    $ligne = $stmt->fetch();
    // Retour simple si ça trouve.
    if ($ligne) {
        return $ligne;
    }
    return null;
}

// On cree un nouvel utilisateur en base.
function utilisateurCreer($nomUtilisateur, $courriel, $motDePasseHache, $role)
{
    global $pdo;
    // Insert des infos de l'utilisateur.
    $sql = 'INSERT INTO users (username, email, password, role) VALUES (:nomUtilisateur, :courriel, :motDePasse, :role)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':nomUtilisateur' => $nomUtilisateur,
        ':courriel' => $courriel,
        ':motDePasse' => $motDePasseHache,
        ':role' => $role
    ));
    // On renvoie l'id cree.
    // => Associer une clé a une valeur dans tableau associatif.
    return $pdo->lastInsertId();
}

// On check si un admin existe deja.
function administrateurExiste()
{
    global $pdo;
    // On teste role.
    $sql = 'SELECT id FROM users WHERE role = :roleAdmin OR role = :roleAdminFr OR username = :nomAdmin OR username = :nomAdminFr LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':roleAdmin' => 'admin',
        ':roleAdminFr' => 'administrateur',
        ':nomAdmin' => 'admin',
        ':nomAdminFr' => 'administrateur'
    ));
    $ligne = $stmt->fetch();
    // Booleen simple.
    if ($ligne) {
        return true;
    }
    return false;
}

// On liste tous les utilisateurs.
function utilisateursLister()
{
    global $pdo;
    // On renvoie id, nom et role pour l'admin.
    $sql = 'SELECT id, username AS nomUtilisateur, role AS role FROM users ORDER BY username ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $lignes = $stmt->fetchAll();
    // On renvoie toujours un tableau.
    if (is_array($lignes)) {
        return $lignes;
    }
    return array();
}
?>
