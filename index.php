<?php
require('vendor/autoload.php');
require("class/ServerError.php");
require("class/SessionController.php");
require('config.php');


$router = new AltoRouter();
$router->setBasePath("");


$router->map('GET', '/', 'HomeController@showIndex');
$router->map('GET', '/products', 'ProductController@listProducts');
$router->map('GET', '/product/[i:id]', 'ProductController@showProduct');
$router->map('POST', '/cart/add', 'CartController@addToCart');
$router->map('GET', '/register', 'RegisterController@showRegisterForm');
$router->map('POST', '/register', 'RegisterController@handleRegister');
$router->map('GET', '/login', 'LoginController@showLoginForm');
$router->map('POST', '/login', 'LoginController@handleLogin');
$router->map('GET', '/logout', 'LogoutController@handleLogout');
$router->map('GET', '/about', 'HomeController@showAbout');
$router->map('GET', '/products/category/[i:categoryId]', 'ProductController@getByCategory');
$router->map('GET', '/cart', 'CartController@showCart');
$router->map('POST', '/cart/update', 'CartController@updateCart');
$router->map('POST', '/cart/remove', 'CartController@removeFromCart');
$router->map('GET', '/products/load-more/[i:categoryId]/[i:offset]', 'ProductController@loadMoreProducts');
$router->map('GET', '/cart/confirm', 'CartController@showConfirmPage');
$router->map('GET', '/order/history', 'OrderController@showPurchaseHistory');
$router->map('POST', '/cart/payment', 'CartController@processPayment');
$router->map('GET', '/order/confirmation/[i:id]', 'OrderController@showConfirmation');
$router->map('GET', '/order/repay/[i:id]', 'OrderController@repayOrder');
$router->map('GET', '/order/cancel/[i:id]', 'OrderController@cancelOrder');
$router->map('POST', '/check-email', 'RegisterController@checkEmail');
$router->map('POST', '/analytics/pageview', 'AnalyticsController@trackPageview');
$router->map('POST', '/analytics/event', 'AnalyticsController@trackEvent');
$router->map('POST', '/analytics/performance', 'AnalyticsController@trackPerformance');
$router->map('POST', '/prometheus-proxy.php', function() {
    include('prometheus-proxy.php');
});
$router->map('POST', '/prometheus-proxy', function() {
    include('prometheus-proxy.php');
});
$router->map('GET', '/test-prometheus', function() {
    include('test-prometheus.php');
});
$router->map('GET', '/search', 'ProductController@search');
$router->map('GET', '/contact', 'HomeController@showContact');
$router->map('GET', '/faq', 'HomeController@showFAQ');
$match = $router->match();


if(!$match) { // No match, which means the user is browsing a non-defined page
    ServerError::throwError(404, 'Path not found');
}else{
    // There is a match
    if(is_callable($match['target'])) { // We are passing a anonymous function/closure
        call_user_func_array($match['target'], $match['params']); // Execute the anonymous function
    }else{
        // Test the syntax: "controller_name@function"
        list($controllerClassName, $methodName) = explode('@', $match['target']);
        if((@include_once("controllers/" . $controllerClassName . ".php")) == TRUE) {
            if (is_callable(array($controllerClassName, $methodName))) {
                call_user_func_array(array($controllerClassName, $methodName), ($match['params']));
            } else {
                // Internal error
                // We have defined a correct route but it is not callable
                ServerError::throwError(500, 'Route not callable');
            }
        }else{
            // Internal error
            // We have defined a correct route but the controller is not includible (and hence not callable)
            ServerError::throwError(500, 'Controller not includible');
        }
    }
}
