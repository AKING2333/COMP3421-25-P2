<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../class/ServerError.php');
require_once(dirname(__FILE__) . '/../class/Database.php');

class Product {
    private $pdo;
    public $id;
    public $name;
    public $description;
    public $price;
    public $stock;
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
        $product->stock = $data['stock'];
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
            "INSERT INTO products (name, description, price, stock, category_id, image_url) 
             VALUES (:name, :description, :price, :stock, :category_id, :image_url)",
            [
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':stock' => $data['stock'],
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
                stock = :stock,
                category_id = :category_id,
                image_url = :image_url
                WHERE id = :id";
                
        return $this->query($sql, [
            ':id' => $this->id,
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':stock' => $data['stock'],
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

    // 库存管理
    public function updateStock(int $quantity): bool {
        return $this->query(
            "UPDATE products SET stock = stock + :quantity WHERE id = :id",
            [
                ':id' => $this->id,
                ':quantity' => $quantity
            ]
        )->rowCount() > 0;
    }

    public function search(string $keyword, int $limit = 10, int $offset = 0): array {
        $stmt = $this->query(
            "SELECT * FROM products 
             WHERE MATCH(name, description) AGAINST(:keyword IN BOOLEAN MODE)
             LIMIT :limit OFFSET :offset",
            [
                ':keyword' => $keyword,
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCategory(int $categoryId, int $limit = 10, int $offset = 0): array {
        $stmt = $this->query(
            "SELECT * FROM products 
             WHERE category_id = :category_id 
             ORDER BY created_at DESC 
             LIMIT :limit OFFSET :offset",
            [
                ':category_id' => $categoryId,
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProducts(): array {
        $stmt = $this->query(
            "SELECT *
             FROM products p 
             ORDER BY id ASC 
             LIMIT 10"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
