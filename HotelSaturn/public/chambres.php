<?php
require_once 'config.php';

// Logique de filtrage par prix
try {
    // Si un prix max est envoyé dans l'URL (GET), on l'utilise, sinon valeur par défaut 1000
    $max_price = !empty($_GET['max_price']) ? (int)$_GET['max_price'] : 1000;
    
    // Requête SQL pour récupérer les chambres correspondant au budget
    $stmt = $pdo->prepare("SELECT * FROM chambres WHERE prix <= ?");
    $stmt->execute([$max_price]);
    $chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $chambres = []; }

template_header('Nos Chambres');
?>
    <section class="features">
        <div class="container">
            <div class="filter-bar">
                <form action="chambres.php" method="GET">
                    <label for="max_price"><strong>Budget max par nuit :</strong></label>
                    <input type="range" id="max_price" name="max_price" min="50" max="1000" step="10" value="<?php echo $max_price; ?>" oninput="this.nextElementSibling.value = this.value + ' €'">
                    <output><?php echo $max_price; ?> €</output>
                    <button type="submit" class="btn">Filtrer</button>
                </form>
            </div>
            <div class="grid">
                <!-- Boucle d'affichage des chambres -->
                <?php foreach($chambres as $chambre): ?>
                    <div class="card room-card">
                        <img src="<?php echo htmlspecialchars($chambre['image_url']); ?>" alt="<?php echo htmlspecialchars($chambre['nom']); ?>" class="room-img">
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($chambre['nom']); ?></h3>
                            <p style="color: #666; font-size: 0.95rem; line-height: 1.5; flex-grow: 1;"><?php echo htmlspecialchars($chambre['description']); ?></p>
                            <div class="price-tag"><span><?php echo htmlspecialchars($chambre['prix']); ?> €</span><small>par nuit</small></div>
                            <?php if(!empty($_SESSION['user_id'])): ?>
                                <a href="reserver.php?id=<?php echo $chambre['id']; ?>" class="btn btn-full">Réserver ce séjour</a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-full" style="background-color: #bdc3c7; color: #fff;">Connexion requise</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php template_footer(); ?>