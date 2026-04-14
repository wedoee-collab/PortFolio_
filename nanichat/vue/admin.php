<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration</title>
    <link rel="stylesheet" href="vue/index.css">
</head>
<body>
    <!-- Entete + logo + boutons -->
    <header>
        <div class="header-content">
            <div class="logo-container">
                <a href="index.php"><img src="vue/nanichatlogo.png" alt="Logo Nanichat" class="logo-img"></a>
            </div>
            <div class="nav-buttons">
                <!-- Retour tchat -->
                <a href="index.php?page=tchat" class="image-btn">
                    <img src="vue/message.png" alt="Retour tchat">
                </a>
                <!-- Deconnexion -->
                <a href="index.php?page=deconnexion" class="image-btn">
                    <img src="vue/logout.png" alt="Deconnexion">
                </a>
            </div>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-box" style="max-width:900px;">
            <h2>Tableau de bord administrateur</h2>
            <!-- Nom de la session -->
            <p>Connecte en tant que <?php echo $utilisateur['nomUtilisateur']; ?>.</p>

            <h3>Creer un salon</h3>
            <!-- Formulaire creation salon -->
            <form action="index.php?page=administration" method="POST" style="display:flex;flex-direction:column;gap:10px;">
                <input type="hidden" name="faireCreerSalon" value="1">
                <input type="text" name="nomSalon" placeholder="Nom du salon" required>
                <input type="text" name="proprietaire" placeholder="Proprietaire (optionnel)">
                <select name="visibilite">
                    <option value="public">Public</option>
                    <option value="prive">Prive</option>
                </select>
                <button type="submit">Creer</button>
            </form>

            <h3 style="margin-top:20px;">Salons existants</h3>
            <!-- Liste des salons -->
            <?php if (empty($salons)) { ?>
                <p>Aucun salon.</p>
            <?php } ?>
            <?php foreach ($salons as $salon) { ?>
                <?php
                // On prepare le libelle public/prive.
                $libellePublic = 'Prive';
                if (!empty($salon['estPublic'])) {
                    $libellePublic = 'Public';
                }

                // On preselect selon la visibilite.
                $selectionPublic = '';
                $selectionPrive = '';
                if (!empty($salon['estPublic'])) {
                    $selectionPublic = 'selected';
                } else {
                    $selectionPrive = 'selected';
                }
                ?>
                <div style="padding:12px;border:1px solid #eee;border-radius:12px;margin-top:10px;">
                    <strong><?php echo $salon['nom']; ?></strong>
                    <div style="font-size:0.9rem;">Proprietaire : <?php echo $salon['proprietaire']; ?> | <?php echo $libellePublic; ?></div>

                    <!-- Formulaire maj salon -->
                    <form action="index.php?page=administration" method="POST" style="display:flex;flex-direction:column;gap:8px;margin-top:10px;">
                        <input type="hidden" name="faireModifierSalon" value="1">
                        <input type="hidden" name="idSalon" value="<?php echo $salon['id']; ?>">
                        <input type="text" name="nomSalon" value="<?php echo $salon['nom']; ?>">
                        <input type="text" name="proprietaire" value="<?php echo $salon['proprietaire']; ?>">
                        <select name="visibilite">
                            <option value="public" <?php echo $selectionPublic; ?>>Public</option>
                            <option value="prive" <?php echo $selectionPrive; ?>>Prive</option>
                        </select>
                        <button type="submit">Mettre a jour</button>
                    </form>

                    <!-- Formulaire suppression -->
                    <form action="index.php?page=administration" method="POST" style="margin-top:8px;">
                        <input type="hidden" name="faireSupprimerSalon" value="1">
                        <input type="hidden" name="idSalon" value="<?php echo $salon['id']; ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                </div>
            <?php } ?>

            <h3 style="margin-top:20px;">Utilisateurs</h3>
            <!-- Liste des utilisateurs -->
            <ul style="list-style:none;padding:0;">
                <?php foreach ($utilisateurs as $utilisateurItem) { ?>
                    <?php
                    // Petit switch pour afficher le role.
                    $roleAffiche = $utilisateurItem['role'];
                    $rolesTraduits = array(
                        'admin' => 'administrateur',
                        'administrateur' => 'administrateur',
                        'user' => 'utilisateur',
                        'utilisateur' => 'utilisateur'
                    );
                    if (isset($rolesTraduits[$roleAffiche])) {
                        $roleAffiche = $rolesTraduits[$roleAffiche];
                    }
                    ?>
                    <li><?php echo $utilisateurItem['nomUtilisateur']; ?> (<?php echo $roleAffiche; ?>)</li>
                <?php } ?>
            </ul>
        </div>
    </main>

    <footer>
        <p>&copy; Nanichat 2026 - JNI</p>
    </footer>
</body>
</html>
