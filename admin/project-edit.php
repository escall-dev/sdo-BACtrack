<?php
/**
 * Edit Project (Draft only)
 * SDO-BACtrack - Project owners can edit draft projects before submitting for BAC review
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/Project.php';

$auth = auth();
$auth->requireLogin();

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$projectModel = new Project();
$project = $projectModel->findById($projectId);

if (!$project) {
    setFlashMessage('error', 'Project not found.');
    $auth->redirect(APP_URL . '/admin/projects.php');
}

// Only project owner can edit, and only DRAFT projects
if ($auth->isProjectOwner() && (int)$project['created_by'] !== (int)$auth->getUserId()) {
    setFlashMessage('error', 'You do not have access to this project.');
    $auth->redirect(APP_URL . '/admin/projects.php');
}

if (($project['approval_status'] ?? '') !== 'DRAFT') {
    setFlashMessage('error', 'Only draft projects can be edited.');
    $auth->redirect(APP_URL . '/admin/project-view.php?id=' . $projectId);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $procurementType = $_POST['procurement_type'] ?? 'PUBLIC_BIDDING';
    $startDate = trim($_POST['project_start_date'] ?? '') ?: null;

    if (empty($title)) {
        $error = 'Project title is required.';
    } else {
        $projectModel->update($projectId, [
            'title' => $title,
            'description' => $description,
            'procurement_type' => $procurementType,
            'project_start_date' => $startDate
        ]);
        setFlashMessage('success', 'Draft updated successfully.');
        $auth->redirect(APP_URL . '/admin/project-view.php?id=' . $projectId);
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $projectId; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Project
        </a>
    </div>
</div>

<div class="data-card">
    <div class="card-header">
        <h2><i class="fas fa-edit"></i> Edit Draft</h2>
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
                       value="<?php echo htmlspecialchars($project['title']); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="procurement_type">Procurement Type *</label>
                <select id="procurement_type" name="procurement_type" class="form-control" required>
                    <?php foreach (PROCUREMENT_TYPES as $key => $value): ?>
                    <option value="<?php echo $key; ?>" <?php echo ($project['procurement_type'] ?? '') === $key ? 'selected' : ''; ?>><?php echo $value; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="project_start_date">Project Start Date (for timeline when submitted)</label>
                <input type="date" id="project_start_date" name="project_start_date" class="form-control"
                       value="<?php echo htmlspecialchars($project['project_start_date'] ?? date('Y-m-d')); ?>">
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $projectId; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
