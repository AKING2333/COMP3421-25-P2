<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../class/ServerError.php');

class User {
    
    private $pdo;
    public $id;
    public $username;
    public $email;
    private $password_hash;
    public $created_at;
    public $last_login;

    public function __construct() {
        require_once(dirname(__FILE__) . '/../class/Database.php');
        $this->pdo = $conn;
    }

    private function query(string $sql, array $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            ServerError::ThrowError(500, "Database operation failed: " . $e->getMessage());
        }
    }

    private function createFromRow(array $data): ?User {
        if(!$data) return null;
        
        $user = new User();
        $user->id = $data['id'];
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password_hash = $data['password_hash'];
        $user->created_at = $data['created_at'];
        $user->last_login = $data['last_login'];
        return $user;
    }

    public function getById(int $id): ?User {
        $stmt = $this->query(
            "SELECT * FROM users WHERE id = :id",
            [':id' => $id]
        );
        return $this->createFromRow($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function getByUsername(string $username): ?User {
        $stmt = $this->query(
            "SELECT * FROM users WHERE username = :username",
            [':username' => $username]
        );
        return $this->createFromRow($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function verifyUser(string $username, string $password): ?User {
        $user = $this->getByUsername($username);
        if($user && password_verify($password, $user->password_hash)) {
            $this->query(
                "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id",
                [':id' => $user->id]
            );
            $user->last_login = date('Y-m-d H:i:s');
            return $user;
        }
        return null;
    }

    public function createUser(string $username, string $password, string $email): ?User {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->query(
            "INSERT INTO users (username, email, password_hash) 
             VALUES (:username, :email, :password_hash)",
            [
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $hash
            ]
        );
        
        if($stmt->rowCount() > 0) {
            return $this->getByUsername($username);
        }
        return null;
    }

    public function updatePassword(string $newPassword): bool {
        $this->password_hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->query(
            "UPDATE users SET password_hash = :password_hash WHERE id = :id",
            [
                ':password_hash' => $this->password_hash,
                ':id' => $this->id
            ]
        )->rowCount() > 0;
    }

    public function getCartItems(): array {
        $stmt = $this->query(
            "SELECT ci.*, p.name, p.price 
             FROM cart_items ci 
             JOIN products p ON ci.product_id = p.id 
             WHERE ci.user_id = :user_id",
            [':user_id' => $this->id]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrders(): array {
        $stmt = $this->query(
            "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC",
            [':user_id' => $this->id]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>