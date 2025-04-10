<?php
require_once __DIR__ . '/../class/View.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../class/SessionController.php';
require_once __DIR__ . '/../class/ServerError.php';

class CartController {
    public static function showCart() {
        $session = SessionController::getInstance();
        $session->MakeSureLoggedIn('/login');  // 确保用户已登录

        $cart = new Cart();
        $cartItems = $cart->getCartItems($session->getUser()->id);

        $view = new View('cart', 'Cart');
        $view->addVar('cartItems', $cartItems);
        $view->render();
    }

    public static function addToCart() {
        $session = SessionController::getInstance();
        $session->MakeSureLoggedIn('/login');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? null;
            $quantity = $_POST['quantity'] ?? 1;

            if ($productId) {
                $cart = new Cart();
                $cart->addToCart($session->getUser()->id, $productId, $quantity);
            }
            header('Location: /cart');
            exit();
        } else {
            ServerError::throwError(405, 'Method Not Allowed');
        }
    }

    public static function updateCart() {
        $session = SessionController::getInstance();
        $session->MakeSureLoggedIn('/login');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? null;
            $quantity = $_POST['quantity'] ?? 1;

            if ($productId) {
                $cart = new Cart();
                $cart->updateCartItem($session->getUser()->id, $productId, $quantity);
            }
            header('Location: /cart');
            exit();
        } else {
            ServerError::throwError(405, 'Method Not Allowed');
        }
    }

    public static function removeFromCart() {
        $session = SessionController::getInstance();
        $session->MakeSureLoggedIn('/login');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? null;

            if ($productId) {
                $cart = new Cart();
                $cart->removeFromCart($session->getUser()->id, $productId);
            }
            header('Location: /cart');
            exit();
        } else {
            ServerError::throwError(405, 'Method Not Allowed');
        }
    }
}
