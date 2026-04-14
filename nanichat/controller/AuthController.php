<?php
// Controller auth : gere connexion + inscription.
// Etape 1 : on check si on tente une connexion.
$tentativeConnexion = false;
if (isset($_POST['identifiant'])) {
    $tentativeConnexion = true;
}

// Etape 2 : on check si les champs de connexion sont la.
$connexionPrete = false;
if (isset($_POST['identifiant']) && !empty($_POST['identifiant']) && isset($_POST['motDePasse']) && !empty($_POST['motDePasse'])) {
    $connexionPrete = true;
}

// Etape 3 : on check si on tente une inscription (au moins un champ present).
$tentativeInscription = false;
if (isset($_POST['nomUtilisateur'])) {
    $tentativeInscription = true;
}
if (isset($_POST['courriel'])) {
    $tentativeInscription = true;
}
if (isset($_POST['motDePasse'])) {
    $tentativeInscription = true;
}

// Etape 4 : on check si tous les champs d'inscription sont la.
$inscriptionPrete = false;
if (isset($_POST['nomUtilisateur']) && !empty($_POST['nomUtilisateur']) && isset($_POST['courriel']) && !empty($_POST['courriel']) && isset($_POST['motDePasse']) && !empty($_POST['motDePasse'])) {
    $inscriptionPrete = true;
}

if ($tentativeConnexion) {
    // Etape 5 : on gere la connexion.
    if ($connexionPrete) {
        // Etape 5.1 : on recup ce que l'utilisateur a tape.
        $identifiant = trim($_POST['identifiant']);
        $motDePasse = trim($_POST['motDePasse']);

        // Etape 5.2 : on cherche l'utilisateur par nom ou mail.
        $utilisateurTrouve = utilisateurTrouverParIdentifiant($identifiant);

        // Etape 5.3 : on check le mot de passe avec le hash en base.
        $motDePasseValide = false;
        if (!empty($utilisateurTrouve)) {
            if (!empty($utilisateurTrouve['motDePasse'])) {
                if (password_verify($motDePasse, $utilisateurTrouve['motDePasse'])) {
                    $motDePasseValide = true;
                }
            }
        }

        if ($motDePasseValide) {
            // Etape 5.4 : on met l'utilisateur en session.
            $_SESSION['utilisateur'] = array(
                'id' => $utilisateurTrouve['id'],
                'nomUtilisateur' => $utilisateurTrouve['nomUtilisateur'],
                'role' => $utilisateurTrouve['role']
            );
            // Etape 5.5 : on renvoie vers le tchat.
            redirect('index.php?page=tchat');
        }

        // Mauvais identifiants.
        redirect('index.php?page=connexion&erreur=identifiantsInvalides');
    }

    // Champs manquants.
    redirect('index.php?page=connexion&erreur=champsManquants');
}

if ($tentativeInscription) {
    // Etape 6 : on gere l'inscription.
    if ($inscriptionPrete) {
        // Etape 6.1 : on recup les champs.
        $nomUtilisateur = trim($_POST['nomUtilisateur']);
        $courriel = trim($_POST['courriel']);
        $motDePasse = trim($_POST['motDePasse']);

        // Etape 6.2 : on check si le compte existe deja (nom ou mail).
        $utilisateurParNom = utilisateurTrouverParNomUtilisateur($nomUtilisateur);
        $utilisateurParCourriel = utilisateurTrouverParCourriel($courriel);

        $dejaExistant = false;
        if (!empty($utilisateurParNom)) {
            $dejaExistant = true;
        }
        if (!empty($utilisateurParCourriel)) {
            $dejaExistant = true;
        }

        if ($dejaExistant) {
            redirect('index.php?page=inscription&erreur=compteExistant');
        }

        // Etape 6.3 : on hash le mdp puis on cree le compte.
        $motDePasseHache = password_hash($motDePasse, PASSWORD_DEFAULT);
        $nouvelId = utilisateurCreer($nomUtilisateur, $courriel, $motDePasseHache, 'utilisateur');

        // Etape 6.4 : on connecte direct apres l'inscription.
        $_SESSION['utilisateur'] = array(
            'id' => $nouvelId,
            'nomUtilisateur' => $nomUtilisateur,
            'role' => 'utilisateur'
        );

        // Etape 6.5 : on renvoie vers le tchat.
        redirect('index.php?page=tchat');
    }

    // Champs manquants.
    redirect('index.php?page=inscription&erreur=champsManquants');
}

// Si rien ne match, on renvoie a la connexion.
redirect('index.php?page=connexion');
?>
