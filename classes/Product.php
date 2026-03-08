<?php

class Product
{
    private $connexion;
    private $nom_Table = "products";

    public function __construct($base_D)
    {
        $this->connexion = $base_D;
    }

    public function lire_Produit()
    {
        $requete = "SELECT * FROM " . $this->nom_Table;
        $stmt = $this->connexion->prepare($requete);
        $stmt->execute();
        return $stmt;
    }

    public function creer_Produit($name, $description, $price, $category_id, $image)
    {
        $requete = "INSERT INTO " . $this->nom_Table . " (name, description, price, category_id, image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->connexion->prepare($requete);
        return $stmt->execute([$name, $description, $price, $category_id, $image]);
    }

    public function delete($id)
    {
        $requete = "DELETE FROM " . $this->nom_Table . " WHERE id = ?";
        $stmt = $this->connexion->prepare($requete);
        return $stmt->execute([$id]);
    }

    public function search($term, $category_id, $orderBy)
    {

        $sql = "SELECT * FROM " . $this->nom_Table . " WHERE 1=1";
        $params = [];

        if (!empty($term)) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%$term%";
            $params[] = "%$term%";
        }


        if (!empty($category_id) && $category_id != "0") {
            $sql .= " AND category_id = ?";
            $params[] = $category_id;
        }

        switch ($orderBy) {
            case 'price_asc':
                $sql .= " ORDER BY price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY price DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY name ASC";
                break;
            case 'name_desc':
                $sql .= " ORDER BY name DESC";
                break;
            default:
                $sql .= " ORDER BY id DESC";
                break;
        }

        $stmt = $this->connexion->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}