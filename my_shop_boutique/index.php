<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();
    require_once 'classes/Database.php';
    require_once 'classes/Product.php';
    require_once 'classes/Category.php';

    $base_donnees = new Database();
    $base_D = $base_donnees->recupConnexion();

    $produit   = new Product($base_D);
    $categorie = new Category($base_D);

    if (isset($_GET['q'])) {
        $q = $_GET['q'];
    } else {
        $q = '';
    }

    if (isset($_GET['cat'])) {
        $cat = $_GET['cat'];
    } else {
        $cat = '0';
    }

    if (isset($_GET['sort'])) {
        $sort = $_GET['sort'];
    } else {
        $sort = 'default';
    }

    if (!empty($q) || ($cat !== "0" && $cat !== "") || $sort !== "default") {
        $produits = $produit->chercher($q, $cat, $sort);
    } else {
        $produits = $produit->lire_Produit();
    }

    $listeProduits = [];
    while ($p = $produits->fetch(PDO::FETCH_ASSOC)) {
        $listeProduits[] = $p;
    }

    $toutes_Cat = $categorie->lire_Categorie();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shop — Boutique</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- HEADER -->
<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="site-logo">
            <i class="bi bi-bag-heart-fill"></i> Y.E.F Shop
        </a>
        <nav class="site-nav">
            <a href="admin.php" class="nav-btn nav-btn-outline">
                <i class="bi bi-speedometer2"></i> Admin
            </a>
        </nav>
    </div>
</header>

<!-- LE CORPS -->
<div class="page-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <p class="sidebar-label">Catégories</p>
            <div class="cat-tree">
                <?php $categorie->afficherStructure(); ?>
            </div>
        </div>

        <div class="sidebar-section">
            <p class="sidebar-label">Trier par</p>
            <form action="index.php" method="GET" id="sort-form">
                <input type="hidden" name="q"   value="<?php echo htmlspecialchars($q); ?>">
                <input type="hidden" name="cat" value="<?php echo htmlspecialchars($cat); ?>">
                <div class="sort-options">

                    <label class="sort-option <?php if ($sort === 'default') { echo 'active'; } ?>">
                        <input type="radio" name="sort" value="default"
                               <?php if ($sort === 'default') { echo 'checked'; } ?>
                               onchange="document.getElementById('sort-form').submit()">
                        Par défaut
                    </label>

                    <label class="sort-option <?php if ($sort === 'price_asc') { echo 'active'; } ?>">
                        <input type="radio" name="sort" value="price_asc"
                               <?php if ($sort === 'price_asc') { echo 'checked'; } ?>
                               onchange="document.getElementById('sort-form').submit()">
                        Prix croissant
                    </label>

                    <label class="sort-option <?php if ($sort === 'price_desc') { echo 'active'; } ?>">
                        <input type="radio" name="sort" value="price_desc"
                               <?php if ($sort === 'price_desc') { echo 'checked'; } ?>
                               onchange="document.getElementById('sort-form').submit()">
                        Prix décroissant
                    </label>

                    <label class="sort-option <?php if ($sort === 'name_asc') { echo 'active'; } ?>">
                        <input type="radio" name="sort" value="name_asc"
                               <?php if ($sort === 'name_asc') { echo 'checked'; } ?>
                               onchange="document.getElementById('sort-form').submit()">
                        Nom A → Z
                    </label>

                    <label class="sort-option <?php if ($sort === 'name_desc') { echo 'active'; } ?>">
                        <input type="radio" name="sort" value="name_desc"
                               <?php if ($sort === 'name_desc') { echo 'checked'; } ?>
                               onchange="document.getElementById('sort-form').submit()">
                        Nom Z → A
                    </label>

                </div>
            </form>
        </div>
    </aside>

    <main class="main-content">

        <!-- BARRE DE RECHERCHE -->
        <section class="search-bar">
            <form action="index.php" method="GET" class="search-form">
                <div class="search-input-wrap">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="q"
                           placeholder="Rechercher un produit..."
                           value="<?php echo htmlspecialchars($q); ?>">
                </div>

                <select name="cat">
                    <option value="0">Toutes les catégories</option>
                    <?php
                    $cats2 = $categorie->lire_Categorie();
                    while ($c = $cats2->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <option value="<?php echo $c['id']; ?>"
                            <?php if ($cat == $c['id']) { echo 'selected'; } ?>>
                            <?php echo htmlspecialchars($c['name']); ?>
                        </option>
                    <?php } ?>
                </select>

                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                <button type="submit"><i class="bi bi-search"></i> Rechercher</button>

                <?php if (!empty($q) || $cat !== "0" || $sort !== "default") { ?>
                    <a href="index.php" class="btn-reset">
                        <i class="bi bi-x"></i> Réinitialiser
                    </a>
                <?php } ?>
            </form>
        </section>

        <!-- TITRE + COMPTEUR -->
        <div class="products-header">
            <h2>
                <?php
                if (!empty($q) || $cat !== "0") {
                    echo 'Résultats de recherche';
                } else {
                    echo 'Nos Produits';
                }
                ?>
            </h2>
            <?php if (!empty($q)) { ?>
                <span class="search-tag">
                    <i class="bi bi-search"></i> "<?php echo htmlspecialchars($q); ?>"
                </span>
            <?php } ?>
        </div>

        <!-- GRILLE PRODUITS -->
        <div class="products-grid">
            <?php if (count($listeProduits) > 0) { ?>

                <?php foreach ($listeProduits as $p) { ?>
                <div class="product-card">
                    <div class="product-img">
                        <?php $img = !empty($p['image']) ? $p['image'] : 'default.png'; ?>
                        <a href="produit.php?id=<?php echo $p['id']; ?>">
                            <img src="ressources/<?php echo htmlspecialchars($img); ?>"
                                 alt="<?php echo htmlspecialchars($p['name'] ?? 'Produit'); ?>">
                        </a>
                    </div>
                    <div class="product-info">
                        <h3>
                            <a href="produit.php?id=<?php echo $p['id']; ?>" style="color: inherit; text-decoration: none;">
                                <?php
                                if (!empty($p['name'])) {
                                    echo htmlspecialchars($p['name']);
                                } else {
                                    echo 'Sans nom';
                                }
                                ?>
                            </a>
                        </h3>
                        <p class="product-desc">
                            <?php
                            if (!empty($p['description'])) {
                                echo htmlspecialchars(substr($p['description'], 0, 80)) . '...';
                            } else {
                                echo '';
                            }
                            ?>
                        </p>
                        <div class="product-footer">
                            <span class="product-price">
                                <?php echo number_format($p['price'] ?? 0, 0, ',', ' '); ?> Fcfa
                            </span>
                            <button class="btn-add">
                                <i class="bi bi-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php } ?>

            <?php } else { ?>
                <div class="etat-vide">
                    <i class="bi bi-box-seam"></i>
                    <p>Aucun produit ne correspond à votre recherche.</p>
                    <a href="index.php">Voir tous les produits</a>
                </div>
            <?php } ?>
        </div>

    </main>
</div>

<!-- LE FOOTER -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-column">
            <h3><i class="bi bi-bag-heart-fill"></i> Y.E.F Shop</h3>
            <p>La solution simple pour vos achats quotidiens. Qualité et rapidité de livraison garanties.</p>
            <div class="social-links">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-whatsapp"></i></a>
            </div>
        </div>

        <div class="footer-column">
            <h4>Navigation</h4>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="index.php">Nos Produits</a></li>
                <li><a href="admin.php">Admin</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>Contact & Aide</h4>
            <p><i class="bi bi-geo-alt"></i> Abidjan, Côte d'Ivoire</p>
            <p><i class="bi bi-telephone"></i> +225 01 01 10 34</p>
            <div class="payment-methods">
                <span class="pay-badge">Orange Money</span>
                <span class="pay-badge">Wave</span>
                <span class="pay-badge">Moov Money</span>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
    </div>
</footer>

</body>
</html>
