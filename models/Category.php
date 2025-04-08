<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../class/ServerError.php');
require_once(dirname(__FILE__) . '/../class/Database.php');

class Category {

    public $id;
    public $name;
    public $created_at;

    private function query(string $sql, array $params = []) {
        try {
            $stmt = Database::prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            ServerError::throwError(500, "Database operation failed: " . $e->getMessage());
        }
    }

    private function createFromRow(array $data): ?Category {
        if(!$data) return null;
        
        $category = new Category();
        $category->id = $data['id'];
        $category->name = $data['name'];
        $category->created_at = $data['created_at'];
        return $category;
    }

    public function getById(int $id): ?Category {
        $stmt = $this->query(
            "SELECT * FROM categories WHERE id = :id",
            [':id' => $id]
        );
        return $this->createFromRow($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function create(array $data): ?Category {
        $existing = $this->query(
            "SELECT id FROM categories WHERE name = :name",
            [':name' => $data['name']]
        )->fetch();
        
        if($existing) {
            ServerError::throwError(409, "Category name already exists");
        }

        $stmt = $this->query(
            "INSERT INTO categories (name) VALUES (:name)",
            [':name' => $data['name']]
        );
        
        if($stmt->rowCount() > 0) {
            $lastId = Database::getInstance()->getPDO()->lastInsertId();
            return $this->getById($lastId);
        }
        return null;
    }

    public function update(array $data): bool {
        return $this->query(
            "UPDATE categories SET name = :name WHERE id = :id",
            [
                ':id' => $this->id,
                ':name' => $data['name']
            ]
        )->rowCount() > 0;
    }

    public function delete(): bool {
        return $this->query(
            "DELETE FROM categories WHERE id = :id",
            [':id' => $this->id]
        )->rowCount() > 0;
    }

    public function getAll(): array {
        $stmt = $this->query(
            "SELECT * FROM categories 
             ORDER BY created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 