<?php
require_once 'config.php';

class ProductSearch {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function searchByName($query, $limit = 10) {
        $query = trim($query);
        if (empty($query)) {
            return [];
        }
        
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.name LIKE :query 
            ORDER BY p.name ASC 
            LIMIT :limit
        ");
        
        $likeQuery = '%' . $query . '%';
        $stmt->bindParam(':query', $likeQuery, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function searchWithFilters($query, $categoryId = null, $minPrice = null, $maxPrice = null, $limit = 20) {
        $sql = "
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE 1=1
        ";
        $params = [];
        
        if (!empty(trim($query))) {
            $sql .= " AND p.name LIKE :query";
            $params[':query'] = '%' . trim($query) . '%';
        }
        
        if ($categoryId && is_numeric($categoryId)) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }
        
        if ($minPrice && is_numeric($minPrice)) {
            $sql .= " AND p.price >= :min_price";
            $params[':min_price'] = $minPrice;
        }
        
        if ($maxPrice && is_numeric($maxPrice)) {
            $sql .= " AND p.price <= :max_price";
            $params[':max_price'] = $maxPrice;
        }
        
        $sql .= " ORDER BY p.name ASC LIMIT :limit";
        $params[':limit'] = $limit;
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $search = new ProductSearch();
    $action = $_GET['action'];
    
    try {
        switch ($action) {
            case 'autocomplete':
                $query = $_GET['q'] ?? '';
                $results = $search->searchByName($query, 8);
                
                $suggestions = array_map(function($product) {
                    return [
                        'id' => $product['id'],
                        'name' => htmlspecialchars($product['name']),
                        'price' => number_format($product['price'], 2),
                        'category' => htmlspecialchars($product['category_name'] ?? 'Non catégorisé'),
                        'image' => $product['image'] ? 'img/product/' . htmlspecialchars($product['image']) : 'img/product/default.jpg'
                    ];
                }, $results);
                
                echo json_encode([
                    'success' => true,
                    'suggestions' => $suggestions
                ]);
                break;
                
            case 'search':
                $query = $_GET['q'] ?? '';
                $categoryId = $_GET['category'] ?? null;
                $minPrice = $_GET['min_price'] ?? null;
                $maxPrice = $_GET['max_price'] ?? null;
                
                $results = $search->searchWithFilters($query, $categoryId, $minPrice, $maxPrice);
                
                $products = array_map(function($product) {
                    return [
                        'id' => $product['id'],
                        'name' => htmlspecialchars($product['name']),
                        'price' => number_format($product['price'], 2),
                        'category' => htmlspecialchars($product['category_name'] ?? 'Non catégorisé'),
                        'image' => $product['image'] ? 'img/product/' . htmlspecialchars($product['image']) : 'img/product/default.jpg',
                        'description' => htmlspecialchars($product['description'] ?? '')
                    ];
                }, $results);
                
                echo json_encode([
                    'success' => true,
                    'products' => $products,
                    'count' => count($products)
                ]);
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Action non valide']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
    }
    exit;
}
?>
