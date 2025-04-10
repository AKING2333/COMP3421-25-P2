<?php

class Order {
    public function createOrder($userId, $cartItems) {
        // 假设我们有一个数据库连接实例 $db
        $db = Database::getInstance()->getPDO();

        // 计算总金额
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        // 插入订单记录
        $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$userId, $totalAmount]);

        $orderId = $db->lastInsertId();

        // 插入订单项
        foreach ($cartItems as $item) {
            $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
        }

        return $orderId;
    }

    public function getUserOrders($userId) {
        $db = Database::getInstance()->getPDO();
        $stmt = $db->prepare("SELECT o.*, GROUP_CONCAT(CONCAT(p.name, ':', oi.quantity) SEPARATOR ', ') AS items FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN products p ON oi.product_id = p.id WHERE o.user_id = ? GROUP BY o.id ORDER BY o.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderDetails($orderId) {
        $db = Database::getInstance()->getPDO();
        $stmt = $db->prepare("SELECT o.*, oi.product_id, oi.quantity, oi.unit_price FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE o.id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateOrderStatus($orderId, $status) {
        $db = Database::getInstance()->getPDO();
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
    }
} 