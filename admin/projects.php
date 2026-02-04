<?php
/**
 * Projects List
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Project.php';

$projectModel = new Project();

// Get filters
$filters = [
    'search' => $_GET['search'] ?? '',
    'procurement_type' => $_GET['type'] ?? ''
];

$projects = $projectModel->getAll($filters);
?>

<div class="page-header">
    <div>
        <h2 style="margin: 0;">BAC Projects</h2>
        <p style="color: var(--text-muted); margin: 4px 0 0;"><?php echo count($projects); ?> project(s) found</p>
    </div>
    <a href="<?php echo APP_URL; ?>/admin/project-create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Project
    </a>
</div>

<div class="filter-bar">
    <form class="filter-form" method="GET">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" class="filter-input" placeholder="Project title..." 
                   value="<?php echo htmlspecialchars($filters['search']); ?>">
        </div>
        <div class="filter-group">
            <label>Procurement Type</label>
            <select name="type" class="filter-select">
                <option value="">All Types</option>
                <?php foreach (PROCUREMENT_TYPES as $key => $value): ?>
                <option value="<?php echo $key; ?>" <?php echo $filters['procurement_type'] === $key ? 'selected' : ''; ?>>
                    <?php echo $value; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <a href="<?php echo APP_URL; ?>/admin/projects.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
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
        <table class="data-table">
            <thead>
                <tr>
                    <th>Project Title</th>
                    <th>Procurement Type</th>
                    <th>Cycles</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <td>
                        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $project['id']; ?>" 
                           style="color: var(--primary); font-weight: 600; text-decoration: none;">
                            <?php echo htmlspecialchars($project['title']); ?>
                        </a>
                        <?php if ($project['description']): ?>
                        <br><small style="color: var(--text-muted);"><?php echo htmlspecialchars(substr($project['description'], 0, 60)); ?>...</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge" style="background: var(--info-bg); color: var(--info);">
                            <?php echo PROCUREMENT_TYPES[$project['procurement_type']] ?? $project['procurement_type']; ?>
                        </span>
                    </td>
                    <td><?php echo $project['cycle_count']; ?></td>
                    <td><?php echo htmlspecialchars($project['creator_name']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($project['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $project['id']; ?>" class="btn btn-icon" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo APP_URL; ?>/admin/calendar.php?project=<?php echo $project['id']; ?>" class="btn btn-icon" title="Calendar">
                                <i class="fas fa-calendar"></i>
                            </a>
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
