<?php
/**
 * Calendar Events API
 * SDO-BACtrack
 * 
 * Returns events for FullCalendar in JSON format
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

$activityModel = new ProjectActivity();

// Get filter parameters
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;
$projectId = isset($_GET['project']) ? (int)$_GET['project'] : null;

// Build query
$sql = "SELECT pa.*, bc.project_id, bc.cycle_number, p.title as project_title
        FROM project_activities pa
        LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
        LEFT JOIN projects p ON bc.project_id = p.id
        WHERE 1=1";
$params = [];

if ($start) {
    $sql .= " AND pa.planned_end_date >= ?";
    $params[] = $start;
}

if ($end) {
    $sql .= " AND pa.planned_start_date <= ?";
    $params[] = $end;
}

if ($projectId) {
    $sql .= " AND bc.project_id = ?";
    $params[] = $projectId;
}

$sql .= " ORDER BY pa.planned_start_date ASC";

$activities = db()->fetchAll($sql, $params);

// Convert to FullCalendar event format
$events = [];
foreach ($activities as $activity) {
    // Determine color based on status
    $color = '#9ca3af'; // Default gray
    switch ($activity['status']) {
        case 'PENDING':
            $color = '#f59e0b'; // Amber
            break;
        case 'IN_PROGRESS':
            $color = '#3b82f6'; // Blue
            break;
        case 'COMPLETED':
            $color = '#10b981'; // Green
            break;
        case 'DELAYED':
            $color = '#ef4444'; // Red
            break;
    }

    // End date for FullCalendar is exclusive, so add 1 day
    $endDate = new DateTime($activity['planned_end_date']);
    $endDate->modify('+1 day');

    $events[] = [
        'id' => $activity['id'],
        'title' => $activity['step_name'],
        'start' => $activity['planned_start_date'],
        'end' => $endDate->format('Y-m-d'),
        'backgroundColor' => $color,
        'borderColor' => $color,
        'url' => APP_URL . '/admin/activity-view.php?id=' . $activity['id'],
        'extendedProps' => [
            'project_id' => $activity['project_id'],
            'project_title' => $activity['project_title'],
            'cycle_number' => $activity['cycle_number'],
            'step_order' => $activity['step_order'],
            'status' => $activity['status'],
            'planned_start_date' => $activity['planned_start_date'],
            'planned_end_date' => $activity['planned_end_date'],
            'actual_completion_date' => $activity['actual_completion_date'],
            'compliance_status' => $activity['compliance_status']
        ]
    ];
}

echo json_encode($events);
