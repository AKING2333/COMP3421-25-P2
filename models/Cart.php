<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../class/ServerError.php');
require_once(dirname(__FILE__) . '/../class/Database.php');

class Cart {
    public $user_id;
    public $product_id;
    public $quantity;

    private function query(string $sql, array $params = []) {
        try {
            $stmt = Database::prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            ServerError::throwError(500, "Database operation failed: " . $e->getMessage().
                " SQL: " . $sql . " Params: " . json_encode($params));
        }
    }

    public function addToCart(int $userId, int $productId, int $quantity = 1): bool {
        if(!$userId){
            throw new Exception("User ID is required.");
        }
        if(!$productId){
            throw new Exception("Product ID is required.");
        }
        if($quantity <= 0){
            throw new Exception("Quantity must be greater than zero.");
        }
        $stmt = $this->query(
            "INSERT INTO cart_items (user_id, product_id, quantity) 
             VALUES (:user_id, :product_id, :quantity)
             ON DUPLICATE KEY UPDATE quantity = quantity + :quantity_update",
            [
                ':user_id' => $userId,
                ':product_id' => $productId,
                ':quantity' => $quantity,
                ':quantity_update' => $quantity
            ]
        );
        return $stmt->rowCount() > 0;
    }

    public function getCartItems(int $userId): array {
        $stmt = $this->query(
            "SELECT ci.*, p.name, p.price 
             FROM cart_items ci 
             JOIN products p ON ci.product_id = p.id 
             WHERE ci.user_id = :user_id",
            [':user_id' => $userId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeFromCart(int $userId, int $productId): bool {
        $stmt = $this->query(
            "DELETE FROM cart_items WHERE user_id = :user_id AND product_id = :product_id",
            [
                ':user_id' => $userId,
                ':product_id' => $productId
            ]
        );
        return $stmt->rowCount() > 0;
    }

    public function updateCartItem(int $userId, int $productId, int $quantity): bool {
        $stmt = $this->query(
            "UPDATE cart_items SET quantity = :quantity 
             WHERE user_id = :user_id AND product_id = :product_id",
            [
                ':user_id' => $userId,
                ':product_id' => $productId,
                ':quantity' => $quantity
            ]
        );
        return $stmt->rowCount() > 0;
    }

    public function clearCart(int $userId): bool {
        $stmt = $this->query(
            "DELETE FROM cart_items WHERE user_id = :user_id",
            [':user_id' => $userId]
        );
        return $stmt->rowCount() > 0;
    }
}
?>