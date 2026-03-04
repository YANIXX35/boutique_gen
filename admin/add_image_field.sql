-- Script SQL pour ajouter le champ 'image' à la table products
-- À exécuter dans votre base de données (phpMyAdmin, MySQL Workbench, etc.)

-- Ajout du champ image à la table products
ALTER TABLE products ADD COLUMN image VARCHAR(255) NULL AFTER category_id;

-- Vérification de la structure mise à jour
DESCRIBE products;

-- Affichage de la table complète pour vérification
SELECT * FROM products ORDER BY id;
