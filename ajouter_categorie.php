<?php
session_start();

require_once 'classes/Database.php';
require_once 'classes/Category.php';

$base_donnees = new Database();
$base_D = $base_donnees->recupConnexion(); // <-- cohérent avec tes autres fichiers
$categorie = new Category($base_D);

if ($_POST) {
    $parent_id = ($_POST['parent_id'] == "0") ? null : $_POST['parent_id'];
    
    $categorie->creer_Categorie($_POST['name'], $parent_id); // <-- cohérent avec ta classe Category
    
    header("Location: admin.php?cat_added=1");
    exit();
}

$categories = $categorie->lire_Categorie();
?>

<form method="post">
    <input type="text" name="name" placeholder="Nom de la catégorie (ex: Pantalons)" required>
    
    <label>Catégorie parente :</label>
    <select name="parent_id">
        <option value="0">Aucune (Racine)</option>
        
        <?php while ($cat = $categories->fetch(PDO::FETCH_ASSOC)): ?>
            <option value="<?php echo $cat['id']; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>
    
    <button type="submit">Créer la catégorie</button>
</form>