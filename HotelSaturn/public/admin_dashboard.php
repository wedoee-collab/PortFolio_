<?php
require_once 'config.php';
require_admin(); // Accès réservé aux administrateurs

// Récupération de TOUTES les données via une jointure SQL (JOIN)
// On lie les tables 'reservations', 'chambres' et 'users' pour avoir toutes les infos en une seule requête
$sql = "SELECT reservations.*, chambres.nom as chambre_nom, chambres.prix, users.nom as user_nom, users.email 
        FROM reservations 
        JOIN chambres ON reservations.chambre_id = chambres.id 
        JOIN users ON reservations.user_id = users.id 
        ORDER BY reservations.date_debut DESC";
$stmt = $pdo->query($sql);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Algorithme de calcul du Chiffre d'Affaires (CA) total
$ca_total = 0;
foreach ($reservations as $res) {
    $debut = new DateTime($res['date_debut']);
    $fin = new DateTime($res['date_fin']);
    
    $nb_jours = $debut->diff($fin)->days; // Calcul de la différence en jours
    if ($nb_jours < 1) { $nb_jours = 1; } // Au moins 1 nuit
    
    $ca_total += $nb_jours * $res['prix']; // Ajout au total
}

// Récupération des derniers inscrits pour l'aperçu
$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC LIMIT 10");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

template_admin_header('Tableau de bord');
?>
    <section class="features" style="background: #fff;">
        <div class="container">
            <h2 style="color: var(--primary-color); border-bottom: 2px solid #eee; padding-bottom: 10px;">Tableau de bord</h2>
            
            <!-- Statistiques -->
            <div class="grid" style="margin-bottom: 40px;">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($ca_total, 2); ?> €</div>
                    <div>Chiffre d'Affaires</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($reservations); ?></div>
                    <div>Réservations Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($users); ?></div>
                    <div>Utilisateurs Inscrits</div>
                </div>
            </div>

            <!-- Gestion Réservations -->
            <h3>Dernières Réservations</h3>
            <table class="reservations-table">
                <thead><tr><th>ID</th><th>Client</th><th>Chambre</th><th>Arrivée</th><th>Départ</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($reservations as $res): ?>
                        <tr>
                            <td>#<?php echo $res['id']; ?></td>
                            <td><?php echo htmlspecialchars($res['user_nom']); ?><br><small><?php echo htmlspecialchars($res['email']); ?></small></td>
                            <td><?php echo htmlspecialchars($res['chambre_nom']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($res['date_debut'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($res['date_fin'])); ?></td>
                            <td>
                                <a href="facture.php?id=<?php echo $res['id']; ?>" class="action-btn btn-info" target="_blank">Facture</a>
                                <a href="supprimer_reservation.php?id=<?php echo $res['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Annuler cette réservation client ?');">X</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Gestion Utilisateurs (Aperçu) -->
            <h3 style="margin-top: 40px;">Derniers Inscrits</h3>
            <ul style="list-style: none; padding: 0;">
                <?php foreach($users as $u): ?>
                    <li style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                        <span><strong><?php echo htmlspecialchars($u['nom']); ?></strong> (<?php echo htmlspecialchars($u['email']); ?>)</span>
                        <div>
                            <span class="badge" style="background: <?php echo $u['role'] == 'admin' ? '#e74c3c' : '#3498db'; ?>; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;"><?php echo strtoupper($u['role'] ?? 'client'); ?></span>
                            <?php if($u['role'] !== 'admin'): ?>
                                <a href="admin_supprimer_user.php?id=<?php echo $u['id']; ?>" style="color: red; margin-left: 10px; text-decoration: none; font-weight: bold;" onclick="return confirm('Supprimer cet utilisateur et toutes ses réservations ?');">X</a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php template_footer(); ?>