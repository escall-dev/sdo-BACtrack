<?php
/**
 * Admin Dashboard
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/ProjectActivity.php';
require_once __DIR__ . '/../models/BacCycle.php';

$projectModel = new Project();
$activityModel = new ProjectActivity();

// Auto-update delayed activities
$activityModel->checkAndUpdateDelayed();

// Get statistics
$projectStats = $projectModel->getStatistics();
$activityStats = $activityModel->getStatistics();

// Get upcoming deadlines
$upcomingDeadlines = $activityModel->getUpcomingDeadlines(DEADLINE_WARNING_DAYS);

// Get delayed activities
$delayedActivities = $activityModel->getDelayedActivities();

// Get recent projects
$recentProjects = $projectModel->getAll();
$recentProjects = array_slice($recentProjects, 0, 5);
?>

<div class="dashboard-grid">
    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-folder-open"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $projectStats['total']; ?></span>
                <span class="stat-label">Total Projects</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $activityStats['by_status']['PENDING'] ?? 0; ?></span>
                <span class="stat-label">Pending Activities</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon in-progress">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $activityStats['by_status']['IN_PROGRESS'] ?? 0; ?></span>
                <span class="stat-label">In Progress</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon completed">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $activityStats['by_status']['COMPLETED'] ?? 0; ?></span>
                <span class="stat-label">Completed</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon delayed">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $activityStats['by_status']['DELAYED'] ?? 0; ?></span>
                <span class="stat-label">Delayed</span>
            </div>
        </div>
    </div>

    <div class="dashboard-content" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Upcoming Deadlines -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-clock"></i> Upcoming Deadlines</h2>
                <a href="<?php echo APP_URL; ?>/admin/activities.php" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingDeadlines)): ?>
                <div class="empty-state small">
                    <div class="empty-icon"><i class="fas fa-calendar-check"></i></div>
                    <p>No upcoming deadlines in the next <?php echo DEADLINE_WARNING_DAYS; ?> days</p>
                </div>
                <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($upcomingDeadlines as $activity): ?>
                    <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $activity['id']; ?>" class="activity-item">
                        <div class="activity-info">
                            <strong><?php echo htmlspecialchars($activity['step_name']); ?></strong>
                            <span><?php echo htmlspecialchars($activity['project_title']); ?></span>
                        </div>
                        <div class="activity-date">
                            <span class="text-warning">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('M j', strtotime($activity['planned_end_date'])); ?>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Delayed Activities -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Delayed Activities</h2>
                <a href="<?php echo APP_URL; ?>/admin/activities.php?status=DELAYED" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($delayedActivities)): ?>
                <div class="empty-state small">
                    <div class="empty-icon"><i class="fas fa-check-circle"></i></div>
                    <p>No delayed activities. Great job!</p>
                </div>
                <?php else: ?>
                <div class="activity-list">
                    <?php foreach (array_slice($delayedActivities, 0, 5) as $activity): ?>
                    <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $activity['id']; ?>" class="activity-item">
                        <div class="activity-info">
                            <strong><?php echo htmlspecialchars($activity['step_name']); ?></strong>
                            <span><?php echo htmlspecialchars($activity['project_title']); ?></span>
                        </div>
                        <div class="activity-date">
                            <span class="status-badge status-delayed">DELAYED</span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Projects -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2><i class="fas fa-folder-open"></i> Recent Projects</h2>
            <a href="<?php echo APP_URL; ?>/admin/projects.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($recentProjects)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-folder-plus"></i></div>
                <h3>No projects yet</h3>
                <p>Create your first project to get started.</p>
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
                            <th>Created By</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProjects as $project): ?>
                        <tr>
                            <td>
                                <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $project['id']; ?>" style="color: var(--primary); font-weight: 500; text-decoration: none;">
                                    <?php echo htmlspecialchars($project['title']); ?>
                                </a>
                            </td>
                            <td><?php echo PROCUREMENT_TYPES[$project['procurement_type']] ?? $project['procurement_type']; ?></td>
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
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.activity-list {
    display: flex;
    flex-direction: column;
}

.activity-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    text-decoration: none;
    color: inherit;
    border-bottom: 1px solid var(--border-light);
    transition: var(--transition-base);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    background: var(--bg-secondary);
    margin: 0 -20px;
    padding-left: 20px;
    padding-right: 20px;
}

.activity-info strong {
    display: block;
    font-size: 0.9rem;
    color: var(--text-primary);
}

.activity-info span {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.activity-date {
    text-align: right;
}

.text-warning {
    color: var(--warning);
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
