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
    } else {
        try {
            $db = db();
            $db->beginTransaction();

            $projectModel = new Project();
            if ($auth->isProjectOwner()) {
                // Project owner: create as DRAFT for BAC to review before submit
                // No cycle/activities yet - created when project owner submits for review
                $projectId = $projectModel->create([
                    'title' => $title,
                    'description' => $description,
                    'procurement_type' => $procurementType,
                    'project_start_date' => $startDate,
                    'created_by' => $auth->getUserId(),
                    'approval_status' => 'DRAFT'
                ]);
            } else {
                // BAC member: create as APPROVED with timeline immediately
                $projectId = $projectModel->create([
                    'title' => $title,
                    'description' => $description,
                    'procurement_type' => $procurementType,
                    'created_by' => $auth->getUserId(),
                    'approval_status' => 'APPROVED'
                ]);
                $cycleModel = new BacCycle();
                $cycleId = $cycleModel->create($projectId, 1);
                $activityModel = new ProjectActivity();
                $activityModel->generateFromTemplate($cycleId, $procurementType, $startDate);
            }

            $db->commit();

            $msg = $auth->isProjectOwner()
                ? 'Project draft created. BAC can review it. Submit for BAC approval when ready to generate the timeline.'
                : 'Project created successfully with timeline generated.';
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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?php echo APP_URL; ?>/admin/projects.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 400px; gap: 24px;">
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

            <form method="POST" action="">
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

                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Create Project
                    </button>
                    <a href="<?php echo APP_URL; ?>/admin/projects.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div>
        <div class="data-card">
            <div class="card-header">
                <h2><i class="fas fa-list-ol"></i> Timeline Template</h2>
            </div>
            <div class="card-body" style="padding: 0;">
                <div style="max-height: 500px; overflow-y: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Step Name</th>
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

        <div class="alert alert-info" style="margin-top: 16px;">
            <i class="fas fa-info-circle"></i>
            <span>Activities will be auto-generated based on the template above when you create the project.</span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
