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

class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function delete($id) {
        // Vérifier si des produits sont associés à cette catégorie
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            return ['success' => false, 'message' => 'Impossible de supprimer cette catégorie car elle contient des produits associés.'];
        }
        
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$id])) {
            return ['success' => true, 'message' => 'Catégorie supprimée avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la suppression de la catégorie'];
        }
    }
    
    public function getProductCount($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    public function getAssociatedProducts($id) {
        $stmt = $this->db->prepare("
            SELECT id, name, price 
            FROM products 
            WHERE category_id = ? 
            ORDER BY name ASC 
            LIMIT 10
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
}

AuthService::requireAdmin();

$category = new Category();
$errors = [];
$success_message = '';

$category_id = $_GET['id'] ?? 0;
$category_data = null;

if ($category_id && is_numeric($category_id)) {
    $category_data = $category->getById($category_id);
    
    if (!$category_data) {
        $_SESSION['error_message'] = "Catégorie non trouvée";
        header('Location: categories.php');
        exit();
    }
} else {
    $_SESSION['error_message'] = "ID de catégorie invalide";
    header('Location: categories.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $category->delete($category_id);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: categories.php');
        exit();
    } else {
        $errors[] = $result['message'];
    }
}

$product_count = $category->getProductCount($category_id);
$associated_products = $category->getAssociatedProducts($category_id);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Supprimer une Catégorie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a1a1a;
            --secondary-color: #333333;
            --accent-color: #667eea;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
        }

        body {
            font-family: 'Nunito Sans', sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 20px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        .delete-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .warning-box h5 {
            color: #856404;
            margin-bottom: 15px;
        }

        .warning-box p {
            color: #856404;
            margin: 0;
        }

        .category-info {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .category-info h6 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .category-info p {
            margin: 0;
            color: #6c757d;
        }

        .btn-back {
            background: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-back:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            color: white;
        }

        .btn-delete {
            background: var(--danger-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: white;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
        }

        .breadcrumb {
            background: none;
            padding: 0;
            margin: 10px 0 0 0;
        }

        .breadcrumb-item {
            color: #6c757d;
        }

        .breadcrumb-item.active {
            color: var(--primary-color);
        }

        .products-list {
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
        }

        .product-item {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .delete-container {
                padding: 20px;
            }
            
            .page-header {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <a href="categories.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Retour aux Catégories
            </a>
            <h1 class="page-title">Supprimer une Catégorie</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="categories.php">Catégories</a></li>
                    <li class="breadcrumb-item active">Supprimer</li>
                </ol>
            </nav>
        </div>

        <!-- Delete Container -->
        <div class="delete-container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Erreur :</strong>
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0 mt-2"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Category Information -->
            <div class="category-info">
                <h6><i class="fas fa-tag me-2"></i>Informations de la catégorie</h6>
                <p><strong>Nom :</strong> <?php echo htmlspecialchars($category_data['name']); ?></p>
                <?php if (!empty($category_data['description'])): ?>
                    <p><strong>Description :</strong> <?php echo htmlspecialchars($category_data['description']); ?></p>
                <?php endif; ?>
                <p><strong>Nombre de produits associés :</strong> <?php echo $product_count; ?></p>
            </div>

            <?php if ($product_count > 0): ?>
                <!-- Warning Box -->
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Attention !</h5>
                    <p>Cette catégorie contient <strong><?php echo $product_count; ?></strong> produit(s) associé(s). La suppression n'est pas possible tant que des produits y sont rattachés.</p>
                    
                    <?php if ($product_count <= 10): ?>
                        <p class="mb-2"><strong>Produits concernés :</strong></p>
                        <div class="products-list">
                            <?php foreach ($associated_products as $product): ?>
                                <div class="product-item">
                                    <i class="fas fa-box me-2"></i>
                                    <?php echo htmlspecialchars($product['name']); ?> - €<?php echo number_format($product['price'], 2); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    <p class="mb-0"><strong>Solution :</strong> Veuillez d'abord déplacer ou supprimer les produits de cette catégorie avant de pouvoir la supprimer.</p>
                </div>

                <div class="d-flex gap-3">
                    <a href="categories.php" class="btn-cancel">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour aux catégories
                    </a>
                </div>
            <?php else: ?>
                <!-- Warning Box -->
                <div class="warning-box">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Confirmation de suppression</h5>
                    <p>Vous êtes sur le point de supprimer définitivement la catégorie <strong>"<?php echo htmlspecialchars($category_data['name']); ?>"</strong>.</p>
                    <p>Cette action est <strong>irréversible</strong>. Êtes-vous sûr de vouloir continuer ?</p>
                </div>

                <form method="POST" action="">
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn-delete" onclick="return confirm('Êtes-vous absolument sûr de vouloir supprimer cette catégorie ? Cette action ne peut pas être annulée.');">
                            <i class="fas fa-trash me-2"></i>
                            Confirmer la suppression
                        </button>
                        <a href="categories.php" class="btn-cancel">
                            <i class="fas fa-times me-2"></i>
                            Annuler
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
