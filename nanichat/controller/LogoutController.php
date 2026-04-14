<?php
// Controller de deconnexion : on vide la session et on renvoie a l'accueil.
// Etape 1 : on vide la session.
$_SESSION = array();
session_destroy();
// Etape 2 : on renvoie a l'accueil.
redirect('index.php?page=accueil');
?>
