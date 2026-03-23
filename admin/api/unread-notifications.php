<?php
/**
 * Unread Notifications API
 * SDO-BACtrack
 * Returns the most recent unread notifications for the logged-in user.
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

// Fetch unread notifications from the last 1 minute (for polling)
// or just return all unread notifications if they are few.
$unread = $notificationModel->getByUser($auth->getUserId(), 5, true);

echo json_encode(['unread' => $unread]);
