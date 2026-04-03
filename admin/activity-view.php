<?php
/**
 * View Activity
 * SDO-BACtrack
 */

// Load auth and flash helpers first so we can safely handle
// redirects and flash messages before any HTML output.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/timeline.php';

// Require models used on this page
require_once __DIR__ . '/../models/ProjectActivity.php';
require_once __DIR__ . '/../models/ActivityHistoryLog.php';
require_once __DIR__ . '/../models/AdjustmentRequest.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../services/ProcurementTimelineService.php';

// Ensure user is authenticated and get auth helper
$auth = auth();
$auth->requireLogin();

$activityId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$activityModel = new ProjectActivity();
$activity = $activityModel->findById($activityId);

if (!$activity) {
    setFlashMessage('error', 'Process not found.');
    $auth->redirect(APP_URL . '/admin/activities.php');
}

// Project Owners can only view activities from their own projects (privacy)
require_once __DIR__ . '/../models/Project.php';
$projectModel = new Project();
$project = $projectModel->findById($activity['project_id'] ?? 0);
if (!$project) {
    setFlashMessage('error', 'Project not found.');
    $auth->redirect(APP_URL . '/admin/activities.php');
}
if ($auth->isProjectOwner() && (int)$project['created_by'] !== (int)$auth->getUserId()) {
    setFlashMessage('error', 'You do not have access to this process.');
    $auth->redirect(APP_URL . '/admin/activities.php');
}
$projectApproved = ($project['approval_status'] ?? 'APPROVED') === 'APPROVED';
$projectActivities = $activityModel->getByProject($project['id']);
$timelineSummary = timelineProjectSummary($projectActivities);
$activityTiming = timelineActivityMeta($activity);
$currentActivity = $timelineSummary['current_activity'];
$nextActivity = $timelineSummary['next_activity'];

$historyModel = new ActivityHistoryLog();
$history = $historyModel->getByActivity($activityId);

$adjustmentModel = new AdjustmentRequest();
$adjustments = $adjustmentModel->getByActivity($activityId);
$hasPendingAdjustment = $adjustmentModel->hasPendingRequest($activityId);

$timelineEngine = new ProcurementTimelineService();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $enforcedActions = procurementConfig()['enforced_actions'] ?? [];
    if (in_array($action, $enforcedActions, true)) {
        $implementationDate = trim((string)($project['project_start_date'] ?? ''));
        if ($implementationDate === '') {
            setFlashMessage('error', 'Implementation date is required before workflow actions can proceed.');
            $auth->redirect(APP_URL . '/admin/activity-view.php?id=' . $activityId);
        }

        try {
            $computedTimeline = $timelineEngine->generateTimeline($implementationDate, $project['procurement_type'] ?? 'PUBLIC_BIDDING');
            $activityStageKey = $timelineEngine->mapStepNameToStageKey($activity['step_name'] ?? '');

            $justificationText = trim($_POST['reason'] ?? '');
            $hasDelayJustification = !empty($adjustments) || !empty($justificationText);

            $stageValidation = $timelineEngine->validateActionForCurrentStage(
                $computedTimeline,
                $activityStageKey,
                $action,
                [
                    'is_completed' => ((int)$timelineSummary['remaining_steps'] === 0),
                    'has_justification' => $hasDelayJustification,
                ]
            );

            if (!$stageValidation['allowed']) {
                setFlashMessage('error', $stageValidation['message']);
                $auth->redirect(APP_URL . '/admin/activity-view.php?id=' . $activityId);
            }
        } catch (Exception $e) {
            setFlashMessage('error', 'Unable to validate procurement stage: ' . $e->getMessage());
            $auth->redirect(APP_URL . '/admin/activity-view.php?id=' . $activityId);
        }
    }

    // Update Status (Procurement only) - blocked if project not approved
    if ($action === 'update_status' && $auth->canUpdateActivity() && $projectApproved) {
        $newStatus = $_POST['status'] ?? '';
        $oldStatus = $activity['status'];

        if ($newStatus === 'COMPLETED') {
            $activityModel->markComplete($activityId);
        } else {
            $activityModel->updateStatus($activityId, $newStatus);
        }

        $historyModel->logStatusChange($activityId, $oldStatus, $newStatus, $auth->getUserId());

        // Notify if delayed
        if ($newStatus === 'DELAYED') {
            $notificationModel = new Notification();
            $notificationModel->notifyActivityDelayed($activityId, $activity['step_name'], $activity['project_title']);
        }

        setFlashMessage('success', 'Process status updated successfully.');
        $auth->redirect(APP_URL . '/admin/activity-view.php?id=' . $activityId);
    }

    // Set Compliance (Procurement only) - blocked if project not approved
    if ($action === 'set_compliance' && $auth->canSetCompliance() && $projectApproved) {
        $complianceStatus = $_POST['compliance_status'] ?? '';
        $complianceRemarks = trim($_POST['compliance_remarks'] ?? '');

        if ($complianceStatus === 'NON_COMPLIANT' && empty($complianceRemarks)) {
            setFlashMessage('error', 'Remarks are required when marking as Non-Compliant.');
        } else {
            $oldCompliance = $activity['compliance_status'];
            $activityModel->setCompliance($activityId, $complianceStatus, $complianceRemarks);
            $historyModel->logComplianceTag($activityId, $oldCompliance, $complianceStatus, $complianceRemarks, $auth->getUserId());

            setFlashMessage('success', 'Compliance status updated successfully.');
        }
        $auth->redirect(APP_URL . '/admin/activity-view.php?id=' . $activityId);
    }

    // Request Timeline Adjustment (All users)
    if ($action === 'request_adjustment' && $auth->canRequestAdjustment()) {
        $newStartDate = $_POST['new_start_date'] ?? '';
        $newEndDate = $_POST['new_end_date'] ?? '';
        $reason = trim($_POST['reason'] ?? '');

        if (empty($newStartDate) || empty($newEndDate) || empty($reason)) {
            setFlashMessage('error', 'All fields are required for adjustment request.');
        } else {
            $requestId = $adjustmentModel->create([
                'activity_id' => $activityId,
                'requested_by' => $auth->getUserId(),
                'reason' => $reason,
                'new_start_date' => $newStartDate,
                'new_end_date' => $newEndDate
            ]);

            // Notify procurement users
            $notificationModel = new Notification();
            $notificationModel->notifyAdjustmentRequest($requestId, $activity['step_name'], $activity['project_title'], $auth->getUserName());

            setFlashMessage('success', 'Timeline adjustment request submitted.');
        }
        $auth->redirect(APP_URL . '/admin/activity-view.php?id=' . $activityId);
    }
}

// Refresh data
$activity = $activityModel->findById($activityId);

// Only include the header (which outputs HTML) after all
// redirects and header() calls above are done.
require_once __DIR__ . '/../includes/header.php';
?>

<div class="activity-view-page">

<div class="page-header">
    <div>
        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $activity['project_id']; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Project
        </a>
    </div>
</div>

<!-- Dashboard Overlays -->
<div class="dash-stats grid-4-col">
    <div class="stat-card">
        <div class="stat-label">Remaining Process</div>
        <div class="stat-value"><?php echo $timelineSummary['remaining_steps']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Due Today</div>
        <div class="stat-value stat-value-info"><?php echo $timelineSummary['due_today_steps']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Overdue Process</div>
        <div class="stat-value stat-value-danger"><?php echo $timelineSummary['overdue_steps']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Current Phase</div>
        <div class="stat-value"><?php echo 'Process ' . $activity['step_order']; ?></div>
    </div>
</div>

<div class="grid-main-sidebar" style="gap: 32px;">
    <div class="main-column">
        <!-- Process Info -->
        <div class="data-card" style="padding: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px;">
                <div>
                    <h2 style="font-size: 2rem; font-weight: 800; color: #111827; margin: 0;"><?php echo htmlspecialchars($activity['step_name']); ?></h2>
                    <p style="font-size: 1.1rem; color: #6b7280; margin-top: 8px;">
                        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $activity['project_id']; ?>" style="color: inherit; text-decoration: none; font-weight: 600;"><?php echo htmlspecialchars($activity['project_title']); ?></a>
                        <span style="margin: 0 8px; opacity: 0.3;">&bull;</span>
                        Process <?php echo $activity['step_order']; ?>
                    </p>
                </div>
                <div>
                    <span class="status-badge status-<?php echo strtolower(str_replace('_', '-', $activity['status'])); ?>" style="padding: 8px 20px; font-size: 0.95rem;">
                        <?php echo ACTIVITY_STATUSES[$activity['status']] ?? $activity['status']; ?>
                    </span>
                </div>
            </div>

            <div class="grid-3-col" style="padding: 24px; background: #f9fafb; border-radius: 16px; border: 1px solid #e5e7eb;">
                <div>
                    <label style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 8px;">Planned Start</label>
                    <div style="font-size: 1.1rem; font-weight: 700; color: #111827;"><?php echo date('M j, Y', strtotime($activity['planned_start_date'])); ?></div>
                </div>
                <div>
                    <label style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 8px;">Planned End</label>
                    <div style="font-size: 1.1rem; font-weight: 700; color: #111827;"><?php echo date('M j, Y', strtotime($activity['planned_end_date'])); ?></div>
                </div>
                <div>
                    <label style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 8px;">Timeline Status</label>
                    <div style="font-size: 1rem; font-weight: 600;"><?php echo htmlspecialchars($activityTiming['timing_label']); ?></div>
                </div>
            </div>

            <div class="process-context-row">
                <div class="process-context-chip">
                    <span class="context-label">Current Process</span>
                    <strong><?php echo htmlspecialchars($currentActivity['step_name'] ?? 'N/A'); ?></strong>
                </div>
                <div class="process-context-chip">
                    <span class="context-label">Next Process</span>
                    <strong><?php echo htmlspecialchars($nextActivity['step_name'] ?? 'Final Stage'); ?></strong>
                </div>
            </div>
        </div>

        <div class="data-card">
            <div class="card-header">
                <h2>Activity Trail</h2>
                <span style="font-size: 0.85rem; background: #f3f4f6; padding: 4px 10px; border-radius: 6px; font-weight: 600;"><?php echo count($history); ?> Entries</span>
            </div>
            <div class="card-body">
                <?php if (empty($history)): ?>
                <div class="empty-state small" style="padding: 40px; text-align: center;">
                    <p style="color: #9ca3af;">No updates recorded for this process yet.</p>
                </div>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($history as $log): ?>
                    <?php
                        $actionType = (string)($log['action_type'] ?? 'UPDATE');
                        $actionLabel = ucwords(strtolower(str_replace('_', ' ', $actionType)));
                        $actionIcon = 'fas fa-pen';
                        if ($actionType === 'STATUS_CHANGE') {
                            $actionIcon = 'fas fa-flag-checkered';
                        } elseif ($actionType === 'COMPLIANCE_TAG') {
                            $actionIcon = 'fas fa-shield-check';
                        } elseif ($actionType === 'DATE_CHANGE') {
                            $actionIcon = 'fas fa-calendar-days';
                        }

                        $summary = 'Record updated.';
                        if ($actionType === 'STATUS_CHANGE') {
                            $summary = 'Status changed from ' . ($log['old_value'] ?: 'N/A') . ' to ' . ($log['new_value'] ?: 'N/A') . '.';
                        } elseif ($actionType === 'DATE_CHANGE') {
                            $oldDates = json_decode((string)($log['old_value'] ?? ''), true) ?: [];
                            $newDates = json_decode((string)($log['new_value'] ?? ''), true) ?: [];
                            $summary = 'Dates moved from ' .
                                (!empty($oldDates['start']) ? date('M j, Y', strtotime($oldDates['start'])) : 'N/A') . ' - ' .
                                (!empty($oldDates['end']) ? date('M j, Y', strtotime($oldDates['end'])) : 'N/A') .
                                ' to ' .
                                (!empty($newDates['start']) ? date('M j, Y', strtotime($newDates['start'])) : 'N/A') . ' - ' .
                                (!empty($newDates['end']) ? date('M j, Y', strtotime($newDates['end'])) : 'N/A') . '.';
                        } elseif ($actionType === 'COMPLIANCE_TAG') {
                            $compliance = json_decode((string)($log['new_value'] ?? ''), true) ?: [];
                            $summary = 'Compliance marked as ' . (!empty($compliance['status']) ? $compliance['status'] : 'N/A') . '.';
                            if (!empty($compliance['remarks'])) {
                                $summary .= ' Notes: ' . $compliance['remarks'];
                            }
                        }
                    ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"><i class="<?php echo $actionIcon; ?>"></i></div>
                        <div class="timeline-content">
                            <div class="timeline-title"><?php echo htmlspecialchars($actionLabel); ?></div>
                            <div class="timeline-desc"><?php echo htmlspecialchars($summary); ?></div>
                            <div class="timeline-meta">
                                <span class="timeline-user">
                                    <?php if (!empty($log['changed_by_avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($log['changed_by_avatar']); ?>" alt="" class="timeline-avatar">
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($log['changed_by_name'] ?: 'System'); ?>
                                </span>
                                <span class="timeline-date">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('M j, Y g:i A', strtotime($log['changed_at'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="data-card">
            <div class="card-header">
                <h2>Adjustment Requests</h2>
                <span style="font-size: 0.85rem; background: #f3f4f6; padding: 4px 10px; border-radius: 6px; font-weight: 600;"><?php echo count($adjustments); ?> Total</span>
            </div>
            <div class="card-body">
                <?php if (empty($adjustments)): ?>
                <div class="empty-state small" style="padding: 24px; text-align: center;">
                    <p style="color: #9ca3af;">No date adjustment requests for this process.</p>
                </div>
                <?php else: ?>
                <div class="adjustment-list">
                    <?php foreach ($adjustments as $request): ?>
                    <div class="adjustment-item">
                        <div class="adjustment-head">
                            <span class="status-pill status-pill-<?php echo strtolower((string)($request['status'] ?? 'PENDING')); ?>"><?php echo htmlspecialchars((string)($request['status'] ?? 'PENDING')); ?></span>
                            <small><?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?></small>
                        </div>
                        <div class="adjustment-dates">
                            <strong><?php echo date('M j, Y', strtotime($request['new_start_date'])); ?></strong>
                            <span>to</span>
                            <strong><?php echo date('M j, Y', strtotime($request['new_end_date'])); ?></strong>
                        </div>
                        <p class="adjustment-reason"><?php echo nl2br(htmlspecialchars($request['reason'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>


    </div>

    <div class="sidebar-column">
        <!-- Action Center -->
        <div class="data-card" style="position: sticky; top: 24px;">
            <div class="card-header">
                <h2>Action Center</h2>
            </div>
            <div class="card-body" style="gap: 32px;">
                <!-- Status Update -->
                <?php if ($auth->canUpdateActivity()): ?>
                <div class="action-section">
                    <label class="form-label" style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        Progress Management
                    </label>
                    <?php if (!$projectApproved): ?>
                    <div class="alert alert-warning" style="margin: 0; padding: 12px; font-size: 0.85rem;">
                        BAC Approval required
                    </div>
                    <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <select name="status" class="form-control" required>
                                <?php foreach (ACTIVITY_STATUSES as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php echo $activity['status'] === $key ? 'selected' : ''; ?>>
                                    <?php echo $value; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Commit Change</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Compliance -->
                <?php if ($auth->canSetCompliance() && $projectApproved): ?>
                <div class="action-section">
                    <label class="form-label" style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        Compliance Check
                    </label>
                    
                    <?php if ($activity['compliance_status']): ?>
                    <div style="margin-bottom: 16px; padding: 12px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb;">
                        <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 4px;">Current Standing</div>
                        <span class="compliance-badge compliance-<?php echo strtolower(str_replace('_', '-', $activity['compliance_status'])); ?>" style="padding: 2px 10px; font-size: 0.8rem;">
                            <?php echo COMPLIANCE_STATUSES[$activity['compliance_status']] ?? $activity['compliance_status']; ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="action" value="set_compliance">
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <select name="compliance_status" class="form-control" required id="complianceStatus">
                                <option value="">Assign Status...</option>
                                <?php foreach (COMPLIANCE_STATUSES as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div id="remarksGroup" style="display: none;">
                                <textarea name="compliance_remarks" class="form-control" rows="2" placeholder="Mandatory remarks for Non-Compliant status..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success" style="width: 100%;">Verify Process</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Adjustments -->
                <div class="action-section">
                    <label class="form-label" style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        Timeline Adjustment
                    </label>
                    
                    <?php if ($hasPendingAdjustment): ?>
                    <div class="alert alert-warning" style="margin: 0; padding: 12px; font-size: 0.85rem;">
                        Pending review...
                    </div>
                    <?php elseif ($auth->canRequestAdjustment()): ?>
                    <button type="button" class="btn btn-secondary" id="showAdjFormBtn" style="width: 100%; border-style: dashed;">
                        Request New Dates
                    </button>
                    <div id="adjForm" style="display: none;">
                        <form method="POST">
                            <input type="hidden" name="action" value="request_adjustment">
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                    <input type="date" name="new_start_date" class="form-control" style="padding: 8px; font-size: 0.85rem;" required>
                                    <input type="date" name="new_end_date" class="form-control" style="padding: 8px; font-size: 0.85rem;" required>
                                </div>
                                <textarea name="reason" class="form-control" rows="2" placeholder="Why is this change needed?" required></textarea>
                                <button type="submit" class="btn btn-warning" style="width: 100%;">Submit Request</button>
                                <button type="button" class="btn btn-sm btn-secondary" id="hideAdjFormBtn">Cancel</button>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <div style="font-size: 0.85rem; color: #6b7280; font-style: italic;">
                        No pending requests.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.activity-view-page {
    --av-border: #e5e7eb;
    --av-muted: #6b7280;
}

.activity-view-page .page-header,
.activity-view-page .dash-stats,
.activity-view-page .data-card {
    animation: av-slide-up 0.45s ease-out both;
}

.activity-view-page .dash-stats {
    margin-bottom: 32px;
}

.activity-view-page .data-card {
    border: 1px solid var(--av-border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15, 76, 117, 0.05);
}

.activity-view-page .stat-card {
    border: 1px solid #e8eef4;
    border-radius: 14px;
    padding: 22px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
}

.activity-view-page .stat-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: var(--av-muted);
}

.activity-view-page .stat-value {
    font-size: 1.75rem;
    font-weight: 800;
    color: #0f172a;
}

.activity-view-page .stat-value-info {
    color: #2563eb;
}

.activity-view-page .stat-value-danger {
    color: #dc2626;
}

.activity-view-page .process-context-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 16px;
}

.activity-view-page .process-context-chip {
    border: 1px solid #dbe5ef;
    background: #f8fbff;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.activity-view-page .process-context-chip .context-label {
    color: var(--av-muted);
    font-size: 0.72rem;
    text-transform: uppercase;
    font-weight: 700;
}

.activity-view-page .process-context-chip strong {
    color: #0f172a;
    font-size: 0.95rem;
}

.activity-view-page .card-header {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.activity-view-page .card-header h2 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 750;
}

.activity-view-page .card-body {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-view-page .form-control {
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 11px 14px;
    background: #f9fafb;
}

.activity-view-page .timeline {
    position: relative;
    padding-left: 0;
}

.activity-view-page .timeline::before {
    content: '';
    position: absolute;
    left: 16px;
    top: 10px;
    bottom: 10px;
    width: 2px;
    background: #dbe5ef;
}

.activity-view-page .timeline-item {
    position: relative;
    padding-left: 46px;
    padding-bottom: 20px;
}

.activity-view-page .timeline-item:last-child {
    padding-bottom: 0;
}

.activity-view-page .timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #c8d8e7;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
}

.activity-view-page .timeline-title {
    font-weight: 700;
    color: #0f172a;
}

.activity-view-page .timeline-desc {
    margin: 3px 0 8px;
    color: #374151;
    font-size: 0.9rem;
}

.activity-view-page .timeline-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    color: var(--av-muted);
    font-size: 0.8rem;
}

.activity-view-page .timeline-user {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.activity-view-page .timeline-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    object-fit: cover;
}

.activity-view-page .adjustment-list {
    display: grid;
    gap: 12px;
}

.activity-view-page .adjustment-item {
    border: 1px solid var(--av-border);
    border-radius: 12px;
    background: #fcfdff;
    padding: 14px;
}

.activity-view-page .adjustment-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.activity-view-page .adjustment-dates {
    display: inline-flex;
    gap: 6px;
    color: #111827;
    margin-bottom: 8px;
}

.activity-view-page .adjustment-reason {
    margin: 0;
    font-size: 0.88rem;
    color: #4b5563;
}

.activity-view-page .status-pill {
    font-size: 0.75rem;
    padding: 3px 9px;
    border-radius: 999px;
    font-weight: 700;
    text-transform: uppercase;
}

.activity-view-page .status-pill-pending {
    background: #fef3c7;
    color: #92400e;
}

.activity-view-page .status-pill-approved {
    background: #dcfce7;
    color: #166534;
}

.activity-view-page .status-pill-disapproved {
    background: #fee2e2;
    color: #991b1b;
}

@keyframes av-slide-up {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 1100px) {
    .activity-view-page .dash-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .activity-view-page .process-context-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .activity-view-page .dash-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.getElementById('complianceStatus')?.addEventListener('change', function() {
    const remarksGroup = document.getElementById('remarksGroup');
    if (this.value === 'NON_COMPLIANT') {
        remarksGroup.style.display = 'block';
        remarksGroup.querySelector('textarea').required = true;
    } else {
        remarksGroup.style.display = 'none';
        remarksGroup.querySelector('textarea').required = false;
    }
});

const showAdjFormBtn = document.getElementById('showAdjFormBtn');
const hideAdjFormBtn = document.getElementById('hideAdjFormBtn');
const adjForm = document.getElementById('adjForm');

showAdjFormBtn?.addEventListener('click', function() {
    showAdjFormBtn.style.display = 'none';
    if (adjForm) {
        adjForm.style.display = 'block';
    }
});

hideAdjFormBtn?.addEventListener('click', function() {
    if (adjForm) {
        adjForm.style.display = 'none';
    }
    if (showAdjFormBtn) {
        showAdjFormBtn.style.display = 'inline-flex';
    }
});
</script>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
