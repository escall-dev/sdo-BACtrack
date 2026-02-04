<?php
/**
 * Activities List
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/ProjectActivity.php';

$projectModel = new Project();
$activityModel = new ProjectActivity();

// Auto-update delayed activities
$activityModel->checkAndUpdateDelayed();

// Get filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'project' => $_GET['project'] ?? ''
];

$projects = $projectModel->getAll();

// Get all activities
$sql = "SELECT pa.*, bc.project_id, bc.cycle_number, p.title as project_title
        FROM project_activities pa
        LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
        LEFT JOIN projects p ON bc.project_id = p.id
        WHERE 1=1";
$params = [];

if (!empty($filters['status'])) {
    $sql .= " AND pa.status = ?";
    $params[] = $filters['status'];
}

if (!empty($filters['project'])) {
    $sql .= " AND bc.project_id = ?";
    $params[] = $filters['project'];
}

$sql .= " ORDER BY pa.planned_start_date ASC, pa.step_order ASC";

$activities = db()->fetchAll($sql, $params);
?>

<div class="page-header">
    <div>
        <h2 style="margin: 0;">All Activities</h2>
        <p style="color: var(--text-muted); margin: 4px 0 0;"><?php echo count($activities); ?> activity(ies) found</p>
    </div>
</div>

<div class="filter-bar">
    <form class="filter-form" method="GET">
        <div class="filter-group">
            <label>Project</label>
            <select name="project" class="filter-select">
                <option value="">All Projects</option>
                <?php foreach ($projects as $project): ?>
                <option value="<?php echo $project['id']; ?>" <?php echo $filters['project'] == $project['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($project['title']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <?php foreach (ACTIVITY_STATUSES as $key => $value): ?>
                <option value="<?php echo $key; ?>" <?php echo $filters['status'] === $key ? 'selected' : ''; ?>>
                    <?php echo $value; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?php echo APP_URL; ?>/admin/activities.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="data-card">
    <?php if (empty($activities)): ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-tasks"></i></div>
        <h3>No activities found</h3>
        <p>Create a project to generate BAC activities.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
            <tr>
                <th>Activity</th>
                <th>Project</th>
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
                    <td>
                        <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $activity['id']; ?>" 
                           style="color: var(--primary); font-weight: 500; text-decoration: none;">
                            <?php echo htmlspecialchars($activity['step_name']); ?>
                        </a>
                        <br><small style="color: var(--text-muted);">Step <?php echo $activity['step_order']; ?></small>
                    </td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $activity['project_id']; ?>" style="text-decoration: none; color: inherit;">
                            <?php echo htmlspecialchars($activity['project_title']); ?>
                        </a>
                        <br><small style="color: var(--text-muted);">Cycle <?php echo $activity['cycle_number']; ?></small>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($activity['planned_start_date'])); ?></td>
                    <td><?php echo date('M j, Y', strtotime($activity['planned_end_date'])); ?></td>
                    <td>
                        <?php if (!empty($activity['planned_start_date']) && !empty($activity['planned_end_date'])): ?>
                            <?php
                                $startDate = new DateTime($activity['planned_start_date']);
                                $endDate = new DateTime($activity['planned_end_date']);
                                $durationDays = $endDate >= $startDate
                                    ? $startDate->diff($endDate)->days + 1
                                    : null;
                            ?>
                            <?php echo $durationDays !== null ? $durationDays . ' days' : '-'; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower(str_replace('_', '-', $activity['status'])); ?>">
                            <?php echo ACTIVITY_STATUSES[$activity['status']] ?? $activity['status']; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($activity['compliance_status']): ?>
                        <span class="compliance-badge compliance-<?php echo strtolower(str_replace('_', '-', $activity['compliance_status'])); ?>">
                            <?php echo COMPLIANCE_STATUSES[$activity['compliance_status']] ?? $activity['compliance_status']; ?>
                        </span>
                        <?php else: ?>
                        <span style="color: var(--text-muted);">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $activity['id']; ?>" class="btn btn-icon" title="View">
                                <i class="fas fa-eye"></i>
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
