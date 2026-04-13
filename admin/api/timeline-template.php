<?php
/**
 * Timeline Template API
 * SDO-BACtrack
 * 
 * Returns timeline template steps for a given mode of procurement
 */

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../services/ProcurementTimelineService.php';

$estimatorMode = isset($_GET['estimator']) && $_GET['estimator'] === '1';

$auth = auth();
if (!$auth->isLoggedIn() && !$estimatorMode) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../models/TimelineTemplate.php';

$procurementType = $_GET['type'] ?? '';

if (empty($procurementType) || !array_key_exists($procurementType, PROCUREMENT_TYPES)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid mode of procurement']);
    exit;
}

$templateModel = new TimelineTemplate();
$steps = $templateModel->getByProcurementType($procurementType);

// Calculate total duration
$totalDays = 0;
foreach ($steps as $step) {
    $totalDays += $step['default_duration_days'];
}

$estimatorPayload = null;

function validateEstimatorBackwardRows(array $rows): array {
    $invalidRows = [];

    foreach ($rows as $index => $row) {
        $stageName = trim((string)($row['stage'] ?? ''));
        $startDateRaw = trim((string)($row['planned_start_date'] ?? ''));
        $endDateRaw = trim((string)($row['planned_end_date'] ?? ''));

        if ($startDateRaw === '' || $endDateRaw === '') {
            $invalidRows[] = [
                'index' => (int)$index,
                'stage' => $stageName,
                'start_date' => $startDateRaw,
                'end_date' => $endDateRaw,
                'reason' => 'missing_date',
            ];
            continue;
        }

        try {
            $startDate = new DateTimeImmutable($startDateRaw);
            $endDate = new DateTimeImmutable($endDateRaw);
        } catch (Exception $e) {
            $invalidRows[] = [
                'index' => (int)$index,
                'stage' => $stageName,
                'start_date' => $startDateRaw,
                'end_date' => $endDateRaw,
                'reason' => 'invalid_date_format',
            ];
            continue;
        }

        if ($endDate < $startDate) {
            $invalidRows[] = [
                'index' => (int)$index,
                'stage' => $stageName,
                'start_date' => $startDateRaw,
                'end_date' => $endDateRaw,
                'reason' => 'end_before_start',
            ];
        }
    }

    return [
        'is_valid' => empty($invalidRows),
        'invalid_rows' => $invalidRows,
    ];
}

if ($estimatorMode) {
    $today = new DateTimeImmutable('today');
    $implementationDate = trim((string)($_GET['implementation_date'] ?? ''));
    $startDateToday = $today->format('Y-m-d');

    $estimatorPayload = [
        'start_date_today' => $startDateToday,
        'implementation_date' => $implementationDate,
        'interval_days' => null,
        'first_stage_interval_days' => null,
        'interval_source' => 'first_stage_start_today_to_end_interval',
        'interval_end_date' => null,
        'first_stage_end_date' => null,
        'latest_allowable_implementation_date' => null,
        'backward_rows' => [],
        'invalid_transaction' => false,
        'validation_message' => '',
        'invalid_rows' => [],
    ];

    if ($implementationDate !== '') {
        try {
            $timelineService = new ProcurementTimelineService();
            $timelineRows = $timelineService->generateTimeline($implementationDate, $procurementType);

            $workflows = procurementConfig()['workflows'] ?? [];
            $workflow = $workflows[$procurementType] ?? null;
            if (!is_array($workflow)) {
                throw new RuntimeException('Workflow configuration is missing for this mode of procurement.');
            }

            $backwardStages = $workflow['backward_timeline_stages'] ?? [];
            $backwardCount = count($backwardStages);
            $backwardRows = array_slice($timelineRows, 0, $backwardCount);

            if (!empty($backwardRows)) {
                $backwardRows[0]['computed_planned_start_date'] = $backwardRows[0]['planned_start_date'];
                $backwardRows[0]['planned_start_date'] = $startDateToday;
            }

            $rowValidation = validateEstimatorBackwardRows($backwardRows);
            $hasInvalidTransaction = !$rowValidation['is_valid'];
            $validationMessage = $hasInvalidTransaction
                ? 'Transaction invalid. Please choose or set a new implementation date.'
                : '';

            $firstStageEndDate = $backwardRows[0]['planned_end_date'] ?? null;
            $implementation = new DateTimeImmutable($implementationDate);
            $firstStageIntervalDays = 0;
            $firstStageStartDate = null;

            if (!empty($backwardRows)) {
                $firstStageStartDate = $backwardRows[0]['planned_start_date'] ?? $startDateToday;
            }

            if (!empty($firstStageStartDate) && !empty($firstStageEndDate)) {
                try {
                    $firstStageStart = new DateTimeImmutable((string)$firstStageStartDate);
                    $firstStageEnd = new DateTimeImmutable((string)$firstStageEndDate);
                    $signedFirstStageIntervalDays = (int)$firstStageStart->diff($firstStageEnd)->format('%r%a');
                    $firstStageIntervalDays = max(0, $signedFirstStageIntervalDays);
                } catch (Exception $e) {
                    $firstStageIntervalDays = 0;
                }
            }

            $intervalEndDate = !empty($firstStageEndDate) ? (string)$firstStageEndDate : null;

            $latestAllowable = null;
            if (!$hasInvalidTransaction) {
                $latestAllowableDate = $implementation->modify('-' . $firstStageIntervalDays . ' days');
                $latestAllowable = $latestAllowableDate->format('Y-m-d');
            }

            $estimatorPayload = [
                'start_date_today' => $startDateToday,
                'implementation_date' => $implementationDate,
                'interval_days' => $firstStageIntervalDays,
                'first_stage_interval_days' => $firstStageIntervalDays,
                'interval_source' => 'first_stage_start_today_to_end_interval',
                'interval_end_date' => $intervalEndDate,
                'first_stage_end_date' => $firstStageEndDate,
                'latest_allowable_implementation_date' => $latestAllowable,
                'backward_rows' => $backwardRows,
                'invalid_transaction' => $hasInvalidTransaction,
                'validation_message' => $validationMessage,
                'invalid_rows' => $rowValidation['invalid_rows'],
            ];
        } catch (Exception $e) {
            $estimatorPayload['error'] = $e->getMessage();
        }
    }
}

echo json_encode([
    'procurement_type' => $procurementType,
    'procurement_type_label' => PROCUREMENT_TYPES[$procurementType],
    'total_steps' => count($steps),
    'total_days' => $totalDays,
    'steps' => $steps,
    'estimator' => $estimatorPayload,
]);
