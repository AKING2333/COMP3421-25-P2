<?php
require_once __DIR__ . '/../class/View.php';
require_once __DIR__ . '/../class/SessionController.php';
require_once __DIR__ . '/../class/ServerError.php';
class LogoutController {
    public static function handleLogout() {
        // 获取会话控制器实例
        $sessionController = SessionController::getInstance();
        
        // 执行登出操作
        $sessionController->logout();
        
        // 重定向到首页
        header("Location: /login");
        exit();
    }
}