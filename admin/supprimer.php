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

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? 0;
    
    if ($product_id) {
        try {
            // Vérifier si le produit existe
            $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product) {
                // Supprimer le produit
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                
                $_SESSION['success_message'] = "Produit '" . htmlspecialchars($product['name']) . "' supprimé avec succès !";
            } else {
                $_SESSION['error_message'] = "Produit introuvable.";
            }
            
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de la suppression : " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "ID du produit non valide.";
    }
    
    // Rediriger vers la liste des produits
    header('Location: products.php');
    exit();
}

// Si accès direct sans POST, rediriger
header('Location: products.php');
exit();
?>
