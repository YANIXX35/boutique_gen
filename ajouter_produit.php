<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Product.php';

// 1. LA SÉCURITÉ
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// 2. LE TRAITEMENT
if ($_POST) {
    $base_donnees = new Database();
    $base_D = $base_donnees->recupConnexion();
    $produit = new Product($base_D);

    $monImage = "default.png";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $dossierImages = "uploads/";
        if (!file_exists($dossierImages)) {
            mkdir($dossierImages, 0777, true);
        }

        $monImage = time() . "_" . basename($_FILES["image"]["name"]);
        $cheminImage = $dossierImages . $monImage;
        move_uploaded_file($_FILES["image"]["tmp_name"], $cheminImage);
    }

    // **Méthode corrigée**
    if ($produit->creer_Produit($_POST['name'], $_POST['description'], $_POST['price'], $_POST['category_id'], $monImage)) {
        header("Location: admin.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un produit</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Ajouter un nouveau produit</h2>
    
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Nom du produit" required><br>
        
        <textarea name="description" placeholder="Description du produit"></textarea><br>
        
        <input type="number" step="0.01" name="price" placeholder="Prix" required><br>
        
        <label>Image du produit :</label>
        <input type="file" name="image"><br>
        
        <input type="number" name="category_id" placeholder="ID Catégorie (ex: 1)"><br>
        
        <button type="submit">Ajouter au catalogue</button>
    </form>
    
    <a href="admin.php">Retour</a>
</body>
</html>