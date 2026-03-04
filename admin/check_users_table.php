<?php
require_once '../config.php';

echo "<h3>Structure de la table 'users' :</h3>";

try {
    $result = $pdo->query("DESCRIBE users");
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; padding: 10px;'>";
        echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Exemples de données :</h3>";
        $data = $pdo->query("SELECT * FROM users LIMIT 3");
        if ($data) {
            echo "<table border='1' style='border-collapse: collapse; padding: 10px;'>";
            echo "<tr>";
            foreach ($data->fetch(PDO::FETCH_ASSOC) as $key => $value) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            
            $data = $pdo->query("SELECT * FROM users LIMIT 3");
            while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
