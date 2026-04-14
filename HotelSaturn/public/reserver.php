<?php
session_start();
require_once 'config.php';

// Sécurité : Seuls les utilisateurs connectés peuvent réserver
if (empty($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$message = "";
$chambre = null;

// Récupération des infos de la chambre sélectionnée pour l'affichage
if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM chambres WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $chambre = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Traitement de la demande de réservation (POST)
if (!empty($_POST['chambre_id'])) {
    // Vérification CRITIQUE de la disponibilité (Gestion des chevauchements de dates)
    // La logique est : "Y a-t-il une réservation qui ne finit pas avant mon arrivée ET qui ne commence pas après mon départ ?"
    $check = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE chambre_id = ? AND NOT (date_fin <= ? OR date_debut >= ?)");
    $check->execute([$_POST['chambre_id'], $_POST['date_debut'], $_POST['date_fin']]);
    
    if ($check->fetchColumn() > 0) {
        $message = "Désolé, cette chambre n'est plus disponible pour les dates sélectionnées.";
    } else {
        // Si disponible, on insère la réservation en base
        $stmt = $pdo->prepare("INSERT INTO reservations (user_id, chambre_id, date_debut, date_fin) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $_POST['chambre_id'], $_POST['date_debut'], $_POST['date_fin']])) {
            $new_id = $pdo->lastInsertId();
            header("Location: facture.php?id=" . $new_id);
            exit();
        } else {
            $message = "Erreur lors de la réservation.";
        }
    }
    // Recharger chambre
    $stmt = $pdo->prepare("SELECT * FROM chambres WHERE id = ?");
    $stmt->execute([$_POST['chambre_id']]);
    $chambre = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver - Hôtel Saturn</title>
    <link rel="stylesheet" href="style.css?v=2">
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
            <h2 style="text-align: center; color: var(--primary-color);">Réserver : <?php echo htmlspecialchars($chambre['nom'] ?? ''); ?></h2>
            <?php if(!empty($message)): ?><div style="text-align: center; margin-bottom: 15px;"><?php echo $message; ?></div><?php endif; ?>
            <?php if(!empty($chambre)): ?>
            <form action="reserver.php" method="POST">
                <input type="hidden" name="chambre_id" value="<?php echo $chambre['id']; ?>">
                <div class="form-group"><label>Arrivée</label><input type="date" name="date_debut" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo !empty($_POST['date_debut']) ? $_POST['date_debut'] : ''; ?>"></div>
                <div class="form-group"><label>Départ</label><input type="date" name="date_fin" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo !empty($_POST['date_fin']) ? $_POST['date_fin'] : ''; ?>"></div>
                <button type="submit" class="btn btn-full">Confirmer</button>
            </form>
            <?php endif; ?>
        </div>
    </section>
    <footer><p>&copy; <?php echo date('Y'); ?> Hôtel Saturn JNI. Tous droits réservés.</p></footer>
</body>
</html>