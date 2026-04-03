<?php
/**
 * Projects List
 * SDO-BACtrack - Project Owners see only their projects
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/Project.php';

$auth = auth();
$auth->requireLogin();

$projectModel = new Project();

// Handle project actions before rendering.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_project') {
        $projectId = (int)($_POST['project_id'] ?? 0);
        $project = $projectModel->findById($projectId);

        if ($projectId <= 0 || !$project) {
            setFlashMessage('error', 'Project not found.');
            $auth->redirect(APP_URL . '/admin/projects.php');
        }

        $canManageProject = $auth->isProcurement() || ((int)$project['created_by'] === (int)$auth->getUserId());
        if (!$canManageProject) {
            setFlashMessage('error', 'You do not have permission to delete this project.');
            $auth->redirect(APP_URL . '/admin/projects.php');
        }

        try {
            $deleted = $projectModel->delete($projectId);
            if ($deleted) {
                setFlashMessage('success', 'Project deleted successfully.');
            } else {
                setFlashMessage('error', 'Unable to delete project.');
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'SQLSTATE[23000]') !== false) {
                setFlashMessage('error', 'Cannot delete this project because it has linked activities or related records.');
            } else {
                setFlashMessage('error', 'Unable to delete project right now. Please try again.');
            }
        }

        $auth->redirect(APP_URL . '/admin/projects.php');
    }
}

require_once __DIR__ . '/../includes/header.php';

// Get filters
$filters = [
    'search' => $_GET['search'] ?? '',
    'procurement_type' => $_GET['type'] ?? '',
    'created_by' => $_GET['owner'] ?? '',
    'approval_status' => $_GET['approval'] ?? ''
];

// Project Owners see only their own projects (privacy)
if ($auth->isProjectOwner()) {
    $filters['created_by'] = $auth->getUserId();
}

// BAC members: filter by project owner (bidder)
$projectOwners = [];
if ($auth->isProcurement()) {
    $projectOwners = $projectModel->getProjectOwners();
}

$projects = $projectModel->getAll($filters);
?>

<div class="page-header">
    <div>
        <!-- Heading removed as requested -->
        <p style="color: var(--text-muted); margin: 4px 0 0;"><?php echo count($projects); ?> project(s) found</p>
    </div>
    <a href="<?php echo APP_URL; ?>/admin/project-create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Project
    </a>
</div>

<div class="filter-bar calendar-filter-bar">
    <div class="calendar-filter-header">
        <div class="calendar-filter-right">
            <form class="filter-form calendar-filter-form" method="GET" onkeydown="if(event.key==='Enter'){event.preventDefault();this.submit();}">
                <div class="filter-group">
                    <label>Search</label>
                          <input type="text" name="search" class="filter-input" placeholder="Project title or ID..." 
                           value="<?php echo htmlspecialchars($filters['search']); ?>">
                </div>
                <div class="filter-group">
                    <label>Mode of Procurement</label>
                    <select name="type" class="filter-select">
                        <option value="">All Types</option>
                        <?php foreach (PROCUREMENT_TYPES as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $filters['procurement_type'] === $key ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select name="approval" class="filter-select" onchange="this.form.submit()">
                        <option value="">All</option>
                        <?php foreach (PROJECT_APPROVAL_STATUSES as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($filters['approval_status'] ?? '') === $key ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($auth->isProcurement()): ?>
                <?php if (!empty($projectOwners)): ?>
                <div class="filter-group">
                    <label>Project Proponent</label>
                    <select name="owner" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Proponents</option>
                        <?php foreach ($projectOwners as $owner): ?>
                        <option value="<?php echo (int)$owner['id']; ?>" <?php echo $filters['created_by'] === (string)$owner['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($owner['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    <a href="<?php echo APP_URL; ?>/admin/projects.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="data-card">
    <?php if (empty($projects)): ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-folder-plus"></i></div>
        <h3>No projects found</h3>
        <p>Create your first BAC project to get started with timeline tracking.</p>
        <a href="<?php echo APP_URL; ?>/admin/project-create.php" class="btn btn-primary" style="margin-top: 16px;">
            <i class="fas fa-plus"></i> Create Project
        </a>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table" data-paginate="15">
            <thead>
                <tr>
                    <th style="text-align: center;">Project ID</th>
                    <th style="text-align: center;">Project Title</th>
                    <th style="text-align: center;">Mode of Procurement</th>
                    <th style="text-align: center;">Project Proponent</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Implementation Date</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <td style="text-align: center; font-weight: 700; letter-spacing: 0.02em;">
                        <?php echo htmlspecialchars($project['bactrack_id'] ?? ('PR-' . str_pad((string)$project['id'], 4, '0', STR_PAD_LEFT))); ?>
                    </td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $project['id']; ?>" 
                           style="color: #000; font-weight: 600; text-decoration: none;">
                            <?php echo htmlspecialchars($project['title']); ?>
                        </a>
                    </td>
                    <td style="text-align: center;">
                        <?php echo PROCUREMENT_TYPES[$project['procurement_type']] ?? $project['procurement_type']; ?>
                    </td>
                    <td style="text-align: center;">
                        <span><?php echo htmlspecialchars($project['creator_name'] ?? '-'); ?></span>
                    </td>
                    <td style="text-align: center;">
                        <?php $approval = $project['approval_status'] ?? 'APPROVED'; ?>
                        <span class="status-badge status-<?php echo strtolower(str_replace('_', '-', $approval)); ?>">
                            <?php echo PROJECT_APPROVAL_STATUSES[$approval] ?? $approval; ?>
                        </span>
                    </td>
                    <td style="text-align: center;"><?php echo date('M j, Y', strtotime(!empty($project['project_start_date']) ? $project['project_start_date'] : $project['created_at'])); ?></td>
                    <td style="text-align: center;">
                        <?php
                            $canManageProject = $auth->isProcurement() || ((int)$project['created_by'] === (int)$auth->getUserId());
                        ?>
                        <div class="action-buttons" style="justify-content: center;">
                            <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $project['id']; ?>" class="btn btn-icon" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($canManageProject): ?>
                            <a href="<?php echo APP_URL; ?>/admin/project-edit.php?id=<?php echo $project['id']; ?>" class="btn btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($auth->isProcurement()): ?>
                            <a href="<?php echo APP_URL; ?>/admin/calendar.php?project=<?php echo $project['id']; ?>" class="btn btn-icon" title="Calendar">
                                <i class="fas fa-calendar"></i>
                            </a>
                            <?php endif; ?>
                            <a href="<?php echo APP_URL; ?>/admin/reports.php?project=<?php echo $project['id']; ?>" class="btn btn-icon" title="Report">
                                <i class="fas fa-file-alt"></i>
                            </a>
                            <?php if ($canManageProject): ?>
                            <button type="button" class="btn btn-icon btn-danger" title="Delete"
                                onclick='openDeleteProjectModal(<?php echo (int)$project["id"]; ?>, <?php echo json_encode($project["title"] ?? "Untitled Project"); ?>, <?php echo json_encode($project["bactrack_id"] ?? ("PR-" . str_pad((string)$project["id"], 4, "0", STR_PAD_LEFT))); ?>)'>
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Project Confirmation Modal -->
<div id="deleteProjectModal" class="modal-overlay">
    <div class="modal-container modal-confirm">
        <form method="POST" id="deleteProjectForm">
            <div class="modal-header">
                <h2><i class="fas fa-trash" style="margin-right: 8px;"></i>Delete Project</h2>
                <button class="modal-close" type="button" onclick="closeDeleteProjectModal()">&times;</button>
            </div>

            <input type="hidden" name="action" value="delete_project">
            <input type="hidden" name="project_id" id="deleteProjectId" value="">

            <div class="modal-body">
                <p class="modal-confirm-message">Are you sure you want to delete this project?</p>
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px; margin: 12px 0;">
                    <div style="font-weight: 700; color: var(--text-primary);" id="deleteProjectTitle">-</div>
                    <div style="color: var(--text-muted); margin-top: 2px;" id="deleteProjectCode">-</div>
                </div>
                <p class="form-hint" style="color: var(--danger);">This action cannot be undone.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteProjectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete Project</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteProjectModal(projectId, projectTitle, projectCode) {
    document.getElementById('deleteProjectId').value = String(projectId || '');
    document.getElementById('deleteProjectTitle').textContent = projectTitle || 'Untitled Project';
    document.getElementById('deleteProjectCode').textContent = projectCode || '-';
    document.getElementById('deleteProjectModal').classList.add('show');
}

function closeDeleteProjectModal() {
    document.getElementById('deleteProjectModal').classList.remove('show');
}

document.getElementById('deleteProjectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteProjectModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    if (document.getElementById('deleteProjectModal').classList.contains('show')) {
        closeDeleteProjectModal();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
