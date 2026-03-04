<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Boutique Fashion - Contact">
    <meta name="keywords" content="Boutique, Fashion, contact">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Boutique Fashion | Contact</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Css Styles Essentiels -->
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
    
    <style>
        /* Supprimer le preloader */
        #preloder {
            display: none !important;
        }
        
        /* Cacher les éléments qui nécessitent JS */
        .offcanvas-menu-wrapper,
        .offcanvas-menu-overlay,
        .canvas__open {
            display: none !important;
        }
        
        /* Alternative pour le menu mobile */
        @media (max-width: 991px) {
            .header__menu {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
                position: absolute;
                right: 20px;
                top: 20px;
                background: #1a1a1a;
                color: white;
                border: none;
                padding: 10px 15px;
                cursor: pointer;
                border-radius: 5px;
            }
            
            .mobile-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                z-index: 1000;
            }
            
            .mobile-menu.active {
                display: block;
            }
            
            .mobile-menu ul {
                list-style: none;
                padding: 20px;
                margin: 0;
            }
            
            .mobile-menu li {
                padding: 10px 0;
                border-bottom: 1px solid #eee;
            }
            
            .mobile-menu a {
                color: #1a1a1a;
                text-decoration: none;
                font-weight: 600;
            }
        }
        
        @media (min-width: 992px) {
            .mobile-menu-toggle {
                display: none !important;
            }
        }
        
        /* Cacher la recherche qui nécessite JS */
        .search-model {
            display: none !important;
        }
        
        /* Hero slider alternatif */
        .hero {
            padding: 0;
        }
        
        .hero__slider {
            height: 400px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }
        
        .hero__text {
            max-width: 600px;
        }
        
        .hero__text h6 {
            color: rgba(255,255,255,0.8);
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .hero__text h2 {
            font-size: 42px;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .hero__text p {
            font-size: 16px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .primary-btn {
            display: inline-block;
            padding: 15px 30px;
            background: #1a1a1a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .primary-btn:hover {
            background: #333;
            transform: translateY(-2px);
        }
        
        /* Styles pour les alertes */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-3">
                    <div class="header__logo">
                        <a href="./index.php"><img src="img/logo.png" alt="Boutique Fashion"></a>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <nav class="header__menu mobile-menu">
                        <ul>
                            <li><a href="./index.php">Accueil</a></li>
                            <li><a href="./shop.php">Boutique</a></li>
                            <li><a href="./about.php">À propos</a></li>
                            <li class="active"><a href="./contact_final.php">Contact</a></li>
                        </ul>
                    </nav>
                    <!-- Menu mobile alternatif -->
                    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                        <i class="fa fa-bars"></i> Menu
                    </button>
                    <div class="mobile-menu" id="mobileMenu">
                        <ul>
                            <li><a href="./index.php">Accueil</a></li>
                            <li><a href="./shop.php">Boutique</a></li>
                            <li><a href="./shopping-cart.php">Panier</a></li>
                            <li><a href="./about.php">À propos</a></li>
                            <li class="active"><a href="./contact_final.php">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <div class="header__nav__option">
                        <a href="./shopping-cart.php"><img src="img/icon/cart.png" alt=""> <span>0</span></a>
                        <div class="price">0.00€</div>
                    </div>
                </div>
            </div>
        </div>
    </header>


    <!-- Contact Section Begin -->
    <section class="contact spad" id="contact">
        <div class="container">
            <!-- Messages de succès/erreur -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <strong>✅ Message envoyé avec succès !</strong><br>
                    Nous vous répondrons dans les plus brefs délais.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['errors'])): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erreurs :</strong><br>
                    <?php 
                    $errors = explode('|', urldecode($_GET['errors']));
                    foreach ($errors as $error): ?>
                        - <?php echo htmlspecialchars($error); ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="contact__text">
                        <div class="section-title">
                            <span>Information</span>
                            <h2>Contactez-nous</h2>
                            <p>Nous sommes à votre disposition pour répondre à toutes vos questions.</p>
                        </div>
                        <ul>
                            <li>
                                <h4>🇫🇷 France</h4>
                                <p>123 Avenue de la Mode<br />75001 Paris<br />+33 1 23 45 67 89</p>
                            </li>
                            <li>
                                <h4>📧 Email</h4>
                                <p>contact@boutique-fashion.com<br />Support 24/7</p>
                            </li>
                            <li>
                                <h4>🕐 Horaires</h4>
                                <p>Lun-Ven: 9h-19h<br />Sam: 10h-18h<br />Dim: Fermé</p>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="contact__form">
                        <form method="POST" action="contact_handler.php">
                            <div class="row">
                                <div class="col-lg-6">
                                    <input type="text" name="name" placeholder="Votre nom" required>
                                </div>
                                <div class="col-lg-6">
                                    <input type="email" name="email" placeholder="Votre email" required>
                                </div>
                                <div class="col-lg-12">
                                    <textarea name="message" placeholder="Votre message" rows="5" required></textarea>
                                    <button type="submit" class="site-btn">Envoyer le message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section Begin -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="footer__copyright">
                        <div class="footer__copyright__text">
                            <p>&copy; <script>document.write(new Date().getFullYear());</script> Boutique Fashion. Tous droits réservés.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript minimal pour le menu mobile -->
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('active');
        }
        
        // Fermer le menu en cliquant ailleurs
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobileMenu');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                menu.classList.remove('active');
            }
        });
    </script>
</body>
</html>
