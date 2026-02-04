<?php
/**
 * View Activity
 * SDO-BACtrack
 */

// Load auth and flash helpers first so we can safely handle
// redirects and flash messages before any HTML output.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';

// Require models used on this page
require_once __DIR__ . '/../models/ProjectActivity.php';
require_once __DIR__ . '/../models/ActivityDocument.php';
require_once __DIR__ . '/../models/ActivityHistoryLog.php';
require_once __DIR__ . '/../models/AdjustmentRequest.php';
require_once __DIR__ . '/../models/Notification.php';

// Ensure user is authenticated and get auth helper
$auth = auth();
$auth->requireLogin();

$activityId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$activityModel = new ProjectActivity();
$activity = $activityModel->findById($activityId);

if (!$activity) {
    setFlashMessage('error', 'Activity not found.');
    header('Location: ' . APP_URL . '/admin/activities.php');
    exit;
}

$documentModel = new ActivityDocument();
$documents = $documentModel->getByActivity($activityId);

$historyModel = new ActivityHistoryLog();
$history = $historyModel->getByActivity($activityId);

$adjustmentModel = new AdjustmentRequest();
$adjustments = $adjustmentModel->getByActivity($activityId);
$hasPendingAdjustment = $adjustmentModel->hasPendingRequest($activityId);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Update Status (Procurement only)
    if ($action === 'update_status' && $auth->canUpdateActivity()) {
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

        setFlashMessage('success', 'Activity status updated successfully.');
        header('Location: ' . APP_URL . '/admin/activity-view.php?id=' . $activityId);
        exit;
    }

    // Set Compliance (Procurement only)
    if ($action === 'set_compliance' && $auth->canSetCompliance()) {
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
        header('Location: ' . APP_URL . '/admin/activity-view.php?id=' . $activityId);
        exit;
    }

    // Upload Document (Procurement only)
    if ($action === 'upload_document' && $auth->canUploadDocuments()) {
        if (isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $documentModel->upload($_FILES['document'], $activityId, $auth->getUserId());

                // Notify about document upload
                $notificationModel = new Notification();
                $notificationModel->notifyDocumentUploaded($activityId, $activity['step_name'], $activity['project_title'], $auth->getUserName());

                setFlashMessage('success', 'Document uploaded successfully.');
            } catch (Exception $e) {
                setFlashMessage('error', 'Failed to upload document: ' . $e->getMessage());
            }
        } else {
            setFlashMessage('error', 'Please select a file to upload.');
        }
        header('Location: ' . APP_URL . '/admin/activity-view.php?id=' . $activityId);
        exit;
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
        header('Location: ' . APP_URL . '/admin/activity-view.php?id=' . $activityId);
        exit;
    }
}

// Refresh data
$activity = $activityModel->findById($activityId);
$documents = $documentModel->getByActivity($activityId);

// Only include the header (which outputs HTML) after all
// redirects and header() calls above are done.
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $activity['project_id']; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Project
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px;">
    <div>
        <!-- Activity Info -->
        <div class="data-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Activity Details</h2>
                <span class="status-badge status-<?php echo strtolower(str_replace('_', '-', $activity['status'])); ?>">
                    <?php echo ACTIVITY_STATUSES[$activity['status']] ?? $activity['status']; ?>
                </span>
            </div>
            <div class="card-body">
                <h3 style="font-size: 1.5rem; margin-bottom: 8px;"><?php echo htmlspecialchars($activity['step_name']); ?></h3>
                <p style="color: var(--text-muted); margin-bottom: 20px;">
                    Step <?php echo $activity['step_order']; ?> • 
                    <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $activity['project_id']; ?>"><?php echo htmlspecialchars($activity['project_title']); ?></a> • 
                    Cycle <?php echo $activity['cycle_number']; ?>
                </p>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Planned Start</label>
                        <p style="font-weight: 600; font-size: 1.1rem;"><?php echo date('F j, Y', strtotime($activity['planned_start_date'])); ?></p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Planned End</label>
                        <p style="font-weight: 600; font-size: 1.1rem;"><?php echo date('F j, Y', strtotime($activity['planned_end_date'])); ?></p>
                    </div>
                    <?php if ($activity['actual_completion_date']): ?>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Actual Completion</label>
                        <p style="font-weight: 600; font-size: 1.1rem;"><?php echo date('F j, Y', strtotime($activity['actual_completion_date'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Duration</label>
                        <p style="font-weight: 600; font-size: 1.1rem;">
                            <?php 
                            $start = new DateTime($activity['planned_start_date']);
                            $end = new DateTime($activity['planned_end_date']);
                            $diff = $start->diff($end);
                            echo ($diff->days + 1) . ' day(s)';
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Section -->
        <div class="data-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2><i class="fas fa-file-alt"></i> Documents (<?php echo count($documents); ?>)</h2>
            </div>
            <div class="card-body">
                <?php if ($auth->canUploadDocuments()): ?>
                <form method="POST" enctype="multipart/form-data" style="margin-bottom: 20px; padding: 16px; background: var(--bg-secondary); border-radius: var(--radius-md);">
                    <input type="hidden" name="action" value="upload_document">
                    <div style="display: flex; gap: 12px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label class="form-label">Upload Document</label>
                            <input type="file" name="document" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </div>
                    <small style="color: var(--text-muted);">Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG. Max 10MB.</small>
                </form>
                <?php endif; ?>

                <?php if (empty($documents)): ?>
                <div class="empty-state small">
                    <div class="empty-icon"><i class="fas fa-file-alt"></i></div>
                    <p>No documents uploaded yet.</p>
                </div>
                <?php else: ?>
                <div class="documents-list">
                    <?php foreach ($documents as $doc): ?>
                    <div class="document-item">
                        <i class="fas fa-file-pdf" style="font-size: 1.5rem; color: var(--danger);"></i>
                        <div class="document-info">
                            <div class="document-name"><?php echo htmlspecialchars($doc['original_name']); ?></div>
                            <div class="document-meta">
                                Uploaded by <?php echo htmlspecialchars($doc['uploader_name']); ?> on 
                                <?php echo date('M j, Y g:i A', strtotime($doc['uploaded_at'])); ?>
                            </div>
                        </div>
                        <a href="<?php echo APP_URL; ?>/uploads/<?php echo $doc['file_path']; ?>" class="btn btn-sm btn-secondary" target="_blank">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- History Log -->
        <div class="data-card">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Activity History</h2>
            </div>
            <div class="card-body">
                <?php if (empty($history)): ?>
                <div class="empty-state small">
                    <p>No history records yet.</p>
                </div>
                <?php else: ?>
                <div class="timeline" style="padding-left: 24px;">
                    <?php foreach ($history as $log): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <strong><?php echo ACTION_TYPES[$log['action_type']] ?? $log['action_type']; ?></strong>
                            <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 4px 0;">
                                <?php 
                                if ($log['action_type'] === 'STATUS_CHANGE') {
                                    echo 'Changed from ' . $log['old_value'] . ' to ' . $log['new_value'];
                                } elseif ($log['action_type'] === 'DATE_CHANGE') {
                                    $old = json_decode($log['old_value'], true);
                                    $new = json_decode($log['new_value'], true);
                                    echo 'Dates changed from ' . $old['start'] . ' - ' . $old['end'] . ' to ' . $new['start'] . ' - ' . $new['end'];
                                } elseif ($log['action_type'] === 'COMPLIANCE_TAG') {
                                    $new = json_decode($log['new_value'], true);
                                    echo 'Set to ' . $new['status'];
                                    if (!empty($new['remarks'])) {
                                        echo ': ' . $new['remarks'];
                                    }
                                }
                                ?>
                            </p>
                            <small style="color: var(--text-muted);">
                                By <?php echo htmlspecialchars($log['changed_by_name']); ?> • 
                                <?php echo date('M j, Y g:i A', strtotime($log['changed_at'])); ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <!-- Update Status (Procurement only) -->
        <?php if ($auth->canUpdateActivity()): ?>
        <div class="data-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2><i class="fas fa-edit"></i> Update Status</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" required>
                            <?php foreach (ACTIVITY_STATUSES as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo $activity['status'] === $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Compliance Section (Procurement only) -->
        <?php if ($auth->canSetCompliance()): ?>
        <div class="data-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2><i class="fas fa-clipboard-check"></i> Compliance</h2>
            </div>
            <div class="card-body">
                <?php if ($activity['compliance_status']): ?>
                <div style="margin-bottom: 16px; padding: 12px; background: var(--bg-secondary); border-radius: var(--radius-md);">
                    <label style="font-size: 0.75rem; color: var(--text-muted);">Current Status</label>
                    <p>
                        <span class="compliance-badge compliance-<?php echo strtolower(str_replace('_', '-', $activity['compliance_status'])); ?>">
                            <?php echo COMPLIANCE_STATUSES[$activity['compliance_status']] ?? $activity['compliance_status']; ?>
                        </span>
                    </p>
                    <?php if ($activity['compliance_remarks']): ?>
                    <label style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Remarks</label>
                    <p style="font-size: 0.9rem;"><?php echo htmlspecialchars($activity['compliance_remarks']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="set_compliance">
                    <div class="form-group">
                        <label class="form-label">Compliance Status</label>
                        <select name="compliance_status" class="form-control" required id="complianceStatus">
                            <option value="">Select status...</option>
                            <?php foreach (COMPLIANCE_STATUSES as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="remarksGroup" style="display: none;">
                        <label class="form-label">Remarks <span style="color: var(--danger);">*</span></label>
                        <textarea name="compliance_remarks" class="form-control" rows="3" placeholder="Required for Non-Compliant status"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success" style="width: 100%;">
                        <i class="fas fa-check"></i> Set Compliance
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Timeline Adjustment Request -->
        <div class="data-card">
            <div class="card-header">
                <h2><i class="fas fa-calendar-alt"></i> Request Adjustment</h2>
            </div>
            <div class="card-body">
                <?php if ($hasPendingAdjustment): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-clock"></i>
                    <span>There is a pending adjustment request for this activity.</span>
                </div>
                <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="action" value="request_adjustment">
                    <div class="form-group">
                        <label class="form-label">New Start Date</label>
                        <input type="date" name="new_start_date" class="form-control" required 
                               value="<?php echo $activity['planned_start_date']; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">New End Date</label>
                        <input type="date" name="new_end_date" class="form-control" required 
                               value="<?php echo $activity['planned_end_date']; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reason for Adjustment</label>
                        <textarea name="reason" class="form-control" rows="3" required 
                                  placeholder="Please provide the reason for this timeline adjustment request..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </form>
                <?php endif; ?>

                <?php if (!empty($adjustments)): ?>
                <hr style="margin: 20px 0;">
                <h4 style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">Previous Requests</h4>
                <?php foreach ($adjustments as $adj): ?>
                <div style="padding: 10px; background: var(--bg-secondary); border-radius: var(--radius-sm); margin-bottom: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <small><?php echo date('M j, Y', strtotime($adj['created_at'])); ?></small>
                        <span class="status-badge status-<?php echo strtolower($adj['status']); ?>"><?php echo $adj['status']; ?></span>
                    </div>
                    <p style="font-size: 0.85rem; margin: 4px 0;"><?php echo htmlspecialchars(substr($adj['reason'], 0, 100)); ?>...</p>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 8px;
    bottom: 8px;
    width: 2px;
    background: var(--border-color);
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
    padding-left: 24px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 4px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--primary);
    border: 2px solid var(--card-bg);
    box-shadow: 0 0 0 2px var(--primary);
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
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
