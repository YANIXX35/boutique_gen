<?php
session_start();
require_once '../config.php';

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

AuthService::requireAdmin();

$product_id = $_GET['id'] ?? 0;
if (!$product_id) {
    header('Location: products.php');
    exit();
}

$productModel = new Product();
$product = $productModel->find($product_id);

if (!$product) {
    header('Location: products.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $image = $product['image'];
    $errors = [];
    
    $errors = ValidationService::validateProduct($name, $price, $category_id);
    
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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Produit - Admin</title>
</head>
<body>
    <h2>Modifier le Produit</h2>
    
    <a href="products.php">← Retour</a>
    
    <?php if (!empty($errors)): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin: 10px 0;">
            <?php foreach ($errors as $error): ?>
                <?= htmlspecialchars($error) ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <td><label for="name">Nom du produit:</label></td>
                <td><input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>" required></td>
            </tr>
            <tr>
                <td><label for="price">Prix (€):</label></td>
                <td><input type="number" step="0.01" name="price" value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>" required></td>
            </tr>
            <tr>
                <td><label for="category_id">Catégorie:</label></td>
                <td>
                    <select name="category_id" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"
                                    <?= ((isset($_POST['category_id']) ? $_POST['category_id'] : $product['category_id']) == $category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
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
                        <img src="../img/product/<?= htmlspecialchars($product['image']) ?>" 
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
