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

// Traitement du formulaire d'ajout de catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $parent_id = $_POST['parent_id'] ?? null;
    $errors = [];
    
    // Validation
    if (empty($name)) $errors[] = "Le nom de la catégorie est obligatoire";
    
    if (empty($errors)) {
        try {
            // Insérer la catégorie
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, parent_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$name, $parent_id]);
            
            $_SESSION['success_message'] = "Catégorie ajoutée avec succès !";
            header('Location: categories.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'ajout de la catégorie : " . $e->getMessage();
        }
    }
}

// Récupérer toutes les catégories avec le nombre de produits
$categories = [];
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des catégories";
}

// Récupérer les catégories pour le select parent
$parent_categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $parent_categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $parent_categories = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories - Admin</title>
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

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .category-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .category-header {
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
        }

        .category-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
        }

        .category-name {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .category-content {
            padding: 20px;
        }

        .category-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            text-align: center;
        }

        .category-stat {
            flex: 1;
        }

        .category-stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .category-stat-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
        }

        .category-description {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 20px;
            min-height: 50px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn-edit {
            background: var(--accent-color);
            color: white;
        }

        .btn-edit:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: var(--danger-color);
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
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
            .categories-grid {
                grid-template-columns: 1fr;
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
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title mb-0">Gestion des Catégories</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Catégories</li>
                        </ol>
                    </nav>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter une catégorie
                </button>
            </div>
        </div>

        <!-- Messages de succès/erreur -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['success_message']); 
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <?php echo htmlspecialchars($error); ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Categories Grid -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h4>Aucune catégorie trouvée</h4>
                <p>Il n'y a aucune catégorie enregistrée pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                        </div>
                        <div class="category-content">
                            <div class="category-stats">
                                <div class="category-stat">
                                    <div class="category-stat-value"><?php echo $category['product_count']; ?></div>
                                    <div class="category-stat-label">Produits</div>
                                </div>
                                <div class="category-stat">
                                    <div class="category-stat-value">
                                        <?php echo $category['product_count'] > 0 ? 'Actif' : 'Vide'; ?>
                                    </div>
                                    <div class="category-stat-label">Statut</div>
                                </div>
                            </div>
                            <div class="category-description">
                                <?php 
                                $description = $category['description'] ?? 'Aucune description disponible';
                                echo strlen($description) > 100 ? substr(htmlspecialchars($description), 0, 100) . '...' : htmlspecialchars($description);
                                ?>
                            </div>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" onclick="editCategory(<?php echo $category['id']; ?>)">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <button class="btn-action btn-delete" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal d'ajout de catégorie -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter une nouvelle catégorie
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom de la catégorie</label>
                            <input type="text" class="form-control" name="name" 
                                   placeholder="Ex: Vêtements Homme" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catégorie parente</label>
                            <select class="form-select" name="parent_id">
                                <option value="">Aucune (catégorie principale)</option>
                                <?php foreach ($parent_categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Ajouter la catégorie
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editCategory(categoryId) {
            // TODO: Implémenter la fonction d'édition
            alert('Fonction d\'édition à implémenter pour la catégorie ID: ' + categoryId);
        }

        function deleteCategory(categoryId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
                window.location.href = 'supprimer_categorie.php?id=' + categoryId;
            }
        }

        // Réinitialiser le formulaire du modal à la fermeture
        document.getElementById('addCategoryModal').addEventListener('hidden.bs.modal', function () {
            document.querySelector('#addCategoryModal form').reset();
        });
    </script>
</body>
</html>
