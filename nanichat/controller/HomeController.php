<?php
// Controller accueil : on decide si on affiche la page ou si on va direct au tchat.
// Etape 1 : si l'utilisateur est deja connecte, on saute l'accueil.
$utilisateurOk = false;
if (isset($_SESSION['utilisateur']) && !empty($_SESSION['utilisateur'])) {
    if (isset($_SESSION['utilisateur']['nomUtilisateur']) && !empty($_SESSION['utilisateur']['nomUtilisateur'])) {
        $utilisateurOk = true;
    }
}

if ($utilisateurOk) {
    // Deja connecte -> on va direct au tchat.
    redirect('index.php?page=tchat');
}

// Etape 2 : on affiche la page d'accueil.
require __DIR__ . '/../vue/home.php';
?>
