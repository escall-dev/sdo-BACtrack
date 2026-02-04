<?php
/**
 * Activity Detail API
 * SDO-BACtrack
 * 
 * Returns detailed activity information for modal display
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

require_once __DIR__ . '/../../models/ProjectActivity.php';
require_once __DIR__ . '/../../models/ActivityDocument.php';

$activityId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$activityId) {
    http_response_code(400);
    echo json_encode(['error' => 'Activity ID required']);
    exit;
}

$activityModel = new ProjectActivity();
$activity = $activityModel->findById($activityId);

if (!$activity) {
    http_response_code(404);
    echo json_encode(['error' => 'Activity not found']);
    exit;
}

$documentModel = new ActivityDocument();
$documents = $documentModel->getByActivity($activityId);

// Calculate duration
$start = new DateTime($activity['planned_start_date']);
$end = new DateTime($activity['planned_end_date']);
$duration = $start->diff($end)->days + 1;

// Format dates
$activity['planned_start_date_formatted'] = date('F j, Y', strtotime($activity['planned_start_date']));
$activity['planned_end_date_formatted'] = date('F j, Y', strtotime($activity['planned_end_date']));
$activity['actual_completion_date_formatted'] = $activity['actual_completion_date'] 
    ? date('F j, Y', strtotime($activity['actual_completion_date'])) 
    : null;
$activity['duration_days'] = $duration;
$activity['status_label'] = ACTIVITY_STATUSES[$activity['status']] ?? $activity['status'];
$activity['compliance_label'] = $activity['compliance_status'] 
    ? (COMPLIANCE_STATUSES[$activity['compliance_status']] ?? $activity['compliance_status'])
    : null;

// Format documents
$formattedDocs = [];
foreach ($documents as $doc) {
    $formattedDocs[] = [
        'id' => $doc['id'],
        'original_name' => $doc['original_name'],
        'file_path' => APP_URL . '/uploads/' . $doc['file_path'],
        'uploaded_at' => date('M j, Y g:i A', strtotime($doc['uploaded_at'])),
        'uploader_name' => $doc['uploader_name']
    ];
}

$activity['documents'] = $formattedDocs;
$activity['view_url'] = APP_URL . '/admin/activity-view.php?id=' . $activityId;

echo json_encode($activity);
