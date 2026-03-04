<?php
// Démarrer la session et vérifier si l'utilisateur est admin
session_start();
require_once '../config.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: ../signin.php');
    exit();
}

// Vérifier si l'utilisateur est admin
$is_admin = false;
try {
    $stmt = $pdo->prepare("SELECT admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user && $user['admin'] == 1) {
        $is_admin = true;
    }
} catch (PDOException $e) {
    $is_admin = false;
}

if (!$is_admin) {
    header('Location: ../index.php');
    exit();
}

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $image = '';
    $errors = [];
    
    // Gestion de l'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_types)) {
            // Créer un nom de fichier unique
            $new_filename = uniqid('product_', true) . '.' . $file_extension;
            $upload_path = '../img/product/' . $new_filename;
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir('../img/product')) {
                mkdir('../img/product', 0777, true);
            }
            
            // Déplacer le fichier
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $new_filename;
            } else {
                $errors[] = "Erreur lors du téléchargement de l'image";
            }
        } else {
            $errors[] = "Format d'image non autorisé. Formats acceptés: jpg, jpeg, png, gif, webp";
        }
    }
    
    // Validation
    if (empty($name)) $errors[] = "Le nom du produit est obligatoire";
    if (empty($price) || !is_numeric($price)) $errors[] = "Le prix doit être un nombre valide";
    if (empty($category_id)) $errors[] = "La catégorie est obligatoire";
    
    if (empty($errors)) {
        try {
            // Insérer le produit (avec image si disponible)
            $stmt = $pdo->prepare("
                INSERT INTO products (name, price, category_id, image) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $price, $category_id, $image]);
            
            $success_message = "Produit ajouté avec succès !";
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'ajout du produit : " . $e->getMessage();
        }
    }
}

// Récupérer les catégories
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit - Admin</title>
</head>
<body>
    <h2>Ajouter un Nouveau Produit</h2>
    
    <?php if (!empty($errors)): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin: 10px 0;">
            <?php foreach ($errors as $error): ?>
                <?php echo htmlspecialchars($error); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        <div style="color: green; border: 1px solid green; padding: 10px; margin: 10px 0;">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <td><label for="name">Nom du produit:</label></td>
                <td><input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required></td>
            </tr>
            <tr>
                <td><label for="price">Prix (€):</label></td>
                <td><input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required></td>
            </tr>
            <tr>
                <td><label for="category_id">Catégorie:</label></td>
                <td>
                    <select name="category_id" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="image">Image du produit:</label></td>
                <td>
                    <input type="file" name="image" accept="image/*">
                    <br><small>Formats acceptés: JPG, JPEG, PNG, GIF, WEBP</small>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="submit" value="Ajouter le produit">
                    <a href="products.php" style="margin-left: 20px;">Retour</a>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>
