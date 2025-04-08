<?php
require_once __DIR__ . '/../class/View.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../class/SessionController.php';
require_once __DIR__ . '/../class/ServerError.php';

class RegisterController {
    public static function showRegisterForm() {
        $session = SessionController::getInstance();
        $session->makeSureLoggedOut('/');  // 已登录用户跳转首页

        $view = new View('register', 'Register');
        $view->render();
    }

    public static function handleRegister() {
        $session = SessionController::getInstance();
        $session->makeSureLoggedOut('/');  // 防止已登录用户重复注册

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // 获取表单数据
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                $email = $_POST['email'] ?? '';

                // 基础验证
                if (empty($username) || empty($password) || empty($email)) {
                    throw new Exception('All fields are required');
                }

                if ($password !== $confirm_password) {
                    throw new Exception('Passwords do not match');
                }

                // 创建用户
                $user = new User();
                $newUser = $user->createUser($username, $password, $email);
                
                
                if ($newUser) {
                    // 自动登录
                    $session->login($newUser);
                    header('Location: /');
                    exit();
                }

                throw new Exception('Registration failed');

            } catch (Exception $e) {
                // 显示错误信息
                $view = new View('register', 'Register');
                $view->addVar('error', $e->getMessage());
                $view->render();
            }
        } else {
            ServerError::throwError(405, 'Method Not Allowed');
        }
    }
}
