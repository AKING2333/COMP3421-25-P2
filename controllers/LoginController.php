<?php
require_once __DIR__ . '/../class/View.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../class/SessionController.php';
require_once __DIR__ . '/../class/ServerError.php';

class LoginController {
    public static function showLoginForm() {
        $session = SessionController::getInstance();
        $session->makeSureLoggedOut('/');  // 如果已登录则重定向到首页

        $view = new View('login', 'Login');
        $view->render();
    }

    public static function handleLogin() {
        $session = SessionController::getInstance();
        $session->makeSureLoggedOut('/');  // 防止重复登录

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // 获取表单数据
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';

                // 基础验证
                if (empty($username) || empty($password)) {
                    throw new Exception('username and password cannot be empty');
                }

                // 验证用户
                $user = new User();
                $loggedInUser = $user->verifyUser($username, $password);
                
                if ($loggedInUser) {
                    // 登录成功
                    $session->login($loggedInUser);
                    header('Location: /');
                    exit();
                }

                throw new Exception('username or password is incorrect');

            } catch (Exception $e) {
                // 显示错误信息
                $view = new View('login', 'Login');
                $view->addVar('error', $e->getMessage());
                $view->render();
            }
        } else {
            ServerError::throwError(405, 'Method Not Allowed');
        }
    }
}