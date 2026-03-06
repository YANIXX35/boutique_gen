<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Product.php';

// 1. BARRIÈRE DE SÉCURITÉ
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
    <title>Administration - My Shop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>⚙️ Panneau d'administration</h1>
    <a href="index.php">⬅ Retour au site</a>

    <!-- SECTION PRODUITS -->
    <section>
        <h2>Gestion des Produits</h2>
        <a href="ajouter_produit.php" style="background: #000; color: #fff; padding: 10px; text-decoration: none;">+ Ajouter un produit</a>
        
        <table border="1" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <tr style="background: #eee;">
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
                         alt="<?php echo htmlspecialchars($ligne['name']); ?>" 
                         style="width:50px; height:50px; object-fit:cover;">
                </td>
                <td><?php echo htmlspecialchars($ligne['name']); ?></td>
                <td><?php echo number_format($ligne['price'], 2); ?> €</td>
                <td>
                    <a href="modifier_produit.php?id=<?php echo $ligne['id']; ?>">Modifier</a> | 
                    <a href="admin.php?delete_product=<?php echo $ligne['id']; ?>" 
                       onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?')" 
                       style="color: red;">Supprimer</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </section>

    <!-- SECTION UTILISATEURS -->
    <section style="margin-top: 50px;">
        <h2>Gestion des Utilisateurs</h2>
        <table border="1" style="width: 100%; border-collapse: collapse;">
            <tr style="background: #eee;">
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
                    <strong><?php echo ($u['admin'] == 1) ? "👑 Administrateur" : "👤 Membre"; ?></strong>
                </td>
                <td>
                    <a href="admin.php?toggle_admin=<?php echo $u['id']; ?>&status=<?php echo ($u['admin'] == 1 ? 0 : 1); ?>">
                        Changer rôle
                    </a> | 
                    <a href="admin.php?delete_user=<?php echo $u['id']; ?>" 
                       onclick="return confirm('Supprimer cet utilisateur ?')" 
                       style="color: red;">Bannir</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </section>
</body>
</html>