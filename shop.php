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
    <style>
        .search-section {
            background: #f5f5f5;
            padding: 20px 0;
        }
        
        .search-bar {
            position: relative;
        }
        
        .search-container {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }
        
        #searchInput {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }
        
        #searchInput:focus {
            border-color: #007bff;
            box-shadow: 0 0 10px rgba(0,123,255,0.1);
        }
        
        #searchBtn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: #007bff;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: white;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        #searchBtn:hover {
            background: #0056b3;
        }
        
        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-top: 5px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .search-suggestions.active {
            display: block;
        }
        
        .suggestion-item {
            padding: 12px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .suggestion-item:hover {
            background: #f8f9fa;
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }
        
        .suggestion-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .suggestion-details {
            flex: 1;
        }
        
        .suggestion-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }
        
        .suggestion-info {
            font-size: 14px;
            color: #666;
        }
        
        .suggestion-price {
            font-weight: 700;
            color: #007bff;
            font-size: 16px;
        }
        
        .search-results {
            margin-top: 20px;
            min-height: 100px;
        }
        
        .search-loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .search-error {
            text-align: center;
            padding: 20px;
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .search-result-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        
        .search-result-item:hover {
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .search-container {
                margin: 0 15px;
            }
            
            #searchInput {
                padding: 12px 45px 12px 15px;
                font-size: 14px;
            }
            
            #searchBtn {
                width: 35px;
                height: 35px;
            }
            
            .suggestion-item {
                padding: 10px 15px;
            }
            
            .suggestion-image {
                width: 40px;
                height: 40px;
            }
        }
    </style>
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

    <section class="search-section spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="search-bar">
                        <div class="search-container">
                            <input type="text" id="searchInput" placeholder="Rechercher un produit..." autocomplete="off">
                            <button id="searchBtn"><i class="fa fa-search"></i></button>
                            <div id="searchSuggestions" class="search-suggestions"></div>
                        </div>
                        <div id="searchResults" class="search-results"></div>
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
                    <div class="row" id="productsContainer">
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
    
    <script>
        $(document).ready(function() {
            let searchTimeout;
            const searchInput = $('#searchInput');
            const searchBtn = $('#searchBtn');
            const searchSuggestions = $('#searchSuggestions');
            const searchResults = $('#searchResults');
            const productsContainer = $('#productsContainer');
            
            function showLoading() {
                searchResults.html('<div class="search-loading"><i class="fa fa-spinner fa-spin"></i> Recherche en cours...</div>');
            }
            
            function showError(message) {
                searchResults.html(`<div class="search-error">${message}</div>`);
            }
            
            function showNoResults() {
                searchResults.html('<div class="no-results"><i class="fa fa-search"></i><h4>Aucun produit trouvé</h4><p>Essayez avec d autres mots-clés.</p></div>');
            }
            
            function displaySearchResults(products) {
                if (products.length === 0) {
                    showNoResults();
                    return;
                }
                
                let html = '<div class="row">';
                products.forEach(product => {
                    html += `
                        <div class="col-lg-4 col-md-6">
                            <div class="product__item">
                                <div class="product__item__pic set-bg" data-setbg="${product.image}">
                                    <ul class="product__item__pic__hover">
                                        <li><a href="#"><i class="fa fa-heart"></i></a></li>
                                        <li><a href="#"><i class="fa fa-shopping-cart"></i></a></li>
                                    </ul>
                                </div>
                                <div class="product__item__text">
                                    <h6>${product.name}</h6>
                                    <h5>€${product.price}</h5>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                searchResults.html(html);
                
                setTimeout(function() {
                    $('.set-bg').each(function() {
                        var bg = $(this).data('setbg');
                        $(this).css('background-image', 'url(' + bg + ')');
                    });
                }, 100);
            }
            
            function fetchSuggestions(query) {
                if (query.length < 2) {
                    searchSuggestions.removeClass('active').empty();
                    return;
                }
                
                $.ajax({
                    url: 'recherche.php',
                    method: 'GET',
                    data: { action: 'autocomplete', q: query },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.suggestions.length > 0) {
                            let html = '';
                            response.suggestions.forEach(suggestion => {
                                html += `
                                    <div class="suggestion-item" data-name="${suggestion.name}">
                                        <img src="${suggestion.image}" alt="${suggestion.name}" class="suggestion-image">
                                        <div class="suggestion-details">
                                            <div class="suggestion-name">${suggestion.name}</div>
                                            <div class="suggestion-info">${suggestion.category}</div>
                                        </div>
                                        <div class="suggestion-price">€${suggestion.price}</div>
                                    </div>
                                `;
                            });
                            searchSuggestions.html(html).addClass('active');
                        } else {
                            searchSuggestions.removeClass('active').empty();
                        }
                    },
                    error: function() {
                        searchSuggestions.removeClass('active').empty();
                    }
                });
            }
            
            function performSearch(query) {
                if (query.trim() === '') {
                    searchResults.empty();
                    productsContainer.show();
                    return;
                }
                
                showLoading();
                productsContainer.hide();
                
                $.ajax({
                    url: 'recherche.php',
                    method: 'GET',
                    data: { action: 'search', q: query },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (response.products.length > 0) {
                                displaySearchResults(response.products);
                            } else {
                                showNoResults();
                            }
                        } else {
                            showError('Erreur lors de la recherche. Veuillez réessayer.');
                        }
                    },
                    error: function() {
                        showError('Erreur de connexion. Veuillez vérifier votre connexion internet.');
                    }
                });
            }
            
            searchInput.on('input', function() {
                const query = $(this).val();
                clearTimeout(searchTimeout);
                
                searchTimeout = setTimeout(function() {
                    fetchSuggestions(query);
                }, 300);
            });
            
            searchInput.on('focus', function() {
                const query = $(this).val();
                if (query.length >= 2) {
                    fetchSuggestions(query);
                }
            });
            
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-container').length) {
                    searchSuggestions.removeClass('active');
                }
            });
            
            $(document).on('click', '.suggestion-item', function() {
                const productName = $(this).data('name');
                searchInput.val(productName);
                searchSuggestions.removeClass('active');
                performSearch(productName);
            });
            
            searchBtn.on('click', function() {
                const query = searchInput.val();
                performSearch(query);
            });
            
            searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    const query = $(this).val();
                    performSearch(query);
                    searchSuggestions.removeClass('active');
                }
            });
        });
    </script>
</body>
</html>