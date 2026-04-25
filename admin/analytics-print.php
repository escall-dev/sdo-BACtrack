<?php
/**
 * Printable Analytics
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/OfficeAnalytics.php';

$auth = auth();
$auth->requireProcurement();

$officeAnalyticsModel = new OfficeAnalytics();
$officeDefinitions = $officeAnalyticsModel->getDefinitions();

$officeSearch = trim($_GET['office_search'] ?? '');
$selectedOffice = strtoupper(trim($_GET['office'] ?? ''));
$selectedUnitSection = trim($_GET['unit_section'] ?? '');
$autoPrint = isset($_GET['autoprint']) && (string)$_GET['autoprint'] === '1';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Report - <?php echo htmlspecialchars($viewCode); ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1f2937;
            background: #f3f4f6;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }
        .header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
        }
        .header p {
            margin: 6px 0 0;
            color: #6b7280;
        }
        .meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            padding: 18px 24px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .meta-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            background: #fff;
        }
        .meta-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.06em;
        }
        .meta-value {
            margin-top: 4px;
            font-size: 16px;
            font-weight: 700;
        }
        .section {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
        }
        .section h2 {
            margin: 0 0 12px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            font-weight: 700;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .no-print {
            position: fixed;
            top: 18px;
            right: 18px;
            display: flex;
            gap: 8px;
            z-index: 10;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #d1d5db;
            color: #374151;
            background: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #1b4a9a;
            color: #fff;
            border-color: #1b4a9a;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .container { border: none; border-radius: 0; }
            .no-print { display: none; }
            .section, .meta, .header { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a class="btn" href="<?php echo APP_URL; ?>/admin/analytics.php?<?php echo htmlspecialchars(http_build_query(['office' => $selectedOffice, 'office_search' => $officeSearch, 'unit_section' => $selectedUnitSection, 'analytics_export' => 'csv'])); ?>">Export CSV</a>
        <button class="btn" onclick="window.print()">Export PDF</button>
        <button class="btn btn-primary" onclick="window.print()">Print</button>
    </div>

    <div class="container">
        <div class="header">
            <h1>Analytics Report</h1>
            <p>Unified statistics dashboard for OSDS, SGOD, and CID.</p>
        </div>

        <div class="meta">
            <div class="meta-card">
                <div class="meta-label">Scope</div>
                <div class="meta-value"><?php echo htmlspecialchars($scopeLabel); ?></div>
            </div>
            <div class="meta-card">
                <div class="meta-label">Code</div>
                <div class="meta-value"><?php echo htmlspecialchars($viewCode); ?></div>
            </div>
            <div class="meta-card">
                <div class="meta-label">Generated</div>
                <div class="meta-value"><?php echo htmlspecialchars($analytics['generated_at']); ?></div>
            </div>
        </div>

        <div class="section">
            <h2>Volume</h2>
            <table>
                <thead><tr><th>Users</th><th>Projects</th><th>Activities</th><th>Documents</th></tr></thead>
                <tbody><tr><td><?php echo $volume['users']; ?></td><td><?php echo $volume['projects']; ?></td><td><?php echo $volume['activities']; ?></td><td><?php echo $volume['documents']; ?></td></tr></tbody>
            </table>
        </div>

        <div class="section grid-2">
            <div>
                <h2>Project Status</h2>
                <table>
                    <thead><tr><th>Status</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($projectStatusLabels as $key => $label): ?>
                        <tr><td><?php echo htmlspecialchars($label); ?></td><td><?php echo (int)$statusProjects[$key]; ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div>
                <h2>Activity Status</h2>
                <table>
                    <thead><tr><th>Status</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($activityStatusLabels as $key => $label): ?>
                        <tr><td><?php echo htmlspecialchars($label); ?></td><td><?php echo (int)$statusActivities[$key]; ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <h2>Response</h2>
            <table>
                <thead><tr><th>Completed Activities</th><th>Average Completion Days</th><th>On-Time Rate</th><th>Delayed Open</th><th>Compliance Rate</th></tr></thead>
                <tbody>
                    <tr>
                        <td><?php echo (int)$response['completed_total']; ?></td>
                        <td><?php echo $response['avg_completion_days'] === null ? 'N/A' : htmlspecialchars((string)$response['avg_completion_days']); ?></td>
                        <td><?php echo htmlspecialchars(number_format((float)$response['on_time_rate'], 1)); ?>%</td>
                        <td><?php echo (int)$response['delayed_open']; ?></td>
                        <td><?php echo htmlspecialchars(number_format((float)$response['compliance_rate'], 1)); ?>%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section grid-2">
            <div>
                <h2>Categories</h2>
                <table>
                    <thead><tr><th>Category</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($categories as $row): ?>
                        <tr><td><?php echo htmlspecialchars($row['display_label']); ?></td><td><?php echo (int)$row['total']; ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div>
                <h2>Units</h2>
                <table>
                    <thead><tr><th>Unit</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($units as $row): ?>
                        <tr><td><?php echo htmlspecialchars($row['display_label']); ?></td><td><?php echo (int)$row['total']; ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <h2>Locations</h2>
            <table>
                <thead><tr><th>Location</th><th>Total</th></tr></thead>
                <tbody>
                    <?php foreach ($locations as $row): ?>
                    <tr><td><?php echo htmlspecialchars($row['display_label']); ?></td><td><?php echo (int)$row['total']; ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Trends</h2>
            <table>
                <thead><tr><th>Month</th><th>Projects</th><th>Completed Activities</th><th>Documents</th></tr></thead>
                <tbody>
                    <?php foreach ($trends as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['label']); ?></td>
                        <td><?php echo (int)$row['projects']; ?></td>
                        <td><?php echo (int)$row['completed']; ?></td>
                        <td><?php echo (int)$row['documents']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($autoPrint): ?>
    <script>
    window.addEventListener('load', function() {
        window.print();
    });
    </script>
    <?php endif; ?>
</body>
</html>
