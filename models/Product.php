<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../class/ServerError.php');
require_once(dirname(__FILE__) . '/../class/Database.php');

class Product {
    public $id;
    public $name;
    public $description;
    public $price;

    public $category_id;
    public $image_url;
    public $created_at;
    public $updated_at;

    private function query(string $sql, array $params = []) {
        try {
            $stmt = Database::prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            ServerError::throwError(500, "Database operation failed: " . $e->getMessage());
        }
    }

    private function createFromRow(array $data): ?Product {
        if(!$data) return null;
        
        $product = new Product();
        $product->id = $data['id'];
        $product->name = $data['name'];
        $product->description = $data['description'];
        $product->price = $data['price'];
        $product->category_id = $data['category_id'];
        $product->image_url = $data['image_url'];
        $product->created_at = $data['created_at'];
        $product->updated_at = $data['updated_at'];
        return $product;
    }

    public function getById(int $id): ?Product {
        $stmt = $this->query(
            "SELECT * FROM products WHERE id = :id",
            [':id' => $id]
        );
        return $this->createFromRow($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function create(array $data): ?Product {
        $existing = $this->query(
            "SELECT id FROM products WHERE name = :name",
            [':name' => $data['name']]
        )->fetch();
        
        if($existing) {
            ServerError::throwError(409, "The product name already exists");
        }

        $stmt = $this->query(
            "INSERT INTO products (name, description, price, category_id, image_url) 
             VALUES (:name, :description, :price, :category_id, :image_url)",
            [
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':category_id' => $data['category_id'] ?? null,
                ':image_url' => $data['image_url'] ?? null
            ]
        );
        
        if($stmt->rowCount() > 0) {
            $lastId = Database::getInstance()->getPDO()->lastInsertId();
            return $this->getById($lastId);
        }
        return null;
    }

    public function update(array $data): bool {
        $sql = "UPDATE products SET 
                name = :name,
                description = :description,
                price = :price,
                category_id = :category_id,
                image_url = :image_url
                WHERE id = :id";
                
        return $this->query($sql, [
            ':id' => $this->id,
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':category_id' => $data['category_id'] ?? null,
            ':image_url' => $data['image_url'] ?? null
        ])->rowCount() > 0;
    }

    public function delete(): bool {
        return $this->query(
            "DELETE FROM products WHERE id = :id",
            [':id' => $this->id]
        )->rowCount() > 0;
    }

    public function search($query) {
        try {
            $db = Database::getInstance()->getPDO();
            
            // Build search SQL
            $sql = "SELECT p.*, c.name as category 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.name LIKE :query 
                       OR p.description LIKE :query 
                       OR c.name LIKE :query";
            
            $stmt = $db->prepare($sql);
            $searchTerm = "%{$query}%";
            $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error occurred when searching products: " . $e->getMessage());
            return [];
        }
    }

    // Get total product count for a category
    public function getCategoryProductCount(int $categoryId): int {
        $sql = "SELECT COUNT(*) FROM products WHERE category_id = :category_id";
        $stmt = Database::getInstance()->getPDO()->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getByCategory(int $categoryId, int $offset = 0, int $limit = 4): array {
        $sql = "SELECT * FROM products 
                WHERE category_id = :category_id 
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = Database::getInstance()->getPDO()->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProducts(int $offset = 0, int $limit = 4): array {
        $sql = "SELECT *
                FROM products 
                ORDER BY id ASC 
                LIMIT :limit OFFSET :offset";
        $stmt = Database::getInstance()->getPDO()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
