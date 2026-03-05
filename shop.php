<?php
require_once 'config.php';

/**
 * Classe Product - Gestion des produits en POO
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

/**
 * Classe Category - Gestion des catégories en POO
 */
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

// Récupérer tous les produits avec leurs catégories en POO
$productModel = new Product();
$products = $productModel->getAllWithCategories();

// Récupérer les catégories pour le filtre
$categoryModel = new Category();
$categories = $categoryModel->getAll();

// Filtrage par catégorie si demandé
if (isset($_GET['category']) && is_numeric($_GET['category'])) {
    $products = $productModel->getByCategory($_GET['category']);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Male Fashion Shop">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | Male Fashion</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- CSS uniquement -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/elegant-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Header -->
    <header class="header">
        <div class="header__top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="header__top__left">
                            <p>Free shipping, 30-day return or refund guarantee.</p>
                        </div>
                    </div>
                    <div class="col-lg-6 text-right">
                        <div class="header__top__links">
                            <a href="signin.php">Sign in</a>
                            <a href="#">FAQs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row align-items-center">
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

                <div class="col-lg-3 text-right">
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

    <!-- Breadcrumb -->
    <section class="breadcrumb-option">
        <div class="container">
            <div class="breadcrumb__text">
                <h4>Shop</h4>
                <div class="breadcrumb__links">
                    <a href="index.php">Home</a>
                    <span>Shop</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Shop Section -->
    <section class="shop spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <span>Nos Produits</span>
                        <h2>Boutique</h2>
                    </div>
                </div>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <p>Aucun produit trouvé pour le moment.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="product__item">
                                <div class="product__item__pic">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="img/product/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 300px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                            <?php echo substr(htmlspecialchars($product['name']), 0, 2); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="product__item__text">
                                    <h6><?php echo htmlspecialchars($product['name']); ?></h6>
                                    <a href="#" class="add-cart">+ Ajouter au panier</a>
                                    <div class="rating">
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star-half-o"></i>
                                    </div>
                                    <h5>€<?php echo number_format($product['price'], 2); ?></h5>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">

                <div class="col-lg-3">
                    <div class="footer__about">
                        <div class="footer__logo">
                            <a href="#"><img src="img/footer-logo.png" alt=""></a>
                        </div>
                        <p>The customer is at the heart of our unique business model.</p>
                        <img src="img/payment.png" alt="">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="footer__widget">
                        <h6>Shopping</h6>
                        <ul>
                            <li><a href="#">Clothing Store</a></li>
                            <li><a href="#">Shoes</a></li>
                            <li><a href="#">Accessories</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="footer__widget">
                        <h6>Support</h6>
                        <ul>
                            <li><a href="#">Contact Us</a></li>
                            <li><a href="#">Delivery</a></li>
                            <li><a href="#">Returns</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="footer__widget">
                        <h6>Newsletter</h6>
                        <p>Subscribe to get updates.</p>
                        <form method="POST" action="#">
                            <input type="email" placeholder="Your email">
                            <button type="submit"><span class="icon_mail_alt"></span></button>
                        </form>
                    </div>
                </div>

            </div>

            <div class="text-center mt-4">
                <p>Copyright © 2026 - All rights reserved</p>
            </div>
        </div>
    </footer>

</body>
</html>