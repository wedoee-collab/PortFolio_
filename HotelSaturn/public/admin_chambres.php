<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { header("Location: login.php"); exit(); }

$stmt = $pdo->query("SELECT * FROM chambres");
$chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Chambres - Admin</title>
    <link rel="stylesheet" href="style.css?v=4">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="admin_dashboard.php" style="color: #fff; text-decoration: none;">Administration</a></h1>
            <nav><a href="admin_dashboard.php">Retour Dashboard</a></nav>
        </div>
    </header>
    <section class="features">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: var(--primary-color); margin: 0;">Liste des Chambres</h2>
                <a href="admin_form_chambre.php" class="btn">Ajouter une chambre</a>
            </div>
            
            <table class="reservations-table">
                <thead><tr><th>Image</th><th>Nom</th><th>Prix</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($chambres as $c): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($c['image_url']); ?>" alt="img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td><?php echo htmlspecialchars($c['nom']); ?></td>
                            <td><?php echo htmlspecialchars($c['prix']); ?> €</td>
                            <td>
                                <a href="admin_form_chambre.php?id=<?php echo $c['id']; ?>" class="action-btn btn-edit">Modifier</a>
                                <a href="admin_supprimer_chambre.php?id=<?php echo $c['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Supprimer cette chambre ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</body>
</html>