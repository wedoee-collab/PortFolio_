<?php
require_once __DIR__ . '/../php/db.php';

// Modele messages : on parle a la table messages.
// On liste les messages d'un salon.

// Récupère l'historique des messages d'un salon spécifique, triés par ID.).
function messagesPourSalon($idSalon)
{
    global $pdo;
    // Requete simple, tri par id.
    $sql = 'SELECT id, room_id AS idSalon, username AS nomUtilisateur, content AS contenu, created_at AS creeLe FROM messages WHERE room_id = :idSalon ORDER BY id ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':idSalon' => $idSalon)); // => associe une clé à une valeur
    $lignes = $stmt->fetchAll();
    // On renvoie un tableau, meme vide.
    if (is_array($lignes)) {
        return $lignes;
    }
    return array();
}
// Stmt stocke la requête préparée et envoie les données à la base de données.
// On ajoute un message en base.
// Sauvegarde un message envoyé.
function messageCreer($idSalon, $nomUtilisateur, $contenu, $creeLe)
{
    global $pdo;
    // Insert du message.
    $sql = 'INSERT INTO messages (room_id, username, content, created_at) VALUES (:idSalon, :nomUtilisateur, :contenu, :creeLe)';
    $stmt = $pdo->prepare($sql);
    $resultat = $stmt->execute(array(
        ':idSalon' => $idSalon,
        ':nomUtilisateur' => $nomUtilisateur,
        ':contenu' => $contenu,
        ':creeLe' => $creeLe
    ));
    // Bool ok/ko.
    return $resultat;
}

// On supprime les messages d'un salon.
function messagesSupprimerParSalon($idSalon)
{
    global $pdo;
    // On cible uniquement les messages du salon.
    $sql = 'DELETE FROM messages WHERE room_id = :idSalon';
    $stmt = $pdo->prepare($sql);
    $resultat = $stmt->execute(array(':idSalon' => $idSalon));
    return $resultat;
}
?>
