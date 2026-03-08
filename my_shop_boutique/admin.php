<?php
    session_start();
    require_once 'classes/Database.php';
    require_once 'classes/User.php';
    require_once 'classes/Product.php';
    require_once 'classes/Category.php';

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header("Location: index.php");
        exit();
    }

    $base_donnees = new Database();
    $base_D       = $base_donnees->recupConnexion();

    $produit     = new Product($base_D);
    $utilisateur = new User($base_D);
    $categorie   = new Category($base_D);

    if (isset($_GET['supprimer_produit'])) {
        $produit->delete($_GET['supprimer_produit']);
        header("Location: admin.php?section=produits");
        exit();
    }

    if (isset($_GET['supprimer_utilisateur'])) {
        $utilisateur->supprimer_Compte($_GET['supprimer_utilisateur']);
        header("Location: admin.php?section=utilisateurs");
        exit();
    }

    $section = $_GET['section'] ?? 'dashboard';

    $stmtProduits     = $produit->lire_Produit();
    $stmtUtilisateurs = $utilisateur->lire_Tout();
    $stmtCategories   = $categorie->lire_Categorie();

    $listeProduits     = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
    $listeUtilisateurs = $stmtUtilisateurs->fetchAll(PDO::FETCH_ASSOC);
    $listeCategories   = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

    $nbProduits     = count($listeProduits);
    $nbUtilisateurs = count($listeUtilisateurs);
    $nbCategories   = count($listeCategories);

    // ── PAGINATION ──
    $parPage = 8;

    // Pour les produits
    $pageProduits    = max(1, intval($_GET['page_produits'] ?? 1));
    $totalPagesProd  = max(1, ceil($nbProduits / $parPage));
    $pageProduits    = min($pageProduits, $totalPagesProd);
    $produitsPagines = array_slice($listeProduits, ($pageProduits - 1) * $parPage, $parPage);

    // Pour les utilisateurs
    $pageUsers       = max(1, intval($_GET['page_users'] ?? 1));
    $totalPagesUsers = max(1, ceil($nbUtilisateurs / $parPage));
    $pageUsers       = min($pageUsers, $totalPagesUsers);
    $usersPagines    = array_slice($listeUtilisateurs, ($pageUsers - 1) * $parPage, $parPage);

    // Pour les catégories
    $pageCats       = max(1, intval($_GET['page_cats'] ?? 1));
    $totalPagesCats = max(1, ceil($nbCategories / $parPage));
    $pageCats       = min($pageCats, $totalPagesCats);
    $catsPaginees   = array_slice($listeCategories, ($pageCats - 1) * $parPage, $parPage);
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — My Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">

<!-- BARRE LATERALE -->
<aside class="barre-laterale">
    <div class="marque-barre">
        <span class="nom-marque">Y.E.F Shop</span>
        <span class="sous-marque">Interface Admin</span>
    </div>

    <nav class="nav-barre">
        <a href="admin.php?section=dashboard"
           class="item-nav <?php echo $section === 'dashboard' ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Tableau de bord</span>
        </a>
        <a href="admin.php?section=produits"
           class="item-nav <?php echo $section === 'produits' ? 'active' : ''; ?>">
            <i class="bi bi-bag"></i>
            <span>Produits</span>
        </a>
        <a href="admin.php?section=utilisateurs"
           class="item-nav <?php echo $section === 'utilisateurs' ? 'active' : ''; ?>">
            <i class="bi bi-people"></i>
            <span>Utilisateurs</span>
        </a>
        <a href="admin.php?section=categories"
           class="item-nav <?php echo $section === 'categories' ? 'active' : ''; ?>">
            <i class="bi bi-tag"></i>
            <span>Catégories</span>
        </a>
    </nav>

    <a href="logout.php" class="btn-deconnexion">
        <i class="bi bi-box-arrow-right"></i> Déconnexion
    </a>
</aside>


<div class="contenu-principal">

    <header class="barre-haut">
        <h1 class="titre-page">
            <?php
            $titres = [
                'dashboard'    => 'Tableau de Bord',
                'produits'     => 'Produits',
                'utilisateurs' => 'Utilisateurs',
                'categories'   => 'Catégories',
            ];
            echo $titres[$section] ?? 'Tableau de Bord';
            ?>
        </h1>
        <div class="utilisateur-barre">
            <span>Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <div class="avatar-utilisateur"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
        </div>
    </header>

<!-- TABLEAU DE BORD -->
    <?php if ($section === 'dashboard') { ?>

    <div class="contenu">

        <!-- Cartes statistiques -->
        <div class="grille-stats">
            <div class="carte-stats stat-bleu">
                <div class="icone-stats fond-bleu"><i class="bi bi-people-fill"></i></div>
                <div class="chiffre-stats"><?php echo $nbUtilisateurs; ?></div>
                <div class="label-stats">Utilisateurs</div>
            </div>
            <div class="carte-stats stat-vert">
                <div class="icone-stats fond-vert"><i class="bi bi-bag-fill"></i></div>
                <div class="chiffre-stats"><?php echo $nbProduits; ?></div>
                <div class="label-stats">Produits</div>
            </div>
            <div class="carte-stats stat-jaune">
                <div class="icone-stats fond-jaune"><i class="bi bi-tag-fill"></i></div>
                <div class="chiffre-stats"><?php echo $nbCategories; ?></div>
                <div class="label-stats">Catégories</div>
            </div>
        </div>

        <!-- Vue d'ensemble -->
        <div class="panneau">
            <div class="entete-panneau">
                <div>
                    <h2 class="titre-panneau">Vue d'ensemble</h2>
                    <p class="sous-titre-panneau">Statistiques générales</p>
                </div>
                <a href="index.php" class="btn-outline">
                    <i class="bi bi-arrow-left"></i> Voir la boutique
                </a>
            </div>

            <div class="grille-apercu">
                <div class="item-apercu">
                    <span class="label-apercu">Produits actifs</span>
                    <span class="valeur-apercu"><?php echo $nbProduits; ?></span>
                </div>
                <div class="item-apercu">
                    <span class="label-apercu">Membres inscrits</span>
                    <span class="valeur-apercu"><?php echo $nbUtilisateurs; ?></span>
                </div>
                <div class="item-apercu">
                    <span class="label-apercu">Catégories créées</span>
                    <span class="valeur-apercu"><?php echo $nbCategories; ?></span>
                </div>
                <div class="item-apercu">
                    <span class="label-apercu">Admins</span>
                    <span class="valeur-apercu">
                        <?php
                        $nbAdmins = 0;
                        foreach ($listeUtilisateurs as $u) {
                            if ($u['admin'] == 1) {
                                $nbAdmins++;
                            }
                        }
                        echo $nbAdmins;
                        ?>
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- PRODUITS -->
    <?php } elseif ($section === 'produits') { ?>

    <div class="contenu">

        <!-- Messages de confirmation -->
        <?php if (isset($_GET['success'])) { ?>
            <div class="alert-success">
                <i class="bi bi-check-circle"></i> Produit ajouté avec succès.
            </div>
        <?php } ?>
        <?php if (isset($_GET['updated'])) { ?>
            <div class="alert-success">
                <i class="bi bi-check-circle"></i> Produit mis à jour.
            </div>
        <?php } ?>

        <div class="panneau">
            <div class="entete-panneau">
                <h2 class="titre-panneau">Liste des produits</h2>
                <a href="ajouter_produit.php" class="btn-primary">
                    <i class="bi bi-plus-lg"></i> Ajouter un produit
                </a>
            </div>

            <?php if (empty($listeProduits)) { ?>

                <p class="etat-vide">Aucun produit pour le moment.</p>

            <?php } else { ?>

                <!-- Tableau des produits -->
                <div class="tableau-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Nom</th>
                                <th>Prix</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produitsPagines as $ligne) { ?>
                            <tr>
                                <td>
                                    <?php $img = !empty($ligne['image']) ? $ligne['image'] : 'img-par-defaut.png'; ?>
                                    <img src="ressources/<?php echo htmlspecialchars($img); ?>"
                                         alt="<?php echo htmlspecialchars($ligne['name']); ?>"
                                         class="miniature-produit">
                                </td>
                                <td class="td-gras"><?php echo htmlspecialchars($ligne['name']); ?></td>
                                <td class="td-prix"><?php echo number_format($ligne['price'], 0); ?> Fcfa</td>
                                <td>
                                    <div class="boutons-actions">
                                        <a href="modifier_produit.php?id=<?php echo $ligne['id']; ?>" class="btn-modifier">
                                            <i class="bi bi-pencil"></i> Modifier
                                        </a>
                                        <a href="admin.php?section=produits&supprimer_produit=<?php echo $ligne['id']; ?>"
                                           onclick="return confirm('Supprimer ce produit ?')"
                                           class="btn-supprimer">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination produits -->
                <?php if ($totalPagesProd > 1) { ?>
                <p class="info-pagination">
                    Page <?php echo $pageProduits; ?> sur <?php echo $totalPagesProd; ?>
                    — <?php echo $nbProduits; ?> produits au total
                </p>
                <div class="pagination">
                    <?php if ($pageProduits > 1) { ?>
                        <a href="admin.php?section=produits&page_produits=<?php echo $pageProduits - 1; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    <?php } ?>

                    <?php for ($i = 1; $i <= $totalPagesProd; $i++) { ?>
                        <?php if ($i === $pageProduits) { ?>
                            <span class="actuelle"><?php echo $i; ?></span>
                        <?php } else { ?>
                            <a href="admin.php?section=produits&page_produits=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php } ?>
                    <?php } ?>

                    <?php if ($pageProduits < $totalPagesProd) { ?>
                        <a href="admin.php?section=produits&page_produits=<?php echo $pageProduits + 1; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php } ?>
                </div>
                <?php } ?>

            <?php } ?>
        </div>

    </div>

  <!-- UTILISATEURS -->
    <?php } elseif ($section === 'utilisateurs') { ?>

    <div class="contenu">
        <div class="panneau">
            <div class="entete-panneau">
                <h2 class="titre-panneau">Liste des utilisateurs</h2>
                <span class="badge-nombre"><?php echo $nbUtilisateurs; ?> membres</span>
            </div>

            <!-- Message de confirmation modification -->
            <?php if (isset($_GET['updated'])) { ?>
                <div class="alert-success">
                    <i class="bi bi-check-circle"></i> Utilisateur mis à jour avec succès.
                </div>
            <?php } ?>

            <!-- Tableau des utilisateurs -->
            <div class="tableau-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usersPagines as $u) { ?>
                        <tr>
                            <td>
                                <div class="cellule-utilisateur">
                                    <div class="avatar-utilisateur">
                                        <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                                    </div>
                                    <span class="td-gras"><?php echo htmlspecialchars($u['username']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <?php if ($u['admin'] == 1) { ?>
                                    <span class="badge badge-admin">Admin</span>
                                <?php } else { ?>
                                    <span class="badge badge-membre">Membre</span>
                                <?php } ?>
                            </td>
                            <td>
                                <div class="boutons-actions">
                                    <a href="modifier_utilisateur.php?id=<?php echo $u['id']; ?>"
                                       class="btn-modifier">
                                        <i class="bi bi-pencil"></i> Modifier
                                    </a>
                                    <a href="admin.php?section=utilisateurs&supprimer_utilisateur=<?php echo $u['id']; ?>"
                                       onclick="return confirm('Supprimer cet utilisateur ?')"
                                       class="btn-supprimer">
                                        <i class="bi bi-person-x"></i> Bannir
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination utilisateurs -->
            <?php if ($totalPagesUsers > 1) { ?>
            <p class="info-pagination">
                Page <?php echo $pageUsers; ?> sur <?php echo $totalPagesUsers; ?>
                — <?php echo $nbUtilisateurs; ?> membres au total
            </p>
            <div class="pagination">
                <?php if ($pageUsers > 1) { ?>
                    <a href="admin.php?section=utilisateurs&page_users=<?php echo $pageUsers - 1; ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                <?php } ?>

                <?php for ($i = 1; $i <= $totalPagesUsers; $i++) { ?>
                    <?php if ($i === $pageUsers) { ?>
                        <span class="actuelle"><?php echo $i; ?></span>
                    <?php } else { ?>
                        <a href="admin.php?section=utilisateurs&page_users=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php } ?>
                <?php } ?>

                <?php if ($pageUsers < $totalPagesUsers) { ?>
                    <a href="admin.php?section=utilisateurs&page_users=<?php echo $pageUsers + 1; ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                <?php } ?>
            </div>
            <?php } ?>

        </div>
    </div>

   <!-- CATEGORIES -->
    <?php } elseif ($section === 'categories') { ?>

    <div class="contenu">
        <div class="panneau">
            <div class="entete-panneau">
                <h2 class="titre-panneau">Catégories</h2>
                <a href="ajouter_categorie.php" class="btn-primary">
                    <i class="bi bi-plus-lg"></i> Ajouter une catégorie
                </a>
            </div>

            <?php if (empty($listeCategories)) { ?>

                <p class="etat-vide">Aucune catégorie pour le moment.</p>

            <?php } else { ?>

                <!-- Tableau des catégories -->
                <div class="tableau-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Catégorie parente</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($catsPaginees as $cat) { ?>
                            <tr>
                                <td class="td-gras"><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td>
                                    <?php
                                    if (!empty($cat['parent_id'])) {
                                        $nomParent = 'Aucune';
                                        foreach ($listeCategories as $c) {
                                            if ($c['id'] == $cat['parent_id']) {
                                                $nomParent = htmlspecialchars($c['name']);
                                                break;
                                            }
                                        }
                                        echo $nomParent;
                                    } else {
                                        echo 'Racine';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination catégories -->
                <?php if ($totalPagesCats > 1) { ?>
                <p class="info-pagination">
                    Page <?php echo $pageCats; ?> sur <?php echo $totalPagesCats; ?>
                    — <?php echo $nbCategories; ?> catégories au total
                </p>
                <div class="pagination">
                    <?php if ($pageCats > 1) { ?>
                        <a href="admin.php?section=categories&page_cats=<?php echo $pageCats - 1; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    <?php } ?>

                    <?php for ($i = 1; $i <= $totalPagesCats; $i++) { ?>
                        <?php if ($i === $pageCats) { ?>
                            <span class="actuelle"><?php echo $i; ?></span>
                        <?php } else { ?>
                            <a href="admin.php?section=categories&page_cats=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php } ?>
                    <?php } ?>

                    <?php if ($pageCats < $totalPagesCats) { ?>
                        <a href="admin.php?section=categories&page_cats=<?php echo $pageCats + 1; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php } ?>
                </div>
                <?php } ?>

            <?php } ?>
        </div>
    </div>

    <?php } ?>

</div><!-- /contenu-principal -->
</body>
</html>