<?php
/**
 * Authentication Helper
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class Auth {
    private $user = null;
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
        $this->userModel = new User();
        
        if ($this->isLoggedIn()) {
            $this->user = $this->userModel->findById($_SESSION['user_id']);
        }
    }

    public function login($email, $password) {
        $user = $this->userModel->verifyPassword($email, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['login_time'] = time();
            $this->user = $user;
            return true;
        }
        
        return false;
    }

    public function logout() {
        $this->user = null;
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function getUser() {
        return $this->user;
    }

    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public function getUserRole() {
        return $_SESSION['user_role'] ?? null;
    }

    public function getUserName() {
        return $_SESSION['user_name'] ?? null;
    }

    public function isProcurement() {
        return $this->getUserRole() === 'PROCUREMENT';
    }

    public function isProjectOwner() {
        return $this->getUserRole() === 'PROJECT_OWNER';
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . APP_URL . '/admin/login.php');
            exit;
        }
    }

    public function requireProcurement() {
        $this->requireLogin();
        if (!$this->isProcurement()) {
            $_SESSION['flash_error'] = 'You do not have permission to perform this action.';
            header('Location: ' . APP_URL . '/admin/');
            exit;
        }
    }

    public function canUpdateActivity() {
        return $this->isProcurement();
    }

    public function canUploadDocuments() {
        return $this->isProcurement();
    }

    public function canSetCompliance() {
        return $this->isProcurement();
    }

    public function canApproveAdjustments() {
        return $this->isProcurement();
    }

    public function canRequestAdjustment() {
        return $this->isLoggedIn();
    }
}

// Helper function to get auth instance
function auth() {
    static $auth = null;
    if ($auth === null) {
        $auth = new Auth();
    }
    return $auth;
}
