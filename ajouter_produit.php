<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Product.php';
require_once 'classes/Category.php';

// 1. SÉCURITÉ : accès réservé aux admins
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// 2. TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $base_donnees = new Database();
    $base_D = $base_donnees->recupConnexion();
    $produit = new Product($base_D);

    $monImage = "default.png"; // Image par défaut si aucune n'est uploadée

    // Gestion de l'upload d'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $dossierImages = "uploads/";
        if (!file_exists($dossierImages)) {
            mkdir($dossierImages, 0777, true); // Crée le dossier si nécessaire
        }
        $monImage = time() . "_" . basename($_FILES["image"]["name"]);
        $cheminImage = $dossierImages . $monImage;
        move_uploaded_file($_FILES["image"]["tmp_name"], $cheminImage);
    }

    if ($produit->creer_Produit(
    $_POST['name'],
    $_POST['description'],
    $_POST['price'],
    $_POST['category_id'],
    $monImage
    )) {
        header("Location: admin.php?success=1");
        exit();
    }
}

// 3. Charger les catégories pour le menu déroulant
$base_donnees = new Database();
$base_D = $base_donnees->recupConnexion();
$categorie = new Category($base_D);
$toutes_Cat = $categorie->lire_Categorie();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit - My Shop</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <div class="admin-header">
        <h1>Ajouter un nouveau produit</h1>
        <a href="admin.php">⬅ Retour à l'administration</a>
    </div>

    <div class="admin-conteneur">
        <section class="admin-section">
            <h2>Informations du produit</h2>

            <form method="post" enctype="multipart/form-data" class="formulaire-admin">

                <div class="champ-formulaire">
                    <label for="name">Nom du produit</label>
                    <input type="text" id="name" name="name" placeholder="Ex: T-shirt classique" required>
                </div>

                <div class="champ-formulaire">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Décrivez le produit..."></textarea>
                </div>

                <div class="champ-formulaire">
                    <label for="price">Prix (Fcfa)</label>
                    <input type="number" id="price" step="0.01" name="price" placeholder="Ex: 5000" required>
                </div>

                <div class="champ-formulaire">
                    <label for="category_id">Catégorie</label>
                    <select id="category_id" name="category_id">
                        <option value="">-- Choisir une catégorie --</option>
                        <?php while ($cat = $toutes_Cat->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php
endwhile; ?>
                    </select>
                </div>

                <div class="champ-formulaire">
                    <label for="image">Image du produit</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <button type="submit" class="btn-soumettre--large">Ajouter au catalogue</button>
            </form>
        </section>
    </div>

</body>
</html>