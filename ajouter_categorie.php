<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Category.php';

// Sécurité : accès réservé aux admins
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

$base_donnees = new Database();
$base_D = $base_donnees->recupConnexion();
$categorie = new Category($base_D);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent_id = ($_POST['parent_id'] == "0") ? null : $_POST['parent_id'];
    $categorie->creer_Categorie($_POST['name'], $parent_id);
    header("Location: admin.php?cat_added=1");
    exit();
}

// On récupère les catégories existantes pour le menu déroulant
$categories = $categorie->lire_Categorie();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une catégorie - My Shop</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <div class="admin-header">
        <h1>Ajouter une catégorie</h1>
        <a href="admin.php">⬅ Retour à l'administration</a>
    </div>

    <div class="admin-conteneur">
        <section class="admin-section">
            <h2>Nouvelle catégorie</h2>

            <form method="post" class="formulaire-admin">

                <div class="champ-formulaire">
                    <label for="name">Nom de la catégorie</label>
                    <input type="text" id="name" name="name" placeholder="Ex: Pantalons" required>
                </div>

                <div class="champ-formulaire">
                    <label for="parent_id">Catégorie parente</label>
                    <select id="parent_id" name="parent_id">
                        <option value="0">Aucune (catégorie principale)</option>
                        <?php while ($cat = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php
endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="btn-soumettre--large">Créer la catégorie</button>
            </form>
        </section>
    </div>

</body>
</html>