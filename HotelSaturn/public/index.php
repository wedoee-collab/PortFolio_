<?php
require_once 'config.php';
template_header('Accueil'); # éviter de répéter du code HTML sur chaque page
?>

    <section class="hero">
        <h2>Bienvenue sur Saturn</h2>
        <p>L'expérience ultime de confort et de luxe pour votre séjour.</p>
        <a href="chambres.php" class="btn">Réserver maintenant</a>
    </section>

    <section class="features">
        <div class="container">
            <h3>Pourquoi choisir l'Hôtel Saturn ?</h3>
            <div class="grid">
                <div class="card"><h4>Confort Absolu</h4><p>Des chambres spacieuses équipées pour votre bien-être total.</p></div>
                <div class="card"><h4>Service Premium</h4><p>Une équipe disponible 24h/24 pour répondre à toutes vos attentes.</p></div>
                <div class="card"><h4>Emplacement Idéal</h4><p>Au cœur de la ville, proche de toutes les commodités et attractions.</p></div>
            </div>
        </div>
    </section>

<?php template_footer(); ?>