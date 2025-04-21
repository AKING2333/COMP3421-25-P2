<?php
require_once __DIR__ . '/../class/View.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../class/SessionController.php';

class OrderController {
    public static function showPurchaseHistory() {
        $session = SessionController::getInstance();
        $session->MakeSureLoggedIn('/login');

        $order = new Order();
        $orders = $order->getUserOrders($session->getUser()->id);

        $view = new View('purchase_history', 'Purchase History');
        $view->addVar('orders', $orders);
        $view->render();
    }

    public static function showConfirmation($id) {
        $session = SessionController::getInstance();
        $session->MakeSureLoggedIn('/login');

        $order = new Order();
        $orderDetails = $order->getOrderDetails($id);

        if (!$orderDetails) {
            ServerError::throwError(404, 'Order not found');
        }

        $order->updateOrderStatus($id, 'completed');

        $view = new View('order_confirmation', 'Order Confirmation');
        $view->addVar('orderDetails', $orderDetails);
        $view->render();
    }

    public static function repayOrder($id) {
        $session = SessionController::getInstance();
        $session->MakeSureLoggedIn('/login');

        $order = new Order();
        $orderDetails = $order->getOrderDetails($id);

        if (!$orderDetails || $orderDetails[0]['status'] !== 'pending') {
            ServerError::throwError(404, 'Order not found or not pending');
        }

        // 假设重新支付成功，更新订单状态
        $order->updateOrderStatus($id, 'completed');

        header('Location: /order/confirmation/' . $id);
        exit();
    }

    public static function cancelOrder($id) {
        $session = SessionController::getInstance();
        $session->MakeSureLoggedIn('/login');

        $order = new Order();
        $orderDetails = $order->getOrderDetails($id);

        if (!$orderDetails || $orderDetails[0]['status'] !== 'pending') {
            ServerError::throwError(404, 'Order not found or not pending');
        }

        // 更新订单状态为取消
        $order->updateOrderStatus($id, 'cancelled');

        header('Location: /order/history');
        exit();
    }
} 