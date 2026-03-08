<?php
require_once 'config.php';

class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getFeatured($limit = 6) {
        $limit = (int)$limit;
        $stmt = $this->db->query("
            SELECT p.*, c.name as cat_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.id DESC 
            LIMIT $limit
        ");
        return $stmt->fetchAll();
    }
}

$products = (new Product())->getFeatured(6);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Male Fashion</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/elegant-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .featured { padding: 60px 0; background: #f8f9fa; }
        .card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s; margin-bottom: 30px; }
        .card:hover { transform: translateY(-5px); }
        .card-img { height: 250px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 24px; }
        .card-body { padding: 20px; }
        .card-title { font-weight: 600; margin-bottom: 10px; color: #333; }
        .card-price { font-size: 18px; font-weight: 700; color: #667eea; }
        .section-title { text-align: center; margin-bottom: 50px; }
        .section-title h2 { font-size: 32px; font-weight: 700; color: #333; margin-bottom: 10px; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header__top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="header__top__left">
                            <p>Livraison gratuite, retour 30 jours</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="header__top__right">
                            <div class="header__top__links">
                                <a href="signin.php">Sign in</a>
                                <a href="#">FAQs</a>
                            </div>
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
                            <li class="active"><a href="index.php">Home</a></li>
                            <li><a href="shop.php">Shop</a></li>
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

    <section class="hero">
        <div class="hero__items" style="background-image: url('img/hero/hero-1.jpg'); background-size: cover; background-position: center; padding: 150px 0;">
            <div class="container">
                <div class="row">
                    <div class="col-xl-5 col-lg-7 col-md-8">
                        <div class="hero__text">
                            <h6>Collection Été</h6>
                            <h2>Automne - Hiver 2030</h2>
                            <p>Création de luxe essentiel. Fabriqué éthiquement avec un engagement pour une qualité exceptionnelle.</p>
                            <a href="shop.php" class="primary-btn">
                                Shop now <span class="arrow_right"></span>
                            </a>

                            <div class="hero__social">
                                <a href="#"><i class="fa fa-facebook"></i></a>
                                <a href="#"><i class="fa fa-twitter"></i></a>
                                <a href="#"><i class="fa fa-pinterest"></i></a>
                                <a href="#"><i class="fa fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="banner spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 offset-lg-4">
                    <div class="banner__item">
                        <div class="banner__item__pic">
                            <img src="img/banner/banner-1.jpg" alt="">
                        </div>
                        <div class="banner__item__text">
                            <h2>Collections Vêtements 2030</h2>
                            <a href="shop.php">Shop now</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="banner__item banner__item--middle">
                        <div class="banner__item__pic">
                            <img src="img/banner/banner-2.jpg" alt="">
                        </div>
                        <div class="banner__item__text">
                            <h2>Accessoires</h2>
                            <a href="shop.php">Shop now</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="banner__item banner__item--last">
                        <div class="banner__item__pic">
                            <img src="img/banner/banner-3.jpg" alt="">
                        </div>
                        <div class="banner__item__text">
                            <h2>Chaussures Printemps 2030</h2>
                            <a href="shop.php">Shop now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($products): ?>
    <section class="featured">
        <div class="container">
            <div class="section-title">
                <h2>Produits Vedettes</h2>
                <p>Découvrez nos derniers articles</p>
            </div>
            
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-img">
                                <?php if ($product['image']): ?>
                                    <img src="img/product/<?= htmlspecialchars($product['image']) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <?= strtoupper(substr(htmlspecialchars($product['name']), 0, 2)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="card-title"><?= htmlspecialchars($product['name']) ?></div>
                                <div class="card-price">€<?= number_format($product['price'] ?? 0, 2) ?></div>
                                <a href="shop.php" class="primary-btn" style="margin-top: 15px; padding: 10px 20px;">
                                    Voir détails
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center" style="margin-top: 40px;">
                <a href="shop.php" class="primary-btn" style="padding: 15px 30px;">
                    Voir tous les produits
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

</body>
</html>