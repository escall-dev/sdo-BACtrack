<?php
/**
 * Authentication Helper
 * SDO-BACtrack - Token-based authentication for multi-tab support
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Session.php';

class Auth {
    private $user = null;
    private $token = null;
    private $userModel;
    private $sessionModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
        $this->userModel = new User();
        $this->sessionModel = new Session();
        $this->validateToken();
    }

    /**
     * Get token from request: GET, POST, Header, or Cookie (tab-specific).
     * Cookies use tab-specific names to prevent cross-tab interference.
     */
    private function getTokenFromRequest() {
        $param = AUTH_TOKEN_PARAM;
        if (!empty($_GET[$param])) {
            return trim($_GET[$param]);
        }
        if (!empty($_POST[$param])) {
            return trim($_POST[$param]);
        }
        if (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
            return trim($_SERVER['HTTP_X_AUTH_TOKEN']);
        }
        // Check for auth token cookie (fallback for refresh)
        if (!empty($_COOKIE[$param])) {
            $token = trim($_COOKIE[$param]);
            if (!empty($token)) {
                return $token;
            }
        }
        return null;
    }

    /**
     * Validate token and load user.
     */
    private function validateToken() {
        $token = $this->getTokenFromRequest();
        if (empty($token)) {
            return;
        }
        $session = $this->sessionModel->findByToken($token);
        if ($session) {
            $this->token = $token;
            $this->user = $this->userModel->findById($session['user_id']);
            // Sliding expiration: extend session on each request so user is only logged out after inactivity
            $this->sessionModel->extendExpiry($token, SESSION_LIFETIME);
            // Refresh cookie expiry to match the extended DB session
            setcookie(AUTH_TOKEN_PARAM, $token, time() + SESSION_LIFETIME, APP_URL . '/', '', false, false);
        }
    }

    /**
     * Login and create session. Returns token on success, false on failure.
     * @return string|false Token on success, false on failure
     */
    public function login($email, $password) {
        $user = $this->userModel->verifyPassword($email, $password);

        if ($user) {
            if (!$this->userModel->isApproved($user)) {
                return false; // Account pending approval
            }
            $token = bin2hex(random_bytes(32));
            $deviceInfo = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
            $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
            $this->sessionModel->create($user['id'], $token, $deviceInfo, $expiresAt);

            // Set auth cookie server-side so it's available on ALL subsequent
            // requests without waiting for JavaScript to store it.
            setcookie(AUTH_TOKEN_PARAM, $token, time() + SESSION_LIFETIME, APP_URL . '/', '', false, false);

            $this->token = $token;
            $this->user = $user;
            return $token;
        }

        return false;
    }

    public function logout() {
        $token = $this->getTokenFromRequest();
        if ($token) {
            $this->sessionModel->deleteByToken($token);
        }
        $this->user = null;
        $this->token = null;
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        // Clear auth token cookie
        setcookie(AUTH_TOKEN_PARAM, '', time() - 42000, APP_URL . '/', '', false, false);

        session_destroy();
    }

    public function isLoggedIn() {
        return $this->user !== null;
    }

    public function getUser() {
        return $this->user;
    }

    /**
     * Get current auth token (for JS link augmentation).
     */
    public function getToken() {
        return $this->token;
    }

    public function getUserId() {
        return $this->user['id'] ?? null;
    }

    public function getUserRole() {
        return $this->user['role'] ?? null;
    }

    public function getUserName() {
        return $this->user['name'] ?? null;
    }

    public function isProcurement() {
        return in_array($this->getUserRole(), ['ADMIN', 'BAC_SECRETARY', 'SUPERADMIN'], true);
    }

    public function isBacSecretary() {
        return $this->getUserRole() === 'BAC_SECRETARY';
    }

    public function isAdmin() {
        return $this->getUserRole() === 'ADMIN';
    }

    public function isProjectOwner() {
        return false;
    }

    public function isSuperAdmin() {
        return $this->getUserRole() === 'SUPERADMIN';
    }

    /**
     * Redirect to URL with auth token appended (keeps user logged in after POST redirect).
     */
    public function redirect($url) {
        $token = $this->getToken();
        if ($token) {
            $sep = strpos($url, '?') !== false ? '&' : '?';
            $url .= $sep . AUTH_TOKEN_PARAM . '=' . urlencode($token);
        }
        header('Location: ' . $url);
        exit;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            // Strip any auth_token parameter from the saved redirect URL
            // to prevent stale tokens from poisoning the re-login redirect.
            $redirectUri = $_SERVER['REQUEST_URI'];
            $redirectUri = preg_replace('/([?&])' . preg_quote(AUTH_TOKEN_PARAM, '/') . '=[^&]*(&|$)/', '$1', $redirectUri);
            $redirectUri = rtrim($redirectUri, '?&');
            $_SESSION['redirect_after_login'] = $redirectUri;
            header('Location: ' . APP_URL . '/admin/landing.php');
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

    public function requireBacSecretary() {
        $this->requireLogin();
        if (!$this->isBacSecretary()) {
            $_SESSION['flash_error'] = 'Only the BAC Secretary can perform this action.';
            header('Location: ' . APP_URL . '/admin/');
            exit;
        }
    }

    public function requireSuperAdmin() {
        $this->requireLogin();
        if (!$this->isSuperAdmin()) {
            $_SESSION['flash_error'] = 'You do not have permission to perform this action.';
            header('Location: ' . APP_URL . '/admin/');
            exit;
        }
    }

    public function canUpdateActivity() {
        return $this->isBacSecretary();
    }

    public function canUploadDocuments() {
        return $this->isAdmin() || $this->isBacSecretary() || $this->isSuperAdmin();
    }

    public function canSetCompliance() {
        return $this->isBacSecretary();
    }

    public function canApproveAdjustments() {
        return $this->isBacSecretary();
    }

    public function canRequestAdjustment() {
        return $this->isAdmin() || $this->isBacSecretary();
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
