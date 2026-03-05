<?php
require_once 'config.php';

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
    
    public function getByCategory($categoryId) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = ? 
            ORDER BY p.id DESC
        ");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
}

class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY name");
        return $stmt->fetchAll();
    }
}

$productModel = new Product();
$products = $productModel->getAllWithCategories();

$categoryModel = new Category();
$categories = $categoryModel->getAll();

$category_filter = $_GET['category'] ?? '';
if ($category_filter && is_numeric($category_filter)) {
    $products = $productModel->getByCategory($category_filter);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Male Fashion | Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="header__top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <p>Livraison gratuite, retour 30 jours</p>
                    </div>
                    <div class="col-lg-6">
                        <div class="header__top__links">
                            <a href="signin.php">Sign in</a>
                            <a href="#">FAQs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="header__logo">
                        <a href="index.php"><img src="img/logo.png" alt=""></a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <nav class="header__menu">
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li class="active"><a href="shop.php">Shop</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="contact.php">Contacts</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-lg-3">
                    <div class="header__nav__option">
                        <a href="#"><img src="img/icon/search.png" alt=""></a>
                        <a href="#"><img src="img/icon/heart.png" alt=""></a>
                        <a href="#"><img src="img/icon/cart.png" alt=""> <span>0</span></a>
                        <div class="price">$0.00</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__text">
                        <h4>Shop</h4>
                        <div class="breadcrumb__links">
                            <a href="index.php">Home</a>
                            <span>Shop</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="shop spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="shop__sidebar">
                        <div class="shop__sidebar__categories">
                            <h4>Catégories</h4>
                            <ul class="shop__sidebar__categories__list">
                                <li><a href="shop.php">Tous les produits</a></li>
                                <?php foreach ($categories as $category): ?>
                                    <li>
                                        <a href="?category=<?= $category['id'] ?>">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="row">
                        <?php if (empty($products)): ?>
                            <div class="col-lg-12 text-center">
                                <h4>Aucun produit trouvé</h4>
                                <p>Essayez une autre catégorie.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="product__item">
                                        <div class="product__item__pic set-bg" 
                                             data-setbg="<?= $product['image'] ? 'img/product/' . htmlspecialchars($product['image']) : 'img/product/default.jpg' ?>">
                                            <ul class="product__item__pic__hover">
                                                <li><a href="#"><i class="fa fa-heart"></i></a></li>
                                                <li><a href="#"><i class="fa fa-shopping-cart"></i></a></li>
                                            </ul>
                                        </div>
                                        <div class="product__item__text">
                                            <h6><?= htmlspecialchars($product['name']) ?></h6>
                                            <h5>€<?= number_format($product['price'], 2) ?></h5>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>