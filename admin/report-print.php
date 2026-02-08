<?php
/**
 * Printable Report
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/BacCycle.php';
require_once __DIR__ . '/../models/ProjectActivity.php';
require_once __DIR__ . '/../models/ActivityDocument.php';

$auth = auth();
$auth->requireLogin();

$projectId = isset($_GET['project']) ? (int)$_GET['project'] : 0;
$cycleId = isset($_GET['cycle']) ? (int)$_GET['cycle'] : null;

$projectModel = new Project();
$project = $projectModel->findById($projectId);

if (!$project) {
    die('Project not found');
}

// Project Owners can only print reports for their own projects
if ($auth->isProjectOwner() && (int)$project['created_by'] !== (int)$auth->getUserId()) {
    die('Access denied. You can only print reports for your own projects.');
}

$cycleModel = new BacCycle();
$activityModel = new ProjectActivity();
$documentModel = new ActivityDocument();

$cycle = null;
$activities = [];

if ($cycleId) {
    $cycle = $cycleModel->findById($cycleId);
    $activities = $activityModel->getByCycle($cycleId);
} else {
    $cycle = $cycleModel->getActiveCycle($projectId);
    if ($cycle) {
        $activities = $activityModel->getByCycle($cycle['id']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Report - <?php echo htmlspecialchars($project['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #0f4c75;
        }

        .print-header h1 {
            font-size: 24px;
            color: #0f4c75;
            margin-bottom: 5px;
        }

        .print-header h2 {
            font-size: 18px;
            font-weight: normal;
            margin-bottom: 10px;
        }

        .print-header p {
            color: #666;
        }

        .print-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .print-info-item {
            flex: 1;
            min-width: 200px;
        }

        .print-info-item label {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .print-info-item span {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px 8px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background: #0f4c75;
            color: white;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }

        .status-pending { background: #fef3c7; color: #b45309; }
        .status-in_progress { background: #dbeafe; color: #1d4ed8; }
        .status-completed { background: #d1fae5; color: #047857; }
        .status-delayed { background: #fee2e2; color: #dc2626; }

        .compliance-compliant { background: #d1fae5; color: #047857; }
        .compliance-non_compliant { background: #fee2e2; color: #dc2626; }

        .remarks-row {
            background: #f1f5f9 !important;
            font-style: italic;
        }

        .remarks-row td {
            font-size: 11px;
            color: #64748b;
        }

        .documents-list {
            font-size: 11px;
            color: #64748b;
        }

        .print-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
        }

        .signature-block {
            width: 200px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }

        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .btn {
            padding: 10px 20px;
            background: #0f4c75;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        .btn:hover {
            background: #1b6ca8;
        }

        @page {
            margin: 0;
        }

        @media print {
            body {
                padding: 15mm 10mm;
            }

            .no-print {
                display: none;
            }

            .print-header {
                border-bottom-color: #000;
            }

            th {
                background: #333 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .status-badge, .compliance-compliant, .compliance-non_compliant {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>

    <div class="print-header">
        <h1><?php echo APP_NAME; ?></h1>
        <h2>BAC Procedural Timeline Report</h2>
        <p>Based on RA 9184 IRR</p>
    </div>

    <div class="print-info">
        <div class="print-info-item">
            <label>Project Title</label>
            <span><?php echo htmlspecialchars($project['title']); ?></span>
        </div>
        <div class="print-info-item">
            <label>Procurement Type</label>
            <span><?php echo PROCUREMENT_TYPES[$project['procurement_type']] ?? $project['procurement_type']; ?></span>
        </div>
        <div class="print-info-item">
            <label>Cycle Number</label>
            <span><?php echo $cycle ? 'Cycle ' . $cycle['cycle_number'] : 'N/A'; ?></span>
        </div>
        <div class="print-info-item">
            <label>Cycle Status</label>
            <span><?php echo $cycle ? $cycle['status'] : 'N/A'; ?></span>
        </div>
        <div class="print-info-item">
            <label>Report Generated</label>
            <span><?php echo date('F j, Y g:i A'); ?></span>
        </div>
        <div class="print-info-item">
            <label>Generated By</label>
            <span><?php echo htmlspecialchars($auth->getUserName()); ?></span>
        </div>
    </div>

    <?php if ($project['description']): ?>
    <div style="margin-bottom: 20px;">
        <strong>Project Description:</strong>
        <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Activity / Step Name</th>
                <th style="width: 100px;">Planned Start</th>
                <th style="width: 100px;">Planned End</th>
                <th style="width: 90px;">Duration (Days)</th>
                <th style="width: 100px;">Actual Completion</th>
                <th style="width: 90px;">Status</th>
                <th style="width: 90px;">Compliance</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($activities)): ?>
            <tr>
                <td colspan="8" style="text-align: center; padding: 30px;">No activities found.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($activities as $activity): 
                $documents = $documentModel->getByActivity($activity['id']);
            ?>
            <tr>
                <td><?php echo $activity['step_order']; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($activity['step_name']); ?></strong>
                    <?php if (!empty($documents)): ?>
                    <div class="documents-list">
                        Documents: 
                        <?php foreach ($documents as $i => $doc): ?>
                            <?php echo htmlspecialchars($doc['original_name']); ?><?php echo $i < count($documents) - 1 ? ', ' : ''; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td><?php echo date('M j, Y', strtotime($activity['planned_start_date'])); ?></td>
                <td><?php echo date('M j, Y', strtotime($activity['planned_end_date'])); ?></td>
                <td>
                    <?php if (!empty($activity['planned_start_date']) && !empty($activity['planned_end_date'])): ?>
                        <?php
                            $startDate = new DateTime($activity['planned_start_date']);
                            $endDate = new DateTime($activity['planned_end_date']);
                            $durationDays = $endDate >= $startDate
                                ? $startDate->diff($endDate)->days + 1
                                : null;
                        ?>
                        <?php echo $durationDays !== null ? $durationDays . ' days' : '-'; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo $activity['actual_completion_date'] 
                        ? date('M j, Y', strtotime($activity['actual_completion_date'])) 
                        : '-'; ?>
                </td>
                <td>
                    <span class="status-badge status-<?php echo strtolower($activity['status']); ?>">
                        <?php echo $activity['status']; ?>
                    </span>
                </td>
                <td>
                    <?php if ($activity['compliance_status']): ?>
                    <span class="status-badge compliance-<?php echo strtolower($activity['compliance_status']); ?>">
                        <?php echo $activity['compliance_status']; ?>
                    </span>
                    <?php else: ?>
                    -
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($activity['compliance_remarks']): ?>
            <tr class="remarks-row">
                <td colspan="8">
                    <strong>Compliance Remarks:</strong> <?php echo htmlspecialchars($activity['compliance_remarks']); ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="print-footer">
        <div class="signature-block">
            <div class="signature-line">Prepared By</div>
        </div>
        <div class="signature-block">
            <div class="signature-line">Reviewed By</div>
        </div>
        <div class="signature-block">
            <div class="signature-line">Approved By</div>
        </div>
    </div>
<script>
    // Strip auth_token from URL so it doesn't appear in browser print headers/footers
    (function() {
        var url = new URL(window.location.href);
        if (url.searchParams.has('auth_token')) {
            url.searchParams.delete('auth_token');
            window.history.replaceState({}, document.title, url.toString());
        }
    })();
</script>
</body>
</html>
