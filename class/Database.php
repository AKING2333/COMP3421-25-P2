<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/ServerError.php');

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_NAME') ?: 'online_store';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: 'root_password';
        
        try {
            $this->pdo = new PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('数据库连接失败: ' . $e->getMessage());
        }
    }

    // Exit application when PDO Exception
    private static function handlePDOException(PDOException $e) {
        $error = "Application DB Error: " . $e->getMessage();
        ServerError::throwError(500, $error);
    }

    // Singleton
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getPDO(): PDO {
        return $this->pdo;
    }

    public static function query(string $query): PDOStatement {
        try {
            return self::getInstance()->getPDO()->query($query);
        } catch (PDOException $e) {
            self::handlePDOException($e);
            throw $e;
        }
    }

    public static function execute(string $statement): bool {
        try {
            $exec = self::getInstance()->getPDO()->exec($statement);
            if ($exec === 0 || $exec === false) {
                return false;
            }
            return true;
        } catch (PDOException $e) {
            self::handlePDOException($e);
            return false;
        }
    }

    // 添加预处理语句方法
    public static function prepare(string $query): PDOStatement {
        try {
            return self::getInstance()->getPDO()->prepare($query);
        } catch (PDOException $e) {
            self::handlePDOException($e);
            throw $e;
        }
    }
}
