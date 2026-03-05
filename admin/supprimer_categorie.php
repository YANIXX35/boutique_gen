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

// Vérification admin avec la classe AuthService
AuthService::requireAdmin();

// Vérifier si l'ID de la catégorie est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID de catégorie invalide";
    header('Location: categories.php');
    exit();
}

$category_id = $_GET['id'];
$categoryModel = new Category();

// Vérifier si la catégorie existe avec POO
$category = $categoryModel->find($category_id);

if (!$category) {
    $_SESSION['error_message'] = "Catégorie non trouvée";
    header('Location: categories.php');
    exit();
}

// Vérifier si des produits sont associés à cette catégorie avec POO
$productCount = $categoryModel->getProductCount($category_id);

if ($productCount > 0) {
    $_SESSION['error_message'] = "Impossible de supprimer cette catégorie : " . $productCount . " produit(s) y sont associés. Veuillez d'abord déplacer ou supprimer ces produits.";
    header('Location: categories.php');
    exit();
}

// Supprimer la catégorie avec POO
if ($categoryModel->delete($category_id)) {
    $_SESSION['success_message'] = "Catégorie supprimée avec succès !";
} else {
    $_SESSION['error_message'] = "Erreur lors de la suppression de la catégorie";
}

header('Location: categories.php');
exit();
?>
