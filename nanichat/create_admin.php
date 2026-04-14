<?php
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/modele/UserModel.php';

// Petit script pour creer un admin de base.
// A lancer une seule fois au debut.
echo "--- Initialisation du Super Administrateur ---\n";
if (administrateurExiste()) {
    // Si un admin existe deja, on stoppe.
    echo "[INFO] Un administrateur existe deja.\n";
    exit();
}

// Infos admin par defaut (a changer apres).
$nomUtilisateur = 'JNI';
$courriel = 'JNI@google.com';
$motDePasseClair = 'password';
// On hash le mot de passe avant d'envoyer en base.
$motDePasseHache = password_hash($motDePasseClair, PASSWORD_DEFAULT);
utilisateurCreer($nomUtilisateur, $courriel, $motDePasseHache, 'administrateur');

// Petit recap dans la console.
echo "[SUCCES] L'administrateur '$nomUtilisateur' a ete cree.\n";
echo "Mot de passe : $motDePasseClair\n";
