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
require_once __DIR__ . '/../models/ActivityDocument.php';
require_once __DIR__ . '/../models/ProjectDocument.php';
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

$documentModel = new ActivityDocument();
$documents = $documentModel->getByActivity($activityId);

// Project documents uploaded by project owner for this activity's step (category matches step_name)
$projectDocModel = new ProjectDocument();
$projectDocuments = $projectDocModel->getByProjectAndCategory($activity['project_id'] ?? 0, $activity['step_name'] ?? '');

$historyModel = new ActivityHistoryLog();
$history = $historyModel->getByActivity($activityId);

$adjustmentModel = new AdjustmentRequest();
$adjustments = $adjustmentModel->getByActivity($activityId);
$hasPendingAdjustment = $adjustmentModel->hasPendingRequest($activityId);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

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

    // Upload Document (Procurement only) - blocked if project not approved
    if ($action === 'upload_document' && $auth->canUploadDocuments() && $projectApproved) {
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
$documents = $documentModel->getByActivity($activityId);
$projectDocuments = $projectDocModel->getByProjectAndCategory($activity['project_id'] ?? 0, $activity['step_name'] ?? '');

// Only include the header (which outputs HTML) after all
// redirects and header() calls above are done.
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $activity['project_id']; ?>" class="back-link">
            Back to Project
        </a>
    </div>
</div>

<!-- Dashboard Overlays -->
<div class="dash-stats">
    <div class="stat-card">
        <div class="stat-label">Remaining Process</div>
        <div class="stat-value"><?php echo $timelineSummary['remaining_steps']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Due Today</div>
        <div class="stat-value" style="color: var(--info);"><?php echo $timelineSummary['due_today_steps']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Overdue Process</div>
        <div class="stat-value" style="color: var(--danger);"><?php echo $timelineSummary['overdue_steps']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Current Cycle</div>
        <div class="stat-value"><?php echo $activity['cycle_number']; ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 380px; gap: 32px; align-items: flex-start;">
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

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; padding: 24px; background: #f9fafb; border-radius: 16px; border: 1px solid #e5e7eb;">
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
        </div>

        <!-- Documents Section -->
        <div class="data-card">
            <div class="card-header">
                <h2>Repository Documents</h2>
                <span style="font-size: 0.85rem; background: #f3f4f6; padding: 4px 10px; border-radius: 6px; font-weight: 600;"><?php echo count($documents) + count($projectDocuments); ?> Total</span>
            </div>
            <div class="card-body">
                <?php if ($auth->canUploadDocuments() && $projectApproved): ?>
                <form method="POST" enctype="multipart/form-data" 
                      style="margin-bottom: 24px; padding: 20px; background: #f9fafb; border-radius: 12px; border: 1px dashed #d1d5db; position: relative; transition: all 0.3s ease;"
                      onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#f0f7ff';"
                      onmouseout="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb';">
                    <input type="hidden" name="action" value="upload_document">
                    <div style="display: flex; gap: 16px; align-items: center;">
                        <input type="file" name="document" class="form-control" style="flex: 1; border-style: none; background: transparent;" required>
                        <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                            Upload New
                        </button>
                    </div>
                </form>
                <?php endif; ?>

                <?php if (empty($documents) && empty($projectDocuments)): ?>
                <div class="empty-state small" style="padding: 40px; text-align: center;">
                    <p style="color: #9ca3af;">No documents in this repository folder yet.</p>
                </div>
                <?php else: ?>
                <div class="docs-grid">
                    <?php foreach ($projectDocuments as $doc): ?>
                    <div class="doc-card">
                        <div class="doc-icon" style="color: var(--primary);"></div>
                        <div class="doc-main">
                            <div class="doc-title"><?php echo htmlspecialchars($doc['original_name']); ?></div>
                            <div class="doc-meta">
                                <span style="color: var(--primary); font-weight: 600;">Project Level</span> &bull;
                                <?php echo htmlspecialchars($doc['uploader_name']); ?> &bull; 
                                <?php echo date('M j, Y', strtotime($doc['uploaded_at'])); ?>
                            </div>
                        </div>
                        <div class="doc-actions">
                            <button type="button" class="btn btn-sm btn-secondary document-preview-btn" 
                                    data-url="<?php echo htmlspecialchars(APP_URL . '/uploads/' . $doc['file_path']); ?>"
                                    data-name="<?php echo htmlspecialchars($doc['original_name']); ?>">
                                View
                            </button>
                            <a href="<?php echo APP_URL; ?>/uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" class="btn btn-sm btn-secondary" target="_blank">
                                Download
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php foreach ($documents as $doc): ?>
                    <div class="doc-card">
                        <div class="doc-icon" style="color: var(--danger);"></div>
                        <div class="doc-main">
                            <div class="doc-title"><?php echo htmlspecialchars($doc['original_name']); ?></div>
                            <div class="doc-meta">
                                <?php echo htmlspecialchars($doc['uploader_name']); ?> &bull; 
                                <?php echo date('M j, Y', strtotime($doc['uploaded_at'])); ?>
                            </div>
                        </div>
                        <div class="doc-actions">
                            <button type="button" class="btn btn-sm btn-secondary document-preview-btn" 
                                    data-url="<?php echo htmlspecialchars(APP_URL . '/uploads/' . $doc['file_path']); ?>"
                                    data-name="<?php echo htmlspecialchars($doc['original_name']); ?>">
                                View
                            </button>
                            <a href="<?php echo APP_URL; ?>/uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" class="btn btn-sm btn-secondary" target="_blank">
                                Download
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- History Log -->
        <div class="data-card">
            <div class="card-header">
                <h2>Process Pulse</h2>
            </div>
            <div class="card-body">
                <?php if (empty($history)): ?>
                <div class="empty-state small">
                    <p>No activity recorded yet.</p>
                </div>
                <?php else: ?>
                <!-- ... existing timeline HTML ... -->
<?php /* Timeline HTML from previous turn remains here */ ?>
                <div class="timeline" style="padding-left: 8px;">
                    <?php foreach ($history as $log): 
                        $icon = 'fa-history';
                        if ($log['action_type'] === 'STATUS_CHANGE') $icon = 'fa-sync-alt';
                        elseif ($log['action_type'] === 'DATE_CHANGE') $icon = 'fa-calendar-alt';
                        elseif ($log['action_type'] === 'COMPLIANCE_TAG') $icon = 'fa-tag';
                        elseif ($log['action_type'] === 'DOCUMENT_UPLOAD') $icon = 'fa-file-upload';
                    ?>
                    <div class="timeline-item">
                        <div class="timeline-marker">
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">
                                <?php echo ACTION_TYPES[$log['action_type']] ?? $log['action_type']; ?>
                            </div>
                            <div class="timeline-desc">
                                <?php 
                                if ($log['action_type'] === 'STATUS_CHANGE') {
                                    echo 'Changed status from <span style="font-weight:600;">' . $log['old_value'] . '</span> to <span style="font-weight:700; color:var(--primary);">' . $log['new_value'] . '</span>';
                                } elseif ($log['action_type'] === 'DATE_CHANGE') {
                                    $old = json_decode($log['old_value'], true);
                                    $new = json_decode($log['new_value'], true);
                                    echo 'Adjusted schedule: <span style="text-decoration:line-through;opacity:0.6;">' . $old['start'] . '</span> &rarr; <span style="font-weight:600;">' . $new['start'] . '</span>';
                                } elseif ($log['action_type'] === 'COMPLIANCE_TAG') {
                                    $new = json_decode($log['new_value'], true);
                                    echo 'Tagged as <span class="compliance-badge compliance-' . strtolower(str_replace('_', '-', $new['status'])) . '" style="padding: 2px 8px; font-size: 0.75rem;">' . (COMPLIANCE_STATUSES[$new['status']] ?? $new['status']) . '</span>';
                                    if (!empty($new['remarks'])) {
                                        echo ' &bull; <span style="font-style:italic; color:var(--text-muted);">"' . htmlspecialchars($new['remarks']) . '"</span>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="timeline-meta">
                                <span class="timeline-user">
                                    <?php echo htmlspecialchars($log['changed_by_name']); ?>
                                </span>
                                <span class="timeline-date">
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
                    <?php else: ?>
                    <button type="button" class="btn btn-secondary" style="width: 100%; border-style: dashed;" onclick="this.style.display='none'; document.getElementById('adjForm').style.display='block';">
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
                                <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('adjForm').style.display='none'; document.querySelector('.btn-secondary[style*=\'dashed\']').style.display='block';">Cancel</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body {
    font-family: 'Inter', sans-serif;
    background-color: #f3f4f6;
}

.page-header {
    margin-bottom: 24px;
    animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.data-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: none;
    margin-bottom: 24px;
    padding: 24px;
    border: 1px solid #e5e7eb;
    position: relative;
    overflow: hidden;
    animation: slideUp 0.6s ease-out both;
}

.stat-card {
    background: #ffffff;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    text-align: left;
}

.stat-icon {
    display: none;
}

.data-card:nth-child(1) { animation-delay: 0.1s; }
.data-card:nth-child(2) { animation-delay: 0.2s; }
.data-card:nth-child(3) { animation-delay: 0.3s; }



.card-header {
    margin-bottom: 24px;
    padding-bottom: 16px;
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: center;
}



.card-header h2 {
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-header h2 i {
    display: none;
}

.card-body {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-label {
    font-size: 0.95rem;
    color: #374151;
    font-weight: 600;
}

.form-control {
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 1rem;
    background: #f9fafb;
    transition: all 0.2s ease;
    color: #111827;
}

.form-control:focus {
    border-color: #154c79;
    background: #ffffff;
    outline: none;
    box-shadow: 0 0 0 4px rgba(21, 76, 121, 0.1);
}

.form-control:disabled {
    background: #f3f4f6;
    color: #6b7280;
    cursor: not-allowed;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 10px;
    padding: 12px 24px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.btn-primary {
    background: #154c79;
    color: #ffffff;
    box-shadow: 0 4px 6px -1px rgba(21, 76, 121, 0.2), 0 2px 4px -1px rgba(21, 76, 121, 0.1);
}

.btn-primary:hover {
    background: #0f3a5e;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(21, 76, 121, 0.3), 0 4px 6px -1px rgba(21, 76, 121, 0.2);
}

.btn-success {
    background: #059669;
    color: #ffffff;
    box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2), 0 2px 4px -1px rgba(5, 150, 105, 0.1);
}

.btn-success:hover {
    background: #047857;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(5, 150, 105, 0.3), 0 4px 6px -1px rgba(5, 150, 105, 0.2);
}

.btn-warning {
    background: #d97706;
    color: #ffffff;
    box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.2), 0 2px 4px -1px rgba(217, 119, 6, 0.1);
}

.btn-warning:hover {
    background: #b45309;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(217, 119, 6, 0.3), 0 4px 6px -1px rgba(217, 119, 6, 0.2);
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover {
    background: #e5e7eb;
    color: #111827;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.85rem;
    border-radius: 6px;
}

.document-item {
    display: flex;
    align-items: center;
    padding: 16px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
}

.document-item:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transform: translateY(-1px);
}

.document-info {
    flex: 1;
    margin-left: 16px;
    margin-right: 16px;
}

.document-name {
    font-weight: 600;
    color: #111827;
    font-size: 1rem;
    margin-bottom: 4px;
}

.document-meta {
    font-size: 0.85rem;
    color: #6b7280;
}

.timeline {
    position: relative;
    margin-top: 10px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 17px;
    top: 8px;
    bottom: 8px;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item {
    position: relative;
    padding-bottom: 28px;
    padding-left: 48px;
    transition: all 0.3s ease;
}

.timeline-item:hover {
    transform: translateX(4px);
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #ffffff;
    border: 2px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #154c79;
    z-index: 1;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.timeline-item:hover .timeline-marker {
    border-color: #154c79;
    background: #154c79;
    color: #ffffff;
    box-shadow: 0 0 0 4px rgba(21, 76, 121, 0.1);
}

.timeline-content {
    background: #ffffff;
    padding: 2px 0;
}

.timeline-title {
    font-weight: 700;
    color: #111827;
    font-size: 1rem;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.timeline-desc {
    font-size: 0.9rem;
    color: #4b5563;
    margin-bottom: 8px;
}

.timeline-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #6b7280;
    font-size: 0.8rem;
}

.timeline-user {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #374151;
    font-weight: 500;
}

.timeline-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    object-fit: cover;
}

.timeline-date {
    display: flex;
    align-items: center;
    gap: 4px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: capitalize;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
/* Dash Stats */
.dash-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: #ffffff;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    border: 1px solid rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    gap: 8px;
    animation: slideUp 0.6s ease-out both;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }

.stat-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 800;
    color: #111827;
}

.stat-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 1.5rem;
    opacity: 0.1;
}

/* Document Item Enhancement */
.docs-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.doc-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s ease;
}

.doc-card:hover {
    background: #ffffff;
    border-color: #154c79;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transform: translateY(-2px);
}

.doc-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
}

.doc-main {
    flex: 1;
    min-width: 0;
}

.doc-title {
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}

.doc-meta {
    font-size: 0.75rem;
    color: #6b7280;
}

.doc-actions {
    display: flex;
    gap: 8px;
}

@media (max-width: 1100px) {
    .dash-stats { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
    .dash-stats { grid-template-columns: 1fr; }
}
</style>

<!-- Document Preview Modal -->
<div id="documentPreviewModal" class="document-preview-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: var(--card-bg, #1a1d24); border-radius: 12px; max-width: 95vw; max-height: 95vh; width: 900px; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--border-color, #2d333b);">
            <h3 id="documentPreviewTitle" style="margin: 0; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Document</h3>
            <div style="display: flex; gap: 8px;">
                <a id="documentPreviewDownload" href="#" target="_blank" class="btn btn-sm btn-primary">Download</a>
                <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('documentPreviewModal').style.display='none'">Close</button>
            </div>
        </div>
        <div id="documentPreviewBody" style="flex: 1; min-height: 400px; overflow: auto; padding: 20px; display: flex; align-items: center; justify-content: center;">
            <iframe id="documentPreviewIframe" style="width: 100%; height: 70vh; border: none; display: none;"></iframe>
            <img id="documentPreviewImg" style="max-width: 100%; max-height: 70vh; object-fit: contain; display: none;" alt="Preview">
            <div id="documentPreviewFallback" style="text-align: center; color: var(--text-muted); display: none; padding: 40px;">
                <p>Preview not available for this file type.</p>
                <a id="documentPreviewFallbackLink" href="#" target="_blank" class="btn btn-primary">Download to view</a>
            </div>
        </div>
    </div>
</div>

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

(function() {
    const PREVIEW_TYPES = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
    document.querySelectorAll('.document-preview-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const url = this.dataset.url;
            const name = this.dataset.name;
            const ext = (name.split('.').pop() || '').toLowerCase();
            const modal = document.getElementById('documentPreviewModal');
            const iframe = document.getElementById('documentPreviewIframe');
            const img = document.getElementById('documentPreviewImg');
            const fallback = document.getElementById('documentPreviewFallback');
            const fallbackLink = document.getElementById('documentPreviewFallbackLink');
            const downloadBtn = document.getElementById('documentPreviewDownload');

            document.getElementById('documentPreviewTitle').textContent = name;
            downloadBtn.href = url;
            fallbackLink.href = url;

            iframe.style.display = 'none';
            img.style.display = 'none';
            fallback.style.display = 'none';

            if (PREVIEW_TYPES.includes(ext)) {
                if (ext === 'pdf') {
                    iframe.src = url;
                    iframe.style.display = 'block';
                } else {
                    img.src = url;
                    img.style.display = 'block';
                }
            } else {
                fallback.style.display = 'block';
            }

            modal.style.display = 'flex';
        });
    });
    document.getElementById('documentPreviewModal')?.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
