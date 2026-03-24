<?php
/**
 * Create Project
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/BacCycle.php';
require_once __DIR__ . '/../models/ProjectActivity.php';
require_once __DIR__ . '/../models/TimelineTemplate.php';
require_once __DIR__ . '/../models/ProjectDocument.php';

$auth = auth();
$auth->requireLogin();




$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $procurementType = $_POST['procurement_type'] ?? 'PUBLIC_BIDDING';
    $startDate = $_POST['start_date'] ?? '';

    if (empty($title)) {
        $error = 'Project title is required.';
    } elseif (empty($startDate)) {
        $error = 'Project start date is required.';
    }

    if (empty($error)) {
        try {
            $db = db();
            $db->beginTransaction();

            $projectModel = new Project();
            // All roles now create APPROVED projects with timeline immediately
            $projectId = $projectModel->create([
                'title' => $title,
                'description' => $description,
                'procurement_type' => $procurementType,
                'project_start_date' => $startDate,
                'created_by' => $auth->getUserId(),
                'approval_status' => 'APPROVED'
            ]);
            
            $cycleModel = new BacCycle();
            $cycleId = $cycleModel->create($projectId, 1);
            $activityModel = new ProjectActivity();
            $activityModel->generateFromTemplate($cycleId, $procurementType, $startDate);

            $db->commit();

            $msg = 'Project created successfully with timeline generated.';
            setFlashMessage('success', $msg);
            $auth->redirect(APP_URL . '/admin/project-view.php?id=' . $projectId);
        } catch (Exception $e) {
            $db->rollback();
            $error = 'Failed to create project: ' . $e->getMessage();
        }
    }
}


// Get template info for display
$templateModel = new TimelineTemplate();
$templates = $templateModel->getByProcurementType('PUBLIC_BIDDING');
$totalDays = $templateModel->getTotalDuration('PUBLIC_BIDDING');
$timelineStepCount = count($templates);

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.project-create-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 420px;
    gap: 24px;
    align-items: start;
}

.project-create-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.project-create-layout .data-card {
    margin-bottom: 0;
}

.project-create-layout .data-card .card-body {
    padding-bottom: 32px;
}

.required-docs-note {
    color: var(--text-muted);
    font-size: 0.88rem;
    margin: 0 0 14px;
}

.required-docs-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 14px;
}

.required-docs-head .required-docs-note {
    margin: 0;
}

.docs-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--primary);
    background: rgba(44, 90, 160, 0.12);
    border: 1px solid rgba(44, 90, 160, 0.2);
    white-space: nowrap;
}

.docs-count-badge.is-complete {
    color: #047857;
    background: #d1fae5;
    border-color: #86efac;
}

.required-docs-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.required-doc-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    background: linear-gradient(180deg, var(--bg-secondary) 0%, #ffffff 100%);
    transition: border-color 0.2s ease, transform 0.2s ease;
}

.required-doc-item:hover {
    border-color: var(--primary);
    transform: translateX(2px);
}

.required-doc-item.doc-ready {
    border-color: rgba(4, 120, 87, 0.35);
    background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%);
}

.required-doc-label {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.required-doc-actions {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.doc-status {
    display: none;
    align-items: center;
    justify-content: center;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.2px;
    white-space: nowrap;
}

.doc-status-ready {
    display: inline-flex;
    color: #047857;
    background: #d1fae5;
    border: 1px solid #a7f3d0;
}

.required-doc-upload {
    cursor: pointer;
    flex-shrink: 0;
}

.upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.upload-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(44, 90, 160, 0.14);
    color: var(--primary);
    font-size: 0.72rem;
    line-height: 1;
}

.upload-btn.file-selected {
    background: var(--success-bg);
    color: #047857;
}

.upload-btn.file-selected .upload-icon {
    background: rgba(4, 120, 87, 0.14);
    color: #047857;
}

.upload-text {
    display: inline-block;
    max-width: 130px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.project-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    flex-wrap: wrap;
}

.timeline-template-card .card-body {
    padding: 0 !important;
}

.timeline-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    background: linear-gradient(180deg, rgba(44, 90, 160, 0.06) 0%, rgba(44, 90, 160, 0) 100%);
}

.timeline-summary span {
    font-size: 0.78rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.timeline-summary strong {
    color: var(--text-primary);
    font-size: 0.9rem;
    margin-right: 4px;
}

.timeline-template-card .data-table th:first-child,
.timeline-template-card .data-table td:first-child {
    width: 56px;
    text-align: center;
}

.timeline-template-card .data-table th:last-child,
.timeline-template-card .data-table td:last-child {
    width: 72px;
    text-align: center;
}

@media (max-width: 1200px) {
    .project-create-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 560px) {
    .required-doc-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .required-doc-actions {
        width: 100%;
        justify-content: space-between;
    }

    .required-docs-head {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<div class="page-header">
    <div>
        <a href="<?php echo APP_URL; ?>/admin/projects.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>
</div>


<div class="project-create-layout">
    <div class="project-create-column">
        <div class="data-card">
            <div class="card-header">
                <h2><i class="fas fa-folder-plus"></i> Create New Project</h2>
            </div>
            <div class="card-body">
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="createProjectForm">
                <div class="form-group">
                    <label class="form-label" for="title">Project Title *</label>
                    <input type="text" id="title" name="title" class="form-control" required
                           placeholder="Enter project title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4"
                              placeholder="Enter project description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="procurement_type">Procurement Type *</label>
                    <select id="procurement_type" name="procurement_type" class="form-control" required>
                        <?php foreach (PROCUREMENT_TYPES as $key => $value): ?>
                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="start_date">Project Start Date *</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required
                           value="<?php echo htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')); ?>">
                    <small style="color: var(--text-muted);">The first activity will start on this date. Total timeline: <?php echo $totalDays; ?> days.</small>
                </div>

                <div class="project-actions">
                    <button type="submit" class="btn btn-primary">
                        Create Project
                    </button>
                    <a href="<?php echo APP_URL; ?>/admin/projects.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    </div>

    <div class="project-create-column">
        <div class="data-card timeline-template-card">
            <div class="card-header">
                <h2><i class="fas fa-list-ol"></i> Timeline Template</h2>
            </div>
            <div class="card-body">
                <div class="timeline-summary">
                    <span><strong><?php echo $timelineStepCount; ?></strong> Process</span>
                    <span><strong><?php echo (int)$totalDays; ?></strong> Total Days</span>
                </div>
                <div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Process Name</th>
                                <th>Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($templates as $template): ?>
                            <tr>
                                <td><?php echo $template['step_order']; ?></td>
                                <td><?php echo htmlspecialchars($template['step_name']); ?></td>
                                <td><?php echo $template['default_duration_days']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-info" style="margin: 0;">
            <i class="fas fa-info-circle"></i>
            <span>Activities will be auto-generated based on the template above when you create the project.</span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<!-- Validation Modal -->



