<?php
session_start();
require_once '../config.php';

/**
 * Classe AuthService - Vérification admin
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
 * Classe User - Gestion utilisateurs
 */
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllUsers() {
        $stmt = $this->db->query("SELECT id, username, email, admin FROM users ORDER BY id DESC");
        return $stmt->fetchAll();
    }
    
    public function create($username, $email, $password, $admin = 0) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, admin) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $hashed_password, $admin]);
    }
    
    public function update($id, $username, $email, $admin) {
        $stmt = $this->db->prepare("UPDATE users SET username = ?, email = ?, admin = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $admin, $id]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function toggleAdmin($id) {
        $stmt = $this->db->prepare("SELECT admin FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $newAdmin = $user['admin'] == 1 ? 0 : 1;
            $stmt = $this->db->prepare("UPDATE users SET admin = ? WHERE id = ?");
            return $stmt->execute([$newAdmin, $id]);
        }
        return false;
    }
}

/**
 * Classe Product - Gestion produits
 */
class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllWithCategories() {
        $stmt = $this->db->query("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.id DESC
        ");
        return $stmt->fetchAll();
    }
}

/**
 * Classe Category - Gestion catégories
 */
class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllWithProductCount() {
        $stmt = $this->db->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id 
            ORDER BY c.name ASC
        ");
        return $stmt->fetchAll();
    }
}

// Vérification admin
AuthService::requireAdmin();

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userModel = new User();
    
    switch ($_POST['action']) {
        case 'create_user':
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $admin = isset($_POST['admin']) ? 1 : 0;
            
            if (!empty($username) && !empty($email) && !empty($password)) {
                if ($userModel->create($username, $email, $password, $admin)) {
                    $success_message = "Utilisateur créé";
                } else {
                    $error_message = "Erreur création";
                }
            } else {
                $error_message = "Champs obligatoires";
            }
            break;
            
        case 'edit_user':
            $userId = $_POST['user_id'] ?? 0;
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $admin = isset($_POST['admin']) ? 1 : 0;
            
            if (!empty($username) && !empty($email)) {
                if ($userModel->update($userId, $username, $email, $admin)) {
                    $success_message = "Utilisateur modifié";
                } else {
                    $error_message = "Erreur modification";
                }
            } else {
                $error_message = "Champs obligatoires";
            }
            break;
            
        case 'delete_user':
            $userId = $_POST['user_id'] ?? 0;
            
            if ($userId == $_SESSION['user_id']) {
                $error_message = "Impossible supprimer votre compte";
            } else {
                if ($userModel->delete($userId)) {
                    $success_message = "Utilisateur supprimé";
                } else {
                    $error_message = "Erreur suppression";
                }
            }
            break;
            
        case 'toggle_admin':
            $userId = $_POST['user_id'] ?? 0;
            
            if ($userId == $_SESSION['user_id']) {
                $error_message = "Impossible modifier vos droits";
            } else {
                if ($userModel->toggleAdmin($userId)) {
                    $success_message = "Droits modifiés";
                } else {
                    $error_message = "Erreur modification droits";
                }
            }
            break;
    }
}

// Récupérer les données
$stats = [];
try {
    $db = Database::getInstance()->getConnection();
    $stats['users'] = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    $stats['products'] = $db->query("SELECT COUNT(*) as count FROM products")->fetch()['count'];
    $stats['categories'] = $db->query("SELECT COUNT(*) as count FROM categories")->fetch()['count'];
} catch (PDOException $e) {
    $stats = ['users' => 0, 'products' => 0, 'categories' => 0];
}

$userModel = new User();
$productModel = new Product();
$categoryModel = new Category();

$users_list = $userModel->getAllUsers();
$products_list = $productModel->getAllWithCategories();
$categories_list = $categoryModel->getAllWithProductCount();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a1a1a;
            --secondary: #333;
            --accent: #667eea;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
        }

        body {
            font-family: 'Nunito Sans', sans-serif;
            background: var(--light);
            margin: 0;
            padding: 0;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 20px;
            z-index: 1000;
        }

        .sidebar-header {
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 30px;
        }

        .sidebar-header h3 {
            color: white;
            font-weight: 700;
            margin: 0;
        }

        .sidebar-header p {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            margin: 5px 0 0 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-menu i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }

        .logout-btn a {
            background: var(--danger);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-align: center;
            display: block;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-btn a:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }

        .top-header {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-header h1 {
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .stat-card.users { border-left-color: var(--accent); }
        .stat-card.products { border-left-color: var(--success); }
        .stat-card.categories { border-left-color: var(--warning); }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
            background: rgba(102, 126, 234, 0.1);
            color: var(--accent);
        }

        .stat-card.products .stat-icon {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .stat-card.categories .stat-icon {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        .content-section {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .content-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin: 0 0 5px 0;
        }

        .page-header p {
            color: #6c757d;
            margin: 0;
        }

        .table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 0;
        }

        .table th {
            background: var(--light);
            border: none;
            padding: 15px;
            font-weight: 600;
            color: var(--primary);
            text-transform: uppercase;
            font-size: 12px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid var(--light);
        }

        .table tbody tr:hover {
            background: var(--light);
        }

        .user-avatar-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 15px;
        }

        .user-info-small {
            display: flex;
            align-items: center;
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
            color: white;
        }

        .btn-edit { background: var(--accent); }
        .btn-edit:hover { background: #5a67d8; }
        
        .btn-admin { background: var(--warning); }
        .btn-admin:hover { background: #e0a800; }
        
        .btn-delete { background: var(--danger); }
        .btn-delete:hover { background: #c82333; }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: white;
        }

        .badge-admin { background: var(--success); }
        .badge-user { background: #6c757d; }
        .badge-category { background: var(--accent); }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
        }

        .card-header {
            background: var(--primary);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            border: none;
            padding: 15px 20px;
        }

        .card-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--primary);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-primary {
            background: var(--accent);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Male Fashion</h3>
            <p>Panel Admin</p>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="#overview" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    Tableau de bord
                </a>
            </li>
            <li>
                <a href="products.php">
                    <i class="fas fa-box"></i>
                    Produits
                </a>
            </li>
            <li>
                <a href="#users">
                    <i class="fas fa-users"></i>
                    Utilisateurs
                </a>
            </li>
            <li>
                <a href="categories.php">
                    <i class="fas fa-tags"></i>
                    Catégories
                </a>
            </li>
        </ul>

        <div class="logout-btn">
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                Déconnexion
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h1>Tableau de Bord</h1>
            <div class="user-info">
                <span>Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card users">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo $stats['users']; ?></div>
                <div class="stat-label">Utilisateurs</div>
            </div>

            <div class="stat-card products">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-value"><?php echo $stats['products']; ?></div>
                <div class="stat-label">Produits</div>
            </div>

            <div class="stat-card categories">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-value"><?php echo $stats['categories']; ?></div>
                <div class="stat-label">Catégories</div>
            </div>
        </div>

        <div id="content-area">
            <div id="overview-content" class="content-section active">
                <div class="page-header">
                    <h2>Vue d'ensemble</h2>
                    <p>Statistiques générales</p>
                </div>
            </div>

            <div id="users-content" class="content-section">
                <div class="page-header">
                    <h2>Gestion des Utilisateurs</h2>
                    <p>Liste des utilisateurs</p>
                </div>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Ajouter un utilisateur</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="create_user">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nom d'utilisateur:</label>
                                        <input type="text" class="form-control" name="username" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Email:</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Mot de passe:</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Admin:</label>
                                        <select class="form-control" name="admin">
                                            <option value="0">Non</option>
                                            <option value="1">Oui</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="table">
                    <?php if (empty($users_list)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h4>Aucun utilisateur trouvé</h4>
                            <p>Il n'y a aucun utilisateur enregistré.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom d'utilisateur</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users_list as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td>
                                                <div class="user-info-small">
                                                    <div class="user-avatar-small">
                                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <?php if ($user['admin'] == 1): ?>
                                                    <span class="badge-admin">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge-user">Utilisateur</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>N/A</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="edit_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                        <input type="hidden" name="admin" value="<?php echo $user['admin']; ?>">
                                                        <button type="submit" class="btn-action btn-edit" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_admin">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn-action btn-admin" title="Admin">
                                                            <i class="fas fa-user-shield"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                                            <input type="hidden" name="action" value="delete_user">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <button type="submit" class="btn-action btn-delete" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
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

            <div id="products-content" class="content-section">
                <div class="page-header">
                    <h2>Gestion des Produits</h2>
                    <p>Liste des produits</p>
                </div>
                
                <div class="table">
                    <?php if (empty($products_list)): ?>
                        <div class="empty-state">
                            <i class="fas fa-box"></i>
                            <h4>Aucun produit trouvé</h4>
                            <p>Il n'y a aucun produit enregistré.</p>
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
                                    <?php foreach ($products_list as $product): ?>
                                        <tr>
                                            <td>
                                                <div class="user-info-small">
                                                    <?php if (!empty($product['image'])): ?>
                                                        <img src="../img/product/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="user-avatar-small">
                                                    <?php else: ?>
                                                        <div class="user-avatar-small" style="background: var(--light); display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-image" style="color: #ccc;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                                        <br><small>Aucune description</small>
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
                                                <span class="badge-admin">Disponible</span>
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

            <div id="categories-content" class="content-section">
                <div class="page-header">
                    <h2>Gestion des Catégories</h2>
                    <p>Liste des catégories</p>
                </div>
                
                <?php if (empty($categories_list)): ?>
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <h4>Aucune catégorie trouvée</h4>
                        <p>Il n'y a aucune catégorie enregistrée.</p>
                    </div>
                <?php else: ?>
                    <div class="stats-grid">
                        <?php foreach ($categories_list as $category): ?>
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div class="stat-value"><?php echo htmlspecialchars($category['name']); ?></div>
                                <div class="stat-label"><?php echo $category['product_count']; ?> produits</div>
                                <div style="margin-top: 15px;">
                                    <button class="btn-action btn-edit" onclick="editCategory(<?php echo $category['id']; ?>)">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        function showSection(sectionId) {
            const allSections = document.querySelectorAll('.content-section');
            allSections.forEach(section => {
                section.classList.remove('active');
            });

            const targetSection = document.getElementById(sectionId + '-content');
            if (targetSection) {
                targetSection.classList.add('active');
            }

            const allMenuItems = document.querySelectorAll('.sidebar-menu a');
            allMenuItems.forEach(item => {
                item.classList.remove('active');
            });

            const targetMenuItem = document.querySelector(`[href="#${sectionId}"]`);
            if (targetMenuItem) {
                targetMenuItem.classList.add('active');
            }

            const titles = {
                'overview': 'Tableau de Bord',
                'users': 'Gestion des Utilisateurs',
                'products': 'Gestion des Produits',
                'categories': 'Gestion des Catégories'
            };

            const titleElement = document.querySelector('.top-header h1');
            if (titleElement && titles[sectionId]) {
                titleElement.textContent = titles[sectionId];
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.sidebar-menu a[href^="#"]');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const sectionId = this.getAttribute('href').substring(1);
                    showSection(sectionId);
                });
            });

            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebar');
                const toggle = document.querySelector('.mobile-toggle');
                
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(event.target) && 
                    !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            });
        });

        function editProduct(id) {
            window.location.href = 'modifier.php?id=' + id;
        }

        function deleteProduct(id) {
            if (confirm('Supprimer ce produit ?')) {
                window.location.href = 'supprimer.php?id=' + id;
            }
        }

        function editCategory(id) {
            alert('Fonction de modification de catégorie à implémenter');
        }

        function deleteCategory(id) {
            if (confirm('Supprimer cette catégorie ?')) {
                window.location.href = 'supprimer_categorie.php?id=' + id;
            }
        }
    </script>
</body>
</html>
