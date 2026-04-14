<?php
// Controller connexion : affiche le formulaire et les erreurs.
// Etape 1 : si deja connecte, on file au tchat.
$utilisateurOk = false;
if (isset($_SESSION['utilisateur']) && !empty($_SESSION['utilisateur'])) {
    if (isset($_SESSION['utilisateur']['nomUtilisateur']) && !empty($_SESSION['utilisateur']['nomUtilisateur'])) {
        $utilisateurOk = true;
    }
}

if ($utilisateurOk) {
    // Pas besoin de se reconnecter.
    redirect('index.php?page=tchat');
}

// Etape 2 : on recup l'erreur si elle vient de l'URL.
$erreur = '';
if (isset($_GET['erreur']) && !empty($_GET['erreur'])) {
    $erreur = $_GET['erreur'];
}

// Etape 3 : on traduit le code en message simple.
$messageErreur = '';
$carteErreurs = array(
    'champsManquants' => 'Veuillez remplir tous les champs.',
    'identifiantsInvalides' => 'Identifiant ou mot de passe incorrect.'
);
if (isset($carteErreurs[$erreur]) && !empty($carteErreurs[$erreur])) {
    $messageErreur = $carteErreurs[$erreur];
}

// Etape 4 : on affiche la page connexion.
require __DIR__ . '/../vue/connexion.php';
?>
