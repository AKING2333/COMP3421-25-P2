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

        // 识别 AJAX 请求
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
        {
            // 直接包含局部模板
            include __DIR__ . '/../views/partials/product_list.php';
            exit;
        }
    }

    public static function showProduct($id) {
        // 获取单个产品信息
        $productInstance = new Product(); 
        $product = $productInstance->getById($id);
        
        if ($product) {
            // 识别 AJAX 请求
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            {
                // 返回JSON数据
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'product' => $product
                ]);
                exit;
            }
            
            // 正常页面请求
            $productView = new View('product', 'Product Details');
            $productView->addVar('product', $product);
            $productView->render();
        } else {
            // 处理产品未找到的情况
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

        // 识别 AJAX 请求
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
        {
            include __DIR__ . '/../views/partials/product_list.php';
            exit;
        }
    }

    public static function search() {
        $query = $_GET['q'] ?? '';
        $productInstance = new Product();
        $products = $productInstance->search($query);
        
        // 识别 AJAX 请求
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
        
        // 正常页面请求
        $searchView = new View('search', 'Search Results');
        $searchView->addVar('products', $products);
        $searchView->addVar('query', $query);
        $searchView->render();
    }
}
