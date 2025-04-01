<?php
require_once(dirname(__FILE__) . '/../models/User.php');
class SessionController {
    private static $instance=null;
    function __construct() {
        session_start();
    }
    public static function getInstance() {
        if(self::$instance == null) {
            self::$instance = new SessionController();
        }
        return self::$instance;
    }
    function setRole(string $role) {
        $_SESSION["user_role"]=$role;
    }
    function getRole(): string {
        $role = $_SESSION['user_role'] ?? 'guest';
        return $role;
    }
    // Make sure the user is logged in, or else, redirect to $failureRedirectPath
    function MakeSureLoggedIn(string $failureRedirectPath) {
        if(!$this->isUserLoggedIn()) {
            header("Location: " . $failureRedirectPath);
            exit();
        }
    }
    // Make sure the user is logged out, or else, redirect to $failureRedirectPath
    function makeSureLoggedOut(string $failureRedirectPath) {
        if($this->isUserLoggedIn()) {
            header("Location: " . $failureRedirectPath);
            exit();
        }
    }
    function isUserLoggedIn(): bool {
        return ($this->getRole() === "user");
    }
    function getUser(): ?User {
        return $_SESSION["user"];
    }
    function setUser(?User $user): void {
        $_SESSION["user"] = $user;
    }
    function login(User $user) {
        $this->setRole('user');
        $this->setUser($user);
    }
    function logout() {
        $this->setRole('guest');
        $this->setUser(null);
    }
}
?>