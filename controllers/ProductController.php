<?php
require_once __DIR__  . '/../class/View.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../class/ServerError.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController {

    public static function listProducts() {
        $productInstance = new Product();
        $categoryInstance = new Category();
        
        $products = $productInstance->getProducts();
        $categories = $categoryInstance->getAll();        
        
        $mainView = new View('products', 'Products');
        $mainView->addVar('products', $products);
        $mainView->addVar('categories', $categories);
        $mainView->render();
    }

    public static function getByCategory($categoryId) {
        $productInstance = new Product();
        $products = $productInstance->getByCategory($categoryId);
        $totalCount = $productInstance->getCategoryProductCount($categoryId);
        $hasMore = count($products) < $totalCount;

        // Identify AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
        {
            // Include partial template
            include __DIR__ . '/../views/partials/product_list.php';
            exit;
        }
    }

    public static function showProduct($id) {
        // Get single product information
        $productInstance = new Product(); 
        $product = $productInstance->getById($id);
        
        if ($product) {
            // Identify AJAX request
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            {
                // Return JSON data
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'product' => $product
                ]);
                exit;
            }
            
            // Normal page request
            $productView = new View('product', 'Product Details');
            $productView->addVar('product', $product);
            $productView->render();
        } else {
            // Handle product not found
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Product not found'
                ]);
                exit;
            }
            ServerError::throwError(404, 'Product not found');
        }
    }

    public static function loadMoreProducts($categoryId, $offset) {
        $productInstance = new Product();
        $products = $productInstance->getByCategory($categoryId, $offset);
        $totalCount = $productInstance->getCategoryProductCount($categoryId);
        $hasMore = ($offset + count($products)) < $totalCount;

        // Identify AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
        {
            // Return both products and hasMore flag
            header('Content-Type: application/json');
            echo json_encode([
                'html' => self::renderPartialToString('partials/product_list.php', ['products' => $products]),
                'hasMore' => $hasMore
            ]);
            exit;
        }
    }

    // Helper method to render a partial template to string
    private static function renderPartialToString($template, $vars = []) {
        extract($vars);
        ob_start();
        include __DIR__ . '/../views/' . $template;
        return ob_get_clean();
    }

    public static function search() {
        $query = $_GET['q'] ?? '';
        $productInstance = new Product();
        $products = $productInstance->search($query);
        
        // Identify AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
        {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'products' => $products
            ]);
            exit;
        }
        
        // Normal page request
        $searchView = new View('search', 'Search Results');
        $searchView->addVar('products', $products);
        $searchView->addVar('query', $query);
        $searchView->render();
    }
}
