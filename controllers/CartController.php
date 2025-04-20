<?php
require_once __DIR__ . '/../class/View.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../class/SessionController.php';
require_once __DIR__ . '/../class/ServerError.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';

class CartController {
    public static function showCart() {
        $session = SessionController::getInstance();
        $session->MakeSureLoggedIn('/login');  // Make sure user is logged in

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
                
                // Track product add to cart for analytics
                $product = new Product();
                $productInfo = $product->getById($productId);
                if ($productInfo) {
                    // Track detailed product metrics
                    self::trackProductAddToCart($productId, $productInfo->name, $productInfo->category_id, $quantity);
                }
            }
            header('Location: /cart');
            exit();
        } else {
            // Handle JSON API request for AJAX calls
            if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true);
                $productId = $data['product_id'] ?? null;
                $quantity = $data['quantity'] ?? 1;
                
                if ($productId) {
                    try {
                        $cart = new Cart();
                        $cart->addToCart($session->getUser()->id, $productId, $quantity);
                        
                        // Track product add to cart for analytics
                        $product = new Product();
                        $productInfo = $product->getById($productId);
                        if ($productInfo) {
                            // Track detailed product metrics
                            self::trackProductAddToCart($productId, $productInfo->name, $productInfo->category_id, $quantity);
                        }
                        
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true]);
                        exit();
                    } catch (Exception $e) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                        exit();
                    }
                }
            }
            
            ServerError::throwError(405, 'Method Not Allowed');
        }
    }

    // Track product metrics for cart additions
    private static function trackProductAddToCart($productId, $productName, $categoryId, $quantity = 1) {
        $data = [
            'event_type' => 'add_to_cart',
            'category' => 'Product',
            'action' => 'add_to_cart',
            'label' => $productName,
            'value' => $quantity,
            'product_id' => $productId,
            'category_id' => $categoryId
        ];
        
        // Send analytics data
        $ch = curl_init('http://localhost/analytics/event');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'X-Requested-With: XMLHttpRequest']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
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

    public static function showConfirmPage() {
        $session = SessionController::getInstance();
        $session->MakeSureLoggedIn('/login');

        $cart = new Cart();
        $cartItems = $cart->getCartItems($session->getUser()->id);

        if (empty($cartItems)) {
            header('Location: /cart');
            exit();
        }

        $view = new View('confirm', 'Confirm Order');
        $view->addVar('cartItems', $cartItems);
        $view->render();
    }

    public static function processPayment() {
        $session = SessionController::getInstance();
        $session->MakeSureLoggedIn('/login');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cart = new Cart();
            $cartItems = $cart->getCartItems($session->getUser()->id);

            if (empty($cartItems)) {
                header('Location: /cart');
                exit();
            }

            // Assuming payment is successful, clear cart and record order
            $order = new Order();
            $orderId = $order->createOrder($session->getUser()->id, $cartItems);

            $cart->clearCart($session->getUser()->id);

            header('Location: /order/confirmation/' . $orderId);
            exit();
        } else {
            ServerError::throwError(405, 'Method Not Allowed');
        }
    }
}
