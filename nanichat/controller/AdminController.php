<?php
// Controller admin : on protege l'acces et on gere les salons.
// Etape 1 : on recup l'utilisateur en session.
$utilisateur = null;
if (isset($_SESSION['utilisateur']) && !empty($_SESSION['utilisateur'])) {
    $utilisateur = $_SESSION['utilisateur'];
}

// Etape 2 : on check que c'est bien un admin.
$estAdministrateur = false;
if (!empty($utilisateur) && isset($utilisateur['role']) && !empty($utilisateur['role'])) {
    $rolesAdministrateur = array('administrateur' => true, 'admin' => true);
    if (isset($rolesAdministrateur[$utilisateur['role']])) {
        $estAdministrateur = true;
    }
}

if (!$estAdministrateur) {
    // Si pas admin, on renvoie a la connexion.
    redirect('index.php?page=connexion');
}

// Etape 3 : on recup le nom de l'admin.
$nomUtilisateur = $utilisateur['nomUtilisateur'];

// Etape 4 : on recup les donnees pour la vue admin.
$salons = salonsTous();
$utilisateurs = utilisateursLister();

// Etape 5 : on check quelles actions viennent des formulaires.
$faireCreerSalon = false;
if (isset($_POST['faireCreerSalon']) && !empty($_POST['faireCreerSalon'])) {
    $faireCreerSalon = true;
}

$faireModifierSalon = false;
if (isset($_POST['faireModifierSalon']) && !empty($_POST['faireModifierSalon'])) {
    $faireModifierSalon = true;
}

$faireSupprimerSalon = false;
if (isset($_POST['faireSupprimerSalon']) && !empty($_POST['faireSupprimerSalon'])) {
    $faireSupprimerSalon = true;
}

if ($faireCreerSalon) {
    // Etape 6 : creation d'un salon via le form admin.
    $nom = '';
    if (isset($_POST['nomSalon']) && !empty($_POST['nomSalon'])) {
        $nom = trim($_POST['nomSalon']);
    }

    $proprietaire = '';
    if (isset($_POST['proprietaire']) && !empty($_POST['proprietaire'])) {
        $proprietaire = trim($_POST['proprietaire']);
    }

    $estPublic = true;
    if (isset($_POST['visibilite']) && !empty($_POST['visibilite'])) {
        $valeursPrive = array('prive' => true);
        if (isset($valeursPrive[$_POST['visibilite']])) {
            $estPublic = false;
        }
    }

    // Etape 6.1 : si le nom existe, on continue.
    if (!empty($nom)) {
        if (empty($proprietaire)) {
            // Si pas de proprio, on met l'admin.
            $proprietaire = $nomUtilisateur;
        }
        // Etape 6.2 : on cree le salon.
        salonCreer($nom, $proprietaire, $estPublic, '', date('Y-m-d H:i'));
    }
    // Retour a l'admin pour voir la liste.
    redirect('index.php?page=administration');
}

if ($faireModifierSalon) {
    // Etape 7 : on modifie un salon.
    $idSalon = '';
    if (isset($_POST['idSalon']) && !empty($_POST['idSalon'])) {
        $idSalon = $_POST['idSalon'];
    }

    $nom = '';
    if (isset($_POST['nomSalon']) && !empty($_POST['nomSalon'])) {
        $nom = trim($_POST['nomSalon']);
    }

    $proprietaire = '';
    if (isset($_POST['proprietaire']) && !empty($_POST['proprietaire'])) {
        $proprietaire = trim($_POST['proprietaire']);
    }

    $estPublic = true;
    if (isset($_POST['visibilite']) && !empty($_POST['visibilite'])) {
        $valeursPrive = array('prive' => true);
        if (isset($valeursPrive[$_POST['visibilite']])) {
            $estPublic = false;
        }
    }

    // Etape 7.1 : on charge le salon.
    $salon = salonTrouver($idSalon);
    if (!empty($salon)) {
        // Etape 7.2 : si un champ est vide, on garde l'ancien.
        $nomActuel = $nom;
        if (empty($nomActuel) && isset($salon['nom']) && !empty($salon['nom'])) {
            $nomActuel = $salon['nom'];
        }

        $proprietaireActuel = $proprietaire;
        if (empty($proprietaireActuel) && isset($salon['proprietaire']) && !empty($salon['proprietaire'])) {
            $proprietaireActuel = $salon['proprietaire'];
        }

        $texteAutorises = '';
        if (isset($salon['autorises']) && !empty($salon['autorises'])) {
            $texteAutorises = $salon['autorises'];
        }
        if ($estPublic) {
            // Si public, pas de liste d'autorises.
            $texteAutorises = '';
        }

        // Etape 7.3 : on update le salon.
        salonMettreAJour($idSalon, $nomActuel, $proprietaireActuel, $estPublic, $texteAutorises);
    }
    // Retour ecran admin.
    redirect('index.php?page=administration');
}

if ($faireSupprimerSalon) {
    // Etape 8 : on supprime le salon + messages.
    $idSalon = '';
    if (isset($_POST['idSalon']) && !empty($_POST['idSalon'])) {
        $idSalon = $_POST['idSalon'];
    }

    // Etape 8.1 : si on a un id, on supprime.
    if (!empty($idSalon)) {
        salonSupprimer($idSalon);
        messagesSupprimerParSalon($idSalon);
    }

    // Retour a l'admin apres suppression.
    redirect('index.php?page=administration');
}

// On affiche la vue admin.
require __DIR__ . '/../vue/admin.php';
?>
