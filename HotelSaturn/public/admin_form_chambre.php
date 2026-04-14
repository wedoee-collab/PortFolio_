<?php
session_start();
require_once 'config.php';

// Sécurité Admin
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { header("Location: login.php"); exit(); }

// Initialisation : On regarde si on est en mode "Modification" (ID présent) ou "Ajout"
$id = !empty($_GET['id']) ? $_GET['id'] : null;
$chambre = ['nom' => '', 'description' => '', 'prix' => '', 'image_url' => ''];

// Si mode Modification, on pré-remplit les données depuis la BDD
if (!empty($id)) {
    $stmt = $pdo->prepare("SELECT * FROM chambres WHERE id = ?");
    $stmt->execute([$id]);
    $chambre = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Traitement du formulaire (Soumission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $desc = $_POST['description'];
    $prix = $_POST['prix'];
    $img = $_POST['image_url'];

    if (!empty($id)) {
        // UPDATE : Mise à jour d'une chambre existante
        $stmt = $pdo->prepare("UPDATE chambres SET nom=?, description=?, prix=?, image_url=? WHERE id=?");
        $stmt->execute([$nom, $desc, $prix, $img, $id]);
    } else {
        // INSERT : Création d'une nouvelle chambre
        $stmt = $pdo->prepare("INSERT INTO chambres (nom, description, prix, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $desc, $prix, $img]);
    }
    header("Location: admin_chambres.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $id ? 'Modifier' : 'Ajouter'; ?> Chambre</title>
    <link rel="stylesheet" href="style.css?v=4">
</head>
<body>
    <header>
        <div class="container">
            <h1>Administration</h1>
            <nav><a href="admin_chambres.php">Retour</a></nav>
        </div>
    </header>
    <section class="login-section">
        <div class="card">
            <h2 style="text-align: center; color: var(--primary-color);"><?php echo $id ? 'Modifier' : 'Ajouter'; ?> une chambre</h2>
            <form action="" method="POST">
                <div class="form-group"><label>Nom</label><input type="text" name="nom" value="<?php echo htmlspecialchars($chambre['nom']); ?>" required></div>
                <div class="form-group"><label>Description</label><textarea name="description" rows="4" style="width:100%; padding:10px; border-radius:10px; border:1px solid #eee;" required><?php echo htmlspecialchars($chambre['description']); ?></textarea></div>
                <div class="form-group"><label>Prix (€)</label><input type="number" name="prix" value="<?php echo htmlspecialchars($chambre['prix']); ?>" required></div>
                <div class="form-group"><label>URL Image</label><input type="text" name="image_url" value="<?php echo htmlspecialchars($chambre['image_url']); ?>" required></div>
                <button type="submit" class="btn btn-full">Enregistrer</button>
            </form>
        </div>
    </section>
</body>
</html>