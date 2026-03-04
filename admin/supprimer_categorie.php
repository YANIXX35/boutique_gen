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

// Vérifier si l'ID de la catégorie est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID de catégorie invalide";
    header('Location: categories.php');
    exit();
}

$category_id = $_GET['id'];

// Vérifier si la catégorie existe
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        $_SESSION['error_message'] = "Catégorie non trouvée";
        header('Location: categories.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la vérification de la catégorie";
    header('Location: categories.php');
    exit();
}

// Vérifier si des produits sont associés à cette catégorie
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as product_count FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $result = $stmt->fetch();
    
    if ($result['product_count'] > 0) {
        $_SESSION['error_message'] = "Impossible de supprimer cette catégorie : " . $result['product_count'] . " produit(s) y sont associés. Veuillez d'abord déplacer ou supprimer ces produits.";
        header('Location: categories.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la vérification des produits associés";
    header('Location: categories.php');
    exit();
}

// Supprimer la catégorie
try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    
    $_SESSION['success_message'] = "Catégorie supprimée avec succès !";
    header('Location: categories.php');
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la suppression de la catégorie : " . $e->getMessage();
    header('Location: categories.php');
    exit();
}
?>
