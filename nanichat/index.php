<?php
// Fichier principal de l'app.
// Etape 1 : on check si on doit demarrer la session.
$doitDemarrer = true;
if (function_exists('session_status')) {
    $statut = session_status();
    $statutsAucun = array(PHP_SESSION_NONE => true);
    if (!isset($statutsAucun[$statut])) {
        $doitDemarrer = false;
    }
}

// Etape 2 : demarrage de session si besoin.
if ($doitDemarrer) {
    // On ouvre la session pour garder l'utilisateur.
    session_start();
}

// Etape 3 : on charge les helpers et les modeles.
require_once __DIR__ . '/php/helpers.php';
require_once __DIR__ . '/modele/UserModel.php';
require_once __DIR__ . '/modele/RoomModel.php';
require_once __DIR__ . '/modele/MessageModel.php';

// Etape 4 : on recup la page demandee.
$pageDemandee = '';
if (isset($_GET['page']) && !empty($_GET['page'])) {
    $pageDemandee = $_GET['page'];
}

// Etape 5 : liste des routes dispo (page -> controller).
$pagesDisponibles = array(
    'accueil' => '/controller/HomeController.php',
    'connexion' => '/controller/ConnexionController.php',
    'inscription' => '/controller/InscriptionController.php',
    'authentification' => '/controller/AuthController.php',
    'tchat' => '/controller/ChatController.php',
    'administration' => '/controller/AdminController.php',
    'deconnexion' => '/controller/LogoutController.php'
);

// Etape 6 : on charge le bon controller, sinon accueil par defaut.
if (isset($pagesDisponibles[$pageDemandee]) && !empty($pagesDisponibles[$pageDemandee])) {
    require __DIR__ . $pagesDisponibles[$pageDemandee];
} else {
    // Si la page n'existe pas, on met l'accueil.
    require __DIR__ . '/controller/HomeController.php';
}
?>
