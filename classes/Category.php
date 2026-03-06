<?php
// On crée une "fiche de fabrication" pour gérer les catégories
class Category {
    // Les outils de la classe
    private $connexion; // C'est notre connexion à la base de données
    private $nom_Table = "categories"; // Le nom de la table dans MySQL

    // Le "Constructeur" : il se lance tout seul quand on crée un objet
    public function __construct($base_D) {
        $this->connexion = $base_D; // On donne la connexion à notre classe
    }

    // Fonction pour AJOUTER une catégorie
    public function creer_Categorie($name, $parent_id = null) {
        $requete = "INSERT INTO " . $this->nom_Table . " (name, parent_id) VALUES (?, ?)";
        $stmt = $this->connexion->prepare($requete);
        return $stmt->execute([$name, $parent_id]);
    }

    // Fonction pour LIRE toutes les catégories
    public function lire_Categorie() {
        $requete= "SELECT * FROM " . $this->nom_Table;
        $stmt = $this->connexion->prepare($requete);
        $stmt->execute();
        return $stmt;
    }

    // Fonction pour AFFICHER les catégories (avec les sous-catégories)
    // C'est ce qu'on appelle une fonction "récursive" (elle s'appelle elle-même)
    public function afficherStructure($parent_id = null) {
        // 1. On prépare la question pour SQL
        if ($parent_id === null) {
            $requete = "SELECT * FROM categories WHERE parent_id IS NULL";
            $stmt = $this->connexion->prepare($requete);
            $stmt->execute();
        } else {
            $requete = "SELECT * FROM categories WHERE parent_id = ?";
            $stmt = $this->connexion->prepare($requete);
            $stmt->execute([$parent_id]);
        }

        // 2. Si on trouve des résultats, on crée une liste
        if ($stmt->rowCount() > 0) {
            echo "<ul>";
            while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<li>" . htmlspecialchars($ligne['name']);
                
                // MAGIE : On demande à la fonction de recommencer pour chercher les enfants
                $this->affciherStructure($ligne['id']);
                
                echo "</li>";
            }
            echo "</ul>";
        }
    }

}
?>