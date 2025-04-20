<?php
require_once __DIR__ . '/../class/View.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../class/SessionController.php';
require_once __DIR__ . '/../class/ServerError.php';

class LoginController {
    public static function showLoginForm() {
        $session = SessionController::getInstance();
        $session->makeSureLoggedOut('/');  // Redirect to homepage if already logged in

        $view = new View('login', 'Login');
        $view->render();
    }

    public static function handleLogin() {
        $session = SessionController::getInstance();
        $session->makeSureLoggedOut('/');  // Prevent duplicate login

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Get form data
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';

                // Basic validation
                if (empty($username) || empty($password)) {
                    throw new Exception('Username and password cannot be empty');
                }

                // Verify user
                $user = new User();
                $loggedInUser = $user->verifyUser($username, $password);
                
                if ($loggedInUser) {
                    // Login successful
                    $session->login($loggedInUser);
                    header('Location: /');
                    exit();
                } else {
                    // Invalid credentials - better user experience than throwing exception
                    $view = new View('login', 'Login');
                    $view->addVar('error', 'Invalid username or password');
                    $view->render();
                    return;
                }

            } catch (Exception $e) {
                // Display error message
                $view = new View('login', 'Login');
                $view->addVar('error', $e->getMessage());
                $view->render();
            }
        } else {
            ServerError::throwError(405, 'Method Not Allowed');
        }
    }
}