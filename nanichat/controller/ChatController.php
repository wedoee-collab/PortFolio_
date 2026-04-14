<?php
// Controller tchat : on check l'acces, on gere les actions, puis on affiche.
// Ici c'est le cerveau du chat : on verifie qui est connecte, on gere les actions,
// puis on prepare tout pour l'affichage.
// Etape 1 : on recup la session si elle existe.
// On part avec un utilisateur vide, puis on remplira si la session est ok.
$utilisateur = null;
if (isset($_SESSION['utilisateur']) && !empty($_SESSION['utilisateur'])) {
    // Si la session a un utilisateur, on le garde en memoire.
    $utilisateur = $_SESSION['utilisateur'];
}

// Etape 2 : on verifie si l'utilisateur est bien present.
// On veut etre sur qu'il y a un nom d'utilisateur valide.
$utilisateurOk = false;
if (!empty($utilisateur) && isset($utilisateur['nomUtilisateur']) && !empty($utilisateur['nomUtilisateur'])) {
    // Tout est bon, l'utilisateur est reconnu.
    $utilisateurOk = true;
}

if (!$utilisateurOk) {
    // Pas connecte ? on renvoie a la connexion.
    // On ne laisse pas entrer sans session.
    redirect('index.php?page=connexion');
}

// Etape 3 : on garde le nom d'utilisateur courant.
// On garde le pseudo pour toutes les actions plus bas.
$nomUtilisateur = $utilisateur['nomUtilisateur'];

// Etape 4 : on check le role (admin ou non).
// On regarde si la personne est admin pour donner plus de droits.
$estAdministrateur = false;
if (isset($utilisateur['role']) && !empty($utilisateur['role'])) {
    // Petite liste rapide des roles admin acceptes.
    $rolesAdministrateur = array('administrateur' => true, 'admin' => true);
    if (isset($rolesAdministrateur[$utilisateur['role']])) {
        // Role ok => admin.
        $estAdministrateur = true;
    }
}

// Etape 5 : bouton admin visible si admin.
// Bouton admin visible si admin.
// Ce flag sert juste a afficher un bouton en plus dans la vue.
$afficherBoutonAdmin = false;
if ($estAdministrateur) {
    $afficherBoutonAdmin = true;
}

// Etape 6 : petit index pour reconnaitre ses messages.
// Pour reconnaitre ses propres messages.
// On fait un mini dictionnaire pour checker vite si un message est a moi.
$indexMoi = array();
if (!empty($nomUtilisateur)) {
    $indexMoi[$nomUtilisateur] = true;
}

// Petit helper : "a,b,c" => liste clean.
// On transforme un texte "a,b,c" en tableau propre.
function listeAutorises($texte)
{
    // Si vide ou null, on met vide.
    if (!isset($texte)) {
        $texte = '';
    }
    if (empty($texte)) {
        $texte = '';
    }
    $elements = array();
    if (empty($texte)) {
        // Rien a renvoyer.
        return $elements;
    }
    // On coupe par virgule.
    $parties = explode(',', $texte);
    foreach ($parties as $partie) {
        // On nettoie chaque nom.
        $nom = trim($partie);
        if (!empty($nom)) {
            // On garde seulement les noms valides.
            $elements[] = $nom;
        }
    }
    return $elements;
}

// On check quelles actions viennent du form.
// Chaque action a un flag pour savoir quel bouton a ete clique.
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

$faireAjouterUtilisateur = false;
if (isset($_POST['faireAjouterUtilisateur']) && !empty($_POST['faireAjouterUtilisateur'])) {
    $faireAjouterUtilisateur = true;
}

$faireRetirerUtilisateur = false;
if (isset($_POST['faireRetirerUtilisateur']) && !empty($_POST['faireRetirerUtilisateur'])) {
    $faireRetirerUtilisateur = true;
}

$faireEnvoyerMessage = false;
if (isset($_POST['faireEnvoyerMessage']) && !empty($_POST['faireEnvoyerMessage'])) {
    $faireEnvoyerMessage = true;
}

// Action : creer un salon.
// Si le formulaire "creer salon" est envoye, on passe ici.
if ($faireCreerSalon) {
    // Etape 1 : on lit le nom du salon.
    // On recup les champs du form.
    $nom = '';
    if (isset($_POST['nomSalon']) && !empty($_POST['nomSalon'])) {
        // On enleve les espaces en trop.
        $nom = trim($_POST['nomSalon']);
    }

    // Etape 2 : on check que le nom n'est pas vide.
    if (empty($nom)) {
        // Pas de nom => erreur.
        // On renvoie au tchat avec un code erreur simple.
        redirect('index.php?page=tchat&erreur=salonManquant');
    }

    // Etape 3 : on decide si le salon est public ou prive.
    // Public par defaut, sauf si on demande "prive".
    $estPublic = true;
    if (isset($_POST['visibilite']) && !empty($_POST['visibilite'])) {
        // Si le champ dit "prive", on passe le salon en prive.
        $valeursPrive = array('prive' => true);
        if (isset($valeursPrive[$_POST['visibilite']])) {
            $estPublic = false;
        }
    }

    // Etape 4 : on cree le salon en base.
    // On cree le salon en base puis on renvoie dessus.
    // On garde l'auteur comme createur et on met la date actuelle.
    $nouvelId = salonCreer($nom, $nomUtilisateur, $estPublic, '', date('Y-m-d H:i'));
    // Etape 5 : on renvoie vers le salon cree.
    // On recharge la page avec l'id du nouveau salon.
    redirect('index.php?page=tchat&idSalon=' . $nouvelId);
}

// Action : modifier un salon.
// Ici on modifie le nom, la visibilite, etc.
if ($faireModifierSalon) {
    // Etape 1 : on recup l'id du salon.
    // On recup les infos de mise a jour.
    $idSalon = '';
    if (isset($_POST['idSalon']) && !empty($_POST['idSalon'])) {
        $idSalon = $_POST['idSalon'];
    }

    // Etape 2 : on recup le nouveau nom si donne.
    $nom = '';
    if (isset($_POST['nomSalon']) && !empty($_POST['nomSalon'])) {
        // On nettoie le nom tape.
        $nom = trim($_POST['nomSalon']);
    }

    // Etape 3 : on recup la visibilite.
    $estPublic = true;
    if (isset($_POST['visibilite']) && !empty($_POST['visibilite'])) {
        // Si "prive" => on bascule en prive.
        $valeursPrive = array('prive' => true);
        if (isset($valeursPrive[$_POST['visibilite']])) {
            $estPublic = false;
        }
    }

    // Etape 4 : on charge le salon.
    // On charge le salon.
    // On doit charger l'ancien salon pour comparer et garder les valeurs.
    $salon = salonTrouver($idSalon);

    // Etape 5 : on check que le salon existe.
    // On check que le salon existe.
    $salonExiste = false;
    if (!empty($salon)) {
        // Salon trouve en base.
        $salonExiste = true;
    }

    // Etape 6 : on check si l'utilisateur est le proprio.
    // On check si l'utilisateur est le proprio.
    $estProprietaire = false;
    if ($salonExiste && isset($salon['proprietaire']) && !empty($salon['proprietaire'])) {
        // On compare le proprio avec l'utilisateur actuel.
        $indexProprietaire = array($salon['proprietaire'] => true);
        if (isset($indexProprietaire[$nomUtilisateur])) {
            $estProprietaire = true;
        }
    }

    // Etape 7 : on check les droits de modif.
    // Droit de modif : admin ou proprio.
    // Si on n'a pas les droits, on ne change rien.
    $peutModifier = false;
    if ($salonExiste) {
        if ($estAdministrateur) {
            $peutModifier = true;
        }
        if ($estProprietaire) {
            $peutModifier = true;
        }
    }

    if ($peutModifier) {
        // Etape 8 : on prepare les valeurs finales.
        // Si un champ est vide, on garde l'ancien.
        // Exemple : si le nom n'a pas ete rempli, on garde l'ancien.
        $nomActuel = $nom;
        if (empty($nomActuel) && isset($salon['nom']) && !empty($salon['nom'])) {
            $nomActuel = $salon['nom'];
        }

        // Etape 9 : on garde le proprio actuel.
        $proprietaireActuel = '';
        if (isset($salon['proprietaire']) && !empty($salon['proprietaire'])) {
            // On ne change pas le createur ici.
            $proprietaireActuel = $salon['proprietaire'];
        }

        // Etape 10 : on gere la liste des autorises.
        $texteAutorises = '';
        if (isset($salon['autorises']) && !empty($salon['autorises'])) {
            $texteAutorises = $salon['autorises'];
        }
        if ($estPublic) {
            // Public => pas de liste d'autorises.
            // On vide la liste si le salon devient public.
            $texteAutorises = '';
        }

        // Etape 11 : on sauvegarde.
        // On sauve en base.
        // On envoie tout en base avec les valeurs finales.
        salonMettreAJour($idSalon, $nomActuel, $proprietaireActuel, $estPublic, $texteAutorises);
    }
    // Etape 12 : on renvoie au tchat.
    // Retour au tchat, salon selectionne.
    redirect('index.php?page=tchat&idSalon=' . $idSalon);
}

// Action : supprimer un salon.
// Ici on supprime un salon si on a les droits.
if ($faireSupprimerSalon) {
    // Etape 1 : on recup l'id du salon.
    // On recup l'id du salon.
    $idSalon = '';
    if (isset($_POST['idSalon']) && !empty($_POST['idSalon'])) {
        $idSalon = $_POST['idSalon'];
    }

    // Etape 2 : on charge le salon.
    // On charge le salon.
    $salon = salonTrouver($idSalon);
    $salonExiste = false;
    if (!empty($salon)) {
        // Salon trouve.
        $salonExiste = true;
    }

    // Etape 3 : on check si l'utilisateur est le proprio.
    // On check si l'utilisateur est le proprio.
    $estProprietaire = false;
    if ($salonExiste && isset($salon['proprietaire']) && !empty($salon['proprietaire'])) {
        // On compare le proprietaire avec l'utilisateur actuel.
        $indexProprietaire = array($salon['proprietaire'] => true);
        if (isset($indexProprietaire[$nomUtilisateur])) {
            $estProprietaire = true;
        }
    }

    // Etape 4 : on check le droit de suppression.
    // Droit de suppression : admin ou proprio.
    // Si pas de droits, on ne supprime rien.
    $peutSupprimer = false;
    if ($salonExiste) {
        if ($estAdministrateur) {
            $peutSupprimer = true;
        }
        if ($estProprietaire) {
            $peutSupprimer = true;
        }
    }

    if ($peutSupprimer) {
        // Etape 5 : on supprime salon + messages.
        // On supprime le salon et ses messages.
        // D'abord le salon, puis tous les messages associes.
        salonSupprimer($idSalon);
        messagesSupprimerParSalon($idSalon);
    }
    // Etape 6 : on retourne a la liste.
    // Retour a la liste des salons.
    redirect('index.php?page=tchat');
}

// Action : ajouter un utilisateur dans un salon prive.
// Ici on autorise un nouveau membre dans un salon prive.
if ($faireAjouterUtilisateur) {
    // Etape 1 : on recup l'id et le nom.
    // On recup les champs.
    $idSalon = '';
    if (isset($_POST['idSalon']) && !empty($_POST['idSalon'])) {
        $idSalon = $_POST['idSalon'];
    }

    $nouvelUtilisateur = '';
    if (isset($_POST['nouvelUtilisateur']) && !empty($_POST['nouvelUtilisateur'])) {
        // On nettoie le nom saisi.
        $nouvelUtilisateur = trim($_POST['nouvelUtilisateur']);
    }

    // Etape 2 : on check que le nom est rempli.
    if (empty($nouvelUtilisateur)) {
        // Il faut un nom.
        // Sans nom, on renvoie une erreur claire.
        redirect('index.php?page=tchat&idSalon=' . $idSalon . '&erreur=ajoutUtilisateurManquant');
    }

    // Etape 3 : on charge le salon cible.
    // On check le salon cible.
    $salon = salonTrouver($idSalon);
    if (empty($salon)) {
        // Salon introuvable => on bloque.
        redirect('index.php?page=tchat&erreur=accesRefuse');
    }

    // Etape 4 : on check droits (admin ou proprio).
    // On check droits : admin ou proprio.
    $estProprietaire = false;
    if (isset($salon['proprietaire']) && !empty($salon['proprietaire'])) {
        // On verifie si l'utilisateur actuel est le createur.
        $indexProprietaire = array($salon['proprietaire'] => true);
        if (isset($indexProprietaire[$nomUtilisateur])) {
            $estProprietaire = true;
        }
    }

    if (!$estAdministrateur) {
        if (!$estProprietaire) {
            // Ni admin ni proprio => pas le droit.
            redirect('index.php?page=tchat&erreur=accesRefuse');
        }
    }

    // Etape 5 : si salon public, on bloque.
    // Salon public -> pas d'ajout manuel.
    $estPublic = false;
    if (isset($salon['estPublic']) && !empty($salon['estPublic'])) {
        $estPublic = true;
    }

    if ($estPublic) {
        // On ne gere pas la liste d'autorises pour un salon public.
        redirect('index.php?page=tchat&idSalon=' . $idSalon . '&erreur=ajoutUtilisateurSalonPublic');
    }

    // Etape 6 : on check que l'utilisateur existe.
    // On check que l'utilisateur existe.
    $utilisateurTrouve = utilisateurTrouverParNomUtilisateur($nouvelUtilisateur);
    if (empty($utilisateurTrouve)) {
        // L'utilisateur n'existe pas dans la base.
        redirect('index.php?page=tchat&idSalon=' . $idSalon . '&erreur=ajoutUtilisateurInconnu');
    }

    // Etape 7 : on evite d'ajouter le proprio.
    if (isset($salon['proprietaire']) && !empty($salon['proprietaire'])) {
        $indexProprietaire = array($salon['proprietaire'] => true);
        if (isset($indexProprietaire[$nouvelUtilisateur])) {
            // Pas besoin d'ajouter le createur, il est deja dedans.
            redirect('index.php?page=tchat&idSalon=' . $idSalon . '&erreur=ajoutUtilisateurProprietaire');
        }
    }

    // Etape 8 : on prepare la liste des autorises.
    // On met les autorises en index rapide.
    $texteAutorises = '';
    if (isset($salon['autorises']) && !empty($salon['autorises'])) {
        $texteAutorises = $salon['autorises'];
    }
    $autorises = listeAutorises($texteAutorises);
    $indexAutorises = array();
    foreach ($autorises as $nomAutorise) {
        if (!empty($nomAutorise)) {
            // Petit index rapide pour tester plus tard.
            $indexAutorises[$nomAutorise] = true;
        }
    }

    // Etape 9 : on check s'il est deja dedans.
    if (isset($indexAutorises[$nouvelUtilisateur])) {
        // Deja dans la liste.
        // On stoppe pour eviter un doublon.
        redirect('index.php?page=tchat&idSalon=' . $idSalon . '&erreur=ajoutUtilisateurExistant');
    }

    // Etape 10 : on ajoute le nom au CSV.
    // On ajoute dans la liste CSV.
    if (empty($texteAutorises)) {
        // Liste vide => on met juste le nom.
        $texteAutorises = $nouvelUtilisateur;
    } else {
        // Liste non vide => on ajoute avec une virgule.
        $texteAutorises = $texteAutorises . ',' . $nouvelUtilisateur;
    }

    // Etape 11 : on update le salon.
    // On update le salon puis retour.
    // On sauvegarde la nouvelle liste dans la base.
    salonMettreAJour($idSalon, $salon['nom'], $salon['proprietaire'], $estPublic, $texteAutorises);
    // Etape 12 : on revient sur le salon.
    redirect('index.php?page=tchat&idSalon=' . $idSalon);
}

// Action : retirer un utilisateur d'un salon prive.
// Ici on retire un membre d'un salon prive.
if ($faireRetirerUtilisateur) {
    // Etape 1 : on recup l'id et le nom.
    // On recup les champs.
    $idSalon = '';
    if (isset($_POST['idSalon']) && !empty($_POST['idSalon'])) {
        $idSalon = $_POST['idSalon'];
    }

    $utilisateurRetirer = '';
    if (isset($_POST['utilisateurRetirer']) && !empty($_POST['utilisateurRetirer'])) {
        // On nettoie le nom saisi.
        $utilisateurRetirer = trim($_POST['utilisateurRetirer']);
    }

    // Etape 2 : on check que le nom est rempli.
    if (empty($utilisateurRetirer)) {
        // Nom obligatoire.
        // Sans nom, on ne peut rien retirer.
        redirect('index.php?page=tchat&idSalon=' . $idSalon . '&erreur=retraitUtilisateurManquant');
    }

    // Etape 3 : on charge le salon cible.
    // Salon cible obligatoire.
    $salon = salonTrouver($idSalon);
    if (empty($salon)) {
        // Salon pas trouve => on bloque.
        redirect('index.php?page=tchat&erreur=accesRefuse');
    }

    // Etape 4 : on check droits (admin ou proprio).
    // On check droits : admin ou proprio.
    $estProprietaire = false;
    if (isset($salon['proprietaire']) && !empty($salon['proprietaire'])) {
        // On check si l'utilisateur est le createur.
        $indexProprietaire = array($salon['proprietaire'] => true);
        if (isset($indexProprietaire[$nomUtilisateur])) {
            $estProprietaire = true;
        }
    }

    if (!$estAdministrateur) {
        if (!$estProprietaire) {
            // Si pas admin ni proprio, pas le droit.
            redirect('index.php?page=tchat&erreur=accesRefuse');
        }
    }

    // Etape 5 : si salon public, on bloque.
    // Salon public -> on ne retire pas.
    $estPublic = false;
    if (isset($salon['estPublic']) && !empty($salon['estPublic'])) {
        $estPublic = true;
    }

    if ($estPublic) {
        // Pas de gestion des membres pour salon public.
        redirect('index.php?page=tchat&idSalon=' . $idSalon . '&erreur=retraitUtilisateurSalonPublic');
    }

    // Etape 6 : on evite de retirer le proprio.
    if (isset($salon['proprietaire']) && !empty($salon['proprietaire'])) {
        $indexProprietaire = array($salon['proprietaire'] => true);
        if (isset($indexProprietaire[$utilisateurRetirer])) {
            // On ne retire pas le createur du salon.
            redirect('index.php?page=tchat&idSalon=' . $idSalon . '&erreur=retraitUtilisateurProprietaire');
        }
    }

    // Etape 7 : on recup la liste des autorises.
    // On recup la liste actuelle d'autorises.
    $texteAutorises = '';
    if (isset($salon['autorises']) && !empty($salon['autorises'])) {
        $texteAutorises = $salon['autorises'];
    }
    $autorises = listeAutorises($texteAutorises);
    $indexAutorises = array();
    foreach ($autorises as $nomAutorise) {
        if (!empty($nomAutorise)) {
            // On indexe pour tester vite.
            $indexAutorises[$nomAutorise] = true;
        }
    }

    // Etape 8 : on check qu'il est bien dans la liste.
    if (!isset($indexAutorises[$utilisateurRetirer])) {
        // Si pas dans la liste, on stoppe.
        // Pas besoin de retirer quelqu'un qui n'est pas dedans.
        redirect('index.php?page=tchat&idSalon=' . $idSalon . '&erreur=retraitUtilisateurAbsent');
    }

    // Etape 9 : on retire le nom de la liste.
    // On filtre la liste pour enlever l'utilisateur.
    $nouveauxAutorises = array();
    foreach ($autorises as $nomAutorise) {
        if (!empty($nomAutorise)) {
            // On garde tout sauf l'utilisateur a retirer.
            $garder = true;
            if (!empty($utilisateurRetirer)) {
                $indexRetrait = array($utilisateurRetirer => true);
                if (isset($indexRetrait[$nomAutorise])) {
                    $garder = false;
                }
            }
            if ($garder) {
                $nouveauxAutorises[] = $nomAutorise;
            }
        }
    }

    $nouveauTexteAutorises = '';
    if (!empty($nouveauxAutorises)) {
        // On reconstruit le texte CSV sans l'utilisateur.
        $nouveauTexteAutorises = implode(',', $nouveauxAutorises);
    }

    // Etape 10 : on update le salon.
    // On update le salon puis retour.
    // On sauvegarde la nouvelle liste.
    salonMettreAJour($idSalon, $salon['nom'], $salon['proprietaire'], $estPublic, $nouveauTexteAutorises);
    // Etape 11 : on revient sur le salon.
    redirect('index.php?page=tchat&idSalon=' . $idSalon);
}

// Action : envoyer un message.
// Ici on envoie un message dans un salon.
if ($faireEnvoyerMessage) {
    // Etape 1 : on recup le salon + le texte.
    // On recup les champs utiles.
    $idSalon = '';
    if (isset($_POST['idSalon']) && !empty($_POST['idSalon'])) {
        $idSalon = $_POST['idSalon'];
    }

    $texteMessage = '';
    if (isset($_POST['messageTexte']) && !empty($_POST['messageTexte'])) {
        // On nettoie le texte du message.
        $texteMessage = trim($_POST['messageTexte']);
    }

    // Etape 2 : on check que le message n'est pas vide.
    if (empty($texteMessage)) {
        // Message vide => erreur.
        // On renvoie avec une erreur simple.
        redirect('index.php?page=tchat&idSalon=' . $idSalon . '&erreur=messageManquant');
    }

    // Etape 3 : on charge le salon pour verifier l'acces.
    // On charge le salon cible.
    $salonCible = salonTrouver($idSalon);
    if (empty($salonCible)) {
        // Salon introuvable => pas d'acces.
        redirect('index.php?page=tchat&erreur=accesRefuse');
    }

    $texteAutorises = '';
    if (isset($salonCible['autorises']) && !empty($salonCible['autorises'])) {
        $texteAutorises = $salonCible['autorises'];
    }
    $autorises = listeAutorises($texteAutorises);
    $indexAutorises = array();
    foreach ($autorises as $nomAutorise) {
        if (!empty($nomAutorise)) {
            // Index rapide des autorises.
            $indexAutorises[$nomAutorise] = true;
        }
    }

    // Etape 4 : on check si le salon est public.
    // On check si le salon est public ou prive.
    $estPublic = false;
    if (isset($salonCible['estPublic']) && !empty($salonCible['estPublic'])) {
        $estPublic = true;
    }

    // Etape 5 : on check si on est proprio.
    // On check si on est proprio.
    $estProprietaire = false;
    if (isset($salonCible['proprietaire']) && !empty($salonCible['proprietaire'])) {
        // Le createur a toujours le droit.
        $indexProprietaire = array($salonCible['proprietaire'] => true);
        if (isset($indexProprietaire[$nomUtilisateur])) {
            $estProprietaire = true;
        }
    }

    // Etape 6 : on check si on est dans les autorises.
    // On check si l'utilisateur est autorise.
    $estAutorise = false;
    if (isset($indexAutorises[$nomUtilisateur])) {
        // Il est dans la liste.
        $estAutorise = true;
    }

    // Etape 7 : on calcule le droit final d'envoi.
    // Calcul final des droits.
    // On combine toutes les regles pour autoriser l'envoi.
    $accesAutorise = false;
    if ($estAdministrateur) {
        $accesAutorise = true;
    }
    if ($estPublic) {
        $accesAutorise = true;
    }
    if ($estProprietaire) {
        $accesAutorise = true;
    }
    if ($estAutorise) {
        $accesAutorise = true;
    }

    // Etape 8 : si pas autorise, on stoppe.
    if (!$accesAutorise) {
        // Pas le droit d'envoyer dans ce salon.
        redirect('index.php?page=tchat&erreur=accesRefuse');
    }

    // Etape 9 : on cree le message en base.
    // On cree le message puis on renvoie.
    // On enregistre le message avec la date du moment.
    messageCreer($salonCible['id'], $nomUtilisateur, $texteMessage, date('Y-m-d H:i'));
    // Etape 10 : on revient sur le salon.
    redirect('index.php?page=tchat&idSalon=' . $idSalon);
}

// Etape 9 : on prepare la liste des salons visibles.
// On prepare la liste des salons visibles.
// On charge tous les salons, puis on filtre ceux qu'on peut voir.
$salons = salonsTous();
$salonsParId = array();
$salonsVisibles = array();
$indexVisibles = array();

foreach ($salons as $salon) {
    // Petit index par id.
    if (isset($salon['id']) && !empty($salon['id'])) {
        // On stocke le salon par id pour acces rapide.
        $salonsParId[$salon['id']] = $salon;
    }

    // Liste d'autorises en index rapide.
    $texteAutorises = '';
    if (isset($salon['autorises']) && !empty($salon['autorises'])) {
        $texteAutorises = $salon['autorises'];
    }
    $autorises = listeAutorises($texteAutorises);
    $indexAutorises = array();
    foreach ($autorises as $nomAutorise) {
        if (!empty($nomAutorise)) {
            // Index rapide des autorises pour ce salon.
            $indexAutorises[$nomAutorise] = true;
        }
    }

    // Visibilite du salon.
    $salonEstPublic = false;
    if (isset($salon['estPublic']) && !empty($salon['estPublic'])) {
        $salonEstPublic = true;
    }

    // On check si l'utilisateur est proprio.
    $salonEstProprietaire = false;
    if (isset($salon['proprietaire']) && !empty($salon['proprietaire'])) {
        // Le createur du salon a toujours acces.
        $indexProprietaire = array($salon['proprietaire'] => true);
        if (isset($indexProprietaire[$nomUtilisateur])) {
            $salonEstProprietaire = true;
        }
    }

    // On check si l'utilisateur est dans les autorises.
    $salonEstAutorise = false;
    if (isset($indexAutorises[$nomUtilisateur])) {
        // L'utilisateur est dans la liste autorisee.
        $salonEstAutorise = true;
    }

    // Regle finale pour voir le salon.
    $peutVoir = false;
    if ($estAdministrateur) {
        $peutVoir = true;
    }
    if ($salonEstPublic) {
        $peutVoir = true;
    }
    if ($salonEstProprietaire) {
        $peutVoir = true;
    }
    if ($salonEstAutorise) {
        $peutVoir = true;
    }

    if ($peutVoir) {
        // Si ok, on garde ce salon.
        // On ajoute a la liste visible et on garde un index rapide.
        $salonsVisibles[] = $salon;
        if (isset($salon['id']) && !empty($salon['id'])) {
            $indexVisibles[$salon['id']] = true;
        }
    }
}

// Etape 10 : on choisit le salon actif.
// Salon choisi via URL, sinon on prend le premier visible.
// On decide quel salon afficher a droite.
$idSalonSelectionne = '';
if (isset($_GET['idSalon']) && !empty($_GET['idSalon'])) {
    $idSalonSelectionne = $_GET['idSalon'];
}

if (empty($idSalonSelectionne)) {
    if (!empty($salonsVisibles)) {
        if (isset($salonsVisibles[0]) && isset($salonsVisibles[0]['id']) && !empty($salonsVisibles[0]['id'])) {
            // Pas d'id en URL => on prend le premier salon visible.
            $idSalonSelectionne = $salonsVisibles[0]['id'];
        }
    }
}

// Etape 11 : index pour surligner le salon actif.
// Index pour surligner le salon actif.
// Sert juste pour la vue (mettre en surbrillance).
$indexSalonActif = array();
if (!empty($idSalonSelectionne)) {
    $indexSalonActif[$idSalonSelectionne] = true;
}

// Etape 12 : on recup le salon selectionne.
// Salon selectionne final.
// On recup le salon si il est bien visible.
$salonSelectionne = null;
if (!empty($idSalonSelectionne)) {
    if (isset($indexVisibles[$idSalonSelectionne]) && isset($salonsParId[$idSalonSelectionne])) {
        $salonSelectionne = $salonsParId[$idSalonSelectionne];
    }
}

// Etape 13 : on charge les messages du salon.
// Messages du salon en cours.
// On charge les messages a afficher dans la vue.
$messagesSalon = array();
if (!empty($salonSelectionne) && isset($salonSelectionne['id']) && !empty($salonSelectionne['id'])) {
    $messagesSalon = messagesPourSalon($salonSelectionne['id']);
}

// Etape 14 : on prepare la liste des membres.
// Liste des membres et autorises pour l'affichage.
// On prepare une liste propre pour montrer les membres.
$listeMembres = array();
$membresAutorises = array();
$proprietaireSelectionne = '';
if (!empty($salonSelectionne)) {
    if (isset($salonSelectionne['proprietaire']) && !empty($salonSelectionne['proprietaire'])) {
        $proprietaireSelectionne = $salonSelectionne['proprietaire'];
        // Le createur est toujours dans la liste.
        $listeMembres[] = $proprietaireSelectionne;
    }

    // On ajoute les autorises sans doublons.
    $texteAutorises = '';
    if (isset($salonSelectionne['autorises']) && !empty($salonSelectionne['autorises'])) {
        $texteAutorises = $salonSelectionne['autorises'];
    }
    $autorises = listeAutorises($texteAutorises);

    foreach ($autorises as $nomAutorise) {
        if (!empty($nomAutorise)) {
            $dejaDansListe = false;
            // Petit index pour eviter les doublons.
            $indexMembres = array();
            foreach ($listeMembres as $nomMembre) {
                $indexMembres[$nomMembre] = true;
            }
            if (isset($indexMembres[$nomAutorise])) {
                $dejaDansListe = true;
            }
            if (!$dejaDansListe) {
                // On ajoute l'utilisateur s'il n'est pas deja la.
                $listeMembres[] = $nomAutorise;
            }
        }
    }

    // Liste separee des autorises (sans le proprio).
    foreach ($autorises as $nomAutorise) {
        if (!empty($nomAutorise)) {
            $estProprietaireNom = false;
            if (!empty($proprietaireSelectionne)) {
                $indexProprietaire = array($proprietaireSelectionne => true);
                if (isset($indexProprietaire[$nomAutorise])) {
                    $estProprietaireNom = true;
                }
            }

            if (!$estProprietaireNom) {
                $dejaAutorise = false;
                // Index rapide pour eviter les doublons.
                $indexAutorisesNoms = array();
                foreach ($membresAutorises as $nomMembre) {
                    $indexAutorisesNoms[$nomMembre] = true;
                }
                if (isset($indexAutorisesNoms[$nomAutorise])) {
                    $dejaAutorise = true;
                }
                if (!$dejaAutorise) {
                    // On garde une liste sans doublon des autorises.
                    $membresAutorises[] = $nomAutorise;
                }
            }
        }
    }
}

// Etape 15 : on check si on peut gerer le salon.
// On check si on peut gerer le salon.
// Gérer = modifier, ajouter, retirer, supprimer.
$peutGererSalon = false;
if ($estAdministrateur) {
    $peutGererSalon = true;
}
if (!empty($proprietaireSelectionne)) {
    $indexProprietaire = array($proprietaireSelectionne => true);
    if (isset($indexProprietaire[$nomUtilisateur])) {
        // Le createur peut tout gerer.
        $peutGererSalon = true;
    }
}

// Etape 16 : on recup le code erreur dans l'URL.
// On recup le code erreur dans l'URL.
// Si un message d'erreur est present, on le recup.
$erreur = '';
if (isset($_GET['erreur']) && !empty($_GET['erreur'])) {
    $erreur = $_GET['erreur'];
}

// Etape 17 : on traduit l'erreur en texte.
// Message d'erreur simple.
// On mappe le code erreur vers un texte lisible.
$messageErreur = '';
$carteErreurs = array(
    'salonManquant' => 'Nom du salon obligatoire.',
    'messageManquant' => 'Message vide.',
    'accesRefuse' => 'Acces au salon refuse.',
    'ajoutUtilisateurManquant' => "Nom d'utilisateur obligatoire.",
    'ajoutUtilisateurSalonPublic' => 'Salon public : ajoutez des membres seulement sur un salon prive.',
    'ajoutUtilisateurExistant' => "Cet utilisateur est deja dans le salon.",
    'ajoutUtilisateurInconnu' => "Cet utilisateur n'existe pas.",
    'ajoutUtilisateurProprietaire' => "Le createur du salon est deja membre.",
    'retraitUtilisateurManquant' => "Nom d'utilisateur obligatoire.",
    'retraitUtilisateurSalonPublic' => 'Salon public : suppression de membres non applicable.',
    'retraitUtilisateurProprietaire' => "Impossible de retirer le createur du salon.",
    'retraitUtilisateurAbsent' => "Cet utilisateur n'est pas dans le salon."
);
if (isset($carteErreurs[$erreur]) && !empty($carteErreurs[$erreur])) {
    // On garde le message final a afficher.
    $messageErreur = $carteErreurs[$erreur];
}

// Etape 18 : on affiche la vue.
// On affiche la vue.
// On envoie toutes les variables a la vue du chat.
require __DIR__ . '/../vue/chat.php';
?>
