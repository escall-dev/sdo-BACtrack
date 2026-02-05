<?php
/**
 * Mark Notifications as Read API
 * SDO-BACtrack
 * GET with auth_token = mark all as read (for link clicks)
 * POST with JSON = mark all or specific notification
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$auth = auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../models/Notification.php';

$notificationModel = new Notification();

// GET request (link click) = mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $notificationModel->markAllAsRead($auth->getUserId());
    $redirect = APP_URL . '/admin/';
    $token = $auth->getToken();
    if ($token) {
        $redirect .= (strpos($redirect, '?') !== false ? '&' : '?') . AUTH_TOKEN_PARAM . '=' . urlencode($token);
    }
    header('Location: ' . $redirect);
    exit;
}

// POST request with JSON body
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    if (isset($input['all']) && $input['all'] === true) {
        $notificationModel->markAllAsRead($auth->getUserId());
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
        exit;
    }

    if (isset($input['id'])) {
        $notificationId = (int)$input['id'];
        $notificationModel->markAsRead($notificationId, $auth->getUserId());
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        exit;
    }
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
