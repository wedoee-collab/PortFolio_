<?php
session_start();
require_once 'config.php';

// Vérification de la connexion et de l'ID
if (empty($_SESSION['user_id']) || empty($_GET['id'])) { header("Location: mes_reservations.php"); exit(); }

// Récupération de la réservation à modifier
$stmt = $pdo->prepare("SELECT reservations.*, chambres.nom, reservations.chambre_id FROM reservations JOIN chambres ON reservations.chambre_id = chambres.id WHERE reservations.id = ? AND reservations.user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);
if (empty($reservation)) die("Introuvable");

$error = "";

// Traitement de la mise à jour
if (!empty($_POST['date_debut'])) {
    // Vérification disponibilité (exclusion de la réservation actuelle via id != ?)
    $check = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE chambre_id = ? AND id != ? AND NOT (date_fin <= ? OR date_debut >= ?)");
    $check->execute([$reservation['chambre_id'], $_GET['id'], $_POST['date_debut'], $_POST['date_fin']]);
    
    if ($check->fetchColumn() > 0) {
        $error = "La chambre n'est pas disponible à ces dates.";
    } else {
        $stmt = $pdo->prepare("UPDATE reservations SET date_debut = ?, date_fin = ? WHERE id = ?");
        $stmt->execute([$_POST['date_debut'], $_POST['date_fin'], $_GET['id']]);
        header("Location: mes_reservations.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier - Hôtel Saturn</title>
    <link rel="stylesheet" href="style.css?v=3">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php" style="color: #fff; text-decoration: none;">Hôtel Saturn</a></h1>
            <nav>
                <a href="index.php">Accueil</a><a href="chambres.php">Nos Chambres</a><a href="mes_reservations.php">Mes Réservations</a>
                <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" style="color: #f39c12;">Administration</a>
                <?php endif; ?>
                <a href="logout.php">Déconnexion</a>
            </nav>
        </div>
    </header>
    <section class="login-section">
        <div class="card">
            <h2 style="text-align: center; color: var(--primary-color);">Modifier : <?php echo htmlspecialchars($reservation['nom']); ?></h2>
            <?php if(!empty($error)): ?><div style="color: red; text-align: center; margin-bottom: 15px;"><?php echo $error; ?></div><?php endif; ?>
            <div style="text-align: center; margin-bottom: 20px;">
                <a href="facture.php?id=<?php echo $reservation['id']; ?>" class="btn btn-info" target="_blank">Voir la facture</a>
            </div>
            <form action="" method="POST">
                <div class="form-group"><label>Arrivée</label><input type="date" name="date_debut" value="<?php echo $reservation['date_debut']; ?>" required></div>
                <div class="form-group"><label>Départ</label><input type="date" name="date_fin" value="<?php echo $reservation['date_fin']; ?>" required></div>
                <button type="submit" class="btn btn-full">Enregistrer</button>
                <a href="mes_reservations.php" style="display:block; text-align:center; margin-top:15px;">Annuler</a>
            </form>
        </div>
    </section>
    <footer><p>&copy; <?php echo date('Y'); ?> Hôtel Saturn JNI. Tous droits réservés.</p></footer>
</body>
</html>