<?php
/**
 * View Project
 * SDO-BACtrack - Project Owners see only their own projects
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
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

// Handle approve action (BAC only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'approve_project' && $auth->isProcurement() && $projectId) {
    if ($project && ($project['approval_status'] ?? 'APPROVED') === 'PENDING_APPROVAL') {
        $projectModel->approve($projectId);
        setFlashMessage('success', 'Project approved. Progress can now be tracked.');
    }
    $auth->redirect(APP_URL . '/admin/project-view.php?id=' . $projectId);
}

// Handle reject action (BAC only) - remarks required
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reject_project' && $auth->isProcurement() && $projectId) {
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

$docModel = new ProjectDocument();
$projectDocuments = $docModel->getByProjectGrouped($projectId);
$documentCategories = $docModel->getCategories($project['procurement_type'] ?? 'PUBLIC_BIDDING');
$isPendingApproval = ($project['approval_status'] ?? 'APPROVED') === 'PENDING_APPROVAL';
$isRejected = ($project['approval_status'] ?? '') === 'REJECTED';

require_once __DIR__ . '/../includes/header.php';
?>

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
        <?php elseif ($auth->isProcurement() && $isPendingApproval): ?>
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

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px;">
    <div>
        <!-- Project Info -->
        <div class="data-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Project Information</h2>
                <?php if (isset($project['approval_status'])): ?>
                <span class="status-badge status-<?php echo strtolower(str_replace('_', '-', $project['approval_status'])); ?>">
                    <?php echo PROJECT_APPROVAL_STATUSES[$project['approval_status']] ?? $project['approval_status']; ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
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
                <?php if ($auth->isProcurement()): ?>
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
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Procurement Type</label>
                        <p style="font-weight: 500;"><?php echo PROCUREMENT_TYPES[$project['procurement_type']] ?? $project['procurement_type']; ?></p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Created By</label>
                        <p style="font-weight: 500;"><?php echo htmlspecialchars($project['creator_name']); ?></p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Created Date</label>
                        <p style="font-weight: 500;"><?php echo date('F j, Y', strtotime($project['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities List -->
        <div class="data-card">
            <div class="card-header">
                <h2><i class="fas fa-tasks"></i> BAC Activities - Cycle <?php echo $activeCycle ? $activeCycle['cycle_number'] : 1; ?></h2>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($activities)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-tasks"></i></div>
                    <h3>No activities found</h3>
                    <p>This project has no activities generated yet.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Activity</th>
                                <th>Planned Start</th>
                                <th>Planned End</th>
                                <th>Duration (Days)</th>
                                <th>Status</th>
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
                                </td>
                                <td><?php echo date('M j, Y', strtotime($activity['planned_start_date'])); ?></td>
                                <td><?php echo date('M j, Y', strtotime($activity['planned_end_date'])); ?></td>
                                <td>
                                    <?php if (!empty($activity['planned_start_date']) && !empty($activity['planned_end_date'])): ?>
                                        <?php
                                            $startDate = new DateTime($activity['planned_start_date']);
                                            $endDate = new DateTime($activity['planned_end_date']);
                                            $durationDays = $endDate >= $startDate
                                                ? $startDate->diff($endDate)->days + 1 // inclusive of start & end
                                                : null;
                                        ?>
                                        <?php echo $durationDays !== null ? $durationDays . ' days' : '-'; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td style="white-space: nowrap;">
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
        <div class="data-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2><i class="fas fa-folder-open"></i> Project Documents</h2>
                <span style="color: var(--text-muted); font-weight: 400;">
                    <?php echo array_sum(array_map('count', $projectDocuments)); ?> file(s)
                </span>
            </div>
            <div class="card-body">
                <?php if ($auth->isProjectOwner() && (int)$project['created_by'] === (int)$auth->getUserId()): ?>
                <form method="POST" enctype="multipart/form-data" style="margin-bottom: 20px; padding: 16px; background: var(--bg-secondary); border-radius: var(--radius-md);">
                    <input type="hidden" name="action" value="upload_project_document">
                    <div style="display: grid; gap: 12px;">
                        <div>
                            <label class="form-label">Category (Procurement Procedure)</label>
                            <select name="category" class="form-control" required>
                                <?php foreach ($documentCategories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                <?php endforeach; ?>
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
        <!-- Progress Card -->
        <div class="data-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2><i class="fas fa-chart-pie"></i> Progress</h2>
            </div>
            <div class="card-body">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 3rem; font-weight: 800; color: var(--primary);"><?php echo $progress; ?>%</div>
                    <p style="color: var(--text-muted);">Overall Completion</p>
                </div>
                
                <div style="background: var(--bg-secondary); border-radius: 10px; height: 10px; margin-bottom: 20px;">
                    <div style="background: var(--success); border-radius: 10px; height: 100%; width: <?php echo $progress; ?>%;"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="text-align: center; padding: 12px; background: var(--warning-bg); border-radius: var(--radius-md);">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);"><?php echo $stats['pending']; ?></div>
                        <small style="color: var(--text-muted);">Pending</small>
                    </div>
                    <div style="text-align: center; padding: 12px; background: var(--info-bg); border-radius: var(--radius-md);">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--info);"><?php echo $stats['in_progress']; ?></div>
                        <small style="color: var(--text-muted);">In Progress</small>
                    </div>
                    <div style="text-align: center; padding: 12px; background: var(--success-bg); border-radius: var(--radius-md);">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);"><?php echo $stats['completed']; ?></div>
                        <small style="color: var(--text-muted);">Completed</small>
                    </div>
                    <div style="text-align: center; padding: 12px; background: var(--danger-bg); border-radius: var(--radius-md);">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger);"><?php echo $stats['delayed']; ?></div>
                        <small style="color: var(--text-muted);">Delayed</small>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($auth->isProcurement()): ?>
        <!-- Cycle Info - BAC Members only -->
        <div class="data-card">
            <div class="card-header">
                <h2><i class="fas fa-sync"></i> BAC Cycles</h2>
            </div>
            <div class="card-body">
                <?php if (empty($cycles)): ?>
                <p style="color: var(--text-muted);">No cycles found.</p>
                <?php else: ?>
                <?php foreach ($cycles as $cycle): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: var(--bg-secondary); border-radius: var(--radius-md); margin-bottom: 8px;">
                    <div>
                        <strong>Cycle <?php echo $cycle['cycle_number']; ?></strong>
                        <br><small style="color: var(--text-muted);">Created <?php echo date('M j, Y', strtotime($cycle['created_at'])); ?></small>
                    </div>
                    <span class="status-badge status-<?php echo strtolower($cycle['status']); ?>">
                        <?php echo $cycle['status']; ?>
                    </span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
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
