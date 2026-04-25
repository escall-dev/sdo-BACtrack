<?php
/**
 * Analytics Page
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/OfficeAnalytics.php';

$auth = auth();
$auth->requireProcurement();

$officeAnalyticsModel = new OfficeAnalytics();
$officeDefinitions = $officeAnalyticsModel->getDefinitions();

$officeSearch = trim($_GET['office_search'] ?? '');
$selectedOffice = strtoupper(trim($_GET['office'] ?? ''));
$selectedUnitSection = trim($_GET['unit_section'] ?? '');
$statusFilter = strtoupper(trim($_GET['status'] ?? ''));
$roleFilter = strtoupper(trim($_GET['role'] ?? ''));
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$targetDays = trim($_GET['target_days'] ?? '14');
$volumeView = strtolower(trim($_GET['volume_view'] ?? 'overall'));

if (!in_array($volumeView, ['weekly', 'monthly', 'overall'], true)) {
    $volumeView = 'overall';
}

$volumeViewLabels = [
    'weekly' => 'Weekly',
    'monthly' => 'Monthly',
    'overall' => 'Overall',
];

$allowedStatusFilters = ['', 'PENDING', 'IN_PROGRESS', 'COMPLETED', 'DELAYED'];
if (!in_array($statusFilter, $allowedStatusFilters, true)) {
    $statusFilter = '';
}

$allowedRoleFilters = ['', 'BAC_SECRETARY', 'ADMIN', 'SUPERADMIN'];
if (!in_array($roleFilter, $allowedRoleFilters, true)) {
    $roleFilter = '';
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
    $dateFrom = '';
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    $dateTo = '';
}

if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
    $tmpDate = $dateFrom;
    $dateFrom = $dateTo;
    $dateTo = $tmpDate;
}

if (!ctype_digit($targetDays) || (int)$targetDays <= 0) {
    $targetDays = '14';
}

if ($selectedOffice !== '' && !isset($officeDefinitions[$selectedOffice])) {
    $selectedOffice = '';
}

if ($selectedOffice === '' && $officeSearch !== '') {
    $selectedOffice = '';
    $needle = strtoupper($officeSearch);
    foreach ($officeDefinitions as $code => $meta) {
        $haystack = strtoupper($code . ' ' . ($meta['short_name'] ?? '') . ' ' . ($meta['name'] ?? ''));
        if (strpos($haystack, $needle) !== false) {
            $selectedOffice = $code;
            break;
        }
    }
}

$analytics = $officeAnalyticsModel->getAnalytics($selectedOffice !== '' ? $selectedOffice : null);

function buildAnalyticsUrl($overrides = []) {
    $params = $_GET;
    unset($params['analytics_export']);

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }

    $query = http_build_query($params);
    return APP_URL . '/admin/analytics.php' . ($query ? '?' . $query : '');
}

function buildAnalyticsPrintUrl($overrides = []) {
    $params = $_GET;
    unset($params['analytics_export']);

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }

    $query = http_build_query($params);
    return APP_URL . '/admin/analytics-print.php' . ($query ? '?' . $query : '');
}

$projectStatusKeys = ['APPROVED', 'PENDING_APPROVAL', 'REJECTED'];
$activityStatusKeys = ['PENDING', 'IN_PROGRESS', 'COMPLETED', 'DELAYED'];

$viewName = 'All Offices (OSDS + SGOD + CID)';
$viewCode = 'ALL';

if ($selectedOffice !== '') {
    $officeData = $analytics['offices'][$selectedOffice] ?? null;

    if (!$officeData) {
        $selectedOffice = '';
    }
}

if ($selectedOffice !== '') {
    $officeData = $analytics['offices'][$selectedOffice];
    $viewName = $officeData['name'];
    $viewCode = $officeData['short_name'];

    $volume = [
        'users' => (int)$officeData['volume']['users'],
        'projects' => (int)$officeData['volume']['projects'],
        'activities' => (int)$officeData['volume']['activities'],
        'documents' => (int)$officeData['volume']['documents'],
    ];

    $statusProjects = [];
    foreach ($projectStatusKeys as $key) {
        $statusProjects[$key] = (int)($officeData['status']['projects'][$key] ?? 0);
    }

    $statusActivities = [];
    foreach ($activityStatusKeys as $key) {
        $statusActivities[$key] = (int)($officeData['status']['activities'][$key] ?? 0);
    }

    $response = [
        'completed_total' => (int)$officeData['response']['completed_total'],
        'avg_completion_days' => $officeData['response']['avg_completion_days'],
        'on_time_rate' => (float)$officeData['response']['on_time_rate'],
        'delayed_open' => (int)$officeData['response']['delayed_open'],
        'compliance_rate' => (float)$officeData['response']['compliance_rate'],
    ];

    $categories = $officeData['categories'];
    $units = $officeData['units'];
    $trends = $officeData['trends'];
    $locations = array_values(array_filter($analytics['locations'], function ($location) use ($selectedOffice) {
        return ($location['label'] ?? '') === $selectedOffice;
    }));
} else {
    $volume = [
        'users' => (int)$analytics['overall']['users'],
        'projects' => (int)$analytics['overall']['projects'],
        'activities' => (int)$analytics['overall']['activities'],
        'documents' => (int)$analytics['overall']['documents'],
    ];

    $statusProjects = array_fill_keys($projectStatusKeys, 0);
    $statusActivities = array_fill_keys($activityStatusKeys, 0);

    $weightedAvgDaysNumerator = 0;
    $weightedAvgDaysDenominator = 0;
    $weightedOnTimeNumerator = 0;
    $weightedOnTimeDenominator = 0;
    $weightedComplianceNumerator = 0;
    $weightedComplianceDenominator = 0;

    $combinedCategories = [];
    $combinedUnits = [];
    $combinedTrends = [];

    foreach ($analytics['offices'] as $officeData) {
        foreach ($projectStatusKeys as $key) {
            $statusProjects[$key] += (int)($officeData['status']['projects'][$key] ?? 0);
        }
        foreach ($activityStatusKeys as $key) {
            $statusActivities[$key] += (int)($officeData['status']['activities'][$key] ?? 0);
        }

        $completedTotal = (int)($officeData['response']['completed_total'] ?? 0);
        $avgCompletionDays = $officeData['response']['avg_completion_days'];

        if ($avgCompletionDays !== null && $completedTotal > 0) {
            $weightedAvgDaysNumerator += ((float)$avgCompletionDays * $completedTotal);
            $weightedAvgDaysDenominator += $completedTotal;
        }

        $weightedOnTimeNumerator += ((float)($officeData['response']['on_time_rate'] ?? 0) * $completedTotal);
        $weightedOnTimeDenominator += $completedTotal;

        $weightedComplianceNumerator += ((float)($officeData['response']['compliance_rate'] ?? 0) * max($completedTotal, 1));
        $weightedComplianceDenominator += max($completedTotal, 1);

        foreach ($officeData['categories'] as $category) {
            $label = $category['display_label'];
            if (!isset($combinedCategories[$label])) {
                $combinedCategories[$label] = ['display_label' => $label, 'total' => 0];
            }
            $combinedCategories[$label]['total'] += (int)$category['total'];
        }

        foreach ($officeData['units'] as $unit) {
            $unitLabel = ($officeData['short_name'] ?? '') . ' - ' . $unit['display_label'];
            if (!isset($combinedUnits[$unitLabel])) {
                $combinedUnits[$unitLabel] = ['display_label' => $unitLabel, 'total' => 0];
            }
            $combinedUnits[$unitLabel]['total'] += (int)$unit['total'];
        }

        foreach ($officeData['trends'] as $trend) {
            $monthKey = $trend['month_key'];
            if (!isset($combinedTrends[$monthKey])) {
                $combinedTrends[$monthKey] = [
                    'month_key' => $monthKey,
                    'label' => $trend['label'],
                    'projects' => 0,
                    'completed' => 0,
                    'documents' => 0,
                ];
            }
            $combinedTrends[$monthKey]['projects'] += (int)$trend['projects'];
            $combinedTrends[$monthKey]['completed'] += (int)$trend['completed'];
            $combinedTrends[$monthKey]['documents'] += (int)$trend['documents'];
        }
    }

    uasort($combinedCategories, function ($a, $b) {
        return $b['total'] <=> $a['total'];
    });

    uasort($combinedUnits, function ($a, $b) {
        return $b['total'] <=> $a['total'];
    });

    ksort($combinedTrends);

    $response = [
        'completed_total' => array_sum(array_map(function ($officeData) {
            return (int)($officeData['response']['completed_total'] ?? 0);
        }, $analytics['offices'])),
        'avg_completion_days' => $weightedAvgDaysDenominator > 0 ? round($weightedAvgDaysNumerator / $weightedAvgDaysDenominator, 1) : null,
        'on_time_rate' => $weightedOnTimeDenominator > 0 ? round($weightedOnTimeNumerator / $weightedOnTimeDenominator, 1) : 0,
        'delayed_open' => array_sum(array_map(function ($officeData) {
            return (int)($officeData['response']['delayed_open'] ?? 0);
        }, $analytics['offices'])),
        'compliance_rate' => $weightedComplianceDenominator > 0 ? round($weightedComplianceNumerator / $weightedComplianceDenominator, 1) : 0,
    ];

    $categories = array_values($combinedCategories);
    $units = array_values($combinedUnits);
    $trends = array_values($combinedTrends);
    $locations = $analytics['locations'];
}

$unitOptions = [];
$selectedUnitLabel = '';

if ($selectedOffice !== '') {
    $unitOptions = $officeDefinitions[$selectedOffice]['units'] ?? [];

    foreach ($units as $unit) {
        $label = trim((string)($unit['label'] ?? ''));
        if ($label !== '' && !isset($unitOptions[$label])) {
            $unitOptions[$label] = $unit['display_label'] ?? $label;
        }
    }

    if ($selectedUnitSection !== '' && !isset($unitOptions[$selectedUnitSection])) {
        $selectedUnitSection = '';
    }

    $unitTotalsByLabel = [];
    foreach ($units as $unit) {
        $label = trim((string)($unit['label'] ?? ''));
        if ($label === '') {
            continue;
        }
        $unitTotalsByLabel[$label] = (int)($unit['total'] ?? 0);
    }

    $normalizedUnits = [];
    foreach ($unitOptions as $label => $displayLabel) {
        $normalizedUnits[] = [
            'label' => $label,
            'display_label' => $displayLabel,
            'total' => (int)($unitTotalsByLabel[$label] ?? 0),
        ];
    }

    if ($selectedUnitSection !== '') {
        $selectedUnitLabel = $unitOptions[$selectedUnitSection] ?? '';
        $units = array_values(array_filter($normalizedUnits, function ($unit) use ($selectedUnitSection) {
            return ($unit['label'] ?? '') === $selectedUnitSection;
        }));
    } else {
        $units = $normalizedUnits;
    }
} else {
    $selectedUnitSection = '';
}

$scopeLabel = $viewName;
if ($selectedUnitLabel !== '') {
    $scopeLabel .= ' - ' . $selectedUnitLabel;
}

// Recompute volume from actual filtered records so the chart reflects current filters.
$db = db();

$volumeDateFrom = $dateFrom;
$volumeDateTo = $dateTo;
$volumeWindowLabel = '';
$hasExplicitDateRange = ($dateFrom !== '' || $dateTo !== '');

if (!$hasExplicitDateRange && $volumeView !== 'overall') {
    $today = new DateTimeImmutable('today');

    if ($volumeView === 'weekly') {
        $volumeDateFrom = $today->modify('monday this week')->format('Y-m-d');
        $volumeDateTo = $today->modify('sunday this week')->format('Y-m-d');
        $volumeWindowLabel = $today->modify('monday this week')->format('M d') . ' - ' . $today->modify('sunday this week')->format('M d, Y');
    } else {
        $volumeDateFrom = $today->modify('first day of this month')->format('Y-m-d');
        $volumeDateTo = $today->modify('last day of this month')->format('Y-m-d');
        $volumeWindowLabel = $today->format('F Y');
    }
}

$usersWhere = [];
$usersParams = [];

$projectsWhere = [];
$projectsParams = [];

$activitiesWhere = [];
$activitiesParams = [];

$projectDocsWhere = [];
$projectDocsParams = [];

$activityDocsWhere = [];
$activityDocsParams = [];

if ($selectedOffice !== '') {
    $usersWhere[] = 'u.office = ?';
    $usersParams[] = $selectedOffice;

    $projectsWhere[] = 'u.office = ?';
    $projectsParams[] = $selectedOffice;

    $activitiesWhere[] = 'u.office = ?';
    $activitiesParams[] = $selectedOffice;

    $projectDocsWhere[] = 'u.office = ?';
    $projectDocsParams[] = $selectedOffice;

    $activityDocsWhere[] = 'u.office = ?';
    $activityDocsParams[] = $selectedOffice;
}

if ($selectedUnitSection !== '') {
    $usersWhere[] = 'u.unit_section = ?';
    $usersParams[] = $selectedUnitSection;

    $projectsWhere[] = 'u.unit_section = ?';
    $projectsParams[] = $selectedUnitSection;

    $activitiesWhere[] = 'u.unit_section = ?';
    $activitiesParams[] = $selectedUnitSection;

    $projectDocsWhere[] = 'u.unit_section = ?';
    $projectDocsParams[] = $selectedUnitSection;

    $activityDocsWhere[] = 'u.unit_section = ?';
    $activityDocsParams[] = $selectedUnitSection;
}

if ($volumeDateFrom !== '') {
    $usersWhere[] = 'DATE(u.created_at) >= ?';
    $usersParams[] = $volumeDateFrom;

    $projectsWhere[] = 'DATE(p.created_at) >= ?';
    $projectsParams[] = $volumeDateFrom;

    $activitiesWhere[] = 'DATE(pa.created_at) >= ?';
    $activitiesParams[] = $volumeDateFrom;

    $projectDocsWhere[] = 'DATE(pd.uploaded_at) >= ?';
    $projectDocsParams[] = $volumeDateFrom;

    $activityDocsWhere[] = 'DATE(ad.uploaded_at) >= ?';
    $activityDocsParams[] = $volumeDateFrom;
}

if ($volumeDateTo !== '') {
    $usersWhere[] = 'DATE(u.created_at) <= ?';
    $usersParams[] = $volumeDateTo;

    $projectsWhere[] = 'DATE(p.created_at) <= ?';
    $projectsParams[] = $volumeDateTo;

    $activitiesWhere[] = 'DATE(pa.created_at) <= ?';
    $activitiesParams[] = $volumeDateTo;

    $projectDocsWhere[] = 'DATE(pd.uploaded_at) <= ?';
    $projectDocsParams[] = $volumeDateTo;

    $activityDocsWhere[] = 'DATE(ad.uploaded_at) <= ?';
    $activityDocsParams[] = $volumeDateTo;
}

if ($statusFilter !== '') {
    $activitiesWhere[] = 'pa.status = ?';
    $activitiesParams[] = $statusFilter;
}

$usersSql = 'SELECT COUNT(*) AS total FROM users u';
if (!empty($usersWhere)) {
    $usersSql .= ' WHERE ' . implode(' AND ', $usersWhere);
}

$projectsSql = 'SELECT COUNT(*) AS total FROM projects p INNER JOIN users u ON u.id = p.created_by';
if (!empty($projectsWhere)) {
    $projectsSql .= ' WHERE ' . implode(' AND ', $projectsWhere);
}

$activitiesSql = 'SELECT COUNT(*) AS total
                  FROM project_activities pa
                  INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
                  INNER JOIN projects p ON p.id = bc.project_id
                  INNER JOIN users u ON u.id = p.created_by';
if (!empty($activitiesWhere)) {
    $activitiesSql .= ' WHERE ' . implode(' AND ', $activitiesWhere);
}

$projectDocsSql = 'SELECT COUNT(*) AS total
                   FROM project_documents pd
                   INNER JOIN projects p ON p.id = pd.project_id
                   INNER JOIN users u ON u.id = p.created_by';
if (!empty($projectDocsWhere)) {
    $projectDocsSql .= ' WHERE ' . implode(' AND ', $projectDocsWhere);
}

$activityDocsSql = 'SELECT COUNT(*) AS total
                    FROM activity_documents ad
                    INNER JOIN project_activities pa ON pa.id = ad.activity_id
                    INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
                    INNER JOIN projects p ON p.id = bc.project_id
                    INNER JOIN users u ON u.id = p.created_by';
if (!empty($activityDocsWhere)) {
    $activityDocsSql .= ' WHERE ' . implode(' AND ', $activityDocsWhere);
}

$usersTotalFiltered = (int)(($db->fetch($usersSql, $usersParams) ?: [])['total'] ?? 0);
$projectsTotalFiltered = (int)(($db->fetch($projectsSql, $projectsParams) ?: [])['total'] ?? 0);
$activitiesTotalFiltered = (int)(($db->fetch($activitiesSql, $activitiesParams) ?: [])['total'] ?? 0);
$projectDocsTotalFiltered = (int)(($db->fetch($projectDocsSql, $projectDocsParams) ?: [])['total'] ?? 0);
$activityDocsTotalFiltered = (int)(($db->fetch($activityDocsSql, $activityDocsParams) ?: [])['total'] ?? 0);

$volume = [
    'users' => $usersTotalFiltered,
    'projects' => $projectsTotalFiltered,
    'activities' => $activitiesTotalFiltered,
    'documents' => $projectDocsTotalFiltered + $activityDocsTotalFiltered,
];

// At-risk activities: open longer than targetDays without completion
$atRiskWhere = [];
$atRiskParams = [];
if ($selectedOffice !== '') {
    $atRiskWhere[] = 'u.office = ?';
    $atRiskParams[] = $selectedOffice;
}
if ($selectedUnitSection !== '') {
    $atRiskWhere[] = 'u.unit_section = ?';
    $atRiskParams[] = $selectedUnitSection;
}
$atRiskWhere[] = "pa.status IN ('PENDING', 'IN_PROGRESS')";
$atRiskWhere[] = 'DATEDIFF(CURDATE(), pa.created_at) > ?';
$atRiskParams[] = (int)$targetDays;
$atRiskSql = 'SELECT COUNT(*) AS total
              FROM project_activities pa
              INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
              INNER JOIN projects p ON p.id = bc.project_id
              INNER JOIN users u ON u.id = p.created_by
              WHERE ' . implode(' AND ', $atRiskWhere);
$atRiskCount = (int)(($db->fetch($atRiskSql, $atRiskParams) ?: [])['total'] ?? 0);

// Category trends: monthly project count per procurement_type
$catTrendWhere = ['p.created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), \'%Y-%m-01\')'];
$catTrendParams = [];
if ($selectedOffice !== '') {
    $catTrendWhere[] = 'u.office = ?';
    $catTrendParams[] = $selectedOffice;
}
if ($selectedUnitSection !== '') {
    $catTrendWhere[] = 'u.unit_section = ?';
    $catTrendParams[] = $selectedUnitSection;
}
$catTrendSql = 'SELECT p.procurement_type AS cat,
                       DATE_FORMAT(p.created_at, \'%Y-%m\') AS month_key,
                       COUNT(*) AS total
                FROM projects p
                INNER JOIN users u ON u.id = p.created_by
                WHERE ' . implode(' AND ', $catTrendWhere) . '
                GROUP BY cat, month_key
                ORDER BY month_key ASC';
$catTrendRows = $db->fetchAll($catTrendSql, $catTrendParams) ?: [];

// Build 6-month scaffold
$catTrendMonths = [];
for ($mi = 5; $mi >= 0; $mi--) {
    $dt = new DateTimeImmutable("first day of -$mi month");
    $catTrendMonths[$dt->format('Y-m')] = $dt->format('M Y');
}

// Collect all category keys
$allCatKeys = array_unique(array_column($catTrendRows, 'cat'));
if (empty($allCatKeys)) {
    foreach ($categories as $cat) {
        $allCatKeys[] = $cat['label'];
    }
}

$catTrendDatasets = [];
$catTrendColors = ['#1b4a9a','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899'];
foreach ($allCatKeys as $ci => $catKey) {
    $monthly = [];
    foreach (array_keys($catTrendMonths) as $mk) {
        $monthly[$mk] = 0;
    }
    foreach ($catTrendRows as $row) {
        if ($row['cat'] === $catKey && isset($monthly[$row['month_key']])) {
            $monthly[$row['month_key']] = (int)$row['total'];
        }
    }
    $catTrendDatasets[] = [
        'label' => PROCUREMENT_TYPES[$catKey] ?? $catKey,
        'color' => $catTrendColors[$ci % count($catTrendColors)],
        'data'  => array_values($monthly),
    ];
}

$unitOptionsByOffice = [];
foreach ($officeDefinitions as $officeCode => $officeMeta) {
    $unitOptionsByOffice[$officeCode] = $officeMeta['units'] ?? [];
}

// Overdue list: DELAYED activities or activities past planned_end_date still open
$overdueListWhere = [];
$overdueListParams = [];
if ($selectedOffice !== '') {
    $overdueListWhere[] = 'u.office = ?';
    $overdueListParams[] = $selectedOffice;
}
if ($selectedUnitSection !== '') {
    $overdueListWhere[] = 'u.unit_section = ?';
    $overdueListParams[] = $selectedUnitSection;
}
$overdueListWhere[] = "(pa.status = 'DELAYED' OR (pa.status IN ('PENDING', 'IN_PROGRESS') AND pa.planned_end_date < CURDATE()))";
$overdueListSql = "SELECT
    CONCAT('PRJ-', LPAD(p.id, 5, '0')) AS reference,
    pa.step_name,
    pa.status,
    p.title AS project_title,
    DATEDIFF(CURDATE(), pa.created_at) AS days_open
FROM project_activities pa
INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
INNER JOIN projects p ON p.id = bc.project_id
INNER JOIN users u ON u.id = p.created_by
WHERE " . implode(' AND ', $overdueListWhere) . "
ORDER BY days_open DESC
LIMIT 100";
$overdueList = $db->fetchAll($overdueListSql, $overdueListParams) ?: [];

// Average days open for overdue activities
$avgFirstResponseDays = null;
if (!empty($overdueList)) {
    $totalDaysOpen = array_sum(array_column($overdueList, 'days_open'));
    $avgFirstResponseDays = round($totalDaysOpen / count($overdueList), 1);
}

$overdueStatusLabels = [
    'PENDING'     => 'Pending',
    'IN_PROGRESS' => 'In Progress',
    'DELAYED'     => 'Delayed',
    'COMPLETED'   => 'Completed',
];

$statusFilterLabels = [
    '' => 'All Statuses',
    'PENDING' => 'Pending',
    'IN_PROGRESS' => 'In Progress',
    'COMPLETED' => 'Completed',
    'DELAYED' => 'Delayed',
];

$roleFilterLabels = [
    '' => 'All Roles',
    'BAC_SECRETARY' => 'BAC Secretary',
    'ADMIN' => 'Admin',
    'SUPERADMIN' => 'Superadmin',
];

if (isset($_GET['analytics_export'])) {
    $format = strtolower(trim($_GET['analytics_export']));
    $filenameSuffix = $selectedOffice !== '' ? strtolower($selectedOffice) : 'all-offices';

    if ($selectedUnitSection !== '') {
        $unitSuffix = strtolower((string)preg_replace('/[^a-z0-9]+/i', '-', $selectedUnitSection));
        $unitSuffix = trim($unitSuffix, '-');
        if ($unitSuffix !== '') {
            $filenameSuffix .= '-' . $unitSuffix;
        }
    }

    $exportPayload = [
        'generated_at' => $analytics['generated_at'],
        'view' => [
            'code' => $viewCode,
            'name' => $viewName,
            'unit_section' => $selectedUnitSection,
            'unit_label' => $selectedUnitLabel,
        ],
        'volume_view' => $volumeView,
        'volume' => $volume,
        'status' => ['projects' => $statusProjects, 'activities' => $statusActivities],
        'response' => $response,
        'categories' => $categories,
        'units' => $units,
        'locations' => $locations,
        'trends' => $trends,
    ];

    if ($format === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="analytics-' . $filenameSuffix . '-' . date('Ymd-His') . '.json"');
        echo json_encode($exportPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="analytics-' . $filenameSuffix . '-' . date('Ymd-His') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Analytics Export']);
        fputcsv($output, ['Generated At', $analytics['generated_at']]);
        fputcsv($output, ['View', $viewName]);
        if ($selectedUnitLabel !== '') {
            fputcsv($output, ['Unit/Section', $selectedUnitLabel]);
        }
        fputcsv($output, []);

        fputcsv($output, ['Volume']);
        fputcsv($output, ['Users', 'Projects', 'Activities', 'Documents']);
        fputcsv($output, [$volume['users'], $volume['projects'], $volume['activities'], $volume['documents']]);
        fputcsv($output, []);

        fputcsv($output, ['Project Status']);
        fputcsv($output, ['Approved', 'Pending Review', 'Disapproved']);
        fputcsv($output, [
            $statusProjects['APPROVED'],
            $statusProjects['PENDING_APPROVAL'],
            $statusProjects['REJECTED'],
        ]);
        fputcsv($output, []);

        fputcsv($output, ['Activity Status']);
        fputcsv($output, ['Pending', 'In Progress', 'Completed', 'Delayed']);
        fputcsv($output, [
            $statusActivities['PENDING'],
            $statusActivities['IN_PROGRESS'],
            $statusActivities['COMPLETED'],
            $statusActivities['DELAYED'],
        ]);
        fputcsv($output, []);

        fputcsv($output, ['Response']);
        fputcsv($output, ['Completed Activities', 'Average Completion Days', 'On-Time Rate (%)', 'Delayed Open', 'Compliance Rate (%)']);
        fputcsv($output, [
            $response['completed_total'],
            $response['avg_completion_days'] === null ? 'N/A' : $response['avg_completion_days'],
            $response['on_time_rate'],
            $response['delayed_open'],
            $response['compliance_rate'],
        ]);
        fputcsv($output, []);

        fputcsv($output, ['Categories']);
        fputcsv($output, ['Category', 'Total']);
        foreach ($categories as $category) {
            fputcsv($output, [$category['display_label'], $category['total']]);
        }
        fputcsv($output, []);

        fputcsv($output, ['Units']);
        fputcsv($output, ['Unit/Section', 'Total']);
        foreach ($units as $unit) {
            fputcsv($output, [$unit['display_label'], $unit['total']]);
        }
        fputcsv($output, []);

        fputcsv($output, ['Locations']);
        fputcsv($output, ['Location', 'Total']);
        foreach ($locations as $location) {
            fputcsv($output, [$location['display_label'], $location['total']]);
        }
        fputcsv($output, []);

        fputcsv($output, ['Trends']);
        fputcsv($output, ['Month', 'Projects', 'Completed Activities', 'Documents']);
        foreach ($trends as $trend) {
            fputcsv($output, [$trend['label'], $trend['projects'], $trend['completed'], $trend['documents']]);
        }

        fclose($output);
        exit;
    }
}

$projectStatusLabels = [
    'APPROVED' => 'Approved',
    'PENDING_APPROVAL' => 'Pending Review',
    'REJECTED' => 'Disapproved',
];

$activityStatusLabels = [
    'PENDING' => 'Pending',
    'IN_PROGRESS' => 'In Progress',
    'COMPLETED' => 'Completed',
    'DELAYED' => 'Delayed',
];

$statusColors = [
    'APPROVED' => 'var(--success)',
    'PENDING_APPROVAL' => 'var(--warning)',
    'REJECTED' => 'var(--danger)',
    'PENDING' => 'var(--warning)',
    'IN_PROGRESS' => 'var(--info)',
    'COMPLETED' => 'var(--success)',
    'DELAYED' => 'var(--danger)',
];

$categoryMax = 1;
foreach ($categories as $category) {
    $categoryMax = max($categoryMax, (int)$category['total']);
}

$unitMax = 1;
foreach ($units as $unit) {
    $unitMax = max($unitMax, (int)$unit['total']);
}

// Per-unit metrics: completion rate and avg handling days
$unitMetricsWhere = ["u.unit_section IS NOT NULL", "u.unit_section != ''"];
$unitMetricsParams = [];
if ($selectedOffice !== '') {
    $unitMetricsWhere[] = 'u.office = ?';
    $unitMetricsParams[] = $selectedOffice;
} else {
    $unitMetricsWhere[] = "u.office IN ('OSDS', 'SGOD', 'CID')";
}
if ($selectedUnitSection !== '') {
    $unitMetricsWhere[] = 'u.unit_section = ?';
    $unitMetricsParams[] = $selectedUnitSection;
}
if ($volumeDateFrom !== '') {
    $unitMetricsWhere[] = 'DATE(p.created_at) >= ?';
    $unitMetricsParams[] = $volumeDateFrom;
}
if ($volumeDateTo !== '') {
    $unitMetricsWhere[] = 'DATE(p.created_at) <= ?';
    $unitMetricsParams[] = $volumeDateTo;
}
$unitMetricsRows = $db->fetchAll(
    "SELECT
        u.unit_section AS unit_label,
        COUNT(DISTINCT p.id) AS project_count,
        COUNT(pa.id) AS activity_total,
        SUM(CASE WHEN pa.status = 'COMPLETED' THEN 1 ELSE 0 END) AS activity_completed,
        AVG(CASE WHEN pa.actual_completion_date IS NOT NULL THEN DATEDIFF(pa.actual_completion_date, pa.planned_start_date) END) AS avg_days
     FROM users u
     LEFT JOIN projects p ON p.created_by = u.id
     LEFT JOIN bac_cycles bc ON bc.project_id = p.id
     LEFT JOIN project_activities pa ON pa.bac_cycle_id = bc.id
     WHERE " . implode(' AND ', $unitMetricsWhere) . "
     GROUP BY u.unit_section
     ORDER BY project_count DESC",
    $unitMetricsParams
);
$unitMetricsMap = [];
foreach ($unitMetricsRows as $row) {
    $unitMetricsMap[$row['unit_label']] = [
        'activity_total'     => (int)$row['activity_total'],
        'activity_completed' => (int)$row['activity_completed'],
        'completion_rate'    => (int)$row['activity_total'] > 0
            ? round(((int)$row['activity_completed'] / (int)$row['activity_total']) * 100, 1)
            : 0,
        'avg_days' => $row['avg_days'] !== null ? round((float)$row['avg_days'], 1) : null,
    ];
}

$locationMax = 1;
foreach ($locations as $location) {
    $locationMax = max($locationMax, (int)$location['total']);
}

// Per-location (office) metrics: completion rate and avg handling days
$locationMetricsWhere = ["u.office IN ('OSDS', 'SGOD', 'CID')"];
$locationMetricsParams = [];
if ($volumeDateFrom !== '') {
    $locationMetricsWhere[] = 'DATE(p.created_at) >= ?';
    $locationMetricsParams[] = $volumeDateFrom;
}
if ($volumeDateTo !== '') {
    $locationMetricsWhere[] = 'DATE(p.created_at) <= ?';
    $locationMetricsParams[] = $volumeDateTo;
}
$locationMetricsRows = $db->fetchAll(
    "SELECT
        u.office AS office_label,
        COUNT(DISTINCT p.id) AS project_count,
        COUNT(pa.id) AS activity_total,
        SUM(CASE WHEN pa.status = 'COMPLETED' THEN 1 ELSE 0 END) AS activity_completed,
        AVG(CASE WHEN pa.actual_completion_date IS NOT NULL THEN DATEDIFF(pa.actual_completion_date, pa.planned_start_date) END) AS avg_days
     FROM users u
     LEFT JOIN projects p ON p.created_by = u.id
     LEFT JOIN bac_cycles bc ON bc.project_id = p.id
     LEFT JOIN project_activities pa ON pa.bac_cycle_id = bc.id
     WHERE " . implode(' AND ', $locationMetricsWhere) . "
     GROUP BY u.office",
    $locationMetricsParams
);
$locationMetricsMap = [];
foreach ($locationMetricsRows as $row) {
    $locationMetricsMap[$row['office_label']] = [
        'activity_total'     => (int)$row['activity_total'],
        'activity_completed' => (int)$row['activity_completed'],
        'completion_rate'    => (int)$row['activity_total'] > 0
            ? round(((int)$row['activity_completed'] / (int)$row['activity_total']) * 100, 1)
            : 0,
        'avg_days' => $row['avg_days'] !== null ? round((float)$row['avg_days'], 1) : null,
    ];
}

$trendMax = 1;
foreach ($trends as $trend) {
    $trendMax = max($trendMax, (int)$trend['projects'], (int)$trend['completed'], (int)$trend['documents']);
}

$volumeChartData = [
    ['key' => 'users', 'label' => 'Users', 'color' => '#1b4a9a'],
    ['key' => 'projects', 'label' => 'Projects', 'color' => '#0ea5e9'],
    ['key' => 'activities', 'label' => 'Activities', 'color' => '#14b8a6'],
    ['key' => 'documents', 'label' => 'Documents', 'color' => '#ef4444'],
];

$volumeMax = 1;
foreach ($volumeChartData as $point) {
    $volumeMax = max($volumeMax, (int)($volume[$point['key']] ?? 0));
}

foreach ($volumeChartData as &$point) {
    $value = (int)($volume[$point['key']] ?? 0);
    $point['value'] = $value;
    $point['percent'] = $value > 0 ? max(8, round(($value / $volumeMax) * 100, 1)) : 0;
}
unset($point);

$volumeAxisTop = (int)$volumeMax;
$volumeAxisMid = (int)max(1, round($volumeMax / 2));
$volumeAxisBottom = 0;

// Per-status average days computation (no status filter applied, always shows all statuses)
$statusAvgWhere = [];
$statusAvgParams = [];
if ($selectedOffice !== '') {
    $statusAvgWhere[] = 'u.office = ?';
    $statusAvgParams[] = $selectedOffice;
}
if ($selectedUnitSection !== '') {
    $statusAvgWhere[] = 'u.unit_section = ?';
    $statusAvgParams[] = $selectedUnitSection;
}
if ($volumeDateFrom !== '') {
    $statusAvgWhere[] = 'DATE(pa.created_at) >= ?';
    $statusAvgParams[] = $volumeDateFrom;
}
if ($volumeDateTo !== '') {
    $statusAvgWhere[] = 'DATE(pa.created_at) <= ?';
    $statusAvgParams[] = $volumeDateTo;
}
$statusAvgSql = 'SELECT pa.status,
                        ROUND(AVG(TIMESTAMPDIFF(DAY, pa.created_at, COALESCE(CAST(pa.actual_completion_date AS DATETIME), NOW()))), 1) AS avg_days
                 FROM project_activities pa
                 INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
                 INNER JOIN projects p ON p.id = bc.project_id
                 INNER JOIN users u ON u.id = p.created_by';
if (!empty($statusAvgWhere)) {
    $statusAvgSql .= ' WHERE ' . implode(' AND ', $statusAvgWhere);
}
$statusAvgSql .= ' GROUP BY pa.status';
$statusAvgRaw = $db->fetchAll($statusAvgSql, $statusAvgParams) ?: [];
$statusAvgDays = array_fill_keys(array_keys($activityStatusLabels), null);
foreach ($statusAvgRaw as $row) {
    if (array_key_exists($row['status'], $statusAvgDays)) {
        $statusAvgDays[$row['status']] = $row['avg_days'] !== null ? (float)$row['avg_days'] : null;
    }
}

$statusBarColors = [
    'PENDING'     => '#f59e0b',
    'IN_PROGRESS' => '#1b4a9a',
    'COMPLETED'   => '#10b981',
    'DELAYED'     => '#ef4444',
];
$statusBarShadows = [
    'PENDING'     => 'rgba(245,158,11,.28)',
    'IN_PROGRESS' => 'rgba(59,130,246,.28)',
    'COMPLETED'   => 'rgba(16,185,129,.28)',
    'DELAYED'     => 'rgba(239,68,68,.28)',
];
$statusBarMax = max(1, max(array_values($statusActivities)));
$statusChartData = [];
foreach ($activityStatusLabels as $key => $label) {
    $value = (int)($statusActivities[$key] ?? 0);
    $statusChartData[] = [
        'key'     => $key,
        'label'   => $label,
        'value'   => $value,
        'percent' => $value > 0 ? max(6, round(($value / $statusBarMax) * 100, 1)) : 0,
        'color'   => $statusBarColors[$key] ?? '#10b981',
        'shadow'  => $statusBarShadows[$key] ?? 'rgba(16,185,129,.28)',
    ];
}
$statusAxisTop = (int)$statusBarMax;
$statusAxisMid = (int)max(1, round($statusBarMax / 2));

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <p style="color: var(--text-muted); margin: 4px 0 0;">Unified statistics dashboard for OSDS, SGOD, and CID.</p>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <a href="<?php echo htmlspecialchars(buildAnalyticsUrl(['analytics_export' => 'csv'])); ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-file-csv"></i> CSV
        </a>
        <a href="<?php echo htmlspecialchars(buildAnalyticsPrintUrl(['autoprint' => 1])); ?>" class="btn btn-secondary btn-sm" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="<?php echo htmlspecialchars(buildAnalyticsPrintUrl(['autoprint' => 1])); ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-print"></i> Print
        </a>
    </div>
</div>

<div class="dashboard-card" style="overflow: visible;">
    <div class="card-body" style="padding: 24px; overflow: visible;">
        <style>
        .analytics-tabs {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            padding: 10px 24px 12px;
            overflow-x: auto;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 18px;
            position: sticky;
            top: var(--topbar-height, 64px);
            z-index: 40;
            background: var(--card-bg, #fff);
            margin-left: -24px;
            margin-right: -24px;
        }

        .analytics-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 14px;
            border-radius: 9px;
            text-decoration: none;
            font-weight: 600;
            color: var(--text-secondary);
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            white-space: nowrap;
        }

        .analytics-tab.is-active {
            color: #fff;
            background: var(--primary);
            border-color: var(--primary);
        }

        #volume-section, #status-section, #response-section,
        #categories-section, #units-section, #locations-section,
        #trends-section, #exports-section {
            scroll-margin-top: calc(var(--topbar-height, 64px) + 58px) !important;
        }

        .volume-card-shell {
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            padding: 16px;
        }

        .volume-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .volume-card-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .volume-card-subtitle {
            color: #64748b;
            font-size: 0.82rem;
            margin-left: 4px;
        }

        .volume-mode-switch {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-left: 10px;
        }

        .volume-mode-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #dbe3ee;
            color: #475569;
            background: #f8fafc;
            text-decoration: none;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .volume-mode-link.is-active {
            background: #1b4a9a;
            border-color: #1b4a9a;
            color: #ffffff;
        }

        .volume-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .volume-legend-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #dbe3ee;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 0.75rem;
            color: #334155;
            background: #f8fafc;
        }

        .volume-legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .volume-chart {
            position: relative;
            height: 360px;
            border: 1px solid #c9d4e4;
            border-radius: var(--radius-md);
            background: linear-gradient(180deg, #f8fbff 0%, #f2f7fe 100%);
            padding: 14px 14px 12px;
            overflow: hidden;
        }

        .volume-y-axis {
            position: absolute;
            left: 8px;
            top: 18px;
            bottom: 58px;
            width: 46px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
            color: #64748b;
            font-size: 0.74rem;
            font-weight: 600;
            pointer-events: none;
        }

        .volume-chart::before {
            content: '';
            position: absolute;
            left: 60px;
            right: 16px;
            top: 20px;
            bottom: 56px;
            border-left: 1px solid rgba(71, 85, 105, 0.35);
            border-bottom: 1px solid rgba(71, 85, 105, 0.35);
            background: repeating-linear-gradient(
                to top,
                rgba(100, 116, 139, 0.18) 0,
                rgba(100, 116, 139, 0.18) 1px,
                transparent 1px,
                transparent 24%
            );
            pointer-events: none;
        }

        .volume-bars {
            position: absolute;
            left: 68px;
            right: 16px;
            top: 22px;
            bottom: 14px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .volume-bar-item {
            display: grid;
            grid-template-rows: auto 1fr auto;
            align-items: end;
            justify-items: center;
            min-width: 0;
        }

        .volume-bar-value {
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
            font-size: 0.95rem;
            background: #ffffff;
            border: 1px solid #d8e2ef;
            border-radius: 999px;
            padding: 3px 10px;
        }

        .volume-bar-track {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .volume-bar-fill {
            width: min(76px, 82%);
            min-height: 2px;
            height: calc(var(--bar-height, 0) * 1%);
            border-radius: 10px 10px 5px 5px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.35) 0%, var(--bar-color) 18%, var(--bar-color) 100%);
            box-shadow: 0 4px 14px rgba(30, 41, 59, 0.2);
            transform-origin: center bottom;
            transform: scaleY(0);
            transition: transform 420ms ease-out;
        }

        .volume-chart.is-ready .volume-bar-fill {
            transform: scaleY(1);
        }

        .volume-bar-label {
            margin-top: 10px;
            font-size: 0.8rem;
            color: #334155;
            font-weight: 600;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        .status-chart-wrap {
            position: relative;
            height: 260px;
            border: 1px solid #c9d4e4;
            border-radius: var(--radius-md);
            background: linear-gradient(180deg, #f8fbff 0%, #f2f7fe 100%);
            padding: 14px 14px 12px;
            overflow: hidden;
        }

        .status-chart-wrap::before {
            content: '';
            position: absolute;
            left: 54px;
            right: 14px;
            top: 20px;
            bottom: 50px;
            border-left: 1px solid rgba(71,85,105,.35);
            border-bottom: 1px solid rgba(71,85,105,.35);
            background: repeating-linear-gradient(
                to top,
                rgba(100,116,139,.18) 0, rgba(100,116,139,.18) 1px,
                transparent 1px, transparent 25%
            );
            pointer-events: none;
        }

        .status-y-axis {
            position: absolute;
            left: 6px;
            top: 18px;
            bottom: 52px;
            width: 42px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
            color: #64748b;
            font-size: .74rem;
            font-weight: 600;
            pointer-events: none;
        }

        .status-bars {
            position: absolute;
            left: 62px;
            right: 14px;
            top: 22px;
            bottom: 12px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .status-bar-item {
            display: grid;
            grid-template-rows: auto 1fr auto;
            align-items: end;
            justify-items: center;
            min-width: 0;
        }

        .status-bar-value {
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 5px;
            font-size: .88rem;
            background: #fff;
            border: 1px solid #d8e2ef;
            border-radius: 999px;
            padding: 2px 10px;
        }

        .status-bar-track {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .status-bar-fill {
            width: min(70px, 80%);
            min-height: 2px;
            height: calc(var(--bar-height, 0) * 1%);
            border-radius: 10px 10px 5px 5px;
            background: linear-gradient(180deg, rgba(255,255,255,.35) 0%, var(--bar-color, #10b981) 18%, var(--bar-color, #10b981) 100%);
            box-shadow: 0 4px 12px var(--bar-shadow, rgba(16,185,129,.28));
            transform-origin: center bottom;
            transform: scaleY(0);
            transition: transform 420ms ease-out;
        }

        .status-bar-fill.is-ready {
            transform: scaleY(1);
        }

        .status-bar-label {
            margin-top: 8px;
            font-size: .78rem;
            color: #334155;
            font-weight: 600;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        .status-avg-table {
            width: 100%;
            border-collapse: collapse;
        }

        .status-avg-table th {
            text-align: left;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 6px 10px 10px;
            border-bottom: 2px solid #cbd5e1;
        }

        .status-avg-table th:last-child {
            text-align: right;
            color: var(--primary);
        }

        .status-avg-table td {
            padding: 13px 10px;
            font-size: 0.9rem;
            color: #1e293b;
            border-bottom: 1px solid #e2e8f0;
        }

        .status-avg-table td:last-child {
            text-align: right;
            font-weight: 600;
            color: #475569;
        }

        .status-avg-table tr:last-child td {
            border-bottom: none;
        }
        </style>

        <nav class="analytics-tabs" id="analyticsTabs" aria-label="Analytics sections">
            <a href="#volume-section" class="analytics-tab is-active" data-tab-target="volume-section">Volume</a>
            <a href="#status-section" class="analytics-tab" data-tab-target="status-section">Status</a>
            <a href="#response-section" class="analytics-tab" data-tab-target="response-section">Response</a>
            <a href="#categories-section" class="analytics-tab" data-tab-target="categories-section">Categories</a>
            <a href="#units-section" class="analytics-tab" data-tab-target="units-section">Units</a>
            <a href="#locations-section" class="analytics-tab" data-tab-target="locations-section">Locations</a>
            <a href="#trends-section" class="analytics-tab" data-tab-target="trends-section">Trends</a>
            <a href="#exports-section" class="analytics-tab" data-tab-target="exports-section">Exports</a>
        </nav>

        <form method="GET" id="analyticsFilterForm" style="display: grid; grid-template-columns: minmax(150px, 1fr) minmax(170px, 1fr) minmax(150px, 1fr) minmax(150px, 1fr) minmax(150px, 1fr) minmax(150px, 1fr) minmax(120px, 0.9fr) auto; gap: 14px; align-items: end; margin-bottom: 18px; padding: 14px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-secondary);">
            <input type="hidden" name="volume_view" value="<?php echo htmlspecialchars($volumeView); ?>">
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Office</label>
                <select name="office" class="form-control" id="departmentSelect">
                    <option value="">All Offices</option>
                    <?php foreach ($officeDefinitions as $code => $meta): ?>
                    <option value="<?php echo htmlspecialchars($code); ?>" <?php echo $selectedOffice === $code ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($meta['short_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label">Assigned Unit</label>
                <select name="unit_section" class="form-control" id="unitSectionSelect">
                    <option value="">All Units</option>
                    <?php foreach ($unitOptions as $unitCode => $unitLabel): ?>
                    <option value="<?php echo htmlspecialchars($unitCode); ?>" <?php echo $selectedUnitSection === $unitCode ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($unitLabel); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label">Role</label>
                <select name="role" class="form-control">
                    <?php foreach ($roleFilterLabels as $roleCode => $roleLabel): ?>
                    <option value="<?php echo htmlspecialchars($roleCode); ?>" <?php echo $roleFilter === $roleCode ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($roleLabel); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <?php foreach ($statusFilterLabels as $statusCode => $statusLabel): ?>
                    <option value="<?php echo htmlspecialchars($statusCode); ?>" <?php echo $statusFilter === $statusCode ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($statusLabel); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label">Target Days</label>
                <input type="number" min="1" name="target_days" class="form-control" value="<?php echo htmlspecialchars($targetDays); ?>">
            </div>

            <div style="display: flex; justify-content: flex-end; align-items: end; gap: 8px; margin: 0;">
                <a href="<?php echo APP_URL; ?>/admin/analytics.php" class="btn btn-secondary">Reset</a>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>

        <div id="volume-section" style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; margin-bottom: 20px; scroll-margin-top: 80px;">
            <div class="volume-card-shell">
                <div class="volume-card-header">
                    <div class="volume-card-title">
                        <i class="fas fa-chart-column" style="color: #1b4a9a;"></i>
                        Volume Analysis
                        <?php if ($volumeWindowLabel !== ''): ?>
                        <span class="volume-card-subtitle">&mdash; <?php echo htmlspecialchars($volumeWindowLabel); ?></span>
                        <?php endif; ?>
                        <div class="volume-mode-switch">
                            <a href="<?php echo htmlspecialchars(buildAnalyticsUrl(['volume_view' => 'weekly'])); ?>" class="volume-mode-link <?php echo $volumeView === 'weekly' ? 'is-active' : ''; ?>">Weekly</a>
                            <a href="<?php echo htmlspecialchars(buildAnalyticsUrl(['volume_view' => 'monthly'])); ?>" class="volume-mode-link <?php echo $volumeView === 'monthly' ? 'is-active' : ''; ?>">Monthly</a>
                            <a href="<?php echo htmlspecialchars(buildAnalyticsUrl(['volume_view' => 'overall'])); ?>" class="volume-mode-link <?php echo $volumeView === 'overall' ? 'is-active' : ''; ?>">Overall</a>
                        </div>
                    </div>
                    <div class="volume-legend">
                        <?php foreach ($volumeChartData as $point): ?>
                        <span class="volume-legend-chip">
                            <span class="volume-legend-dot" style="background: <?php echo htmlspecialchars($point['color']); ?>;"></span>
                            <?php echo htmlspecialchars($point['label']); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="volume-chart" id="volumeChart">
                    <div class="volume-y-axis">
                        <span><?php echo (int)$volumeAxisTop; ?></span>
                        <span><?php echo (int)$volumeAxisMid; ?></span>
                        <span><?php echo (int)$volumeAxisBottom; ?></span>
                    </div>

                    <div class="volume-bars">
                        <?php foreach ($volumeChartData as $point): ?>
                        <div class="volume-bar-item">
                            <span class="volume-bar-value"><?php echo (int)$point['value']; ?></span>
                            <div class="volume-bar-track">
                                <div
                                    class="volume-bar-fill"
                                    style="--bar-height: <?php echo htmlspecialchars((string)$point['percent']); ?>; --bar-color: <?php echo htmlspecialchars($point['color']); ?>;"
                                    title="<?php echo htmlspecialchars($point['label'] . ': ' . $point['value']); ?>"
                                ></div>
                            </div>
                            <span class="volume-bar-label"><?php echo htmlspecialchars($point['label']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="status-section" style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; margin-bottom: 20px; scroll-margin-top: 80px;">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-list-check" style="color: var(--primary);"></i> Status Tracking
            </h3>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 18px; align-items: start;">

                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: #1e293b; margin-bottom: 12px;">Status Totals</div>
                    <div class="status-chart-wrap">
                        <div class="status-y-axis">
                            <span><?php echo $statusAxisTop; ?></span>
                            <span><?php echo $statusAxisMid; ?></span>
                            <span>0</span>
                        </div>
                        <div class="status-bars">
                            <?php foreach ($statusChartData as $point): ?>
                            <div class="status-bar-item">
                                <span class="status-bar-value"><?php echo $point['value']; ?></span>
                                <div class="status-bar-track">
                                    <div
                                        class="status-bar-fill"
                                        style="--bar-height: <?php echo htmlspecialchars((string)$point['percent']); ?>; --bar-color: <?php echo htmlspecialchars($point['color']); ?>; --bar-shadow: <?php echo htmlspecialchars($point['shadow']); ?>;"
                                        title="<?php echo htmlspecialchars($point['label'] . ': ' . $point['value']); ?>"
                                    ></div>
                                </div>
                                <span class="status-bar-label"><?php echo htmlspecialchars($point['label']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: #1e293b; margin-bottom: 12px;">Average Time to Status (days)</div>
                    <table class="status-avg-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Average Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activityStatusLabels as $key => $label): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($label); ?></td>
                                <td><?php
                                    $avg = $statusAvgDays[$key] ?? null;
                                    echo $avg !== null ? htmlspecialchars(number_format($avg, 1)) . ' days' : 'N/A';
                                ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <div id="response-section" style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; margin-bottom: 20px; scroll-margin-top: 80px;">
            <style>
            .response-metric-cards {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
                margin-bottom: 24px;
            }
            @media (max-width: 700px) {
                .response-metric-cards { grid-template-columns: 1fr; }
            }
            .response-metric-card {
                background: #f1f5f9;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 20px 22px;
            }
            .response-metric-card.is-overdue {
                background: #fff1f2;
                border-color: #fca5a5;
            }
            .response-metric-card .rmc-label {
                font-size: 0.82rem;
                color: #64748b;
                margin-bottom: 6px;
            }
            .response-metric-card.is-overdue .rmc-label {
                color: #b91c1c;
            }
            .response-metric-card .rmc-value {
                font-size: 1.75rem;
                font-weight: 800;
                color: #0f172a;
                line-height: 1.1;
            }
            .response-metric-card.is-overdue .rmc-value {
                color: #b91c1c;
            }
            .overdue-list-table {
                width: 100%;
                border-collapse: collapse;
            }
            .overdue-list-table thead tr {
                background: #1e3a5f;
            }
            .overdue-list-table th {
                text-align: left;
                font-size: 0.72rem;
                font-weight: 700;
                color: #ffffff;
                text-transform: uppercase;
                letter-spacing: .07em;
                padding: 11px 16px;
                border-bottom: none;
            }
            .overdue-list-table td {
                padding: 12px 16px;
                font-size: 0.875rem;
                color: #1e293b;
                border-bottom: none;
                border-top: 6px solid #fff;
            }
            .overdue-list-table tbody tr:nth-child(odd) {
                background: #f0f6ff;
            }
            .overdue-list-table tbody tr:nth-child(even) {
                background: #ffffff;
            }
            .overdue-list-table tbody tr:hover {
            }
            .overdue-list-table tbody tr.is-highlighted {
                background: #f0f6ff;
            }
            .overdue-status-badge {
                display: inline-block;
                padding: 3px 10px;
                border-radius: 999px;
                font-size: 0.75rem;
                font-weight: 600;
            }
            .overdue-status-badge.status-pending     { background: #fef3c7; color: #92400e; }
            .overdue-status-badge.status-in_progress { background: #eff6ff; color: #0f2d5c; }
            .overdue-status-badge.status-delayed     { background: #fee2e2; color: #991b1b; }
            .overdue-status-badge.status-completed   { background: #d1fae5; color: #065f46; }
            </style>

            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-clock" style="color: var(--primary);"></i> Response
            </h3>

            <div class="response-metric-cards">
                <div class="response-metric-card">
                    <div class="rmc-label">Average Days Open (Overdue)</div>
                    <div class="rmc-value"><?php echo $avgFirstResponseDays !== null ? htmlspecialchars(number_format($avgFirstResponseDays, 1)) . ' days' : 'N/A'; ?></div>
                </div>
                <div class="response-metric-card">
                    <div class="rmc-label">Average Resolution</div>
                    <div class="rmc-value"><?php echo $response['avg_completion_days'] !== null ? htmlspecialchars((string)$response['avg_completion_days']) . ' days' : 'N/A'; ?></div>
                </div>
                <div class="response-metric-card is-overdue">
                    <div class="rmc-label">Overdue Process</div>
                    <div class="rmc-value"><?php echo count($overdueList); ?></div>
                </div>
            </div>

            <div style="font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: 12px;">Overdue List</div>
            <?php if (empty($overdueList)): ?>
            <p style="color: var(--text-muted); font-size: 0.9rem;">No overdue process found.</p>
            <?php else: ?>
            <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                <table class="overdue-list-table" id="overdueListTable">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Days Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($overdueList as $i => $row): ?>
                        <?php
                        $statusKey = strtolower($row['status']);
                        $statusLabel = $overdueStatusLabels[$row['status']] ?? $row['status'];
                        ?>
                        <tr class="overdue-row<?php echo $i % 2 === 0 ? ' is-highlighted' : ''; ?>" style="display: <?php echo $i < 10 ? 'table-row' : 'none'; ?>;">
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($row['project_title']); ?></div>
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;"><?php echo htmlspecialchars($row['step_name']); ?></div>
                            </td>
                            <td>
                                <span class="overdue-status-badge status-<?php echo htmlspecialchars($statusKey); ?>">
                                    <?php echo htmlspecialchars($statusLabel); ?>
                                </span>
                            </td>
                            <td style="font-weight: 600;"><?php echo (int)$row['days_open']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (count($overdueList) > 10): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
                <span id="overduePageInfo" style="font-size: 0.82rem; color: #64748b;"></span>
                <div style="display: flex; gap: 8px;">
                    <button type="button" id="overduePrevBtn" class="btn btn-secondary btn-sm" style="display: none;" onclick="changeOverduePage(-1)">
                        <i class="fas fa-chevron-left"></i> Prev
                    </button>
                    <button type="button" id="overdueNextBtn" class="btn btn-primary btn-sm" onclick="changeOverduePage(1)">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <script>
            (function () {
                var rows = document.querySelectorAll('#overdueListTable tbody .overdue-row');
                var total = rows.length;
                var pageSize = 10;
                var currentPage = 0;

                function render() {
                    var start = currentPage * pageSize;
                    var end = start + pageSize;
                    rows.forEach(function (row, i) {
                        row.style.display = (i >= start && i < end) ? 'table-row' : 'none';
                        // reset stripe for current page
                        row.classList.toggle('is-highlighted', (i - start) % 2 === 0 && i >= start && i < end);
                    });
                    var totalPages = Math.ceil(total / pageSize);
                    document.getElementById('overduePageInfo').textContent =
                        'Page ' + (currentPage + 1) + ' of ' + totalPages + ' (' + total + ' total)';
                    document.getElementById('overduePrevBtn').style.display = currentPage > 0 ? '' : 'none';
                    document.getElementById('overdueNextBtn').style.display = currentPage < totalPages - 1 ? '' : 'none';
                }

                window.changeOverduePage = function (dir) {
                    var totalPages = Math.ceil(total / pageSize);
                    currentPage = Math.max(0, Math.min(totalPages - 1, currentPage + dir));
                    render();
                };

                render();
            })();
            </script>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <div id="categories-section" style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; margin-bottom: 20px;">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-tags" style="color: var(--primary);"></i> Categories
            </h3>
            <?php if (empty($categories)): ?>
            <p style="color: var(--text-muted);">No category data available yet.</p>
            <?php else: ?>
            <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 24px; align-items: start;">

                <!-- Donut chart -->
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:18px;">
                    <div style="font-size:0.85rem; font-weight:700; color:#1e293b; margin-bottom:14px;">Distribution by Category</div>
                    <div style="display:flex; justify-content:center;">
                        <canvas id="categoryDonutChart" width="220" height="220"></canvas>
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; justify-content:center; margin-top:14px;" id="categoryDonutLegend"></div>
                </div>

                <!-- Line chart -->
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:18px;">
                    <div style="font-size:0.85rem; font-weight:700; color:#1e293b; margin-bottom:14px;">Category Trends Over Time</div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;" id="categoryLineLegend"></div>
                    <canvas id="categoryLineChart" height="200"></canvas>
                </div>

            </div>
            <script>
            (function () {
                var donutCtx = document.getElementById('categoryDonutChart');
                var lineCtx  = document.getElementById('categoryLineChart');
                if (!donutCtx || !lineCtx) return;

                var donutData  = <?php echo json_encode(array_values(array_map(function($c) { return (int)$c['total']; }, $categories))); ?>;
                var donutLabels = <?php echo json_encode(array_values(array_map(function($c) { return $c['display_label']; }, $categories))); ?>;
                var colors     = <?php echo json_encode(array_values(array_map(function($d) { return $d['color']; }, $catTrendDatasets))); ?>;

                var monthLabels = <?php echo json_encode(array_values($catTrendMonths)); ?>;
                var datasets   = <?php echo json_encode($catTrendDatasets); ?>;

                // Legend helper
                function makeLegend(containerId, labels, cols) {
                    var el = document.getElementById(containerId);
                    if (!el) return;
                    labels.forEach(function (label, i) {
                        var chip = document.createElement('span');
                        chip.style.cssText = 'display:inline-flex;align-items:center;gap:6px;font-size:0.75rem;color:#334155;';
                        var dot = document.createElement('span');
                        dot.style.cssText = 'width:10px;height:10px;border-radius:50%;background:' + cols[i] + ';display:inline-block;flex-shrink:0;';
                        chip.appendChild(dot);
                        chip.appendChild(document.createTextNode(label));
                        el.appendChild(chip);
                    });
                }

                makeLegend('categoryDonutLegend', donutLabels, colors);
                makeLegend('categoryLineLegend', datasets.map(function(d){return d.label;}), datasets.map(function(d){return d.color;}));

                // ---- Donut chart ----
                var dCtx = donutCtx.getContext('2d');
                var dW = donutCtx.width, dH = donutCtx.height;
                var dCx = dW / 2, dCy = dH / 2;
                var outerR = Math.min(dW, dH) / 2 - 4;
                var innerR = outerR * 0.55;
                var total  = donutData.reduce(function(a,b){return a+b;}, 0) || 1;
                var startAngle = -Math.PI / 2;

                donutData.forEach(function (val, i) {
                    var sweep = (val / total) * 2 * Math.PI;
                    dCtx.beginPath();
                    dCtx.moveTo(dCx, dCy);
                    dCtx.arc(dCx, dCy, outerR, startAngle, startAngle + sweep);
                    dCtx.closePath();
                    dCtx.fillStyle = colors[i] || '#cbd5e1';
                    dCtx.fill();
                    startAngle += sweep;
                });

                // cut inner circle
                dCtx.beginPath();
                dCtx.arc(dCx, dCy, innerR, 0, 2 * Math.PI);
                dCtx.fillStyle = '#ffffff';
                dCtx.fill();

                // center text
                dCtx.fillStyle = '#0f172a';
                dCtx.font = 'bold 22px sans-serif';
                dCtx.textAlign = 'center';
                dCtx.textBaseline = 'middle';
                dCtx.fillText(total, dCx, dCy - 8);
                dCtx.font = '11px sans-serif';
                dCtx.fillStyle = '#64748b';
                dCtx.fillText('Total', dCx, dCy + 12);

                // ---- Line chart ----
                var lCtx   = lineCtx.getContext('2d');
                var lW     = lineCtx.offsetWidth || 400;
                lineCtx.width = lW;
                var lH     = 200;
                lineCtx.height = lH;
                var padL = 40, padR = 16, padT = 16, padB = 36;
                var chartW = lW - padL - padR;
                var chartH = lH - padT - padB;

                // find max value
                var allVals = [];
                datasets.forEach(function(d){ allVals = allVals.concat(d.data); });
                var maxVal = Math.max(1, Math.max.apply(null, allVals));

                function xPos(i) { return padL + (i / Math.max(monthLabels.length - 1, 1)) * chartW; }
                function yPos(v) { return padT + chartH - (v / maxVal) * chartH; }

                // grid lines
                lCtx.strokeStyle = '#e2e8f0';
                lCtx.lineWidth = 1;
                [0, 0.25, 0.5, 0.75, 1].forEach(function(frac) {
                    var y = padT + chartH * (1 - frac);
                    lCtx.beginPath(); lCtx.moveTo(padL, y); lCtx.lineTo(padL + chartW, y); lCtx.stroke();
                    lCtx.fillStyle = '#94a3b8';
                    lCtx.font = '10px sans-serif';
                    lCtx.textAlign = 'right';
                    lCtx.fillText(Math.round(maxVal * frac), padL - 4, y + 4);
                });

                // x-axis labels
                lCtx.fillStyle = '#94a3b8';
                lCtx.font = '10px sans-serif';
                lCtx.textAlign = 'center';
                monthLabels.forEach(function (lbl, i) {
                    lCtx.fillText(lbl, xPos(i), padT + chartH + 18);
                });

                // lines + dots
                datasets.forEach(function (ds) {
                    lCtx.strokeStyle = ds.color;
                    lCtx.lineWidth = 2;
                    lCtx.beginPath();
                    ds.data.forEach(function (val, i) {
                        var x = xPos(i), y = yPos(val);
                        if (i === 0) lCtx.moveTo(x, y); else lCtx.lineTo(x, y);
                    });
                    lCtx.stroke();

                    ds.data.forEach(function (val, i) {
                        lCtx.beginPath();
                        lCtx.arc(xPos(i), yPos(val), 4, 0, 2 * Math.PI);
                        lCtx.fillStyle = '#fff';
                        lCtx.fill();
                        lCtx.strokeStyle = ds.color;
                        lCtx.lineWidth = 2;
                        lCtx.stroke();
                    });
                });
            })();
            </script>
            <?php endif; ?>
        </div>

        <div id="units-section" style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; margin-bottom: 20px; scroll-margin-top: 80px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px;">
                <h3 style="font-size: 1rem; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-building" style="color: var(--primary);"></i> Unit &amp; Office Performance
                </h3>
                <?php if (!empty($units)): ?>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem;">
                    <span style="color: var(--text-muted);">Sort by</span>
                    <select id="unitSortSelect" class="form-control" style="width: auto; padding: 4px 10px; font-size: 0.875rem;">
                        <option value="count">Project Count</option>
                        <option value="rate">Completion Rate</option>
                        <option value="days">Avg Days</option>
                        <option value="name">Unit Name</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <?php if (empty($units)): ?>
            <p style="color: var(--text-muted);">No unit data available yet.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="data-table" id="unitsTable">
                    <thead>
                        <tr>
                            <th>UNIT</th>
                            <th>COUNT</th>
                            <th>COMPLETION RATE</th>
                            <th>AVG HANDLING (DAYS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $unitsGrandTotal = 0;
                        $unitsCompletedTotal = 0;
                        $unitsActivityTotal = 0;
                        $unitsDaysSum = 0;
                        $unitsDaysCount = 0;
                        foreach ($units as $unit):
                            $unitKey = $unit['label'] ?? '';
                            $uMeta = $unitMetricsMap[$unitKey] ?? ['completion_rate' => 0, 'avg_days' => null, 'activity_total' => 0, 'activity_completed' => 0];
                            $unitsGrandTotal    += (int)$unit['total'];
                            $unitsCompletedTotal += $uMeta['activity_completed'];
                            $unitsActivityTotal  += $uMeta['activity_total'];
                            if ($uMeta['avg_days'] !== null) { $unitsDaysSum += $uMeta['avg_days']; $unitsDaysCount++; }
                        ?>
                        <tr
                            data-count="<?php echo (int)$unit['total']; ?>"
                            data-rate="<?php echo $uMeta['completion_rate']; ?>"
                            data-days="<?php echo $uMeta['avg_days'] ?? -1; ?>"
                            data-name="<?php echo htmlspecialchars($unit['display_label']); ?>"
                        >
                            <td><?php echo htmlspecialchars($unit['display_label']); ?></td>
                            <td><?php echo (int)$unit['total']; ?></td>
                            <td><?php echo $uMeta['completion_rate']; ?>%</td>
                            <td><?php echo $uMeta['avg_days'] !== null ? $uMeta['avg_days'] . ' days' : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: 600; border-top: 2px solid var(--border-color);">
                            <td>Total</td>
                            <td><?php echo $unitsGrandTotal; ?></td>
                            <td><?php echo $unitsActivityTotal > 0 ? round(($unitsCompletedTotal / $unitsActivityTotal) * 100, 1) : '0.0'; ?>%</td>
                            <td><?php echo $unitsDaysCount > 0 ? round($unitsDaysSum / $unitsDaysCount, 1) . ' days' : '-'; ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div id="locations-section" style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; margin-bottom: 20px; scroll-margin-top: 80px;">
            <h3 style="font-size: 1rem; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-map-marker-alt" style="color: var(--primary);"></i> Locations
            </h3>
            <?php if (empty($locations)): ?>
            <p style="color: var(--text-muted);">No location data available.</p>
            <?php else: ?>
            <div style="display: grid; gap: 18px;">
                <?php foreach ($locations as $location):
                    $locKey  = $location['label'] ?? '';
                    $lMeta   = $locationMetricsMap[$locKey] ?? ['completion_rate' => 0, 'avg_days' => null, 'activity_total' => 0, 'activity_completed' => 0];
                    $barProjects  = $locationMax > 0 ? max(6, ((int)$location['total'] / $locationMax) * 100) : 6;
                    $barRate      = $lMeta['completion_rate'];
                    $barDays      = $lMeta['avg_days'];
                    $maxDaysRef   = 30;
                    $barDaysPct   = $barDays !== null ? max(6, min(($barDays / $maxDaysRef) * 100, 100)) : 0;
                ?>
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px; margin-bottom: 8px;">
                        <span style="font-weight: 600;"><?php echo htmlspecialchars($location['display_label']); ?></span>
                        <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo (int)$location['total']; ?> projects</span>
                    </div>
                    <div style="display: grid; gap: 6px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="min-width: 110px; font-size: 0.78rem; color: var(--text-muted);">Projects</span>
                            <div style="flex: 1; height: 10px; background: var(--card-bg); border-radius: 999px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo $barProjects; ?>%; background: var(--primary); border-radius: 999px;"></div>
                            </div>
                            <span style="min-width: 28px; text-align: right; font-size: 0.82rem; font-weight: 600;"><?php echo (int)$location['total']; ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="min-width: 110px; font-size: 0.78rem; color: var(--text-muted);">Completion Rate</span>
                            <div style="flex: 1; height: 10px; background: var(--card-bg); border-radius: 999px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo max(6, $barRate); ?>%; background: var(--success); border-radius: 999px;"></div>
                            </div>
                            <span style="min-width: 42px; text-align: right; font-size: 0.82rem; font-weight: 600;"><?php echo $barRate; ?>%</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="min-width: 110px; font-size: 0.78rem; color: var(--text-muted);">Avg Days</span>
                            <div style="flex: 1; height: 10px; background: var(--card-bg); border-radius: 999px; overflow: hidden;">
                                <?php if ($barDays !== null): ?>
                                <div style="height: 100%; width: <?php echo $barDaysPct; ?>%; background: var(--accent); border-radius: 999px;"></div>
                                <?php endif; ?>
                            </div>
                            <span style="min-width: 42px; text-align: right; font-size: 0.82rem; font-weight: 600;"><?php echo $barDays !== null ? $barDays . 'd' : '-'; ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 14px; color: var(--text-muted); font-size: 0.82rem;">
                <span><span style="display: inline-block; width: 10px; height: 10px; background: var(--primary); border-radius: 50%; margin-right: 5px;"></span>Projects</span>
                <span><span style="display: inline-block; width: 10px; height: 10px; background: var(--success); border-radius: 50%; margin-right: 5px;"></span>Completion Rate</span>
                <span><span style="display: inline-block; width: 10px; height: 10px; background: var(--accent); border-radius: 50%; margin-right: 5px;"></span>Avg Days</span>
            </div>
            <?php endif; ?>
        </div>

        <div id="trends-section" style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; margin-bottom: 20px; scroll-margin-top: 80px;">
            <h3 style="font-size: 1rem; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-chart-line" style="color: var(--primary);"></i> Trend &amp; Forecasting
            </h3>
            <?php if (empty($trends)): ?>
            <p style="color: var(--text-muted);">No trend data available yet.</p>
            <?php else: ?>
            <?php
            // Build actual data arrays
            $trendLabels   = [];
            $trendProjects = [];
            foreach ($trends as $t) {
                $trendLabels[]   = $t['month_key'];
                $trendProjects[] = (int)$t['projects'];
            }
            $n = count($trendProjects);

            // Simple linear regression over actual months to project 3 forecast months
            $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;
            for ($i = 0; $i < $n; $i++) {
                $sumX  += $i;
                $sumY  += $trendProjects[$i];
                $sumXY += $i * $trendProjects[$i];
                $sumX2 += $i * $i;
            }
            $denom = ($n * $sumX2 - $sumX * $sumX);
            $slope     = $denom != 0 ? ($n * $sumXY - $sumX * $sumY) / $denom : 0;
            $intercept = ($sumY - $slope * $sumX) / $n;

            // Last actual month key — advance by 1 to 3 months
            $lastMonthKey = end($trendLabels);
            $forecastMonthKeys = [];
            $dt = DateTimeImmutable::createFromFormat('Y-m', $lastMonthKey);
            for ($f = 1; $f <= 3; $f++) {
                $dt = $dt->modify('+1 month');
                $forecastMonthKeys[] = $dt->format('Y-m');
            }

            // Actual series: same length as $trendLabels, null for forecast positions
            // Forecast series: null for actual positions except last actual (overlap point), then 3 forecast values
            $allLabels = array_merge($trendLabels, $forecastMonthKeys);
            $actualSeries   = array_fill(0, count($allLabels), 'null');
            $forecastSeries = array_fill(0, count($allLabels), 'null');

            for ($i = 0; $i < $n; $i++) {
                $actualSeries[$i] = $trendProjects[$i];
            }
            // Overlap at last actual point so lines connect
            $forecastSeries[$n - 1] = $trendProjects[$n - 1];
            for ($f = 0; $f < 3; $f++) {
                $val = max(0, round($intercept + $slope * ($n + $f)));
                $forecastSeries[$n + $f] = $val;
            }

            $jsLabels   = json_encode($allLabels);
            $jsActual   = '[' . implode(',', $actualSeries) . ']';
            $jsForecast = '[' . implode(',', $forecastSeries) . ']';
            ?>
            <div style="position: relative; background: #f0f4fa; border-radius: var(--radius-md); padding: 18px 12px 12px; margin-top: 10px;">
                <canvas id="trendForecastChart" style="width: 100%; max-height: 320px;"></canvas>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
            <script>
            (function() {
                var ctx = document.getElementById('trendForecastChart');
                if (!ctx) return;
                var labels   = <?php echo $jsLabels; ?>;
                var actual   = <?php echo $jsActual; ?>;
                var forecast = <?php echo $jsForecast; ?>;

                // Determine y-axis max
                var allVals = actual.concat(forecast).filter(function(v) { return v !== null; });
                var maxVal  = allVals.length ? Math.max.apply(null, allVals) : 10;
                var yMax    = Math.ceil(maxVal / 10) * 10 + 10;

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Actual',
                                data: actual,
                                borderColor: '#1b4a9a',
                                backgroundColor: 'rgba(29,79,145,0.10)',
                                borderWidth: 2.5,
                                pointRadius: 3,
                                pointBackgroundColor: '#1b4a9a',
                                fill: true,
                                tension: 0,
                                spanGaps: false,
                            },
                            {
                                label: 'Forecast',
                                data: forecast,
                                borderColor: '#f59e0b',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                borderDash: [6, 4],
                                pointRadius: 3,
                                pointBackgroundColor: '#f59e0b',
                                fill: false,
                                tension: 0,
                                spanGaps: false,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                align: 'center',
                                labels: {
                                    usePointStyle: false,
                                    boxWidth: 28,
                                    boxHeight: 3,
                                    padding: 18,
                                    font: { size: 12 },
                                    generateLabels: function(chart) {
                                        return chart.data.datasets.map(function(ds, i) {
                                            return {
                                                text: ds.label,
                                                fillStyle: ds.borderColor,
                                                strokeStyle: ds.borderColor,
                                                lineDash: ds.borderDash || [],
                                                lineWidth: 2,
                                                hidden: false,
                                                datasetIndex: i,
                                            };
                                        });
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        if (ctx.parsed.y === null) return null;
                                        return ctx.dataset.label + ': ' + ctx.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: 'rgba(0,0,0,0.07)' },
                                ticks: { font: { size: 11 } }
                            },
                            y: {
                                min: 0,
                                max: yMax,
                                grid: { color: 'rgba(0,0,0,0.07)' },
                                ticks: {
                                    stepSize: Math.ceil(yMax / 6),
                                    font: { size: 11 }
                                }
                            }
                        }
                    }
                });
            })();
            </script>
            <?php endif; ?>
        </div>

        <div id="exports-section" style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; margin-top: 20px; scroll-margin-top: 80px;">
            <h3 style="font-size: 1rem; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-file-export" style="color: var(--primary);"></i> Export &amp; Reporting
            </h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;">
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px 18px;">
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 6px;">Total Projects</div>
                    <div style="font-size: 1.6rem; font-weight: 700; color: var(--text-primary);"><?php echo (int)$volume['projects']; ?></div>
                </div>
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px 18px;">
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 6px;">Completion Rate</div>
                    <div style="font-size: 1.6rem; font-weight: 700; color: var(--text-primary);"><?php echo $response['on_time_rate']; ?>%</div>
                </div>
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px 18px;">
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 6px;">Avg Completion (days)</div>
                    <div style="font-size: 1.6rem; font-weight: 700; color: var(--text-primary);"><?php echo $response['avg_completion_days'] !== null ? $response['avg_completion_days'] . ' days' : 'N/A'; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var analyticsFilterForm = document.getElementById('analyticsFilterForm');
var departmentSelect = document.getElementById('departmentSelect');
var unitSectionSelect = document.getElementById('unitSectionSelect');
var unitOptionsByOffice = <?php echo json_encode($unitOptionsByOffice, JSON_UNESCAPED_SLASHES); ?>;

if (analyticsFilterForm) {
    analyticsFilterForm.addEventListener('keydown', function(event) {
        if (event.key !== 'Enter') {
            return;
        }

        var tagName = (event.target.tagName || '').toLowerCase();
        var inputType = (event.target.type || '').toLowerCase();

        if (tagName === 'textarea' || inputType === 'submit' || inputType === 'button') {
            return;
        }

        event.preventDefault();

        if (typeof analyticsFilterForm.requestSubmit === 'function') {
            analyticsFilterForm.requestSubmit();
            return;
        }

        analyticsFilterForm.submit();
    });
}

function updateUnitOptions(selectedOffice, selectedUnit) {
    if (!unitSectionSelect) {
        return;
    }

    while (unitSectionSelect.firstChild) {
        unitSectionSelect.removeChild(unitSectionSelect.firstChild);
    }

    var defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'All Units';
    unitSectionSelect.appendChild(defaultOption);

    var options = unitOptionsByOffice[selectedOffice] || {};
    Object.keys(options).forEach(function(unitCode) {
        var option = document.createElement('option');
        option.value = unitCode;
        option.textContent = options[unitCode];
        if (unitCode === selectedUnit) {
            option.selected = true;
        }
        unitSectionSelect.appendChild(option);
    });

    if (selectedUnit && !(selectedUnit in options)) {
        unitSectionSelect.value = '';
    }
}

if (departmentSelect && unitSectionSelect) {
    updateUnitOptions(departmentSelect.value, <?php echo json_encode($selectedUnitSection, JSON_UNESCAPED_SLASHES); ?>);

    departmentSelect.addEventListener('change', function() {
        updateUnitOptions(departmentSelect.value, '');
    });
}

var analyticsTabs = document.querySelectorAll('#analyticsTabs .analytics-tab');
var analyticsSections = ['volume-section', 'status-section', 'response-section', 'categories-section', 'units-section', 'locations-section', 'trends-section', 'exports-section']
    .map(function(id) { return document.getElementById(id); })
    .filter(Boolean);

function setActiveTab(sectionId) {
    analyticsTabs.forEach(function(tab) {
        var isMatch = tab.getAttribute('data-tab-target') === sectionId;
        tab.classList.toggle('is-active', isMatch);
    });
}

var stickyOffset = 120;
(function() {
    var topBar = document.querySelector('.top-bar');
    var tabs = document.getElementById('analyticsTabs');
    if (topBar && tabs) {
        stickyOffset = topBar.offsetHeight + tabs.offsetHeight + 8;
    }
})();

analyticsTabs.forEach(function(tab) {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        var targetId = tab.getAttribute('data-tab-target');
        var targetEl = document.getElementById(targetId);
        if (targetEl) {
            var top = targetEl.getBoundingClientRect().top + (window.scrollY || window.pageYOffset) - stickyOffset;
            window.scrollTo({ top: top, behavior: 'smooth' });
        }
        setActiveTab(targetId);
    });
});

function updateActiveTabByScroll() {
    var scrollTop = window.scrollY || window.pageYOffset;
    var activeId = analyticsSections.length > 0 ? analyticsSections[0].id : null;
    for (var i = 0; i < analyticsSections.length; i++) {
        var rect = analyticsSections[i].getBoundingClientRect();
        if (rect.top <= stickyOffset + 16) {
            activeId = analyticsSections[i].id;
        } else {
            break;
        }
    }
    if (activeId) {
        setActiveTab(activeId);
    }
}

window.addEventListener('scroll', updateActiveTabByScroll, { passive: true });
updateActiveTabByScroll();

var volumeChart = document.getElementById('volumeChart');
if (volumeChart) {
    window.requestAnimationFrame(function() {
        volumeChart.classList.add('is-ready');
    });
}

document.querySelectorAll('.status-bar-fill').forEach(function(bar) {
    window.requestAnimationFrame(function() {
        bar.classList.add('is-ready');
    });
});

// Units table sort
var unitSortSelect = document.getElementById('unitSortSelect');
if (unitSortSelect) {
    unitSortSelect.addEventListener('change', function() {
        var sortBy = this.value;
        var table = document.getElementById('unitsTable');
        var tbody = table.querySelector('tbody');
        var rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort(function(a, b) {
            if (sortBy === 'count') return parseInt(b.dataset.count) - parseInt(a.dataset.count);
            if (sortBy === 'rate')  return parseFloat(b.dataset.rate) - parseFloat(a.dataset.rate);
            if (sortBy === 'days') {
                var ad = parseFloat(a.dataset.days), bd = parseFloat(b.dataset.days);
                if (ad < 0) return 1; if (bd < 0) return -1;
                return bd - ad;
            }
            if (sortBy === 'name') return a.dataset.name.localeCompare(b.dataset.name);
            return 0;
        });
        rows.forEach(function(row) { tbody.appendChild(row); });
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
