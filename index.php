<?php
    session_start();
    require_once 'classes/Database.php';
    require_once 'classes/Product.php';
    require_once 'classes/Category.php';

    // Connexion à la base
    $base_donnees = new Database();
    $base_D = $base_donnees->recupConnexion();

    // Création des objets
    $produit = new Product($base_D);
    $categorie = new Category($base_D);

    // Paramètres de recherche / filtre / tri
    $q = $_GET['q'] ?? '';
    $cat = $_GET['cat'] ?? '0';
    $sort = $_GET['sort'] ?? 'default';

    // Récupération des produits
    if (!empty($q) || $cat != "0" || $sort != "default") {
        $produits = $produit->search($q, $cat, $sort);
    } else {
        $produits = $produit->lire_Produit(); // lire tous les produits
    }

    // Récupération des catégories
    $toutes_Cat = $categorie->lire_Categorie();
    ?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Shop - Boutique</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>

    <header>
        <h1>Ma Boutique en Ligne</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <span>Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php">Déconnexion</a>
                <?php if($_SESSION['is_admin'] == 1): ?>
                    <a href="admin.php" class="lien-admin">Admin</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="signin.php">Connexion</a>
                <a href="signup.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="conteneur disposition-principale">
        
        <aside class="barre-laterale">
            <h3>Catégories</h3>
            <div class="arbre-categories">
                <?php $categorie->afficherStructure(); ?>
            </div>
        </aside>

        <main class="zone-contenu">
            
            <section class="barre-recherche">
                <form action="index.php" method="GET">
                    <input type="text" name="q" placeholder="Rechercher un produit..." value="<?php echo htmlspecialchars($q); ?>">
                    
                    <select name="cat">
                        <option value="0">Toutes les catégories</option>
                        <?php while($c = $toutes_Cat->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($cat == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="sort">
                        <option value="default">Trier par...</option>
                        <option value="price_asc">Prix croissant</option>
                        <option value="price_desc">Prix décroissant</option>
                        <option value="name_asc">Nom A-Z</option>
                        <option value="name_desc">Nom Z-A</option>
                    </select>

                    <button type="submit">Rechercher</button>
                </form>
            </section>

            <h2>Nos Produits</h2>

            <div class="grille-produits">
                <?php if($produits->rowCount() > 0): ?>
                    <?php while ($p = $produits->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="carte-produit">
                            <div class="image-produit">
                                <?php $img = !empty($p['image']) ? $p['image'] : 'default.png'; ?>
                                <img src="uploads/<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            </div>
                            
                            <div class="infos-produit">
                                <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                                <p class="description-produit"><?php echo htmlspecialchars($p['description']); ?></p>
                                <p class="prix-produit"><?php echo number_format($p['price'], 2); ?> €</p>
                                <button class="bouton-achat">Ajouter au panier</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Aucun produit ne correspond à votre recherche.</p>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <footer>
        <p>&copy; 2026 My Shop - Tous droits réservés</p>
    </footer>

    </body>
    </html>