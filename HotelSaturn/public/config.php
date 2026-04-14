<?php
// Démarrage de la session : Indispensable pour stocker les infos de l'utilisateur connecté
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'project-database';
$dbname = 'hotel_saturn';
$user = 'root';
$pass = 'root_password';

// Connexion à la base de données via PDO (PHP Data Objects)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// --- Fonctions de simplification ---

// Sécurité : Fonction pour bloquer l'accès aux pages privées
function require_login() {
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Sécurité : Fonction pour bloquer l'accès aux pages d'administration
function require_admin() {
    if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

// Templating : Génère le haut de page HTML pour éviter la répétition
function template_header($title) {
    echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . ' - Hôtel Saturn</title>
    <link rel="stylesheet" href="style.css?v=5">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php" style="color: #fff; text-decoration: none;">Hôtel Saturn</a></h1>
            <nav>
                <a href="index.php">Accueil</a>
                <a href="chambres.php">Nos Chambres</a>';
    
    // Menu dynamique : On affiche des liens différents selon si l'utilisateur est connecté ou Admin
    if (!empty($_SESSION['user_id'])) {
        if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            echo '<a href="admin_dashboard.php" style="color: #f39c12;">Administration</a>';
        }
        echo '<a href="mes_reservations.php">Mes Réservations</a>';
        echo '<span style="color: white; font-weight: 500; padding: 0 15px;">Bonjour, ' . htmlspecialchars($_SESSION['user_nom'] ?? '') . '</span>';
        echo '<a href="logout.php" style="color: var(--accent-color);">Déconnexion</a>';
    } else {
        echo '<a href="login.php">Connexion</a>';
    }
    
    echo '  </nav>
        </div>
    </header>';
}

// Templating : Génère le bas de page (Footer)
function template_footer() {
    echo '<footer><p>&copy; ' . date('Y') . ' Hôtel Saturn JNI. Tous droits réservés.</p></footer>
</body>
</html>';
}

// Helper pour l'admin : Ajoute un suffixe au titre
function template_admin_header($title) {
    template_header($title . " [ADMIN]");
}
?>