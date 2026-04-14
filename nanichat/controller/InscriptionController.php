<?php
// Controller inscription : affiche le formulaire ou renvoie si deja connecte.
// Etape 1 : si deja connecte, on evite l'inscription.
$utilisateurOk = false;
if (isset($_SESSION['utilisateur']) && !empty($_SESSION['utilisateur'])) {
    if (isset($_SESSION['utilisateur']['nomUtilisateur']) && !empty($_SESSION['utilisateur']['nomUtilisateur'])) {
        $utilisateurOk = true;
    }
}

if ($utilisateurOk) {
    // Deja connecte, pas besoin d'inscription.
    redirect('index.php?page=tchat');
}

// Etape 2 : on recup l'erreur si elle est dans l'URL.
$erreur = '';
if (isset($_GET['erreur']) && !empty($_GET['erreur'])) {
    $erreur = $_GET['erreur'];
}

// Etape 3 : on traduit le code erreur pour afficher un message simple.
$messageErreur = '';
$carteErreurs = array(
    'champsManquants' => 'Veuillez remplir tous les champs.',
    'compteExistant' => 'Ce compte existe deja.',
    'motDePasseFaible' => 'Mot de passe trop court.'
);
if (isset($carteErreurs[$erreur]) && !empty($carteErreurs[$erreur])) {
    $messageErreur = $carteErreurs[$erreur];
}

// Etape 4 : on affiche la page inscription.
require __DIR__ . '/../vue/inscription.php';
?>
