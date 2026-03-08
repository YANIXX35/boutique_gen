<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Product.php';

// 1. SÉCURITÉ : Toujours vérifier si c'est l'admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

$base_donnees = new Database();
$base_D = $base_donnees->recupConnexion();

// 2. RÉCUPÉRATION : On va chercher les données actuelles du produit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // On demande à la base de données les infos du produit qui a cet ID
    $requete = "SELECT * FROM products WHERE id = ?";
    $stmt = $base_D->prepare($requete);
    $stmt->execute([$id]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si le produit n'existe pas, on retourne à l'admin
    if (!$produit) {
        header("Location: admin.php");
        exit();
    }
}

// 3. MISE À JOUR : Si l'utilisateur a cliqué sur le bouton "Enregistrer"
if ($_POST) {
    // On prépare la commande SQL "UPDATE" (Mettre à jour)
    $requete = "UPDATE products SET name = ?, price = ?, category_id = ? WHERE id = ?";
    $stmt = $base_D->prepare($requete);
    
    if ($stmt->execute([
        $_POST['name'], 
        $_POST['price'], 
        $_POST['category_id'], 
        $_POST['id'] // L'ID caché nous permet de savoir quel produit modifier
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
</head>
<body>
    <div class="auth-boite"> <h2>Modifier le produit</h2>
        <p>Ancien nom : <strong><?php echo htmlspecialchars($produit['name']); ?></strong></p>

        <form method="post">
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
                <label>ID Catégorie :</label>
                <input type="number" name="category_id" value="<?php echo $produit['category_id']; ?>">
            </div>
            
            <button type="submit" class="btn-soumettre--large">Enregistrer les modifications →</button>
        </form>
        
        <br>
        <a href="admin.php" style="color: #666;">Annuler et retourner</a>
    </div>
</body>
</html>