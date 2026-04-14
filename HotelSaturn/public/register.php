<?php
require_once 'config.php';
$message = "";

// Vérification que tous les champs sont remplis
if (!empty($_POST['nom']) && !empty($_POST['email']) && !empty($_POST['password'])) {
    // Vérification que les deux mots de passe correspondent
    if ($_POST['password'] === $_POST['confirm_password']) {
        try {
            // Vérification si l'email existe déjà en base de données
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            if ($stmt->rowCount() > 0) {
                $message = "Cet email est déjà utilisé.";
            } else {
                // Hachage du mot de passe (Indispensable pour la sécurité, ne jamais stocker en clair)
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                // Insertion du nouvel utilisateur
                $insert = $pdo->prepare("INSERT INTO users (nom, email, password) VALUES (?, ?, ?)");
                if ($insert->execute([$_POST['nom'], $_POST['email'], $hashed_password])) {
                    $message = "Compte créé ! <a href='login.php'>Connectez-vous</a>.";
                }
            }
        } catch (Exception $e) { $message = "Erreur technique."; }
    } else {
        $message = "Les mots de passe ne correspondent pas.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Hôtel Saturn</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php" style="color: #fff; text-decoration: none;">Hôtel Saturn</a></h1>
            <nav><a href="index.php">Accueil</a><a href="chambres.php">Nos Chambres</a><a href="login.php">Connexion</a></nav>
        </div>
    </header>
    <section class="login-section">
        <div class="card">
            <h2 style="text-align: center; color: var(--primary-color);">Créer un compte</h2>
            <?php if(!empty($message)): ?><div style="text-align: center; margin-bottom: 15px;"><?php echo $message; ?></div><?php endif; ?>
            <form action="register.php" method="POST">
                <div class="form-group"><label for="nom">Nom complet</label><input type="text" id="nom" name="nom" required></div>
                <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
                <div class="form-group"><label for="password">Mot de passe</label><input type="password" id="password" name="password" required></div>
                <div class="form-group"><label for="confirm_password">Confirmer</label><input type="password" id="confirm_password" name="confirm_password" required></div>
                <button type="submit" class="btn btn-full">S'inscrire</button>
            </form>
            <div class="auth-switch"><p>Déjà un compte ? <a href="login.php">Se connecter</a></p></div>
        </div>
    </section>
    <footer><p>&copy; <?php echo date('Y'); ?> Hôtel Saturn JNI. Tous droits réservés.</p></footer>
</body>
</html>