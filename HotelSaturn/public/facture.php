<?php
session_start();
require_once 'config.php';

// Vérification de base : il faut être connecté et avoir un ID de réservation
if (empty($_SESSION['user_id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

// Logique de sécurité d'accès aux données
if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    // CAS ADMIN : Il peut voir n'importe quelle facture
    $stmt = $pdo->prepare("
        SELECT reservations.*, chambres.nom as chambre_nom, chambres.prix, users.nom as user_nom, users.email 
        FROM reservations 
        JOIN chambres ON reservations.chambre_id = chambres.id 
        JOIN users ON reservations.user_id = users.id 
        WHERE reservations.id = ?
    ");
    $stmt->execute([$_GET['id']]);
} else {
    // CAS CLIENT : Il ne peut voir QUE ses propres factures (Vérification user_id = session_id)
    $stmt = $pdo->prepare("
        SELECT reservations.*, chambres.nom as chambre_nom, chambres.prix, users.nom as user_nom, users.email 
        FROM reservations 
        JOIN chambres ON reservations.chambre_id = chambres.id 
        JOIN users ON reservations.user_id = users.id 
        WHERE reservations.id = ? AND reservations.user_id = ?
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($reservation)) {
    die("Réservation introuvable.");
}

// Calcul du montant total pour l'affichage
$debut = new DateTime($reservation['date_debut']);
$fin = new DateTime($reservation['date_fin']);
$interval = $debut->diff($fin);
$nb_nuits = $interval->days;
if ($nb_nuits < 1) {
    $nb_nuits = 1;
}
$total = $nb_nuits * $reservation['prix'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture - Hôtel Saturn</title>
    <link rel="stylesheet" href="style.css?v=3">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php" style="color: #fff; text-decoration: none;">Hôtel Saturn</a></h1>
            <nav>
                <a href="index.php">Accueil</a>
                <a href="chambres.php">Nos Chambres</a>
                <a href="mes_reservations.php">Mes Réservations</a>
                <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" style="color: #f39c12;">Administration</a>
                <?php endif; ?>
                <a href="logout.php">Déconnexion</a>
            </nav>
        </div>
    </header>
    <section class="login-section">
        <div class="card" style="max-width: 600px;">
            <div class="invoice-header">
                <h2 style="margin:0; color: var(--primary-color);">Facture #<?php echo $reservation['id']; ?></h2>
                <div class="paid-stamp">PAYÉ</div>
            </div>
            <div class="invoice-details">
                <p><strong>Client</strong> <span><?php echo htmlspecialchars($reservation['user_nom']); ?></span></p>
                <p><strong>Chambre</strong> <span><?php echo htmlspecialchars($reservation['chambre_nom']); ?></span></p>
                <p><strong>Arrivée</strong> <span><?php echo $debut->format('d/m/Y'); ?></span></p>
                <p><strong>Départ</strong> <span><?php echo $fin->format('d/m/Y'); ?></span></p>
                <p><strong>Durée</strong> <span><?php echo $nb_nuits; ?> nuit(s)</span></p>
                <p><strong>Prix unitaire</strong> <span><?php echo number_format($reservation['prix'], 2); ?> € / nuit</span></p>
            </div>
            <div class="invoice-total">Total réglé : <?php echo number_format($total, 2); ?> €</div>
            <div style="text-align: center; margin-top: 30px; display: flex; gap: 10px; justify-content: center;">
                <button onclick="window.print()" class="btn btn-info">Imprimer</button>
                <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn" style="background-color: #7f8c8d;">Retour</a>
                <?php else: ?>
                    <a href="mes_reservations.php" class="btn" style="background-color: #7f8c8d;">Retour</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <footer><p>&copy; <?php echo date('Y'); ?> Hôtel Saturn JNI. Tous droits réservés.</p></footer>
</body>
</html>