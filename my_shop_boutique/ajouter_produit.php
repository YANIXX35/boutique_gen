<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Product.php';
require_once 'classes/Category.php';

// 1. SÉCURITÉ
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// 2. CONNEXION ET RÉCUPÉRATION DES CATÉGORIES
$base_donnees = new Database();
$base_D       = $base_donnees->recupConnexion();
$produit      = new Product($base_D);
$categorie    = new Category($base_D);
$listeCategories = $categorie->lire_Categorie()->fetchAll(PDO::FETCH_ASSOC);

// 3. TRAITEMENT DU FORMULAIRE
if ($_POST) {
    $monImage = "img-par-defaut.png";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $dossierImages = "ressources/";
        if (!file_exists($dossierImages)) {
            mkdir($dossierImages, 0777, true);
        }
        $monImage    = time() . "_" . basename($_FILES["image"]["name"]);
        $cheminImage = $dossierImages . $monImage;
        move_uploaded_file($_FILES["image"]["tmp_name"], $cheminImage);
    }
    if ($produit->creer_Produit($_POST['name'], $_POST['description'], $_POST['price'], $_POST['category_id'], $monImage)) {
        header("Location: admin.php?section=produits&success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit — Y.E.F Shop</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="form-page">
<form method="post" enctype="multipart/form-data" class="form-boite">
    <h2>Ajouter un nouveau produit</h2>

    <label>Nom du produit</label>
    <input type="text" name="name" placeholder="Ex : T-shirt blanc" required>

    <label>Description</label>
    <textarea name="description" placeholder="Description du produit"></textarea>

    <label>Prix (Fcfa)</label>
    <input type="number" step="0.01" name="price" placeholder="Ex : 5000" required>

    <label>Image du produit</label>
    <input type="file" name="image">

    <label>Catégorie</label>
    <select name="category_id">
        <option value="0">Aucune catégorie</option>
        <?php foreach ($listeCategories as $cat) { ?>
            <option value="<?php echo $cat['id']; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php } ?>
    </select>

    <button type="submit">Ajouter au catalogue</button>
    <a href="admin.php?section=produits" class="lien-retour">
        &larr; Retour aux produits
    </a>
</form>
</body>
</html>