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

// BAC Member / Admin / Superadmin dashboard
$projectStats = $projectModel->getStatistics();
$activityStats = $activityModel->getStatistics();
$upcomingDeadlines = $activityModel->getUpcomingDeadlines(DEADLINE_WARNING_DAYS);
$delayedActivities = $activityModel->getDelayedActivities();
$recentProjects = $projectModel->getAll();
$recentProjects = array_slice($recentProjects, 0, 10);

// Projects with at least one IN_PROGRESS activity
$inProgressProjectsCount = db()->fetch(
    "SELECT COUNT(DISTINCT bc.project_id) as count
     FROM bac_cycles bc
     JOIN project_activities pa ON pa.bac_cycle_id = bc.id
     WHERE pa.status = 'IN_PROGRESS'"
)['count'] ?? 0;

// Projects where every activity is COMPLETED
$completedProjectsCount = db()->fetch(
    "SELECT COUNT(*) as count FROM (
         SELECT bc.project_id
         FROM bac_cycles bc
         JOIN project_activities pa ON pa.bac_cycle_id = bc.id
         GROUP BY bc.project_id
         HAVING COUNT(*) > 0
           AND SUM(CASE WHEN pa.status != 'COMPLETED' THEN 1 ELSE 0 END) = 0
     ) as cp"
)['count'] ?? 0;

// Projects with upcoming deadlines (has an activity ending within DEADLINE_WARNING_DAYS)
$projectsWithDeadlines = db()->fetchAll(
    "SELECT DISTINCT p.id, p.title, p.procurement_type,
            MIN(pa.planned_end_date) as nearest_deadline
     FROM projects p
     JOIN bac_cycles bc ON bc.project_id = p.id
     JOIN project_activities pa ON pa.bac_cycle_id = bc.id
     WHERE pa.status NOT IN ('COMPLETED')
       AND pa.planned_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
     GROUP BY p.id, p.title, p.procurement_type
     ORDER BY nearest_deadline ASC
     LIMIT 10",
    [DEADLINE_WARNING_DAYS]
);

// Projects with at least one delayed activity
$delayedProjects = db()->fetchAll(
    "SELECT DISTINCT p.id, p.title, p.procurement_type,
            COUNT(pa.id) as delayed_count
     FROM projects p
     JOIN bac_cycles bc ON bc.project_id = p.id
     JOIN project_activities pa ON pa.bac_cycle_id = bc.id
     WHERE pa.status = 'DELAYED'
     GROUP BY p.id, p.title, p.procurement_type
     ORDER BY delayed_count DESC
     LIMIT 10"
);

// Superadmin: also load user stats
if ($auth->isSuperAdmin()) {
    require_once __DIR__ . '/../models/User.php';
    $userModel = new User();
    $allUsers = $userModel->getAll();
    $totalUsers = count($allUsers);
    $pendingUsers = count(array_filter($allUsers, fn($u) => ($u['status'] ?? 'APPROVED') === 'PENDING'));
}
?>

<!-- BAC Member / Admin / Superadmin Dashboard -->
<div class="dashboard-grid">
    <!-- Stats Toggle -->
    <div class="stats-section">
        <div class="stats-tab-bar">
            <button class="stats-tab-btn active" data-tab="projects">
                Projects
            </button>
            <button class="stats-tab-btn" data-tab="activities">
                Process
            </button>
        </div>

        <!-- Projects Stats Panel -->
        <div class="stats-row tab-stats-panel" data-tab-stats="projects">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $projectStats['total']; ?></span>
                    <span class="stat-label">Total Projects</span>
                </div>
            </div>

            <a href="<?php echo APP_URL; ?>/admin/projects.php?approval=PENDING_APPROVAL" class="stat-card" style="text-decoration: none; color: inherit;">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $projectStats['pending_approval'] ?? 0; ?></span>
                    <span class="stat-label">Pending Approval</span>
                </div>
            </a>

            <div class="stat-card">
                <div class="stat-icon in-progress">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $inProgressProjectsCount; ?></span>
                    <span class="stat-label">In Progress</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $completedProjectsCount; ?></span>
                    <span class="stat-label">Completed</span>
                </div>
            </div>

            <a href="<?php echo APP_URL; ?>/admin/projects.php?approval=REJECTED" class="stat-card" style="text-decoration: none; color: inherit;">
                <div class="stat-icon delayed">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $projectStats['rejected'] ?? 0; ?></span>
                    <span class="stat-label">Rejected</span>
                </div>
            </a>

            <?php if ($auth->isSuperAdmin()): ?>
            <?php if ($pendingUsers > 0): ?>
            <a href="<?php echo APP_URL; ?>/admin/users.php?status=PENDING" class="stat-card" style="text-decoration: none; color: inherit;">
                <div class="stat-icon delayed">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $pendingUsers; ?></span>
                    <span class="stat-label">Pending Users</span>
                </div>
            </a>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Process Stats Panel -->
        <div class="stats-row tab-stats-panel" data-tab-stats="activities" style="display: none;">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $activityStats['total'] ?? 0; ?></span>
                    <span class="stat-label">Total Process</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-hourglass-start"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $activityStats['by_status']['PENDING'] ?? 0; ?></span>
                    <span class="stat-label">Pending</span>
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
    </div>

    <!-- ===== PROJECTS CONTENT ===== -->
    <div class="tab-content-panel" data-tab-content="projects">

        <!-- Projects: Deadlines & Delays -->
        <div class="grid-2-col" style="margin-bottom: 24px;">

            <!-- Recent Projects -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-folder-open"></i> Recent Projects</h2>
                    <a href="<?php echo APP_URL; ?>/admin/projects.php" class="btn btn-sm btn-secondary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentProjects)): ?>
                    <div class="empty-state small">
                        <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                        <p>No projects yet.</p>
                    </div>
                    <?php else: ?>
                    <div class="activity-list" data-paginate="6">
                        <?php foreach ($recentProjects as $proj): ?>
                        <?php 
                        $statusClass = 'pending';
                        $statusLabel = $proj['approval_status'] ?? 'DRAFT';
                        if ($proj['approval_status'] === 'APPROVED') {
                            $statusClass = 'completed';
                        } elseif ($proj['approval_status'] === 'REJECTED') {
                            $statusClass = 'delayed';
                        } elseif ($proj['approval_status'] === 'PENDING_APPROVAL') {
                            $statusLabel = 'PENDING';
                        }
                        ?>
                        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo (int)$proj['id']; ?>" class="activity-item">
                            <div class="activity-info">
                                <strong><?php echo htmlspecialchars($proj['title']); ?></strong>
                                <span><?php echo PROCUREMENT_TYPES[$proj['procurement_type']] ?? $proj['procurement_type']; ?></span>
                            </div>
                            <div class="activity-date">
                                <span class="status-badge-small status-<?php echo $statusClass; ?>" style="font-size: 0.7rem; padding: 2px 8px; border-radius: 10px; border: 1px solid var(--border-color);">
                                    <?php echo htmlspecialchars($statusLabel); ?>
                                </span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

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
                    <div class="activity-list" data-paginate="6">
                        <?php foreach (array_slice($upcomingDeadlines, 0, 5) as $activity): ?>
                        <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo (int)$activity['id']; ?>" class="activity-item">
                            <div class="activity-info">
                                <strong><?php echo htmlspecialchars($activity['step_name']); ?></strong>
                                <span><?php echo htmlspecialchars($activity['project_title']); ?></span>
                            </div>
                            <div class="activity-date">
                                <span class="deadline-badge">
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
        </div>

    </div>

    <!-- ===== PROCESS CONTENT ===== -->
    <div class="tab-content-panel" data-tab-content="activities" style="display: none;">
        <div class="grid-2-col">
            <!-- Recent Process -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-tasks"></i> Recent Process</h2>
                    <a href="<?php echo APP_URL; ?>/admin/activities.php" class="btn btn-sm btn-secondary">View All</a>
                </div>
                <div class="card-body">
                    <?php
                    $recentActivities = db()->fetchAll(
                        "SELECT pa.*, p.title as project_title
                         FROM project_activities pa
                         LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
                         LEFT JOIN projects p ON bc.project_id = p.id
                         ORDER BY pa.id DESC LIMIT 10"
                    );
                    ?>
                    <?php if (empty($recentActivities)): ?>
                    <div class="empty-state small">
                        <div class="empty-icon"><i class="fas fa-tasks"></i></div>
                        <p>No process yet.</p>
                    </div>
                    <?php else: ?>
                    <div class="activity-list" data-paginate="6">
                        <?php foreach ($recentActivities as $act): ?>
                        <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo (int)$act['id']; ?>" class="activity-item">
                            <div class="activity-info">
                                <strong><?php echo htmlspecialchars($act['step_name']); ?></strong>
                                <span><?php echo htmlspecialchars($act['project_title'] ?? ''); ?></span>
                            </div>
                            <div class="activity-date">
                                <span class="status-badge-small status-<?php echo strtolower($act['status']); ?>" style="font-size: 0.7rem; padding: 2px 8px; border-radius: 10px; border: 1px solid var(--border-color);">
                                    <?php echo htmlspecialchars($act['status']); ?>
                                </span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

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
                    <div class="activity-list" data-paginate="6">
                        <?php foreach (array_slice($upcomingDeadlines, 0, 10) as $activity): ?>
                        <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $activity['id']; ?>" class="activity-item">
                            <div class="activity-info">
                                <strong><?php echo htmlspecialchars($activity['step_name']); ?></strong>
                                <span><?php echo htmlspecialchars($activity['project_title']); ?></span>
                            </div>
                            <div class="activity-date">
                                <span class="deadline-badge">
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
        </div>

    </div>

</div>
<?php  ?>

<style>
/* Dashboard adjustments — consistent regardless of data */
.dashboard-card .card-body {
    min-height: 200px;
    overflow: hidden;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
}

/* Remove scrollbars from table-based dashboard cards */
.dashboard-card .card-body .table-responsive {
    overflow: hidden;
    flex: 1;
}

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
    flex-shrink: 0;
}

.activity-date small {
    font-size: 0.8rem;
    color: var(--text-muted);
    white-space: nowrap;
}

.text-warning {
    color: var(--warning);
}

.deadline-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    background: var(--warning-bg);
    color: #b45309;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

/* Stats section toggle */
.stats-section {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.stats-tab-bar {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 14px;
    padding: 4px;
    border: 1px solid var(--border-light);
    border-radius: 999px;
    background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9), 0 2px 8px rgba(15, 23, 42, 0.06);
}

.stats-tab-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 122px;
    padding: 8px 20px;
    border: 1px solid transparent;
    border-radius: 999px;
    background: transparent;
    color: var(--text-muted);
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    cursor: pointer;
    transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.stats-tab-btn:hover {
    color: var(--text-primary);
    background: #ffffff;
    border-color: var(--border-color);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
}

.stats-tab-btn.active {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.26);
    transform: translateY(0);
}

.stats-tab-btn:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

@media (max-width: 768px) {
    .stats-tab-bar {
        display: flex;
        width: 100%;
    }

    .stats-tab-btn {
        flex: 1;
        min-width: 0;
    }
}

/* ── Container quality upgrades (matching users.php) ── */

/* Stat cards: gradient background */
.stat-card {
    background: linear-gradient(180deg, var(--bg-secondary) 0%, #ffffff 100%) !important;
    border: 1px solid var(--border-color);
}

/* Dashboard cards: gradient background */
.dashboard-card {
    background: linear-gradient(180deg, var(--bg-secondary) 0%, #ffffff 100%) !important;
    border: 1px solid var(--border-color);
}

/* Card header: subtle tinted background + stronger bottom border */
.dashboard-card .card-header {
    background: linear-gradient(180deg, var(--bg-secondary) 0%, var(--card-bg) 100%);
    border-bottom: 1.5px solid var(--border-color);
    padding: 16px 24px;
}

/* Card header icon: slightly larger */
.dashboard-card .card-header h2 {
    font-size: 1.02rem;
    font-weight: 700;
}

/* Activity items */
.activity-item {
    border-radius: var(--radius-sm);
    border: 1px solid transparent;
    padding: 11px 14px;
    margin: 0 -14px;
}
</style>

<script>
document.querySelectorAll('.stats-tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var tab = btn.getAttribute('data-tab');

        // Toggle buttons
        document.querySelectorAll('.stats-tab-btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');

        // Toggle stats panels
        document.querySelectorAll('.tab-stats-panel').forEach(function(p) {
            p.style.display = p.getAttribute('data-tab-stats') === tab ? '' : 'none';
        });

        // Toggle content panels
        document.querySelectorAll('.tab-content-panel').forEach(function(p) {
            p.style.display = p.getAttribute('data-tab-content') === tab ? '' : 'none';
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
