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
        $stmt = $this->db->prepare("SELECT name FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

AuthService::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? 0;

    if ($product_id) {
        $productModel = new Product();
        
        $product = $productModel->find($product_id);
        
        if ($product && $productModel->delete($product_id)) {
            $_SESSION['success'] = "Produit supprimé";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression";
        }
    }
}

header('Location: products.php');
exit();
?>
