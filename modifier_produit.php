<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Product.php';

// 1. SÉCURITÉ : accès réservé aux admins
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

$base_donnees = new Database();
$base_D = $base_donnees->recupConnexion();

// 2. RÉCUPÉRATION : on cherche le produit à modifier
if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$id = intval($_GET['id']); // intval() sécurise l'ID (entier uniquement)

$requete = "SELECT * FROM products WHERE id = ?";
$stmt = $base_D->prepare($requete);
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

// Si le produit n'existe pas, retour à l'admin
if (!$produit) {
    header("Location: admin.php");
    exit();
}

// 3. MISE À JOUR : si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On met à jour le nom, la description, le prix et la catégorie
    $requete = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ? WHERE id = ?";
    $stmt = $base_D->prepare($requete);

    if ($stmt->execute([
    $_POST['name'],
    $_POST['description'],
    $_POST['price'],
    $_POST['category_id'],
    $_POST['id']
    ])) {
        header("Location: admin.php?updated=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le produit - My Shop</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <div class="admin-header">
        <h1>Modifier un produit</h1>
        <a href="admin.php">⬅ Retour à l'administration</a>
    </div>

    <div class="admin-conteneur">
        <section class="admin-section">
            <h2>Modifier : <em><?php echo htmlspecialchars($produit['name']); ?></em></h2>

            <form method="post" class="formulaire-admin">
                <!-- Champ caché pour envoyer l'ID du produit -->
                <input type="hidden" name="id" value="<?php echo $produit['id']; ?>">

                <div class="champ-formulaire">
                    <label for="name">Nom du produit</label>
                    <input type="text" id="name" name="name"
                           value="<?php echo htmlspecialchars($produit['name']); ?>" required>
                </div>

                <div class="champ-formulaire">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($produit['description']); ?></textarea>
                </div>

                <div class="champ-formulaire">
                    <label for="price">Prix (Fcfa)</label>
                    <input type="number" id="price" step="0.01" name="price"
                           value="<?php echo $produit['price']; ?>" required>
                </div>

                <div class="champ-formulaire">
                    <label for="category_id">ID Catégorie</label>
                    <input type="number" id="category_id" name="category_id"
                           value="<?php echo $produit['category_id']; ?>">
                </div>

                <button type="submit" class="btn-soumettre--large">Enregistrer les modifications →</button>
            </form>

            <br>
            <a href="admin.php" style="color: #666;">Annuler et retourner</a>
        </section>
    </div>

</body>
</html>