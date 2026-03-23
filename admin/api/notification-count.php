<?php
/**
 * Notification Count API
 * SDO-BACtrack
 * Returns the current unread notification count for the logged-in user.
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
$count = (int) $notificationModel->getUnreadCount($auth->getUserId());

echo json_encode(['unread' => $count]);
