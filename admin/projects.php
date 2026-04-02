<?php
/**
 * Projects List
 * SDO-BACtrack - Project Owners see only their projects
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Project.php';

$projectModel = new Project();

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
                    <input type="text" name="search" class="filter-input" placeholder="Project title..." 
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
                    <th style="text-align: center;">BACTrack ID</th>
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
                        <div class="action-buttons" style="justify-content: center;">
                            <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $project['id']; ?>" class="btn btn-icon" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($auth->isProcurement()): ?>
                            <a href="<?php echo APP_URL; ?>/admin/calendar.php?project=<?php echo $project['id']; ?>" class="btn btn-icon" title="Calendar">
                                <i class="fas fa-calendar"></i>
                            </a>
                            <?php endif; ?>
                            <a href="<?php echo APP_URL; ?>/admin/reports.php?project=<?php echo $project['id']; ?>" class="btn btn-icon" title="Report">
                                <i class="fas fa-file-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
