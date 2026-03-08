<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Product.php';

// 1. BARRIÈRE DE SÉCURITÉ : seul l'admin peut accéder
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// 2. CONNEXION BASE DE DONNÉES
$base_donnees = new Database();
$base_D = $base_donnees->recupConnexion();

$produit = new Product($base_D);
$utilisateur = new User($base_D);

// 3. ACTIONS RAPIDES
if (isset($_GET['delete_product'])) {
    $produit->delete($_GET['delete_product']);
    header("Location: admin.php");
    exit();
}

if (isset($_GET['toggle_admin'])) {
    $utilisateur->miseAjourStatus($_GET['toggle_admin'], $_GET['status']);
    header("Location: admin.php");
    exit();
}

if (isset($_GET['delete_user'])) {
    $utilisateur->supprimer_Compte($_GET['delete_user']);
    header("Location: admin.php");
    exit();
}

// 4. RÉCUPÉRATION DES PRODUITS ET UTILISATEURS
$stmtProduits = $produit->lire_Produit();
$stmtUtilisateurs = $utilisateur->lire_Tout();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - My Shop</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <!-- EN-TÊTE ADMIN -->
    <div class="admin-header">
        <h1>⚙️ Panneau d'administration</h1>
        <a href="index.php">⬅ Retour au site</a>
    </div>

    <div class="admin-conteneur">

        <!-- SECTION PRODUITS -->
        <section class="admin-section">
            <h2>Gestion des Produits</h2>
            <a href="ajouter_produit.php" class="btn-ajouter">+ Ajouter un produit</a>
            <a href="ajouter_categorie.php" class="btn-ajouter" style="margin-left:10px; background:#0369a1;">+ Ajouter une catégorie</a>

            <?php if (isset($_GET['success'])): ?>
                <p style="color:green; margin-top:12px;">✓ Produit ajouté avec succès.</p>
            <?php
endif; ?>
            <?php if (isset($_GET['updated'])): ?>
                <p style="color:green; margin-top:12px;">✓ Produit mis à jour avec succès.</p>
            <?php
endif; ?>
            <?php if (isset($_GET['cat_added'])): ?>
                <p style="color:green; margin-top:12px;">✓ Catégorie ajoutée avec succès.</p>
            <?php
endif; ?>

            <table class="admin-tableau">
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Actions</th>
                </tr>
                <?php while ($ligne = $stmtProduits->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td>
                        <?php $img = !empty($ligne['image']) ? $ligne['image'] : 'default.png'; ?>
                        <img src="uploads/<?php echo htmlspecialchars($img); ?>"
                             alt="<?php echo htmlspecialchars($ligne['name']); ?>">
                    </td>
                    <td><?php echo htmlspecialchars($ligne['name']); ?></td>
                    <td><?php echo number_format($ligne['price'], 2); ?> Fcfa</td>
                    <td>
                        <a href="modifier_produit.php?id=<?php echo $ligne['id']; ?>" class="lien-modifier">Modifier</a>
                        &nbsp;|&nbsp;
                        <a href="admin.php?delete_product=<?php echo $ligne['id']; ?>"
                           onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?')"
                           class="lien-supprimer">Supprimer</a>
                    </td>
                </tr>
                <?php
endwhile; ?>
            </table>
        </section>

        <!-- SECTION UTILISATEURS -->
        <section class="admin-section">
            <h2>Gestion des Utilisateurs</h2>
            <table class="admin-tableau">
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
                <?php while ($u = $stmtUtilisateurs->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <?php if ($u['admin'] == 1): ?>
                            <span class="badge-admin">👑 Administrateur</span>
                        <?php
    else: ?>
                            <span class="badge-membre">👤 Membre</span>
                        <?php
    endif; ?>
                    </td>
                    <td>
                        <a href="admin.php?toggle_admin=<?php echo $u['id']; ?>&status=<?php echo($u['admin'] == 1 ? 0 : 1); ?>"
                           class="lien-modifier">Changer rôle</a>
                        &nbsp;|&nbsp;
                        <a href="admin.php?delete_user=<?php echo $u['id']; ?>"
                           onclick="return confirm('Supprimer cet utilisateur ?')"
                           class="lien-supprimer">Bannir</a>
                    </td>
                </tr>
                <?php
endwhile; ?>
            </table>
        </section>

    </div>
</body>
</html>