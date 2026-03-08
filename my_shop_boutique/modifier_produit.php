<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Product.php';
require_once 'classes/Category.php';

// 1. SÉCURITÉ : Toujours vérifier si c'est l'admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

$base_donnees    = new Database();
$base_D          = $base_donnees->recupConnexion();
$categorie       = new Category($base_D);
$listeCategories = $categorie->lire_Categorie()->fetchAll(PDO::FETCH_ASSOC);

// 2. RÉCUPÉRATION : On va chercher les données actuelles du produit
if (isset($_GET['id'])) {
    $id      = $_GET['id'];
    $requete = "SELECT * FROM products WHERE id = ?";
    $stmt    = $base_D->prepare($requete);
    $stmt->execute([$id]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$produit) {
        header("Location: admin.php");
        exit();
    }
}

// 3. MISE À JOUR : Si l'utilisateur a cliqué sur le bouton "Enregistrer"
if ($_POST) {
    $monImage = $produit['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $dossierImages = "ressources/";
        if (!file_exists($dossierImages)) {
            mkdir($dossierImages, 0777, true);
        }
        $monImage    = time() . "_" . basename($_FILES["image"]["name"]);
        $cheminImage = $dossierImages . $monImage;
        move_uploaded_file($_FILES["image"]["tmp_name"], $cheminImage);
    }
    $requete = "UPDATE products SET name = ?, price = ?, category_id = ?, image = ? WHERE id = ?";
    $stmt    = $base_D->prepare($requete);
    if ($stmt->execute([
        $_POST['name'],
        $_POST['price'],
        $_POST['category_id'],
        $monImage,
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
    <title>Modifier le produit</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="form-page">
<div class="auth-boite">
    <h2>Modifier le produit</h2>
    <p>Ancien nom : <strong><?php echo htmlspecialchars($produit['name']); ?></strong></p>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $produit['id']; ?>">

        <div class="champ-formulaire">
            <label>Nom du produit :</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($produit['name']); ?>" required>
        </div>

        <div class="champ-formulaire">
            <label>Prix (Fcfa) :</label>
            <input type="number" step="0.01" name="price" value="<?php echo $produit['price']; ?>" required>
        </div>

        <div class="champ-formulaire">
            <label>Catégorie :</label>
            <select name="category_id">
                <option value="0">Aucune catégorie</option>
                <?php foreach ($listeCategories as $cat) { ?>
                    <option value="<?php echo $cat['id']; ?>"
                        <?php if ($cat['id'] == $produit['category_id']) { echo 'selected'; } ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="champ-formulaire">
            <label>Image du produit :</label>
            <input type="file" name="image" accept="image/*">
            <?php if (!empty($produit['image']) && $produit['image'] !== 'img-par-defaut.png') { ?>
                <small class="texte-image-actuelle">Image actuelle : <?php echo htmlspecialchars($produit['image']); ?></small>
            <?php } ?>
        </div>

        <button type="submit" class="btn-soumettre--large">Enregistrer les modifications</button>
        <a href="admin.php" class="lien-retour">&larr; Annuler et retourner</a>
    </form>
</div>
</body>
</html>