<?php
require_once 'config.php';

echo "<h3>Structure de la table 'users' :</h3>";

try {
    $result = $conn->query("DESCRIBE users");
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; padding: 10px;'>";
        echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
