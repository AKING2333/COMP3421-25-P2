<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/ServerError.php');

try {
    $conn = new PDO(
        'mysql:host=' . $GLOBALS['appConfig']['mysql']['host'] . 
        ';dbname=' . $GLOBALS['appConfig']['mysql']['database'] . 
        ';charset=utf8mb4',
        $GLOBALS['appConfig']['mysql']['username'],
        $GLOBALS['appConfig']['mysql']['password']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}
