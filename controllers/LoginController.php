<?php
require_once __DIR__ . '/../class/View.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../class/SessionController.php';
require_once __DIR__ . '/../class/ServerError.php';

class LoginController {
    public static function showLoginForm() {
        $session = SessionController::getInstance();
        $session->makeSureLoggedOut('/');  // prevent duplicate login

        $view = new View('login', 'Login');
        $view->render();
    }

    public static function handleLogin() {
        $session = SessionController::getInstance();
        $session->makeSureLoggedOut('/');  // prevent duplicate login

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // 获取表单数据
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';

                // basic validation
                if (empty($username) || empty($password)) {
                    throw new Exception('username and password cannot be empty');
                }

                // verify user
                $user = new User();
                $loggedInUser = $user->verifyUser($username, $password);
                
                if ($loggedInUser) {
                    // login successfully
                    $session->login($loggedInUser);
                    header('Location: /');
                    exit();
                }

                throw new Exception('username or password is incorrect');

            } catch (Exception $e) {
                // show error message
                $view = new View('login', 'Login');
                $view->addVar('error', $e->getMessage());
                $view->render();
            }
        } else {
            ServerError::throwError(405, 'Method Not Allowed');
        }
    }
}