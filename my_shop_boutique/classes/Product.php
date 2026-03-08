<?php
    class Product {

        private $connexion;
        private $nom_Table = "products";

        public function __construct($base_D) {
            $this->connexion = $base_D;
        }

        public function lire_Produit() {
            $requete = "SELECT * FROM " . $this->nom_Table;
            $stmt    = $this->connexion->prepare($requete);
            $stmt->execute();
            return $stmt;
        }

        public function creer_Produit($name, $description, $price, $category_id, $image) {
            // Vérifier si la colonne description existe
            $check_column = $this->connexion->prepare("SHOW COLUMNS FROM products LIKE 'description'");
            $check_column->execute();
            
            if ($check_column->rowCount() > 0) {
                // La colonne description existe, on l'inclut
                $requete = "INSERT INTO " . $this->nom_Table . " (name, description, price, category_id, image) VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->connexion->prepare($requete);
                return $stmt->execute([$name, $description, $price, $category_id, $image]);
            } else {
                // La colonne description n'existe pas, on l'ignore
                $requete = "INSERT INTO " . $this->nom_Table . " (name, price, category_id, image) VALUES (?, ?, ?, ?)";
                $stmt = $this->connexion->prepare($requete);
                return $stmt->execute([$name, $price, $category_id, $image]);
            }
        }

        public function delete($id) {
            $requete = "DELETE FROM " . $this->nom_Table . " WHERE id = ?";
            $stmt    = $this->connexion->prepare($requete);
            return $stmt->execute([$id]);
        }

        public function chercher($term, $category_id, $orderBy) {

            $sql    = "SELECT * FROM " . $this->nom_Table . " WHERE 1=1";
            $params = [];

            if (!empty($term)) {

                $params[] = "%" . $term . "%";
                $sql      = $sql . " AND name LIKE ?";
            }

            if (!empty($category_id) && $category_id != "0") {
                $sql      = $sql . " AND category_id = ?";
                $params[] = $category_id;
            }

            if ($orderBy === 'price_asc') {
                $sql = $sql . " ORDER BY price ASC";
            } elseif ($orderBy === 'price_desc') {
                $sql = $sql . " ORDER BY price DESC";
            } elseif ($orderBy === 'name_asc') {
                $sql = $sql . " ORDER BY name ASC";
            } elseif ($orderBy === 'name_desc') {
                $sql = $sql . " ORDER BY name DESC";
            } else {
                $sql = $sql . " ORDER BY id DESC";
            }

            $stmt = $this->connexion->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }
    }