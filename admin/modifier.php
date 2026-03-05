<?php
session_start();
require_once '../config.php';

/**
 * Classe AuthService - Vérification admin centralisée
 */
class AuthService {
    public static function requireAdmin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../signin.php');
            exit();
        }
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT admin FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user || $user['admin'] != 1) {
            header('Location: ../index.php');
            exit();
        }
    }
}

/**
 * Classe Product - Gestion des produits en POO
 */
class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function update($id, $name, $price, $categoryId, $image = null) {
        if ($image !== null) {
            $stmt = $this->db->prepare("UPDATE products SET name = ?, price = ?, category_id = ?, image = ? WHERE id = ?");
            return $stmt->execute([$name, $price, $categoryId, $image, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE products SET name = ?, price = ?, category_id = ? WHERE id = ?");
            return $stmt->execute([$name, $price, $categoryId, $id]);
        }
    }
}

/**
 * Classe Category - Gestion des catégories en POO
 */
class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY name");
        return $stmt->fetchAll();
    }
}

/**
 * Classe ValidationService - Validation centralisée
 */
class ValidationService {
    public static function validateProduct($name, $price, $categoryId) {
        $errors = [];
        if (empty($name)) $errors[] = "Nom obligatoire";
        if (empty($price) || !is_numeric($price)) $errors[] = "Prix invalide";
        if (empty($categoryId)) $errors[] = "Catégorie obligatoire";
        return $errors;
    }
    
    public static function validateImage($file) {
        if ($file['error'] != 0) return null;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            return ['error' => "Format non autorisé"];
        }
        
        $new_filename = uniqid('product_', true) . '.' . $file_extension;
        $upload_path = '../img/product/' . $new_filename;
        
        if (!is_dir('../img/product')) {
            mkdir('../img/product', 0777, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return ['filename' => $new_filename];
        }
        
        return ['error' => "Erreur upload image"];
    }
}

// Vérification admin avec la classe AuthService
AuthService::requireAdmin();

// Récupérer l'ID du produit
$product_id = $_GET['id'] ?? 0;
if (!$product_id) {
    header('Location: products.php');
    exit();
}

// Récupérer le produit avec POO
$productModel = new Product();
$product = $productModel->find($product_id);

if (!$product) {
    header('Location: products.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $image = $product['image']; // Garder l'ancienne image par défaut
    $errors = [];
    
    // Validation avec ValidationService
    $errors = ValidationService::validateProduct($name, $price, $category_id);
    
    // Gestion de l'image avec ValidationService
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageResult = ValidationService::validateImage($_FILES['image']);
        if (isset($imageResult['error'])) {
            $errors[] = $imageResult['error'];
        } else {
            $image = $imageResult['filename'];
        }
    }
    
    if (empty($errors)) {
        if ($productModel->update($product_id, $name, $price, $category_id, $image)) {
            $_SESSION['success'] = "Produit modifié";
            header('Location: products.php');
            exit();
        } else {
            $errors[] = "Erreur lors de la modification";
        }
    }
}

$category = new Category();
$categories = $category->getAll();
?>

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $image = $product['image'] ?? ''; // Garder l'ancienne image par défaut
    
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
                // Supprimer l'ancienne image si elle existe
                if (!empty($product['image']) && file_exists('../img/product/' . $product['image'])) {
                    unlink('../img/product/' . $product['image']);
                }
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
            // Mettre à jour le produit (avec image si disponible)
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, price = ?, category_id = ?, image = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $price, $category_id, $image, $product_id]);
            
            $success_message = "Produit modifié avec succès !";
            
            // Rafraîchir les données du produit
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la modification du produit : " . $e->getMessage();
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
    <title>Modifier un Produit - Admin</title>
</head>
<body>
    <h2>Modifier le Produit</h2>
    
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
                <td><input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? $product['name']); ?>" required></td>
            </tr>
            <tr>
                <td><label for="price">Prix (€):</label></td>
                <td><input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? $product['price']); ?>" required></td>
            </tr>
            <tr>
                <td><label for="category_id">Catégorie:</label></td>
                <td>
                    <select name="category_id" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"
                                    <?php echo ((isset($_POST['category_id']) ? $_POST['category_id'] : $product['category_id']) == $category['id']) ? 'selected' : ''; ?>>
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
                    <br><small>Laissez vide pour conserver l'image actuelle</small>
                    <?php if (!empty($product['image'])): ?>
                        <br><br>
                        <small>Image actuelle :</small><br>
                        <img src="../img/product/<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="Image actuelle" 
                             style="max-width: 200px; height: 100px; object-fit: cover; border: 1px solid #ccc;">
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="submit" value="Enregistrer les modifications">
                    <a href="products.php" style="margin-left: 20px;">Retour</a>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>
