<?php
require_once __DIR__  . '/../class/View.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../class/ServerError.php';

class ProductController {

    public static function listProducts() {
        // Fetch all products
        $productInstance = new Product(); 
        $products=$productInstance->getProducts();
        $mainView = new View('products', 'Products');
        $mainView->addVar('$products', $products);
        $mainView->render();
    }

    public static function showProduct($id) {
        // Fetch a single product by ID
        $productInstance = new Product(); 
        $product=$productInstance->getById($id);
        if ($product) {
            $ShowProductView = new View('ShowProduct', 'ShowProduct');
            $ShowProductView->addVar('$product', $product);
            $ShowProductView->render();
        } else {
            // Handle product not found
            ServerError::throwError(404, 'Product not found');
        }
    }

}
