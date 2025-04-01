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

    public function __construct() {
        require_once(dirname(__FILE__) . '/../class/Database.php');
        global $conn;
        $this->pdo = $conn;
    }

    private static function query(string $sql, array $params = []){
        try {
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            ServerError::ThrowError(500, "Database operation failed: " . $e->getMessage());
        }
    }

    private static function createFromRow(array $data): ?Product {
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

    public static function getById(int $id): ?Product {
        $stmt = self::query(
            "SELECT * FROM products WHERE id = :id",
            [':id' => $id]
        );
        return self::createFromRow($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public static function create(array $data): ?Product {

        $existing = self::query(
            "SELECT id FROM products WHERE name = :name",
            [':name' => $data['name']]
        )->fetch();
        
        if($existing) {
            ServerError::ThrowError(409, "The product name already exists");
        }


        $stmt = self::query(
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
            return self::getById(self::$pdo->lastInsertId());
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
                
        return self::query($sql, [
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
        return self::query(
            "DELETE FROM products WHERE id = :id",
            [':id' => $this->id]
        )->rowCount() > 0;
    }

    // 库存管理
    public function updateStock(int $quantity): bool {
        return self::query(
            "UPDATE products SET stock = stock + :quantity WHERE id = :id",
            [
                ':id' => $this->id,
                ':quantity' => $quantity
            ]
        )->rowCount() > 0;
    }

    public static function search(string $keyword, int $limit = 10, int $offset = 0): array {
        $stmt = self::query(
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

    public static function getByCategory(int $categoryId, int $limit = 10, int $offset = 0): array {
        $stmt = self::query(
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

    public static function getPopular(int $limit = 10): array {
        $stmt = self::query(
            "SELECT p.*, COUNT(oi.id) as order_count 
             FROM products p 
             LEFT JOIN order_items oi ON p.id = oi.product_id 
             GROUP BY p.id 
             ORDER BY order_count DESC 
             LIMIT :limit",
            [':limit' => $limit]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>
