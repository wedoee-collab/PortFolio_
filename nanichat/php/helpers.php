<?php
// Petites fonctions utiles.
// Redirection simple, on stoppe direct apres.
function redirect($chemin)
{
    // On envoie l'entete puis on coupe tout.
    header('Location: ' . $chemin);
    exit();
}
?>
