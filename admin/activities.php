<?php
/**
 * Activities List
 * SDO-BACtrack - Project Owners see only activities from their projects
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/timeline.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/ProjectActivity.php';

$projectModel = new Project();
$activityModel = new ProjectActivity();

// Auto-update delayed activities
$activityModel->checkAndUpdateDelayed();

// Get filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'project' => $_GET['project'] ?? '',
    'owner' => $_GET['owner'] ?? ''
];

// Project Owners see only their own projects (privacy)
$projectFilters = [];
if ($auth->isProjectOwner()) {
    $projectFilters['created_by'] = $auth->getUserId();
} elseif (!empty($filters['owner'])) {
    $projectFilters['created_by'] = $filters['owner'];
}
$projects = $projectModel->getAll($projectFilters);

// BAC members: get project owners for filter
$projectOwners = [];
if ($auth->isProcurement()) {
    $projectOwners = $projectModel->getProjectOwners();
}

// Get activities - Project Owners only from their projects
$sql = "SELECT pa.*, bc.project_id, bc.cycle_number, p.title as project_title, u.name as project_owner_name
        FROM project_activities pa
        LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
        LEFT JOIN projects p ON bc.project_id = p.id
        LEFT JOIN users u ON p.created_by = u.id
        WHERE 1=1";
$params = [];

if ($auth->isProjectOwner()) {
    $sql .= " AND p.created_by = ?";
    $params[] = $auth->getUserId();
}

if (!empty($filters['status'])) {
    $sql .= " AND pa.status = ?";
    $params[] = $filters['status'];
}

if (!empty($filters['project'])) {
    $sql .= " AND bc.project_id = ?";
    $params[] = $filters['project'];
}

if ($auth->isProcurement() && !empty($filters['owner'])) {
    $sql .= " AND p.created_by = ?";
    $params[] = $filters['owner'];
}

$sql .= " ORDER BY pa.planned_start_date ASC, pa.step_order ASC";

$activities = db()->fetchAll($sql, $params);
?>

<style>
/* ── Activities page: table visibility enhancements ── */


.act-name-link {
    color: var(--primary);
    font-weight: 650;
    font-size: 0.9rem;
    text-decoration: none;
    line-height: 1.4;
}
.act-name-link:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

.step-badge {
    display: inline-block;
    background: #f1f5f9;
    color: #475569;
    font-size: 0.67rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-top: 5px;
    border: 1px solid #e2e8f0;
}

.cycle-badge {
    display: inline-block;
    background: #f8fafc;
    color: #64748b;
    font-size: 0.67rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 999px;
    margin-top: 5px;
    border: 1px solid #e2e8f0;
    letter-spacing: 0.3px;
}

.act-project-link {
    color: #1e293b;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
}
.act-project-link:hover { color: var(--primary); text-decoration: underline; }



.date-cell {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
}

/* Owners see only their own projects */
.data-table .owner-cell {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}


/* Make the owner column stand out a bit */
.data-table .owner-cell {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}
</style>

<p style="color: var(--text-muted); margin: 0 0 8px;"><?php echo count($activities); ?> process(es) found</p>

<div class="filter-bar calendar-filter-bar">
    <div class="calendar-filter-header">
        <div class="calendar-filter-right">
            <form class="filter-form calendar-filter-form" method="GET" onkeydown="if(event.key==='Enter'){event.preventDefault();this.submit();}">
                <?php if ($auth->isProcurement() && !empty($projectOwners)): ?>
                <div class="filter-group">
                    <label>Project Owner / Bidder</label>
                    <select name="owner" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Owners</option>
                        <?php foreach ($projectOwners as $owner): ?>
                        <option value="<?php echo (int)$owner['id']; ?>" <?php echo ($filters['owner'] ?? '') === (string)$owner['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($owner['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
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
    </div>
</div>

<div class="data-card">
    <?php if (empty($activities)): ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-tasks"></i></div>
        <h3>No process found</h3>
        <p>Create a project to generate BAC process.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
            <tr>
                <th style="width: 80px; text-align: center;">Step</th>
                <th style="text-align: center;">Process</th>
                <th style="text-align: center;">Project</th>
                <?php if ($auth->isProcurement()): ?>
                <th>Project Owner</th>
                <?php endif; ?>
                <th>Planned Start</th>
                <th>Planned End</th>
                <th style="text-align: center;">Duration (Days)</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center;">Compliance</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity): ?>
                <?php $rowClass = 'row-' . strtolower(str_replace('_', '-', $activity['status'])); ?>
                <tr class="act-row <?php echo $rowClass; ?>">
                    <td style="font-weight: 700; color: var(--text-muted);">
                        <?php echo $activity['step_order']; ?>
                    </td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $activity['id']; ?>"
                           class="act-name-link">
                            <?php echo htmlspecialchars($activity['step_name']); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $activity['project_id']; ?>" class="act-project-link">
                            <?php echo htmlspecialchars($activity['project_title']); ?>
                        </a>
                        <br><span class="cycle-badge">Cycle <?php echo $activity['cycle_number']; ?></span>
                    </td>
                    <?php if ($auth->isProcurement()): ?>
                    <td class="owner-cell"><?php echo htmlspecialchars($activity['project_owner_name'] ?? '-'); ?></td>
                    <?php endif; ?>
                    <td><span class="date-cell"><?php echo date('M j, Y', strtotime($activity['planned_start_date'])); ?></span></td>
                    <td><span class="date-cell"><?php echo date('M j, Y', strtotime($activity['planned_end_date'])); ?></span></td>
                    <td style="text-align: center;"><span><?php echo htmlspecialchars(timelineDurationLabel($activity['planned_start_date'], $activity['planned_end_date'])); ?></span></td>
                    <td style="text-align: center;">
                        <span class="status-badge status-<?php echo strtolower(str_replace('_', '-', $activity['status'])); ?>">
                            <?php echo ACTIVITY_STATUSES[$activity['status']] ?? $activity['status']; ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <?php if ($activity['compliance_status']): ?>
                        <span>
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
