<?php

class Category {

    private $connexion;
    private $nom_Table = "categories";

    public function __construct($base_D) {
        $this->connexion = $base_D;
    }

    public function creer_Categorie($name, $parent_id = null) {
        $requete = "INSERT INTO " . $this->nom_Table . " (name, parent_id) VALUES (?, ?)";
        $stmt = $this->connexion->prepare($requete);
        return $stmt->execute([$name, $parent_id]);
    }

    public function lire_Categorie() {
        $requete= "SELECT * FROM " . $this->nom_Table;
        $stmt = $this->connexion->prepare($requete);
        $stmt->execute();
        return $stmt;
    }

    public function afficherStructure($parent_id = null) {

        if ($parent_id === null) {
            $requete = "SELECT * FROM categories WHERE parent_id IS NULL";
            $stmt = $this->connexion->prepare($requete);
            $stmt->execute();
        } else {
            $requete = "SELECT * FROM categories WHERE parent_id = ?";
            $stmt = $this->connexion->prepare($requete);
            $stmt->execute([$parent_id]);
        }

        if ($stmt->rowCount() > 0) {
            echo "<ul>";
            while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<li>" . htmlspecialchars($ligne['name']);
                
                $this->afficherStructure($ligne['id']);
                
                echo "</li>";
            }
            echo "</ul>";
        }
    }

}
?>