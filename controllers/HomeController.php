<?php
require_once __DIR__  . '/../class/View.php';

class HomeController {
    public static function showIndex() {
        $homePageView = new View('index', 'Home');
        $homePageView->render();
    }
}
