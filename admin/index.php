<?php
/**
 * Admin Dashboard
 * SDO-BACtrack - Role-based: Project Owner vs BAC Member
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/ProjectActivity.php';
require_once __DIR__ . '/../models/BacCycle.php';
require_once __DIR__ . '/../models/ActivityHistoryLog.php';

$projectModel = new Project();
$activityModel = new ProjectActivity();
$historyModel = new ActivityHistoryLog();

// Auto-update delayed activities
$activityModel->checkAndUpdateDelayed();

if ($auth->isProjectOwner()) {
    // Project Owner dashboard: Project status, Activity history, Progress tracker
    $myProjects = $projectModel->getAll(['created_by' => $auth->getUserId()]);
    $myProjectIds = array_column($myProjects, 'id');
    $activityHistory = $historyModel->getRecentLogsByProjectOwner($auth->getUserId(), 15);

    // Project status: count projects
    $projectStatusCount = count($myProjects);

    // Activity stats for my projects only
    $myActivityStats = ['pending' => 0, 'in_progress' => 0, 'completed' => 0, 'delayed' => 0];
    foreach ($myProjects as $proj) {
        $activities = $activityModel->getByProject($proj['id']);
        foreach ($activities as $act) {
            $key = strtolower($act['status']);
            if (isset($myActivityStats[$key])) {
                $myActivityStats[$key]++;
            }
        }
    }

    // Progress tracker: activities per project (for active cycle)
    $cycleModel = new BacCycle();
    $progressData = [];
    foreach ($myProjects as $proj) {
        $activeCycle = $cycleModel->getActiveCycle($proj['id']);
        if ($activeCycle) {
            $acts = $activityModel->getByCycle($activeCycle['id']);
            $completed = count(array_filter($acts, fn($a) => $a['status'] === 'COMPLETED'));
            $total = count($acts);
            $progressData[] = [
                'project' => $proj,
                'activities' => $acts,
                'completed' => $completed,
                'total' => $total,
                'percent' => $total > 0 ? round(($completed / $total) * 100) : 0
            ];
        }
    }
} else {
    // BAC Member dashboard
    $projectStats = $projectModel->getStatistics();
    $activityStats = $activityModel->getStatistics();
    $upcomingDeadlines = $activityModel->getUpcomingDeadlines(DEADLINE_WARNING_DAYS);
    $delayedActivities = $activityModel->getDelayedActivities();
    $recentProjects = $projectModel->getAll();
    $recentProjects = array_slice($recentProjects, 0, 5);
}
?>

<?php if ($auth->isProjectOwner()): ?>
<!-- Project Owner Dashboard -->
<div class="dashboard-grid">
    <!-- Project Status -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-folder-open"></i></div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $projectStatusCount; ?></span>
                <span class="stat-label">My Projects</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $myActivityStats['pending'] ?? 0; ?></span>
                <span class="stat-label">Pending</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon in-progress"><i class="fas fa-spinner"></i></div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $myActivityStats['in_progress'] ?? 0; ?></span>
                <span class="stat-label">In Progress</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon completed"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $myActivityStats['completed'] ?? 0; ?></span>
                <span class="stat-label">Completed</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon delayed"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $myActivityStats['delayed'] ?? 0; ?></span>
                <span class="stat-label">Delayed</span>
            </div>
        </div>
    </div>

    <div class="dashboard-content" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Activity History -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Activity History</h2>
                <a href="<?php echo APP_URL; ?>/admin/activities.php" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($activityHistory)): ?>
                <div class="empty-state small">
                    <div class="empty-icon"><i class="fas fa-history"></i></div>
                    <p>No activity changes on your projects yet.</p>
                </div>
                <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($activityHistory as $log): ?>
                    <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo (int)$log['activity_id']; ?>" class="activity-item">
                        <div class="activity-info">
                            <strong><?php echo htmlspecialchars($log['step_name'] ?? 'Activity'); ?></strong>
                            <span><?php echo htmlspecialchars($log['project_title'] ?? ''); ?> &middot; <?php echo htmlspecialchars(ACTION_TYPES[$log['action_type']] ?? $log['action_type']); ?></span>
                        </div>
                        <div class="activity-date">
                            <small><?php echo date('M j, g:i A', strtotime($log['changed_at'])); ?></small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Progress Tracker -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-tasks"></i> Progress Tracker</h2>
                <a href="<?php echo APP_URL; ?>/admin/projects.php" class="btn btn-sm btn-secondary">View Projects</a>
            </div>
            <div class="card-body">
                <?php if (empty($progressData)): ?>
                <div class="empty-state small">
                    <div class="empty-icon"><i class="fas fa-tasks"></i></div>
                    <p>No active projects. Create a project to track progress.</p>
                    <a href="<?php echo APP_URL; ?>/admin/project-create.php" class="btn btn-primary btn-sm" style="margin-top: 12px;">Create Project</a>
                </div>
                <?php else: ?>
                <?php foreach ($progressData as $pd): ?>
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $pd['project']['id']; ?>" style="font-weight: 600; color: var(--primary); text-decoration: none;"><?php echo htmlspecialchars($pd['project']['title']); ?></a>
                        <span><?php echo $pd['percent']; ?>%</span>
                    </div>
                    <div style="height: 8px; background: var(--border-color); border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: <?php echo $pd['percent']; ?>%; background: var(--primary); transition: width 0.3s;"></div>
                    </div>
                    <div style="margin-top: 8px; font-size: 0.8rem; color: var(--text-muted);">
                        <?php echo $pd['completed']; ?> of <?php echo $pd['total']; ?> steps completed
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- My Projects -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2><i class="fas fa-folder-open"></i> My Projects</h2>
            <a href="<?php echo APP_URL; ?>/admin/projects.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($myProjects)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-folder-plus"></i></div>
                <h3>No projects yet</h3>
                <p>Create your first project to get started.</p>
                <a href="<?php echo APP_URL; ?>/admin/project-create.php" class="btn btn-primary" style="margin-top: 16px;"><i class="fas fa-plus"></i> Create Project</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project Title</th>
                            <th>Procurement Type</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($myProjects, 0, 5) as $project): ?>
                        <tr>
                            <td>
                                <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $project['id']; ?>" style="color: var(--primary); font-weight: 500; text-decoration: none;"><?php echo htmlspecialchars($project['title']); ?></a>
                            </td>
                            <td><?php echo PROCUREMENT_TYPES[$project['procurement_type']] ?? $project['procurement_type']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($project['created_at'])); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $project['id']; ?>" class="btn btn-icon" title="View"><i class="fas fa-eye"></i></a>
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

<?php else: ?>
<!-- BAC Member Dashboard -->
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
        
        <?php if (($projectStats['pending_approval'] ?? 0) > 0): ?>
        <a href="<?php echo APP_URL; ?>/admin/projects.php?approval=PENDING_APPROVAL" class="stat-card" style="text-decoration: none; color: inherit;">
            <div class="stat-icon delayed">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $projectStats['pending_approval']; ?></span>
                <span class="stat-label">Pending Approval</span>
            </div>
        </a>
        <?php endif; ?>
        
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
                                    <?php if ($auth->isProcurement()): ?>
                                    <a href="<?php echo APP_URL; ?>/admin/calendar.php?project=<?php echo $project['id']; ?>" class="btn btn-icon" title="Calendar">
                                        <i class="fas fa-calendar"></i>
                                    </a>
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
    </div>
</div>
<?php endif; ?>

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
