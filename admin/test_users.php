<?php
require_once '../config.php';

// Vérifier la connexion
if (!$pdo) {
    die("Erreur de connexion à la base de données");
}

echo "<h2>Structure de la table users</h2>";

try {
    // Obtenir la structure de la table
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($column['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>Exemples de données dans la table users</h2>";
    
    // Obtenir quelques exemples de données
    $stmt = $pdo->query("SELECT * FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<p>Aucune donnée dans la table users</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr>";
        foreach ($users[0] as $key => $value) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            foreach ($user as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>Requête utilisée dans le dashboard</h2>";
    echo "<code>SELECT id, username, email, admin, DATE_FORMAT(created_at, '%d/%m/%Y') as created_date FROM users ORDER BY created_at DESC</code>";
    
} catch (PDOException $e) {
    echo "Erreur : " . htmlspecialchars($e->getMessage());
}
?>
