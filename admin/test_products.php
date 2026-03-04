<?php
require_once '../config.php';

echo "<h2>Vérification des tables existantes</h2>";

try {
    // Vérifier la table products
    echo "<h3>Structure de la table 'products' :</h3>";
    $result = $pdo->query("DESCRIBE products");
    
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Vérifier la table categories
    echo "<h3>Structure de la table 'categories' :</h3>";
    $result = $pdo->query("DESCRIBE categories");
    
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Afficher les catégories
    echo "<h3>Catégories disponibles :</h3>";
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
    if (empty($categories)) {
        echo "<p style='color: orange;'>Aucune catégorie trouvée.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Parent ID</th></tr>";
        
        foreach ($categories as $category) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($category['id']) . "</td>";
            echo "<td>" . htmlspecialchars($category['name']) . "</td>";
            echo "<td>" . htmlspecialchars($category['parent_id'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Afficher les produits existants
    echo "<h3>Produits existants :</h3>";
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
    $products = $stmt->fetchAll();
    
    if (empty($products)) {
        echo "<p style='color: orange;'>Aucun produit trouvé. <a href='ajout.php'>Ajouter un produit</a></p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Prix</th><th>Catégorie</th></tr>";
        
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($product['id']) . "</td>";
            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
            echo "<td>" . number_format($product['price'], 2) . " €</td>";
            echo "<td>" . htmlspecialchars($product['category_name'] ?? 'Non catégorisé') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<p><a href="dashboard.php">← Retour au dashboard</a></p>
