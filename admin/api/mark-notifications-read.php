<?php
/**
 * Mark Notifications as Read API
 * SDO-BACtrack
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$notificationModel = new Notification();
$input = json_decode(file_get_contents('php://input'), true);

// Mark all as read
if (isset($input['all']) && $input['all'] === true) {
    $notificationModel->markAllAsRead($auth->getUserId());
    echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    exit;
}

// Mark specific notification as read
if (isset($input['id'])) {
    $notificationId = (int)$input['id'];
    $notificationModel->markAsRead($notificationId, $auth->getUserId());
    echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
