<?php
// Démarrer la session et vérifier si l'utilisateur est admin
session_start();
require_once '../config.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: ../signin.php');
    exit();
}

// Vérifier si l'utilisateur est admin (vous pouvez adapter cette logique)
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

// Traitement des actions CRUD pour la gestion des utilisateurs (PHP classique)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_user':
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $admin = isset($_POST['admin']) ? 1 : 0;
                
                if (!empty($username) && !empty($email) && !empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, admin) VALUES (?, ?, ?, ?)");
                    if ($stmt->execute([$username, $email, $hashed_password, $admin])) {
                        $success_message = "Utilisateur créé avec succès";
                    } else {
                        $error_message = "Erreur lors de la création de l'utilisateur";
                    }
                } else {
                    $error_message = "Tous les champs sont obligatoires";
                }
                break;
                
            case 'edit_user':
                $userId = $_POST['user_id'] ?? 0;
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $admin = isset($_POST['admin']) ? 1 : 0;
                
                if (!empty($username) && !empty($email)) {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, admin = ? WHERE id = ?");
                    if ($stmt->execute([$username, $email, $admin, $userId])) {
                        $success_message = "Utilisateur modifié avec succès";
                    } else {
                        $error_message = "Erreur lors de la modification de l'utilisateur";
                    }
                } else {
                    $error_message = "Le nom d'utilisateur et l'email sont obligatoires";
                }
                break;
                
            case 'delete_user':
                $userId = $_POST['user_id'] ?? 0;
                
                // Empêcher de supprimer son propre compte
                if ($userId == $_SESSION['user_id']) {
                    $error_message = "Vous ne pouvez pas supprimer votre propre compte";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    if ($stmt->execute([$userId])) {
                        $success_message = "Utilisateur supprimé avec succès";
                    } else {
                        $error_message = "Erreur lors de la suppression de l'utilisateur";
                    }
                }
                break;
                
            case 'toggle_admin':
                $userId = $_POST['user_id'] ?? 0;
                
                // Empêcher de retirer les droits admin à soi-même
                if ($userId == $_SESSION['user_id']) {
                    $error_message = "Vous ne pouvez pas retirer vos propres droits admin";
                } else {
                    $stmt = $pdo->prepare("SELECT admin FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();
                    
                    if ($user) {
                        $newAdmin = $user['admin'] == 1 ? 0 : 1;
                        $stmt = $pdo->prepare("UPDATE users SET admin = ? WHERE id = ?");
                        if ($stmt->execute([$newAdmin, $userId])) {
                            $action = $newAdmin == 1 ? "accordés" : "retirés";
                            $success_message = "Droits admin $action avec succès";
                        } else {
                            $error_message = "Erreur lors de la modification des droits admin";
                        }
                    }
                }
                break;
        }
    }
}

// Récupérer les statistiques
$stats = [];
try {
    // Nombre d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['users'] = $stmt->fetch()['count'];
    
    // Nombre de produits
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $stats['products'] = $stmt->fetch()['count'];
    
    // Nombre de catégories
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $stats['categories'] = $stmt->fetch()['count'];
    
} catch (PDOException $e) {
    $stats['users'] = 0;
    $stats['products'] = 0;
    $stats['categories'] = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Male Fashion</title>
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
            --border-color: #dee2e6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito Sans', sans-serif;
            background-color: var(--light-bg);
            color: var(--primary-color);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 20px;
            z-index: 1000;
            transition: all 0.3s ease;
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
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
        }

        .sidebar-menu {
            list-style: none;
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
            background: var(--danger-color);
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

        /* Main Content */
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
            color: var(--primary-color);
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
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Stats Cards */
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
            border-left: 4px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .stat-card.users {
            border-left-color: var(--accent-color);
        }

        .stat-card.products {
            border-left-color: var(--success-color);
        }

        .stat-card.categories {
            border-left-color: var(--warning-color);
        }

        .stat-card.overview {
            border-left-color: var(--primary-color);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .stat-card.users .stat-icon {
            background: rgba(102, 126, 234, 0.1);
            color: var(--accent-color);
        }

        .stat-card.products .stat-icon {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .stat-card.categories .stat-icon {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }

        .stat-card.overview .stat-icon {
            background: rgba(26, 26, 26, 0.1);
            color: var(--primary-color);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        /* Sections Grid */
        .sections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .section-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .section-header {
            padding: 25px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .section-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-content {
            padding: 25px;
        }

        .section-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .section-stat {
            text-align: center;
        }

        .section-stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .section-stat-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
        }

        .section-actions {
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
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--accent-color);
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--light-bg);
            color: var(--primary-color);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        /* Content Sections */
        .content-section {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .content-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            color: var(--primary-color);
            margin: 0 0 5px 0;
        }

        .page-header p {
            color: #6c757d;
            margin: 0;
        }

        /* Tables Styles */
        .users-table, .products-table {
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

        /* Styles optimisés pour le dashboard */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 15px;
        }

        .user-info {
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
        }

        .btn-edit { background: var(--accent-color); color: white; }
        .btn-edit:hover { background: #5a67d8; }
        
        .btn-admin { background: var(--warning-color); color: white; }
        .btn-admin:hover { background: #e0a800; }
        
        .btn-delete { background: var(--danger-color); color: white; }
        .btn-delete:hover { background: #c82333; }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }

        .close:hover { color: var(--danger-color); }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--primary-color);
        }

        .form-group input, .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group input:focus, .form-control:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-primary {
            background: var(--accent-color);
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

        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover { background: #5a6268; }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

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

        .badge-admin { background: var(--success-color); }
        .badge-user { background: #6c757d; }
        .badge-category { background: var(--accent-color); }
        .badge-in-stock { background: var(--success-color); }
        .badge-out-stock { background: var(--danger-color); }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
        }

        .card-header {
            background: var(--primary-color);
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

        /* Categories Grid */
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
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary-color);
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

            .sections-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Male Fashion</h3>
            <p>Panel Administrateur</p>
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

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <div class="top-header">
            <h1>Tableau de Bord</h1>
            <div class="user-info">
                <span>Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card overview">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value"><?php echo $stats['users'] + $stats['products'] + $stats['categories']; ?></div>
                <div class="stat-label">Total Éléments</div>
            </div>

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

        <!-- Dynamic Content Area -->
        <div id="content-area">
            <!-- Overview Content (Default) -->
            <div id="overview-content" class="content-section active">
                <div class="page-header">
                    <h2>Vue d'ensemble</h2>
                    <p>Statistiques générales de votre boutique</p>
                </div>
                
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card overview">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value"><?php echo $stats['users'] + $stats['products'] + $stats['categories']; ?></div>
                        <div class="stat-label">Total Éléments</div>
                    </div>

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
            </div>

            <!-- Users Content -->
            <div id="users-content" class="content-section">
                <div class="page-header">
                    <h2>Gestion des Utilisateurs</h2>
                    <p>Liste de tous les utilisateurs inscrits avec actions de gestion</p>
                </div>
                
                <!-- Messages d'alerte -->
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
                
                <!-- Formulaire de création d'utilisateur -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Ajouter un nouvel utilisateur</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="create_user">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="new_username">Nom d'utilisateur:</label>
                                        <input type="text" class="form-control" id="new_username" name="username" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="new_email">Email:</label>
                                        <input type="email" class="form-control" id="new_email" name="email" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="new_password">Mot de passe:</label>
                                        <input type="password" class="form-control" id="new_password" name="password" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="new_admin">Admin:</label>
                                        <select class="form-control" id="new_admin" name="admin">
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
                
                <!-- Tableau des utilisateurs -->
                <div class="users-table">
                    <?php
                    $users_list = [];
                    try {
                        $stmt = $pdo->query("SELECT id, username, email, admin, DATE_FORMAT(created_at, '%d/%m/%Y') as created_date FROM users ORDER BY created_at DESC");
                        $users_list = $stmt->fetchAll();
                    } catch (PDOException $e) {
                        $error = "Erreur lors de la récupération des utilisateurs";
                    }
                    ?>

                    <?php if (empty($users_list)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h4>Aucun utilisateur trouvé</h4>
                            <p>Il n'y a aucun utilisateur enregistré pour le moment.</p>
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
                                        <th>Date d'inscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users_list as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
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
                                            <td><?php echo $user['created_date']; ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <!-- Formulaire pour modifier -->
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
                                                    
                                                    <!-- Formulaire pour basculer admin -->
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_admin">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn-action btn-admin" title="Basculer admin">
                                                            <i class="fas fa-user-shield"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <!-- Formulaire pour supprimer -->
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
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

            <!-- Products Content -->
            <div id="products-content" class="content-section">
                <div class="page-header">
                    <h2>Gestion des Produits</h2>
                    <p>Liste de tous les produits de votre boutique</p>
                </div>
                
                <div class="products-table">
                    <?php
                    $products_list = [];
                    try {
                        $stmt = $pdo->query("
                            SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            ORDER BY p.created_at DESC
                        ");
                        $products_list = $stmt->fetchAll();
                    } catch (PDOException $e) {
                        $error = "Erreur lors de la récupération des produits";
                    }
                    ?>

                    <?php if (empty($products_list)): ?>
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
                                    <?php foreach ($products_list as $product): ?>
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

            <!-- Categories Content -->
            <div id="categories-content" class="content-section">
                <div class="page-header">
                    <h2>Gestion des Catégories</h2>
                    <p>Liste de toutes les catégories de produits</p>
                </div>
                
                <?php
                $categories_list = [];
                try {
                    $stmt = $pdo->query("
                        SELECT c.*, COUNT(p.id) as product_count 
                        FROM categories c 
                        LEFT JOIN products p ON c.id = p.category_id 
                        GROUP BY c.id 
                        ORDER BY c.name ASC
                    ");
                    $categories_list = $stmt->fetchAll();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la récupération des catégories";
                }
                ?>

                <?php if (empty($categories_list)): ?>
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <h4>Aucune catégorie trouvée</h4>
                        <p>Il n'y a aucune catégorie enregistrée pour le moment.</p>
                    </div>
                <?php else: ?>
                    <div class="categories-grid">
                        <?php foreach ($categories_list as $category): ?>
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
        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Dynamic content switching
        function showSection(sectionId) {
            // Hide all content sections
            const allSections = document.querySelectorAll('.content-section');
            allSections.forEach(section => {
                section.classList.remove('active');
            });

            // Show the selected section
            const targetSection = document.getElementById(sectionId + '-content');
            if (targetSection) {
                targetSection.classList.add('active');
            }

            // Update active menu item
            const allMenuItems = document.querySelectorAll('.sidebar-menu a');
            allMenuItems.forEach(item => {
                item.classList.remove('active');
            });

            // Add active class to clicked menu item
            const targetMenuItem = document.querySelector(`[href="#${sectionId}"]`);
            if (targetMenuItem) {
                targetMenuItem.classList.add('active');
            }

            // Update page title
            updatePageTitle(sectionId);
        }

        function updatePageTitle(sectionId) {
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

        // Add click event listeners to menu items
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.sidebar-menu a[href^="#"]');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const sectionId = this.getAttribute('href').substring(1); // Remove # character
                    showSection(sectionId);
                });
            });
        });
    </script>
</body>
</html>
