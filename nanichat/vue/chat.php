<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - Nanichat</title>
    <link rel="stylesheet" href="vue/index.css">
</head>
<body>
    <!-- Entete du tchat -->
    <header>
        <div class="header-content">
            <div class="logo-container">
                <a href="index.php"><img src="vue/nanichatlogo.png" alt="Logo Nanichat" class="logo-img"></a>
            </div>
            <div class="nav-buttons">
                <?php if (!empty($afficherBoutonAdmin)) { ?>
                    <!-- Bouton admin si t'es admin -->
                    <a href="index.php?page=administration" class="btn-nav">Administration</a>
                <?php } ?>
                <!-- Deconnexion -->
                <a href="index.php?page=deconnexion" class="image-btn">
                    <img src="vue/logout.png" alt="Deconnexion">
                </a>
            </div>
        </div>
    </header>

    <main class="chat-main">
        <div class="chat-box">
            <!-- Colonne gauche : salons -->
            <aside class="chat-sidebar">
                <div class="sidebar-header">
                    <h3>Salons</h3>
                </div>
                <ul class="user-list">
                    <?php if (empty($salonsVisibles)) { ?>
                        <li class="user-item">Aucun salon disponible</li>
                    <?php } ?>
                    <?php foreach ($salonsVisibles as $salon) { ?>
                        <?php
                        // On marque le salon actif.
                        $classeActive = '';
                        if (isset($indexSalonActif[$salon['id']])) {
                            $classeActive = ' active';
                        }

                        // Petit tag si le salon est prive.
                        $libelleSalon = '';
                        if (empty($salon['estPublic'])) {
                            $libelleSalon = ' (prive)';
                        }
                        ?>
                        <li class="user-item<?php echo $classeActive; ?>">
                            <a href="index.php?page=tchat&idSalon=<?php echo $salon['id']; ?>" style="color:inherit;text-decoration:none;">
                                <?php echo $salon['nom']; ?>
                                <?php echo $libelleSalon; ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>

                <div style="padding:15px;">
                    <!-- Formulaire rapide pour creer un salon -->
                    <form action="index.php?page=tchat" method="POST">
                        <input type="hidden" name="faireCreerSalon" value="1">
                        <input type="text" name="nomSalon" placeholder="Nom du salon" required>
                        <select name="visibilite" style="margin-top:8px;">
                            <option value="public">Public</option>
                            <option value="prive">Prive</option>
                        </select>
                        <button type="submit" style="margin-top:8px;">Creer</button>
                    </form>
                </div>
            </aside>

            <!-- Colonne droite : messages + actions -->
            <section class="chat-area">
                <?php if (!empty($salonSelectionne)) { ?>
                    <div style="padding:10px;border-bottom:1px solid #eee;">
                        <strong>Membres :</strong>
                        <?php if (!empty($salonSelectionne['estPublic'])) { ?>
                            <span>Salon public (tous les utilisateurs peuvent entrer)</span>
                        <?php } else { ?>
                            <?php echo implode(', ', $listeMembres); ?>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="messages-container">
                    <?php if (!empty($messageErreur)) { ?>
                        <!-- Message d'erreur simple -->
                        <p style="color:#d9534f;"><?php echo $messageErreur; ?></p>
                    <?php } ?>
                    <?php if (empty($salonSelectionne)) { ?>
                        <p>Selectionnez un salon pour commencer.</p>
                    <?php } ?>
                    <?php foreach ($messagesSalon as $message) { ?>
                        <?php
                        // Style different pour moi et les autres.
                        $classeMessage = 'received';
                        if (isset($indexMoi[$message['nomUtilisateur']])) {
                            $classeMessage = 'sent';
                        }
                        ?>
                        <div class="message <?php echo $classeMessage; ?>">
                            <strong><?php echo $message['nomUtilisateur']; ?>:</strong>
                            <?php echo $message['contenu']; ?>
                            <div style="font-size:0.8rem;opacity:0.85;"><?php echo $message['creeLe']; ?></div>
                        </div>
                    <?php } ?>
                </div>

                <?php if (!empty($salonSelectionne)) { ?>
                    <!-- Formulaire d'envoi -->
                    <form class="chat-input-area" action="index.php?page=tchat" method="POST">
                        <input type="hidden" name="faireEnvoyerMessage" value="1">
                        <input type="hidden" name="idSalon" value="<?php echo $salonSelectionne['id']; ?>">
                        <input type="text" name="messageTexte" placeholder="Ecrivez votre message..." required>
                        <button type="submit" class="image-btn">
                            <img src="vue/message.png" alt="Envoyer">
                        </button>
                    </form>

                    <?php if (!empty($peutGererSalon)) { ?>
                        <!-- Gestion du salon (admin ou proprio) -->
                        <div style="padding:15px;border-top:1px solid #eee;">
                            <?php
                            // On preselect la visibilite.
                            $selectionPublic = '';
                            $selectionPrive = '';
                            if (!empty($salonSelectionne['estPublic'])) {
                                $selectionPublic = 'selected';
                            } else {
                                $selectionPrive = 'selected';
                            }
                            ?>
                            <form action="index.php?page=tchat" method="POST" style="display:flex;flex-direction:column;gap:8px;">
                                <input type="hidden" name="faireModifierSalon" value="1">
                                <input type="hidden" name="idSalon" value="<?php echo $salonSelectionne['id']; ?>">
                                <input type="text" name="nomSalon" value="<?php echo $salonSelectionne['nom']; ?>" placeholder="Nom du salon">
                                <select name="visibilite">
                                    <option value="public" <?php echo $selectionPublic; ?>>Public</option>
                                    <option value="prive" <?php echo $selectionPrive; ?>>Prive</option>
                                </select>
                                <button type="submit">Mettre a jour</button>
                            </form>

                            <?php if (empty($salonSelectionne['estPublic'])) { ?>
                                <!-- Ajout d'utilisateurs (salon prive) -->
                                <form action="index.php?page=tchat" method="POST" style="display:flex;gap:8px;margin-top:10px;">
                                    <input type="hidden" name="faireAjouterUtilisateur" value="1">
                                    <input type="hidden" name="idSalon" value="<?php echo $salonSelectionne['id']; ?>">
                                    <input type="text" name="nouvelUtilisateur" placeholder="Ajouter un utilisateur">
                                    <button type="submit">Ajouter</button>
                                </form>

                                <?php if (!empty($membresAutorises)) { ?>
                                    <div style="margin-top:10px;">
                                        <?php foreach ($membresAutorises as $membre) { ?>
                                            <!-- Enlever un membre autorise -->
                                            <form action="index.php?page=tchat" method="POST" style="display:flex;gap:8px;margin-top:6px;">
                                                <input type="hidden" name="faireRetirerUtilisateur" value="1">
                                                <input type="hidden" name="idSalon" value="<?php echo $salonSelectionne['id']; ?>">
                                                <input type="hidden" name="utilisateurRetirer" value="<?php echo $membre; ?>">
                                                <span><?php echo $membre; ?></span>
                                                <button type="submit">Supprimer</button>
                                            </form>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            <?php } ?>

                            <!-- Suppression du salon -->
                            <form action="index.php?page=tchat" method="POST" style="margin-top:10px;">
                                <input type="hidden" name="faireSupprimerSalon" value="1">
                                <input type="hidden" name="idSalon" value="<?php echo $salonSelectionne['id']; ?>">
                                <button type="submit">Supprimer le salon</button>
                            </form>
                        </div>
                    <?php } ?>
                <?php } ?>
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; Nanichat 2026 - JNI</p>
    </footer>
</body>
</html>
