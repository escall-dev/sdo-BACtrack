<?php
/**
 * View Project
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/BacCycle.php';
require_once __DIR__ . '/../models/ProjectActivity.php';

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$projectModel = new Project();
$project = $projectModel->findById($projectId);

if (!$project) {
    setFlashMessage('error', 'Project not found.');
    header('Location: ' . APP_URL . '/admin/projects.php');
    exit;
}

$cycleModel = new BacCycle();
$cycles = $cycleModel->getByProject($projectId);
$activeCycle = $cycleModel->getActiveCycle($projectId);

$activityModel = new ProjectActivity();
$activities = $activeCycle ? $activityModel->getByCycle($activeCycle['id']) : [];

// Calculate statistics
$stats = [
    'total' => count($activities),
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'delayed' => 0
];

foreach ($activities as $activity) {
    $status = strtolower($activity['status']);
    if (isset($stats[$status])) {
        $stats[$status]++;
    }
}

$progress = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;
?>

<div class="page-header">
    <div>
        <a href="<?php echo APP_URL; ?>/admin/projects.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?php echo APP_URL; ?>/admin/calendar.php?project=<?php echo $projectId; ?>" class="btn btn-secondary">
            <i class="fas fa-calendar"></i> Calendar
        </a>
        <a href="<?php echo APP_URL; ?>/admin/reports.php?project=<?php echo $projectId; ?>" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Report
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px;">
    <div>
        <!-- Project Info -->
        <div class="data-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Project Information</h2>
            </div>
            <div class="card-body">
                <h3 style="font-size: 1.5rem; margin-bottom: 8px;"><?php echo htmlspecialchars($project['title']); ?></h3>
                <?php if ($project['description']): ?>
                <p style="color: var(--text-secondary); margin-bottom: 16px;"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Procurement Type</label>
                        <p style="font-weight: 500;"><?php echo PROCUREMENT_TYPES[$project['procurement_type']] ?? $project['procurement_type']; ?></p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Created By</label>
                        <p style="font-weight: 500;"><?php echo htmlspecialchars($project['creator_name']); ?></p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Created Date</label>
                        <p style="font-weight: 500;"><?php echo date('F j, Y', strtotime($project['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities List -->
        <div class="data-card">
            <div class="card-header">
                <h2><i class="fas fa-tasks"></i> BAC Activities - Cycle <?php echo $activeCycle ? $activeCycle['cycle_number'] : 1; ?></h2>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($activities)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-tasks"></i></div>
                    <h3>No activities found</h3>
                    <p>This project has no activities generated yet.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Activity</th>
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
                                <td><?php echo $activity['step_order']; ?></td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $activity['id']; ?>" 
                                       style="color: var(--primary); font-weight: 500; text-decoration: none;">
                                        <?php echo htmlspecialchars($activity['step_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($activity['planned_start_date'])); ?></td>
                                <td><?php echo date('M j, Y', strtotime($activity['planned_end_date'])); ?></td>
                                <td>
                                    <?php if (!empty($activity['planned_start_date']) && !empty($activity['planned_end_date'])): ?>
                                        <?php
                                            $startDate = new DateTime($activity['planned_start_date']);
                                            $endDate = new DateTime($activity['planned_end_date']);
                                            $durationDays = $endDate >= $startDate
                                                ? $startDate->diff($endDate)->days + 1 // inclusive of start & end
                                                : null;
                                        ?>
                                        <?php echo $durationDays !== null ? $durationDays . ' days' : '-'; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($activity['status']); ?>">
                                        <?php echo $activity['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($activity['compliance_status']): ?>
                                    <span class="compliance-badge compliance-<?php echo strtolower(str_replace('_', '-', $activity['compliance_status'])); ?>">
                                        <?php echo $activity['compliance_status']; ?>
                                    </span>
                                    <?php else: ?>
                                    <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $activity['id']; ?>" class="btn btn-icon" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
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

    <div>
        <!-- Progress Card -->
        <div class="data-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2><i class="fas fa-chart-pie"></i> Progress</h2>
            </div>
            <div class="card-body">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 3rem; font-weight: 800; color: var(--primary);"><?php echo $progress; ?>%</div>
                    <p style="color: var(--text-muted);">Overall Completion</p>
                </div>
                
                <div style="background: var(--bg-secondary); border-radius: 10px; height: 10px; margin-bottom: 20px;">
                    <div style="background: var(--success); border-radius: 10px; height: 100%; width: <?php echo $progress; ?>%;"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="text-align: center; padding: 12px; background: var(--warning-bg); border-radius: var(--radius-md);">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);"><?php echo $stats['pending']; ?></div>
                        <small style="color: var(--text-muted);">Pending</small>
                    </div>
                    <div style="text-align: center; padding: 12px; background: var(--info-bg); border-radius: var(--radius-md);">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--info);"><?php echo $stats['in_progress']; ?></div>
                        <small style="color: var(--text-muted);">In Progress</small>
                    </div>
                    <div style="text-align: center; padding: 12px; background: var(--success-bg); border-radius: var(--radius-md);">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);"><?php echo $stats['completed']; ?></div>
                        <small style="color: var(--text-muted);">Completed</small>
                    </div>
                    <div style="text-align: center; padding: 12px; background: var(--danger-bg); border-radius: var(--radius-md);">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger);"><?php echo $stats['delayed']; ?></div>
                        <small style="color: var(--text-muted);">Delayed</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cycle Info -->
        <div class="data-card">
            <div class="card-header">
                <h2><i class="fas fa-sync"></i> BAC Cycles</h2>
            </div>
            <div class="card-body">
                <?php if (empty($cycles)): ?>
                <p style="color: var(--text-muted);">No cycles found.</p>
                <?php else: ?>
                <?php foreach ($cycles as $cycle): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: var(--bg-secondary); border-radius: var(--radius-md); margin-bottom: 8px;">
                    <div>
                        <strong>Cycle <?php echo $cycle['cycle_number']; ?></strong>
                        <br><small style="color: var(--text-muted);">Created <?php echo date('M j, Y', strtotime($cycle['created_at'])); ?></small>
                    </div>
                    <span class="status-badge status-<?php echo strtolower($cycle['status']); ?>">
                        <?php echo $cycle['status']; ?>
                    </span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
