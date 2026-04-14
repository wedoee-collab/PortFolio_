<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Nanichat</title>
    <link rel="stylesheet" href="vue/index.css">
</head>
<body>
    <!-- Entete + logo -->
    <header>
        <div class="header-content">
            <div class="logo-container">
                <a href="index.php"><img src="vue/nanichatlogo.png" alt="Logo Nanichat" class="logo-img"></a>
            </div>
            <div class="nav-buttons">
                <!-- Lien vers la connexion -->
                <a href="index.php?page=connexion" class="image-btn">
                    <img src="vue/connexion.png" alt="Connexion">
                </a>
            </div>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-box">
            <h2>Creer un compte</h2>
            <?php if (!empty($messageErreur)) { ?>
                <!-- Message d'erreur si besoin -->
                <p style="color:#d9534f;"><?php echo $messageErreur; ?></p>
            <?php } ?>
            <!-- Formulaire d'inscription -->
            <form action="index.php?page=authentification" method="POST">
                <input type="hidden" name="action" value="inscription">
                <input type="text" name="nomUtilisateur" placeholder="Nom d'utilisateur" required>
                <input type="email" name="courriel" placeholder="Courriel" required>
                <input type="password" name="motDePasse" placeholder="Mot de passe" required>
                <button type="submit" class="image-btn">
                    <img src="vue/register.png" alt="S'inscrire">
                </button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; Nanichat 2026 - JNI</p>
    </footer>
</body>
</html>
