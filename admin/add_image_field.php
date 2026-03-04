<?php
require_once '../config.php';

echo "<h2>Ajout du champ image à la table products</h2>";

try {
    // Vérifier si le champ image existe déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'image'");
    $field_exists = $stmt->rowCount() > 0;
    
    if (!$field_exists) {
        echo "<p style='color: orange;'>Le champ 'image' n'existe pas. Ajout du champ...</p>";
        
        // Ajouter le champ image
        $alter_table = "ALTER TABLE products ADD COLUMN image VARCHAR(255) NULL AFTER category_id";
        $pdo->exec($alter_table);
        
        echo "<p style='color: green;'>✅ Champ 'image' ajouté avec succès !</p>";
    } else {
        echo "<p style='color: green;'>✅ Le champ 'image' existe déjà.</p>";
    }
    
    // Afficher la structure mise à jour
    echo "<h3>Structure mise à jour de la table 'products' :</h3>";
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
    
    // Créer le dossier img/product s'il n'existe pas
    if (!is_dir('../img/product')) {
        if (mkdir('../img/product', 0777, true)) {
            echo "<p style='color: green;'>✅ Dossier 'img/product' créé avec succès !</p>";
        } else {
            echo "<p style='color: red;'>❌ Erreur lors de la création du dossier 'img/product'</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Le dossier 'img/product' existe déjà.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<p><a href="ajout.php">→ Ajouter un produit avec image</a></p>
<p><a href="products.php">→ Voir les produits</a></p>
<p><a href="dashboard.php">← Retour au dashboard</a></p>
