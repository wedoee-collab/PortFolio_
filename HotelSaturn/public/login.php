<?php
require_once 'config.php';
$error = "";

// Vérification si le formulaire a été soumis
if (!empty($_POST['email']) && !empty($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

        // Requête SQL préparée pour récupérer l'utilisateur via son email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe haché (sécurité)
        if ($user && password_verify($password, $user['password'])) {
            // Création de la session utilisateur (connexion réussie)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_role'] = $user['role'] ?? 'client'; // Par défaut client si la colonne n'existe pas encore

            // Redirection intelligente selon le rôle
            if ($_SESSION['user_role'] === 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
        } else {
            $error = "Identifiants incorrects.";
        }
}

template_header('Connexion');
?>
    <section class="login-section">
        <div class="card">
            <h2 style="text-align: center; color: var(--primary-color);">Connexion</h2>
            <?php if($error): ?><div style="color: red; text-align: center; margin-bottom: 15px;"><?php echo $error; ?></div><?php endif; ?>
            <form action="login.php" method="POST">
                <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
                <div class="form-group"><label for="password">Mot de passe</label><input type="password" id="password" name="password" required></div>
                <button type="submit" class="btn btn-full">Se connecter</button>
            </form>
            <div class="auth-switch"><p>Pas encore de compte ? <a href="register.php">Créer un compte</a></p></div>
        </div>
    </section>
<?php template_footer(); ?>