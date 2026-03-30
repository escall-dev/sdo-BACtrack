<?php
require_once __DIR__ . '/../../config/database.php';
$db = db();

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$query = trim($query);

if (empty($query)) {
    echo json_encode(['success' => false, 'error' => 'Please enter a Project Number or Title.']);
    exit;
}

// Accept plain numeric IDs (e.g. 27) and formatted project numbers (e.g. PR-0027).
$numericId = null;
if (is_numeric($query)) {
    $numericId = (int) $query;
} elseif (preg_match('/^PR[-_\s]*([0-9]+)$/i', $query, $matches)) {
    $numericId = (int) $matches[1];
}

if ($numericId !== null) {
    $sql = "SELECT p.*, (SELECT COUNT(*) FROM bac_cycles WHERE project_id = p.id) as cycle_count 
            FROM projects p WHERE p.id = ?";
    $params = [$numericId];
} else {
    $sql = "SELECT p.*, (SELECT COUNT(*) FROM bac_cycles WHERE project_id = p.id) as cycle_count 
            FROM projects p
            WHERE p.title LIKE ?
               OR CONCAT('PR-', LPAD(p.id, 4, '0')) LIKE ?
            LIMIT 10";
    $params = ['%' . $query . '%', '%' . $query . '%'];
}

$projects = $db->fetchAll($sql, $params);

if (empty($projects)) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

$results = [];
foreach ($projects as $project) {
    $cycle = $db->fetch("SELECT * FROM bac_cycles WHERE project_id = ? ORDER BY id DESC LIMIT 1", [$project['id']]);
    $currentStatusStr = 'NO ACTIVE CYCLE';
    $activities = [];

    if ($cycle) {
        $activities = $db->fetchAll("SELECT * FROM project_activities WHERE bac_cycle_id = ? ORDER BY step_order ASC", [$cycle['id']]);
        
        $completedSteps = 0;
        $totalSteps = count($activities);
        $currentStep = null;
        
        foreach ($activities as $act) {
            if ($act['status'] === 'COMPLETED') {
                $completedSteps++;
            } elseif (!$currentStep && in_array($act['status'], ['PENDING', 'IN_PROGRESS', 'DELAYED'])) {
                $currentStep = $act;
            }
        }
        
        if ($currentStep) {
            $currentStatusStr = "Step {$currentStep['step_order']}: {$currentStep['step_name']} ({$currentStep['status']})";
        } else if ($totalSteps > 0 && $completedSteps === $totalSteps) {
            $currentStatusStr = "COMPLETED";
        } else {
            $currentStatusStr = $project['approval_status'];
        }
    } else {
        $currentStatusStr = $project['approval_status'];
    }

    $results[] = [
        'id' => $project['id'],
        'title' => $project['title'],
        'description' => $project['description'] ?? '',
        'status' => $project['approval_status'],
        'timeline_status' => $currentStatusStr,
        'activities' => array_map(function($a) {
            return [
                'step' => $a['step_order'],
                'name' => $a['step_name'],
                'status' => $a['status'],
                'planned_start' => $a['planned_start_date'],
                'planned_end' => $a['planned_end_date']
            ];
        }, $activities)
    ];
}

echo json_encode(['success' => true, 'data' => $results]);
