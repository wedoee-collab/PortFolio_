<?php
require_once 'config.php';
require_login(); // Vérifie si connecté avec empty($_SESSION['user_id'])

// Récupération des réservations de l'utilisateur
$stmt = $pdo->prepare("SELECT reservations.*, chambres.nom as chambre_nom, chambres.prix FROM reservations JOIN chambres ON reservations.chambre_id = chambres.id WHERE reservations.user_id = ? ORDER BY reservations.date_debut DESC");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

template_header('Mes Réservations');
?>
    <section class="features">
        <div class="container">
            <h2 style="color: var(--primary-color);">Historique de vos séjours</h2>
            <?php if (!empty($reservations)): ?>
                <table class="reservations-table">
                    <thead><tr><th>Chambre</th><th>Arrivée</th><th>Départ</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($reservations as $res): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($res['chambre_nom']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['date_debut'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['date_fin'])); ?></td>
                                <td>
                                    <a href="modifier_reservation.php?id=<?php echo $res['id']; ?>" class="action-btn btn-edit">Modifier</a>
                                    <a href="facture.php?id=<?php echo $res['id']; ?>" class="action-btn btn-info" target="_blank">Facture</a>
                                    <a href="supprimer_reservation.php?id=<?php echo $res['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Êtes-vous sûr ?');">Annuler</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?><p>Aucune réservation.</p><?php endif; ?>
        </div>
    </section>
<?php template_footer(); ?>