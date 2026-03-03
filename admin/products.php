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

// Récupérer tous les produits avec leurs catégories
$products = [];
try {
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des produits";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
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

        .products-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table {
            margin: 0;
        }

        .table th {
            background: var(--light-bg);
            border: none;
            padding: 15px;
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
            font-size: 12px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid var(--light-bg);
        }

        .table tbody tr:hover {
            background: var(--light-bg);
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
        }

        .product-info {
            display: flex;
            align-items: center;
        }

        .product-details h6 {
            margin: 0;
            font-weight: 600;
            color: var(--primary-color);
        }

        .product-details p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }

        .badge-category {
            background: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-in-stock {
            background: var(--success-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-out-stock {
            background: var(--danger-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 16px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: var(--accent-color);
            color: white;
        }

        .btn-edit:hover {
            background: #5a67d8;
        }

        .btn-delete {
            background: var(--danger-color);
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
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
        }

        .btn-back:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: var(--border-color);
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 14px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <a href="dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Retour au Dashboard
            </a>
            <h1 class="page-title mt-3">Gestion des Produits</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Produits</li>
                </ol>
            </nav>
        </div>

        <!-- Products Table -->
        <div class="products-table">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger m-3">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-box"></i>
                    <h4>Aucun produit trouvé</h4>
                    <p>Il n'y a aucun produit enregistré pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <?php if (!empty($product['image'])): ?>
                                                <img src="../img/product/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                                            <?php else: ?>
                                                <div class="product-image" style="background: var(--light-bg); display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image" style="color: #ccc;"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="product-details">
                                                <h6><?php echo htmlspecialchars($product['name']); ?></h6>
                                                <p><?php echo substr(htmlspecialchars($product['description'] ?? ''), 0, 50) . '...'; ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($product['category_name']): ?>
                                            <span class="badge-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Non catégorisé</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="price"><?php echo number_format($product['price'] ?? 0, 2); ?> €</span>
                                    </td>
                                    <td>
                                        <?php 
                                        $stock = $product['stock'] ?? 0;
                                        if ($stock > 0): ?>
                                            <span class="badge-in-stock">En stock (<?php echo $stock; ?>)</span>
                                        <?php else: ?>
                                            <span class="badge-out-stock">Rupture</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-edit" onclick="editProduct(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function editProduct(productId) {
            // TODO: Implémenter la fonction d'édition
            alert('Fonction d\'édition à implémenter pour le produit ID: ' + productId);
        }

        function deleteProduct(productId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
                // TODO: Implémenter la fonction de suppression
                alert('Fonction de suppression à implémenter pour le produit ID: ' + productId);
            }
        }
    </script>
</body>
</html>
