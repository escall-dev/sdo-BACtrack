<?php
/**
 * Announcements Update API (BAC Secretary only)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/Announcement.php';

$auth = auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!$auth->isBacSecretary()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$id = (int)($_POST['id'] ?? $_POST['announcement_id'] ?? 0);
if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid announcement id']);
    exit;
}

$data = [
    'title' => trim($_POST['title'] ?? ''),
    'body' => trim($_POST['body'] ?? ''),
    'link_url' => trim($_POST['link_url'] ?? ''),
    'is_active' => isset($_POST['is_active']) ? 1 : 0,
    'starts_at' => trim($_POST['starts_at'] ?? ''),
    'ends_at' => trim($_POST['ends_at'] ?? ''),
];

if ($data['title'] === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Title is required']);
    exit;
}

$model = new Announcement();
$existing = $model->findById($id);
if (!$existing) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Announcement not found']);
    exit;
}

$model->update($id, $data);
echo json_encode(['success' => true]);

