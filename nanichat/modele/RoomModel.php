<?php
require_once __DIR__ . '/../php/db.php';

// Gere les salons (salles de discussion).
// Modele salons : on parle a la table rooms.
// Récupère la liste de tous les salons pour l'afficher sur l'accueil.
function salonsTous()
{
    global $pdo;
    // Select simple, tri par id.
    $sql = 'SELECT id, name AS nom, owner AS proprietaire, is_public AS estPublic, allowed AS autorises, created_at AS creeLe FROM rooms ORDER BY id ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $lignes = $stmt->fetchAll();
    // On renvoie toujours un tableau, meme vide.
    if (!is_array($lignes)) {
        return array();
    }
    foreach ($lignes as $index => $ligne) {
        // On garde toujours le champ "autorises".
        if (!isset($ligne['autorises'])) {
            $ligne['autorises'] = '';
        } else {
            if (empty($ligne['autorises'])) {
                $ligne['autorises'] = '';
            }
        }
        $lignes[$index] = $ligne;
    }
    return $lignes;
}
// Stmt stocke la requête préparée et envoie les données à la base de données.
// On trouve un salon par son id.
function salonTrouver($idSalon)
{
    global $pdo;
    // Recherche par id.
    $sql = 'SELECT id, name AS nom, owner AS proprietaire, is_public AS estPublic, allowed AS autorises, created_at AS creeLe FROM rooms WHERE id = :id LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $idSalon));
    $ligne = $stmt->fetch();
    // Rien trouve.
    if (!$ligne) {
        return null;
    }
    if (!isset($ligne['autorises'])) {
        $ligne['autorises'] = '';
    } else {
        if (empty($ligne['autorises'])) {
            $ligne['autorises'] = '';
        }
    }
    return $ligne;
}

// On cree un salon.
function salonCreer($nom, $proprietaire, $estPublic, $texteAutorises, $creeLe)
{
    global $pdo;
    // Convertit le booléen PHP en entier (0/1) pour la base.
    $estPublicValeur = 0;
    if ($estPublic) {
        $estPublicValeur = 1;
    }
    // Insert du salon.
    $sql = 'INSERT INTO rooms (name, owner, is_public, allowed, created_at) VALUES (:nom, :proprietaire, :estPublic, :autorises, :creeLe)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':nom' => $nom, // => associe une clé à une valeur
        ':proprietaire' => $proprietaire,
        ':estPublic' => $estPublicValeur,
        ':autorises' => $texteAutorises,
        ':creeLe' => $creeLe
    ));
    // On renvoie l'id cree.
    return $pdo->lastInsertId();
}

// Permet de changer le nom, la visibilité ou 
// la liste des utilisateurs autorisés (pour les salons privés).
function salonMettreAJour($idSalon, $nom, $proprietaire, $estPublic, $texteAutorises)
{
    global $pdo;
    // Convertit le booléen PHP en entier (0/1) pour la base.
    $estPublicValeur = 0;
    if ($estPublic) {
        $estPublicValeur = 1;
    }
    // Update complet des champs.
    $sql = 'UPDATE rooms SET name = :nom, owner = :proprietaire, is_public = :estPublic, allowed = :autorises WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $resultat = $stmt->execute(array(
        ':nom' => $nom,
        ':proprietaire' => $proprietaire,
        ':estPublic' => $estPublicValeur,
        ':autorises' => $texteAutorises,
        ':id' => $idSalon
    ));
    
    return $resultat;
}

// On supprime un salon.
function salonSupprimer($idSalon)
{
    global $pdo;
    // Suppression simple par id.
    $sql = 'DELETE FROM rooms WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $resultat = $stmt->execute(array(':id' => $idSalon));
    return $resultat;
}
?>
