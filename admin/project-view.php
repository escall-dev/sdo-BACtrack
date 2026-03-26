<?php
/**
 * View Project
 * SDO-BACtrack - Project Owners see only their own projects
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/timeline.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/BacCycle.php';
require_once __DIR__ . '/../models/ProjectActivity.php';
require_once __DIR__ . '/../models/ProjectDocument.php';
require_once __DIR__ . '/../models/Notification.php';

$auth = auth();
$auth->requireLogin();

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$projectModel = new Project();
$project = $projectModel->findById($projectId);

// Handle approve action (BAC Secretary only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'approve_project' && $auth->isBacSecretary() && $projectId) {
    if ($project && ($project['approval_status'] ?? 'APPROVED') === 'PENDING_APPROVAL') {
        $projectModel->approve($projectId);
        setFlashMessage('success', 'Project approved. Progress can now be tracked.');
    }
    $auth->redirect(APP_URL . '/admin/project-view.php?id=' . $projectId);
}

// Handle reject action (BAC Secretary only) - remarks required
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reject_project' && $auth->isBacSecretary() && $projectId) {
    $remarks = trim($_POST['rejection_remarks'] ?? '');
    if ($project && ($project['approval_status'] ?? 'APPROVED') === 'PENDING_APPROVAL') {
        if (empty($remarks)) {
            setFlashMessage('error', 'Remarks or reason are required when declining a project.');
        } else {
            $projectModel->reject($projectId, $remarks, $auth->getUserId());
            $notificationModel = new Notification();
            $notificationModel->notifyProjectRejected($projectId, $project['title'], $remarks, (int)$project['created_by']);
            setFlashMessage('success', 'Project declined. The project owner has been notified with your remarks.');
        }
    }
    $auth->redirect(APP_URL . '/admin/project-view.php?id=' . $projectId);
}

// Handle project document upload (project owner only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_project_document' && $auth->isProjectOwner() && $projectId) {
    if ($project && (int)$project['created_by'] === (int)$auth->getUserId()) {
        $category = trim($_POST['category'] ?? 'Other');
        $description = trim($_POST['description'] ?? '');
        if (isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $docModel = new ProjectDocument();
                $docModel->upload($_FILES['document'], $projectId, $category, $auth->getUserId(), $description ?: null);
                setFlashMessage('success', 'Document uploaded successfully.');
            } catch (Exception $e) {
                setFlashMessage('error', 'Failed to upload: ' . $e->getMessage());
            }
        } else {
            setFlashMessage('error', 'Please select a file to upload.');
        }
    }
    $auth->redirect(APP_URL . '/admin/project-view.php?id=' . $projectId);
}

// Handle project document delete (project owner only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_project_document' && $auth->isProjectOwner() && $projectId) {
    $docId = (int)($_POST['document_id'] ?? 0);
    if ($docId && $project && (int)$project['created_by'] === (int)$auth->getUserId()) {
        $docModel = new ProjectDocument();
        $doc = $docModel->findById($docId);
        if ($doc && (int)$doc['project_id'] === $projectId && (int)$doc['uploaded_by'] === (int)$auth->getUserId()) {
            $docModel->delete($docId);
            setFlashMessage('success', 'Document removed.');
        }
    }
    $auth->redirect(APP_URL . '/admin/project-view.php?id=' . $projectId);
}

// Handle submit for BAC review (project owner only, DRAFT only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_for_review' && $auth->isProjectOwner() && $projectId) {
    $startDate = trim($_POST['project_start_date'] ?? '');
    if ($project && ($project['approval_status'] ?? '') === 'DRAFT' && (int)$project['created_by'] === (int)$auth->getUserId()) {
        if (empty($startDate)) {
            setFlashMessage('error', 'Project start date is required to submit for review.');
        } else {
            try {
                $projectModel->submitForReview($projectId, $startDate);
                setFlashMessage('success', 'Project submitted for BAC review. Timeline has been generated.');
            } catch (Exception $e) {
                setFlashMessage('error', 'Failed to submit: ' . $e->getMessage());
            }
        }
    }
    $auth->redirect(APP_URL . '/admin/project-view.php?id=' . $projectId);
}

if (!$project) {
    setFlashMessage('error', 'Project not found.');
    $auth->redirect(APP_URL . '/admin/projects.php');
}

// Project Owners can only view their own projects (privacy)
if ($auth->isProjectOwner() && (int)$project['created_by'] !== (int)$auth->getUserId()) {
    setFlashMessage('error', 'You do not have access to this project.');
    $auth->redirect(APP_URL . '/admin/projects.php');
}

$cycleModel = new BacCycle();
$cycles = $cycleModel->getByProject($projectId);
$activeCycle = $cycleModel->getActiveCycle($projectId);

$activityModel = new ProjectActivity();
$activities = $activeCycle ? $activityModel->getByCycle($activeCycle['id']) : [];

// Calculate statistics
$stats = [
    'total' => count($activities),
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'delayed' => 0
];

foreach ($activities as $activity) {
    $status = strtolower($activity['status']);
    if (isset($stats[$status])) {
        $stats[$status]++;
    }
}

$progress = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;
$isDraft = ($project['approval_status'] ?? '') === 'DRAFT';
$timelineSummary = timelineProjectSummary($activities);
$activityTiming = $timelineSummary['meta_by_id'];
$currentActivity = $timelineSummary['current_activity'];
$nextActivity = $timelineSummary['next_activity'];

$docModel = new ProjectDocument();
$projectDocuments = $docModel->getByProjectGrouped($projectId);
$documentCategories = $docModel->getCategories($project['procurement_type'] ?? 'PUBLIC_BIDDING');
$isPendingApproval = ($project['approval_status'] ?? 'APPROVED') === 'PENDING_APPROVAL';
$isRejected = ($project['approval_status'] ?? '') === 'REJECTED';

$requiredDocs = [
    'Memorandum',
    'Source of Fund (SAO)',
    'Project Proposal',
    'Signed RFQ (Request for Quotation)',
];
$uploadedCategories = array_keys($projectDocuments);

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* Premium Container Styles for Project View */
.pv-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    box-shadow: 0 10px 25px -5px rgba(15, 76, 117, 0.04), 0 8px 10px -6px rgba(15, 76, 117, 0.02);
    margin-bottom: 24px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pv-card:hover {
    box-shadow: 0 20px 25px -5px rgba(15, 76, 117, 0.08), 0 10px 10px -5px rgba(15, 76, 117, 0.04);
    transform: translateY(-2px);
}
.pv-card-header {
    background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 16px 20px;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.pv-card-header h2 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pv-card-header h2 i {
    color: var(--primary);
    font-size: 1.2rem;
}
.pv-card-body {
    padding: 20px;
}

/* Info Tiles */
.pv-info-grid {
}
.pv-info-tile {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    transition: background 0.2s, border-color 0.2s;
}
.pv-info-tile:hover {
    background: #f0f7ff;
    border-color: #bfdbfe;
}
.pv-info-label {
    font-size: 0.72rem;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    display: block;
}
.pv-info-value {
    font-weight: 600;
    color: #0f172a;
    font-size: 0.95rem;
    margin: 0;
}

/* Progress Bar */
.pv-progress-wrap {
    background: #e2e8f0;
    border-radius: 999px;
    height: 12px;
    margin-bottom: 24px;
    overflow: hidden;
    position: relative;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

/* Procurement table borders */
.proc-table { width:100%; border-collapse: collapse; border: 1px solid #e6eef6; }
.proc-table th, .proc-table td { border: 1px solid #e6eef6; padding: 8px; vertical-align: top; }
.proc-subtable { width:100%; border-collapse: collapse; }
.proc-subtable td { border: none; padding: 2px 0; }
.proc-col-step { width:6%; text-align:center; }
.proc-col-process { width:24%; text-align:left; }
.proc-col-activities { width:46%; text-align:left; }
.proc-col-timeline { width:8%; text-align:center; }
.proc-col-conditions { width:16%; text-align:right; }
.pv-progress-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
    transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
}

/* Stat Blocks */
.pv-stat-grid {
}
.pv-stat-block {
    text-align: center;
    padding: 16px 12px;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pv-stat-block:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.pv-stat-num {
    font-size: 1.75rem;
    font-weight: 800;
    margin-bottom: 4px;
    line-height: 1;
}
.pv-stat-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.pv-stat-pending { border-top: 3px solid #f59e0b; }
.pv-stat-pending .pv-stat-num { color: #d97706; }
.pv-stat-pending .pv-stat-label { color: #b45309; }

.pv-stat-progress { border-top: 3px solid #3b82f6; }
.pv-stat-progress .pv-stat-num { color: #2563eb; }
.pv-stat-progress .pv-stat-label { color: #1d4ed8; }

.pv-stat-completed { border-top: 3px solid #10b981; }
.pv-stat-completed .pv-stat-num { color: #059669; }
.pv-stat-completed .pv-stat-label { color: #047857; }

.pv-stat-delayed { border-top: 3px solid #ef4444; }
.pv-stat-delayed .pv-stat-num { color: #dc2626; }
.pv-stat-delayed .pv-stat-label { color: #b91c1c; }
</style>

<div class="page-header">
    <div>
        <a href="<?php echo APP_URL; ?>/admin/projects.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <?php if ($auth->isProjectOwner() && $isDraft): ?>
        <form method="POST" style="margin: 0; display: inline;" id="submitForReviewForm">
            <input type="hidden" name="action" value="submit_for_review">
            <input type="date" name="project_start_date" value="<?php echo htmlspecialchars($project['project_start_date'] ?? date('Y-m-d')); ?>" required style="margin-right: 8px; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color);">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Submit for BAC Review
            </button>
        </form>
        <a href="<?php echo APP_URL; ?>/admin/project-edit.php?id=<?php echo $projectId; ?>" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Edit Draft
        </a>
        <?php elseif ($auth->isBacSecretary() && $isPendingApproval): ?>
        <form method="POST" style="margin: 0; display: inline;">
            <input type="hidden" name="action" value="approve_project">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check"></i> Accept
            </button>
        </form>
        <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectForm').style.display=document.getElementById('rejectForm').style.display==='none'?'block':'none'">
            <i class="fas fa-times"></i> Decline
        </button>
        <?php endif; ?>
        <?php if ($auth->isProcurement()): ?>
        <a href="<?php echo APP_URL; ?>/admin/calendar.php?project=<?php echo $projectId; ?>" class="btn btn-secondary">
            <i class="fas fa-calendar"></i> Calendar
        </a>
        <?php endif; ?>
        <a href="<?php echo APP_URL; ?>/admin/reports.php?project=<?php echo $projectId; ?>" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Report
        </a>
    </div>
</div>

<div class="grid-main-sidebar">
    <div>
        <!-- Project Info -->
        <div class="pv-card">
            <div class="pv-card-header">
                <h2><i class="fas fa-info-circle"></i> Project Information</h2>
                <?php if (isset($project['approval_status'])): ?>
                <span class="status-badge status-<?php echo strtolower(str_replace('_', '-', $project['approval_status'])); ?>">
                    <?php echo PROJECT_APPROVAL_STATUSES[$project['approval_status']] ?? $project['approval_status']; ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="pv-card-body">
                <?php if ($isDraft): ?>
                <div class="alert alert-info" style="margin-bottom: 16px;">
                    <i class="fas fa-file-alt"></i>
                    <span><strong>Draft project.</strong> BAC can review this project before you submit. When ready, use "Submit for BAC Review" above to generate the timeline and send for approval.</span>
                </div>
                <?php if ($auth->isProcurement()): ?>
                <div class="alert alert-secondary" style="margin-bottom: 16px; background: var(--bg-secondary);">
                    <i class="fas fa-eye"></i>
                    <span>You are reviewing this draft. The project owner will submit it for approval when ready. No Accept/Decline until submitted.</span>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($isPendingApproval): ?>
                <div class="alert alert-warning" style="margin-bottom: 16px;">
                    <i class="fas fa-clock"></i>
                    <span>This project is awaiting BAC approval. Progress cannot be updated until a BAC member accepts or declines it.</span>
                </div>
                <?php if ($auth->isBacSecretary()): ?>
                <div id="rejectForm" style="display: none; margin-bottom: 16px; padding: 16px; background: var(--danger-bg); border-radius: var(--radius-md); border: 1px solid rgba(239,68,68,0.3);">
                    <form method="POST">
                        <input type="hidden" name="action" value="reject_project">
                        <label class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600;">Reason for decline <span style="color: var(--danger);">*</span></label>
                        <textarea name="rejection_remarks" class="form-control" rows="4" required placeholder="Please provide the reason for declining this project. This will be shown to the project owner."></textarea>
                        <div style="display: flex; gap: 8px; margin-top: 12px;">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times"></i> Submit Decline
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('rejectForm').style.display='none'">Cancel</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($isRejected && !empty($project['rejection_remarks'])): ?>
                <div class="alert alert-danger" style="margin-bottom: 16px;">
                    <i class="fas fa-times-circle"></i>
                    <div>
                        <strong>This project was declined by BAC.</strong>
                        <p style="margin: 8px 0 0;"><?php echo nl2br(htmlspecialchars($project['rejection_remarks'])); ?></p>
                        <?php if (!empty($project['rejected_by_name'])): ?>
                        <small style="display: block; margin-top: 8px; opacity: 0.9;">Declined by <?php echo htmlspecialchars($project['rejected_by_name']); ?><?php echo !empty($project['rejected_at']) ? ' on ' . date('M j, Y g:i A', strtotime($project['rejected_at'])) : ''; ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <h3 style="font-size: 1.5rem; margin-bottom: 8px;"><?php echo htmlspecialchars($project['title']); ?></h3>
                <?php if ($project['description']): ?>
                <p style="color: var(--text-secondary); margin-bottom: 16px;"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                <?php endif; ?>
                
                <div class="pv-info-grid grid-3-col">
                    <div class="pv-info-tile">
                        <span class="pv-info-label">Procurement Type</span>
                        <p class="pv-info-value"><?php echo PROCUREMENT_TYPES[$project['procurement_type']] ?? $project['procurement_type']; ?></p>
                    </div>
                    <div class="pv-info-tile">
                        <span class="pv-info-label">Created By</span>
                        <div class="user-cell" style="margin-top: 4px;">
                            <?php if (!empty($project['creator_avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($project['creator_avatar']); ?>" alt="Avatar" class="user-avatar-sm">
                            <?php else: ?>
                            <div class="user-avatar-placeholder-sm">
                                <?php echo strtoupper(substr($project['creator_name'], 0, 1)); ?>
                            </div>
                            <?php endif; ?>
                            <span class="pv-info-value"><?php echo htmlspecialchars($project['creator_name']); ?></span>
                        </div>
                    </div>
                    <div class="pv-info-tile">
                        <span class="pv-info-label">Created Date</span>
                        <p class="pv-info-value"><?php echo date('F j, Y', strtotime($project['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if (($project['procurement_type'] ?? '') === 'SMALL_VALUE_PROCUREMENT_200K'): ?>
        <!-- Procurement Process (Small Value Procurement 200k & Above) -->
        <div class="pv-card">
            <div class="pv-card-header">
                <h2><i class="fas fa-file-contract"></i> Procurement Process — Small Value Procurement (₱200,000 & Above)</h2>
            </div>
            <div class="pv-card-body">
                <div style="overflow:auto;">
                    <table class="table proc-table">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th class="proc-col-step">Step</th>
                                <th class="proc-col-process">Process</th>
                                <th class="proc-col-activities">Activities</th>
                                <th class="proc-col-timeline">Timeline</th>
                                <th class="proc-col-conditions">Conditions / Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php
                                    $remark = 'Receipt by BAC of approved PR initiates process. Activity Proposal required for specific activities.';
                                    $isNonCompliance = preg_match('/(omitted|dispensed|may be omitted|may be dispensed)/i', $remark);
                                    $statusLabel = $isNonCompliance ? 'Non-Compliance' : 'Compliance';
                                ?>
                                <td style="padding:8px; vertical-align:top;">1</td>
                                <td style="padding:8px; vertical-align:top;">Preparation of Purchase Request</td>
                                <td style="padding:8px; vertical-align:top;">Prepares activity proposal and Purchase Request (PR); Budget Clearance; Approves PR and/or Activity Proposal</td>
                                <td style="padding:8px; vertical-align:top;">0 day</td>
                                <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars($remark); ?></td>
                            </tr>
                            <tr>
                                <?php
                                    $remark = '';
                                    $isNonCompliance = $remark && preg_match('/(omitted|dispensed|may be omitted|may be dispensed)/i', $remark);
                                    $statusLabel = $remark ? ($isNonCompliance ? 'Non-Compliance' : 'Compliance') : '';
                                ?>
                                <td style="padding:8px; vertical-align:top;">2</td>
                                <td style="padding:8px; vertical-align:top;">Submission and Receipt of Approved PR</td>
                                <td style="padding:8px; vertical-align:top;">Submits the approved PR to BAC Secretariat for procurement</td>
                                <td style="padding:8px; vertical-align:top;">1 day</td>
                                <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars($remark); ?></td>
                            </tr>
                            <tr>
                                <?php
                                    $remark = '';
                                    $isNonCompliance = $remark && preg_match('/(omitted|dispensed|may be omitted|may be dispensed)/i', $remark);
                                    $statusLabel = $remark ? ($isNonCompliance ? 'Non-Compliance' : 'Compliance') : '';
                                ?>
                                <td style="padding:8px; vertical-align:top;">3</td>
                                <td style="padding:8px; vertical-align:top;">Preparation of Request for Quotation (RFQ)</td>
                                <td style="padding:8px; vertical-align:top;">Prepares Request for Quotation; BAC Chair signs RFQ</td>
                                <td style="padding:8px; vertical-align:top;">1 day; 3 days (signing)</td>
                                <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars($remark); ?></td>
                            </tr>
                            <tr>
                                <?php
                                    $remark = 'Posting in PhilGEPS may be dispensed with for PRs with approved budget of ₱200,000.00 and below.';
                                    $isNonCompliance = preg_match('/(omitted|dispensed|may be omitted|may be dispensed)/i', $remark);
                                    $statusLabel = $isNonCompliance ? 'Non-Compliance' : 'Compliance';
                                ?>
                                <td style="padding:8px; vertical-align:top;">4</td>
                                <td style="padding:8px; vertical-align:top;">Posting of RFQ or Conduct of Canvass</td>
                                <td style="padding:8px; vertical-align:top;">Posts RFQ on website, PhilGEPS, and bulletin board; Sends RFQ to at least three (3) suppliers</td>
                                <td style="padding:8px; vertical-align:top;">3 days</td>
                                <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars($remark); ?></td>
                            </tr>
                            <tr>
                                <?php
                                    $remark = '';
                                    $isNonCompliance = $remark && preg_match('/(omitted|dispensed|may be omitted|may be dispensed)/i', $remark);
                                    $statusLabel = $remark ? ($isNonCompliance ? 'Non-Compliance' : 'Compliance') : '';
                                ?>
                                <td style="padding:8px; vertical-align:top;">5</td>
                                <td style="padding:8px; vertical-align:top;">Preparation of Abstract of Quotation / Resolution to Award</td>
                                <td style="padding:8px; vertical-align:top;">Prepares and signs Abstract of Quotation / Resolution to Award; Approves and signs Abstract of Quotation</td>
                                <td style="padding:8px; vertical-align:top;">1 day; 3 days</td>
                                <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars($remark); ?></td>
                            </tr>
                            <tr>
                                <?php
                                    $remark = 'Issuance of NOA may be omitted for ₱50,000.00 and below.';
                                    $isNonCompliance = preg_match('/(omitted|dispensed|may be omitted|may be dispensed)/i', $remark);
                                    $statusLabel = $isNonCompliance ? 'Non-Compliance' : 'Compliance';
                                ?>
                                <td style="padding:8px; vertical-align:top;">6</td>
                                <td style="padding:8px; vertical-align:top;">Notice of Award</td>
                                <td style="padding:8px; vertical-align:top;">Prepares Notice of Award</td>
                                <td style="padding:8px; vertical-align:top;">2 days</td>
                                <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars($remark); ?></td>
                            </tr>
                            <tr>
                                <?php
                                    $remark = '';
                                    $isNonCompliance = $remark && preg_match('/(omitted|dispensed|may be omitted|may be dispensed)/i', $remark);
                                    $statusLabel = $remark ? ($isNonCompliance ? 'Non-Compliance' : 'Compliance') : '';
                                ?>
                                <td style="padding:8px; vertical-align:top;">7</td>
                                <td style="padding:8px; vertical-align:top;">Preparation and Approval of Purchase Order (PO)</td>
                                <td style="padding:8px; vertical-align:top;">Signs (conforme) Notice of Award; Prepares Purchase Order; Signs (conforme) Purchase Order; Certifies Availability of Funds; HoPE approves Purchase Order</td>
                                <td style="padding:8px; vertical-align:top;">4 days</td>
                                <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars($remark); ?></td>
                            </tr>
                            <tr>
                                <?php
                                    $remark = 'Issuance of NTP may be omitted for PRs with approved budget of ₱50,000.00 and below.';
                                    $isNonCompliance = preg_match('/(omitted|dispensed|may be omitted|may be dispensed)/i', $remark);
                                    $statusLabel = $isNonCompliance ? 'Non-Compliance' : 'Compliance';
                                ?>
                                <td style="padding:8px; vertical-align:top;">8</td>
                                <td style="padding:8px; vertical-align:top;">Preparation and Signing of Notice to Proceed</td>
                                <td style="padding:8px; vertical-align:top;">Prepares Notice to Proceed; Signs and Approves Notice to Proceed; Signs (conforme) Notice to Proceed; Submits PR and PO to COA</td>
                                <td style="padding:8px; vertical-align:top;">2 days</td>
                                <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars($remark); ?></td>
                            </tr>
                            <tr>
                                <?php
                                    $remark = 'Contract implementation begins upon issuance of approved PO and/or NTP.';
                                    $isNonCompliance = preg_match('/(omitted|dispensed|may be omitted|may be dispensed)/i', $remark);
                                    $statusLabel = $isNonCompliance ? 'Non-Compliance' : 'Compliance';
                                ?>
                                <td style="padding:8px; vertical-align:top;">9</td>
                                <td style="padding:8px; vertical-align:top;">Allowance period of the supplier</td>
                                <td style="padding:8px; vertical-align:top;">For the date of delivery of goods or date of activity.</td>
                                <td style="padding:8px; vertical-align:top;">10 days</td>
                                <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars($remark); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="padding:8px; font-weight:700;">TOTAL TIME</td>
                                <td style="padding:8px; font-weight:700;">30 working days</td>
                                <td style="padding:8px; font-weight:700;">(For ₱200,000 and above)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (($project['procurement_type'] ?? '') === 'SMALL_VALUE_PROCUREMENT'): ?>
        <!-- Annex B: Procurement Process (Small Value Procurement 200k & Below) -->
        <div class="pv-card">
            <div class="pv-card-header">
                <h2><i class="fas fa-file-contract"></i> Annex B: Process Flow Chart — Small Value Procurement (₱200,000 and below)</h2>
            </div>
            <div class="pv-card-body">
                <div style="overflow:auto;">
                    <table class="table proc-table">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="padding:8px; text-align:left;">Step</th>
                                <th style="padding:8px; text-align:left;">Process</th>
                                <th style="padding:8px; text-align:left;">Activities</th>
                                <th style="padding:8px; text-align:left;">Timeline</th>
                                <th style="padding:8px; text-align:left;">Conditions / Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="proc-col-step">1</td>
                                <td class="proc-col-process">Preparation of Purchase Request</td>
                                <td class="proc-col-activities">
                                    <table class="proc-subtable">
                                        <tr><td>Prepares activity proposal and Purchase Request (PR)</td></tr>
                                        <tr><td>Budget Clearance</td></tr>
                                        <tr><td>Approves PR and/or Activity Proposal</td></tr>
                                    </table>
                                </td>
                                <td class="proc-col-timeline">0 day</td>
                                <td class="proc-col-conditions">Receipt by BAC of the approved PR initiates procurement process. Activity Proposal required for specific activities.</td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">2</td>
                                <td class="proc-col-process">Submission and Receipt of Approved Purchase Request</td>
                                <td class="proc-col-activities">Submits the approved PR and other attachments (Proposal, Memo and SARO if applicable) to BAC Secretariat</td>
                                <td class="proc-col-timeline">1 day</td>
                                <td class="proc-col-conditions"></td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">3</td>
                                <td class="proc-col-process">Preparation of Request for Quotation (RFQ)</td>
                                <td class="proc-col-activities">
                                    <table class="proc-subtable">
                                        <tr><td>Prepares Request for Quotation</td></tr>
                                        <tr><td>BAC Chair signs RFQ</td></tr>
                                    </table>
                                </td>
                                <td class="proc-col-timeline">1 day / 3 days (signing)</td>
                                <td class="proc-col-conditions">Posting in PhilGEPS may be dispensed with for PRs with approved budget of ₱200,000.00 and below.</td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">4</td>
                                <td class="proc-col-process">Posting of RFQ or Conduct of Canvass</td>
                                <td class="proc-col-activities">
                                    <table class="proc-subtable">
                                        <tr><td>Posts RFQ in the website, PhilGEPS, and bulletin board</td></tr>
                                        <tr><td>Sends RFQ to at least three (3) suppliers</td></tr>
                                    </table>
                                </td>
                                <td class="proc-col-timeline">3 days</td>
                                <td class="proc-col-conditions"></td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">5</td>
                                <td class="proc-col-process">Opening of bids documents / Preparation of Abstract of Quotation</td>
                                <td class="proc-col-activities">
                                    <table class="proc-subtable">
                                        <tr><td>Prepares and signs Abstract of Quotation / Resolution to Award</td></tr>
                                        <tr><td>Approves and signs Abstract of Quotation</td></tr>
                                    </table>
                                </td>
                                <td class="proc-col-timeline">1 day</td>
                                <td class="proc-col-conditions"></td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">6</td>
                                <td class="proc-col-process">Preparation and Approval of Purchase Order (PO)</td>
                                <td class="proc-col-activities">
                                    <table class="proc-subtable">
                                        <tr><td>Prepares Purchase Order</td></tr>
                                        <tr><td>Signs (conforme) Purchase Order</td></tr>
                                        <tr><td>Certifies Availability of Funds</td></tr>
                                        <tr><td>HoPE approves Purchase Order</td></tr>
                                    </table>
                                </td>
                                <td class="proc-col-timeline">4 days</td>
                                <td class="proc-col-conditions"></td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">7</td>
                                <td class="proc-col-process">Allowance period of the supplier</td>
                                <td class="proc-col-activities">For the date of delivery of goods or date of activity.</td>
                                <td class="proc-col-timeline">10 days</td>
                                <td class="proc-col-conditions"></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="padding:8px; font-weight:700;">TOTAL TIME</td>
                                <td class="proc-col-timeline" style="font-weight:700;">23 working days</td>
                                <td class="proc-col-conditions" style="font-weight:700;">Before intended delivery (₱200,000 and below)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (($project['procurement_type'] ?? '') === 'COMPETITIVE_BIDDING'): ?>
        <!-- Annex A: Procurement Process (Competitive Bidding) -->
        <div class="pv-card">
            <div class="pv-card-header">
                <h2><i class="fas fa-file-contract"></i> Annex A: Process Flow Chart for Procurement under Competitive Bidding</h2>
            </div>
            <div class="pv-card-body">
                <div style="overflow:auto;">
                    <table class="table proc-table">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="padding:8px; text-align:left;">Step</th>
                                <th style="padding:8px; text-align:left;">Process</th>
                                <th style="padding:8px; text-align:left;">Activities</th>
                                <th style="padding:8px; text-align:left;">Timeline</th>
                                <th style="padding:8px; text-align:left;">Conditions / Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="proc-col-step">1</td>
                                <td class="proc-col-process">Preparation of Bidding Documents</td>
                                <td class="proc-col-activities">Prepares the bidding documents according to the prescribed template and based on intended specifications.</td>
                                <td class="proc-col-timeline">0</td>
                                <td class="proc-col-conditions"></td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">2</td>
                                <td class="proc-col-process">Pre-Procurement Conference</td>
                                <td class="proc-col-activities">The BAC, with the End-User convenes to finalize preparation for the procurement.</td>
                                <td class="proc-col-timeline">1 day</td>
                                <td class="proc-col-conditions">Optional for ABC of ₱2,000,000.00 and below.</td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">3</td>
                                <td class="proc-col-process">Advertisement / Posting of Invitation to Bid</td>
                                <td class="proc-col-activities">Posts Invitation to Bid in the PhilGEPS, Website and Bulletin Board. Start of Availability of Bidding Documents.</td>
                                <td class="proc-col-timeline">7 days</td>
                                <td class="proc-col-conditions">Start of Availability of Bidding Documents.</td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">4</td>
                                <td class="proc-col-process">Pre-Bid Conference</td>
                                <td class="proc-col-activities">The BAC, with TWG, Secretariat, End-user, discusses with prospective bidder's details of the procurement project, make clarification, if necessary.</td>
                                <td class="proc-col-timeline">12 days</td>
                                <td class="proc-col-conditions">Optional for ABC below ₱1,000,000.00. Not earlier than 7 CDs from Advertisement/Posting. 12 CDs before Deadline of Submission and Receipt of Bids.</td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">5</td>
                                <td class="proc-col-process">Eligibility Check / Deadline of Submission and Receipt of Bids / Bid Opening</td>
                                <td class="proc-col-activities">Determine if bidders meet legal, technical, financial requirements.<br>Prospective bidders submit their bids at the designated time and place.<br>BAC opens bids received.<br>Preliminary examination of bids.</td>
                                <td class="proc-col-timeline"></td>
                                <td class="proc-col-conditions">Last day of Availability of Bidding Documents.</td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">6</td>
                                <td class="proc-col-process">Bid Evaluation</td>
                                <td class="proc-col-activities">Evaluates completeness of bids and make mathematical correction, if any.</td>
                                <td class="proc-col-timeline">1 day</td>
                                <td class="proc-col-conditions"></td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">7</td>
                                <td class="proc-col-process">Post-Qualification</td>
                                <td class="proc-col-activities">Evaluates technical, financial and legal capacity of the bidders, and its bids responsiveness to the requirements of the project.</td>
                                <td class="proc-col-timeline">12 days</td>
                                <td class="proc-col-conditions">The bidder must submit all Post Qualification Requirements within 5 CDs from receipt of notice as bidder with LCB in accordance with Sec. 34.2.</td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">8</td>
                                <td class="proc-col-process">Preparation and Approval of Resolution to Award</td>
                                <td class="proc-col-activities">Prepares and sign resolution to award.<br>Approves and signs Resolution to Award.</td>
                                <td class="proc-col-timeline">11 days</td>
                                <td class="proc-col-conditions"></td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">9</td>
                                <td class="proc-col-process">Issuance and Signing of Notice of Award</td>
                                <td class="proc-col-activities">Prepares Notice of Award.<br>Approves and Signs Notice of Award.<br>Signs (conforme) Notice of Award.</td>
                                <td class="proc-col-timeline"></td>
                                <td class="proc-col-conditions"></td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">10</td>
                                <td class="proc-col-process">Contract Preparation and Signing of Contract</td>
                                <td class="proc-col-activities">Signs Contract.</td>
                                <td class="proc-col-timeline">11 days</td>
                                <td class="proc-col-conditions">If necessary.</td>
                            </tr>
                            <tr>
                                <td class="proc-col-step">11</td>
                                <td class="proc-col-process">Issuance and Signing of Notice to Proceed</td>
                                <td class="proc-col-activities">Issues NTP to the winning bidder.</td>
                                <td class="proc-col-timeline"></td>
                                <td class="proc-col-conditions"></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="padding:8px; font-weight:700;">TOTAL TIME</td>
                                <td class="proc-col-timeline" style="font-weight:700;">55 calendar days</td>
                                <td class="proc-col-conditions" style="font-weight:700;"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Notable Observations removed -->
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($activities)): ?>
        <div class="pv-card">
            <div class="pv-card-header">
                <h2><i class="fas fa-route"></i> Process Awareness</h2>
            </div>
            <div class="pv-card-body">
                <div style="display: grid; gap: 12px;">
                    <div style="padding: 14px; background: var(--bg-secondary); border-radius: var(--radius-md);">
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Current Process</label>
                        <?php if ($currentActivity): ?>
                        <div style="font-weight: 700; margin-top: 4px;"><?php echo htmlspecialchars($currentActivity['step_name']); ?></div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 4px;">
                            <?php echo htmlspecialchars($activityTiming[$currentActivity['id']]['timing_label'] ?? ''); ?>
                        </div>
                        <?php else: ?>
                        <div style="font-weight: 700; margin-top: 4px;">All process completed</div>
                        <?php endif; ?>
                    </div>

                    <div style="padding: 14px; background: var(--bg-secondary); border-radius: var(--radius-md);">
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Next Process</label>
                        <?php if ($nextActivity): ?>
                        <div style="font-weight: 700; margin-top: 4px;"><?php echo htmlspecialchars($nextActivity['step_name']); ?></div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 4px;">
                            <?php echo htmlspecialchars($activityTiming[$nextActivity['id']]['timing_label'] ?? ''); ?>
                        </div>
                        <?php else: ?>
                        <div style="font-weight: 700; margin-top: 4px;">No next process pending</div>
                        <?php endif; ?>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <div style="text-align: center; padding: 12px; background: var(--warning-bg); border-radius: var(--radius-md);">
                            <div style="font-size: 1.4rem; font-weight: 700; color: var(--warning);"><?php echo $timelineSummary['remaining_steps']; ?></div>
                            <small style="color: var(--text-muted);">Remaining</small>
                        </div>
                        <div style="text-align: center; padding: 12px; background: var(--info-bg); border-radius: var(--radius-md);">
                            <div style="font-size: 1.4rem; font-weight: 700; color: var(--info);"><?php echo $timelineSummary['due_today_steps']; ?></div>
                            <small style="color: var(--text-muted);">Due Today</small>
                        </div>
                        <div style="text-align: center; padding: 12px; background: var(--danger-bg); border-radius: var(--radius-md);">
                            <div style="font-size: 1.4rem; font-weight: 700; color: var(--danger);"><?php echo $timelineSummary['overdue_steps']; ?></div>
                            <small style="color: var(--text-muted);">Overdue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Process List -->
        <div class="pv-card">
            <div class="pv-card-header">
                <h2><i class="fas fa-tasks"></i> BAC Process</h2>
            </div>
            <div class="pv-card-body" style="padding: 0;">
                <?php if (empty($activities)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-tasks"></i></div>
                    <h3>No process found</h3>
                    <p>This project has no process generated yet.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Process Name</th>
                                <th>Planned Start</th>
                                <th>Planned End</th>
                                <th>Duration (Days)</th>
                                <th style="text-align: center;">Status</th>
                                <th>Compliance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?php echo $activity['step_order']; ?></td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $activity['id']; ?>" 
                                       style="color: var(--primary); font-weight: 500; text-decoration: none;">
                                        <?php echo htmlspecialchars($activity['step_name']); ?>
                                    </a>
                                    <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap; margin-top: 6px;">
                                        <?php if ($currentActivity && (int)$currentActivity['id'] === (int)$activity['id']): ?>
                                        <span class="status-badge" style="background: var(--info-bg); color: var(--info);">Current</span>
                                        <?php elseif ($nextActivity && (int)$nextActivity['id'] === (int)$activity['id']): ?>
                                        <span class="status-badge" style="background: var(--warning-bg); color: #b45309;">Next</span>
                                        <?php endif; ?>
                                        <small style="color: var(--text-muted);"><?php echo htmlspecialchars($activityTiming[$activity['id']]['timing_label'] ?? ''); ?></small>
                                    </div>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($activity['planned_start_date'])); ?></td>
                                <td><?php echo date('M j, Y', strtotime($activity['planned_end_date'])); ?></td>
                                <td><?php echo htmlspecialchars($activityTiming[$activity['id']]['duration_label'] ?? '-'); ?></td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <span class="status-badge status-<?php echo strtolower(str_replace('_', '-', $activity['status'])); ?>">
                                        <?php echo ACTIVITY_STATUSES[$activity['status']] ?? $activity['status']; ?>
                                    </span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <?php if ($activity['compliance_status']): ?>
                                    <span class="compliance-badge compliance-<?php echo strtolower(str_replace('_', '-', $activity['compliance_status'])); ?>">
                                        <?php echo COMPLIANCE_STATUSES[$activity['compliance_status']] ?? $activity['compliance_status']; ?>
                                    </span>
                                    <?php else: ?>
                                    <span style="color: var(--text-muted);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $activity['id']; ?>" class="btn btn-icon" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Project Documents (Project Owners upload, both can view) -->
        <div class="pv-card">
            <div class="pv-card-header">
                <h2><i class="fas fa-folder-open"></i> Project Documents</h2>
                <span style="color: var(--text-muted); font-weight: 600; font-size: 0.85rem;">
                    <?php echo array_sum(array_map('count', $projectDocuments)); ?> file(s)
                </span>
            </div>
            <div class="pv-card-body">
                <?php if ($auth->isProjectOwner() && (int)$project['created_by'] === (int)$auth->getUserId()): ?>
                <!-- Required Documents Checklist -->
                <div style="margin-bottom: 20px; padding: 16px; background: var(--bg-secondary); border-radius: var(--radius-md); border-left: 4px solid var(--primary);">
                    <h4 style="margin: 0 0 12px; font-size: 0.95rem; color: var(--text-primary);"><i class="fas fa-clipboard-list" style="margin-right: 6px;"></i>Required Documents</h4>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <?php foreach ($requiredDocs as $reqDoc): ?>
                        <?php $uploaded = in_array($reqDoc, $uploadedCategories); ?>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <?php if ($uploaded): ?>
                            <span style="color: var(--success); font-size: 1rem;"><i class="fas fa-check-circle"></i></span>
                            <span style="color: var(--text-secondary); text-decoration: line-through;"><?php echo htmlspecialchars($reqDoc); ?></span>
                            <?php else: ?>
                            <span style="color: var(--danger); font-size: 1rem;"><i class="fas fa-times-circle"></i></span>
                            <span style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($reqDoc); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <form method="POST" enctype="multipart/form-data" style="margin-bottom: 20px; padding: 16px; background: var(--bg-secondary); border-radius: var(--radius-md);">
                    <input type="hidden" name="action" value="upload_project_document">
                    <div style="display: grid; gap: 12px;">
                        <div>
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control" required>
                                <optgroup label="Required Documents">
                                    <?php foreach ($requiredDocs as $req): ?>
                                    <option value="<?php echo htmlspecialchars($req); ?>"><?php echo htmlspecialchars($req); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Procurement Procedure">
                                    <?php foreach ($documentCategories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Description (optional)</label>
                            <input type="text" name="description" class="form-control" placeholder="Brief description of the document">
                        </div>
                        <div style="display: flex; gap: 12px; align-items: flex-end;">
                            <div style="flex: 1;">
                                <label class="form-label">Select File</label>
                                <input type="file" name="document" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                        </div>
                        <small style="color: var(--text-muted);">Allowed: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, TXT, ZIP, RAR, CSV. Max 10MB.</small>
                    </div>
                </form>
                <?php endif; ?>

                <?php if (empty($projectDocuments)): ?>
                <div class="empty-state small">
                    <div class="empty-icon"><i class="fas fa-file-alt"></i></div>
                    <p>No documents uploaded yet.</p>
                    <?php if ($auth->isProjectOwner() && (int)$project['created_by'] === (int)$auth->getUserId()): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Upload documents above, categorized by procurement procedure.</p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <?php foreach ($projectDocuments as $category => $docs): ?>
                <div style="margin-bottom: 20px;">
                    <h4 style="font-size: 0.95rem; color: var(--primary); margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid var(--border-color);">
                        <i class="fas fa-folder"></i> <?php echo htmlspecialchars($category); ?>
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <?php foreach ($docs as $doc): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: var(--bg-secondary); border-radius: var(--radius-md);">
                            <div style="min-width: 0;">
                                <a href="<?php echo APP_URL; ?>/uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-secondary" style="text-decoration: none;">
                                    <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($doc['original_name']); ?>
                                </a>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">
                                    <?php echo number_format($doc['file_size'] / 1024, 1); ?> KB
                                    <?php if ($doc['description']): ?>
                                    &bull; <?php echo htmlspecialchars($doc['description']); ?>
                                    <?php endif; ?>
                                    &bull; <?php echo date('M j, Y', strtotime($doc['uploaded_at'])); ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <button type="button" class="btn btn-sm btn-secondary document-preview-btn" 
                                        data-url="<?php echo htmlspecialchars(APP_URL . '/uploads/' . $doc['file_path']); ?>"
                                        data-name="<?php echo htmlspecialchars($doc['original_name']); ?>" title="Preview">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="<?php echo APP_URL; ?>/uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-secondary" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                            <?php if ($auth->isProjectOwner() && (int)$project['created_by'] === (int)$auth->getUserId() && (int)$doc['uploaded_by'] === (int)$auth->getUserId()): ?>
                            <form method="POST" style="margin: 0;" onsubmit="return confirm('Remove this document?');">
                                <input type="hidden" name="action" value="delete_project_document">
                                <input type="hidden" name="document_id" value="<?php echo $doc['id']; ?>">
                                <button type="submit" class="btn btn-icon btn-danger" title="Remove">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <?php if (!empty($activities)): ?>
        <div class="pv-card">
            <div class="pv-card-header">
                <h2><i class="fas fa-hourglass-half"></i> Timeline Totals</h2>
            </div>
            <div class="pv-card-body">
                <div class="pv-info-tile" style="text-align: center;">
                    <span class="pv-info-label">Planned Process Duration</span>
                    <div style="font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-top: 8px;"><?php echo timelineFormatDayCount($timelineSummary['total_planned_days']); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Progress Card -->
        <div class="pv-card">
            <div class="pv-card-header">
                <h2><i class="fas fa-chart-pie"></i> Progress</h2>
            </div>
            <div class="pv-card-body">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 3.5rem; font-weight: 900; color: var(--primary); line-height: 1; margin-bottom: 4px;"><?php echo $progress; ?>%</div>
                    <p style="color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">Overall Completion</p>
                </div>
                
                <div class="pv-progress-wrap">
                    <div class="pv-progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                </div>

                <div class="pv-stat-grid grid-2-col">
                    <div class="pv-stat-block pv-stat-pending">
                        <div class="pv-stat-num"><?php echo $stats['pending']; ?></div>
                        <div class="pv-stat-label">Pending</div>
                    </div>
                    <div class="pv-stat-block pv-stat-progress">
                        <div class="pv-stat-num"><?php echo $stats['in_progress']; ?></div>
                        <div class="pv-stat-label">In Progress</div>
                    </div>
                    <div class="pv-stat-block pv-stat-completed">
                        <div class="pv-stat-num"><?php echo $stats['completed']; ?></div>
                        <div class="pv-stat-label">Completed</div>
                    </div>
                    <div class="pv-stat-block pv-stat-delayed">
                        <div class="pv-stat-num"><?php echo $stats['delayed']; ?></div>
                        <div class="pv-stat-label">Delayed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BAC Cycles card removed -->
    </div>
</div>

<!-- Document Preview Modal -->
<div id="documentPreviewModal" class="document-preview-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: var(--card-bg, #1a1d24); border-radius: 12px; max-width: 95vw; max-height: 95vh; width: 900px; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--border-color, #2d333b);">
            <h3 id="documentPreviewTitle" style="margin: 0; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Document</h3>
            <div style="display: flex; gap: 8px;">
                <a id="documentPreviewDownload" href="#" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-download"></i> Download</a>
                <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('documentPreviewModal').style.display='none'"><i class="fas fa-times"></i> Close</button>
            </div>
        </div>
        <div id="documentPreviewBody" style="flex: 1; min-height: 400px; overflow: auto; padding: 20px; display: flex; align-items: center; justify-content: center;">
            <iframe id="documentPreviewIframe" style="width: 100%; height: 70vh; border: none; display: none;"></iframe>
            <img id="documentPreviewImg" style="max-width: 100%; max-height: 70vh; object-fit: contain; display: none;" alt="Preview">
            <div id="documentPreviewFallback" style="text-align: center; color: var(--text-muted); display: none; padding: 40px;">
                <i class="fas fa-file-alt" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.5;"></i>
                <p>Preview not available for this file type.</p>
                <a id="documentPreviewFallbackLink" href="#" target="_blank" class="btn btn-primary"><i class="fas fa-download"></i> Download to view</a>
            </div>
        </div>
    </div>
</div>

<script>
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
            document.getElementById('documentPreviewTitle').textContent = name;
            document.getElementById('documentPreviewDownload').href = url;
            fallbackLink.href = url;
            iframe.style.display = 'none';
            img.style.display = 'none';
            fallback.style.display = 'none';
            if (PREVIEW_TYPES.includes(ext)) {
                if (ext === 'pdf') { iframe.src = url; iframe.style.display = 'block'; }
                else { img.src = url; img.style.display = 'block'; }
            } else { fallback.style.display = 'block'; }
            modal.style.display = 'flex';
        });
    });
    document.getElementById('documentPreviewModal')?.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
