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
    
    public function create($name, $price, $categoryId, $image = '') {
        $stmt = $this->db->prepare("INSERT INTO products (name, price, category_id, image) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $price, $categoryId, $image]);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $image = '';
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
        $product = new Product();
        $product->create($name, $price, $category_id, $image);
        
        $_SESSION['success'] = "Produit ajouté";
        header('Location: products.php');
        exit();
    }
}

$category = new Category();
$categories = $category->getAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ajouter Produit</title>
</head>
<body>
    <h2>Ajouter un Produit</h2>
    
    <a href="products.php">← Retour</a>
    
    <?php if (!empty($errors)): ?>
        <div style="color: red; padding: 10px; margin: 10px 0; border: 1px solid red;">
            <?php foreach ($errors as $error): ?>
                <?= $error ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <table>
            <tr>
                <td>Nom:</td>
                <td><input type="text" name="name" required></td>
            </tr>
            <tr>
                <td>Prix:</td>
                <td><input type="number" step="0.01" name="price" required></td>
            </tr>
            <tr>
                <td>Catégorie:</td>
                <td>
                    <select name="category_id" required>
                        <option value="">Choisir...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Image:</td>
                <td><input type="file" name="image" accept="image/*"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <input type="submit" value="Ajouter">
                </td>
            </tr>
        </table>
    </form>
</body>
</html>
