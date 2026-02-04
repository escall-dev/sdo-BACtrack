<?php
/**
 * Timeline Template API
 * SDO-BACtrack
 * 
 * Returns timeline template steps for a given procurement type
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

require_once __DIR__ . '/../../models/TimelineTemplate.php';

$procurementType = $_GET['type'] ?? '';

if (empty($procurementType) || !array_key_exists($procurementType, PROCUREMENT_TYPES)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid procurement type']);
    exit;
}

$templateModel = new TimelineTemplate();
$steps = $templateModel->getByType($procurementType);

// Calculate total duration
$totalDays = 0;
foreach ($steps as $step) {
    $totalDays += $step['default_duration_days'];
}

echo json_encode([
    'procurement_type' => $procurementType,
    'procurement_type_label' => PROCUREMENT_TYPES[$procurementType],
    'total_steps' => count($steps),
    'total_days' => $totalDays,
    'steps' => $steps
]);
