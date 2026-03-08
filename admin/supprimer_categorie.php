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
 * Classe Category - Gestion des catégories en POO
 */
class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getProductCount($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as product_count FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch()['product_count'];
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

AuthService::requireAdmin();

$category_id = $_GET['id'] ?? 0;
$categoryModel = new Category();

$category = $categoryModel->find($category_id);

if (!$category) {
    $_SESSION['error'] = "Catégorie non trouvée";
    header('Location: categories.php');
    exit();
}

$productCount = $categoryModel->getProductCount($category_id);

if ($productCount > 0) {
    $_SESSION['error'] = "Impossible de supprimer : $productCount produit(s) lié(s)";
} else {
    if ($categoryModel->delete($category_id)) {
        $_SESSION['success'] = "Catégorie supprimée";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression";
    }
}

header('Location: categories.php');
exit();
?>
