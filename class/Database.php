<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/ServerError.php');

class Database {
    private static $instance = null;
    private static $pdoObject = null;

    private function __construct() {
        // Connect to the database using PDO in exception mode
        try {
            self::$pdoObject = new PDO(
                'mysql:host=' . $GLOBALS['appConfig']['mysql']['host'] . 
                ';dbname=' . $GLOBALS['appConfig']['mysql']['database'] . 
                ';charset=utf8mb4',
                $GLOBALS['appConfig']['mysql']['username'],
                $GLOBALS['appConfig']['mysql']['password']
            );
            self::$pdoObject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            self::handlePDOException($e);
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
        return self::$pdoObject;
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
