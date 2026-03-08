<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Category.php';

$base_donnees = new Database();
$base_D       = $base_donnees->recupConnexion();
$categorie    = new Category($base_D);

if ($_POST) {
    $parent_id = ($_POST['parent_id'] == "0") ? null : $_POST['parent_id'];
    $categorie->creer_Categorie($_POST['name'], $parent_id);
    header("Location: admin.php?cat_added=1");
    exit();
}

$categories = $categorie->lire_Categorie();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une catégorie — My Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="form-page">

<form method="post" class="form-boite">
    <h2>Ajouter une catégorie</h2>

    <label>Nom de la catégorie</label>
    <input type="text" name="name" placeholder="Ex: Pantalons" required>

    <label>Catégorie parente</label>
    <select name="parent_id">
        <option value="0">Aucune (catégorie principale)</option>
        <?php while ($cat = $categories->fetch(PDO::FETCH_ASSOC)) { ?>
            <option value="<?php echo $cat['id']; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php } ?>
    </select>

    <button type="submit">Créer la catégorie</button>
    <a href="admin.php" class="lien-retour">&larr; Retour</a>
</form>

</body>
</html>