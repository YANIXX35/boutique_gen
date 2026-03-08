<?php
    class Product {

        private $connexion;
        private $nom_Table = "products";

        public function __construct($base_D) {
            $this->connexion = $base_D;
        }

        // 1. LIRE TOUS LES PRODUITS
        public function lire_Produit() {
            $requete = "SELECT * FROM " . $this->nom_Table;
            $stmt    = $this->connexion->prepare($requete);
            $stmt->execute();
            return $stmt;
        }

        // 2. CRÉER UN PRODUIT
        public function creer_Produit($name, $description, $price, $category_id, $image) {
            $requete = "INSERT INTO " . $this->nom_Table . " (name, description, price, category_id, image) VALUES (?, ?, ?, ?, ?)";
            $stmt    = $this->connexion->prepare($requete);
            return $stmt->execute([$name, $description, $price, $category_id, $image]);
        }

        // 3. SUPPRIMER UN PRODUIT
        public function delete($id) {
            $requete = "DELETE FROM " . $this->nom_Table . " WHERE id = ?";
            $stmt    = $this->connexion->prepare($requete);
            return $stmt->execute([$id]);
        }

        // 4. CHERCHER DES PRODUITS
        public function chercher($term, $category_id, $orderBy) {
            // WHERE 1=1 est toujours vrai, ça permet d'ajouter des AND après facilement
            $sql    = "SELECT * FROM " . $this->nom_Table . " WHERE 1=1";
            // Ce tableau va stocker les valeurs à envoyer à la base de données
            $params = [];

            // Si l'utilisateur a tapé quelque chose dans la recherche
            if (!empty($term)) {
                // On cherche le mot uniquement dans le nom du produit
                $params[] = "%" . $term . "%";
                $sql      = $sql . " AND name LIKE ?";
            }

            // Si l'utilisateur a choisi une catégorie spécifique
            if (!empty($category_id) && $category_id != "0") {
                $sql      = $sql . " AND category_id = ?";
                // On ajoute l'ID de la catégorie aux paramètres
                $params[] = $category_id;
            }

            // On choisit comment trier les résultats selon le choix de l'utilisateur
            if ($orderBy === 'price_asc') {
                $sql = $sql . " ORDER BY price ASC";
            } elseif ($orderBy === 'price_desc') {
                $sql = $sql . " ORDER BY price DESC";
            } elseif ($orderBy === 'name_asc') {
                $sql = $sql . " ORDER BY name ASC";
            } elseif ($orderBy === 'name_desc') {
                $sql = $sql . " ORDER BY name DESC";
            } else {
                // Par défaut on trie par ID décroissant (les plus récents en premier)
                $sql = $sql . " ORDER BY id DESC";
            }

            // On prépare la requête pour éviter les piratages (injections SQL)
            $stmt = $this->connexion->prepare($sql);
            // On envoie les valeurs du tableau $params avec la requête
            $stmt->execute($params);
            // On retourne les résultats
            return $stmt;
        }
    }