<?php
require_once __DIR__ . '/../class/View.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../class/SessionController.php';
require_once __DIR__ . '/../class/ServerError.php';

class RegisterController {
    public static function showRegisterForm() {
        $session = SessionController::getInstance();
        $session->makeSureLoggedOut('/');  // Redirect logged in users to homepage

        $view = new View('register', 'Register');
        $view->render();
    }

    public static function handleRegister() {
        $session = SessionController::getInstance();
        $session->makeSureLoggedOut('/');  // Prevent logged in users from registering again

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Get form data
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                $email = $_POST['email'] ?? '';

                // Basic validation
                if (empty($username) || empty($password) || empty($email)) {
                    throw new Exception('All fields are required');
                }

                if ($password !== $confirm_password) {
                    throw new Exception('Passwords do not match');
                }

                // Check if email already exists
                $user = new User();
                if ($user->emailExists($email)) {
                    throw new Exception('This email is already registered. Please use a different email.');
                }

                // Create user
                $newUser = $user->createUser($username, $password, $email);
                
                if ($newUser) {
                    #$session->login($newUser);// Auto login
                    header('Location: /login');
                    exit();
                }

                throw new Exception('Registration failed');

            } catch (Exception $e) {
                // Display error message
                $view = new View('register', 'Register');
                $view->addVar('error', $e->getMessage());
                $view->render();
            }
        } else {
            ServerError::throwError(405, 'Method Not Allowed');
        }
    }

    public static function checkEmail() {
        $email = $_POST['email'] ?? '';
        $user = new User();
        $exists = $user->emailExists($email);
        echo json_encode(['exists' => $exists]);
    }
}
