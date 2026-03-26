<?php
/**
 * Reports Page
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/timeline.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/BacCycle.php';

$projectModel = new Project();
$cycleModel = new BacCycle();

// Project Owners see only their own projects
$projectFilters = [];
if ($auth->isProjectOwner()) {
    $projectFilters['created_by'] = $auth->getUserId();
}
$projects = $projectModel->getAll($projectFilters);
$selectedProject = isset($_GET['project']) ? (int)$_GET['project'] : null;
$selectedCycle = isset($_GET['cycle']) ? (int)$_GET['cycle'] : null;

$cycles = [];
if ($selectedProject) {
    $cycles = $cycleModel->getByProject($selectedProject);
}
?>

<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
        <i class="fas fa-info-circle" style="margin-right: 6px; color: var(--primary);"></i>
        Select a project and cycle to generate a printable timeline report
    </p>
    <?php if ($selectedProject): ?>
    <a href="<?php echo APP_URL; ?>/admin/report-print.php?project=<?php echo $selectedProject; ?><?php echo $selectedCycle ? '&cycle=' . $selectedCycle : ''; ?>" 
       class="btn btn-success" target="_blank">
        <i class="fas fa-print"></i> Print Report
    </a>
    <?php endif; ?>
</div>

<div class="filter-bar calendar-filter-bar">
    <div class="calendar-filter-header">
        <div class="calendar-filter-right">
            <form class="filter-form calendar-filter-form" method="GET" id="reportForm">
                <div class="filter-group">
                    <label>Select Project</label>
                    <select name="project" class="filter-select" id="projectSelect" required>
                        <option value="">Choose a project...</option>
                        <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project['id']; ?>" <?php echo $selectedProject == $project['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($project['title']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Cycle filter removed -->
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Load
                    </button>
                    <a href="<?php echo APP_URL; ?>/admin/reports.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($selectedProject): 
    require_once __DIR__ . '/../models/ProjectActivity.php';
    require_once __DIR__ . '/../models/ActivityDocument.php';
    
    $project = $projectModel->findById($selectedProject);
    $activityModel = new ProjectActivity();
    $documentModel = new ActivityDocument();
    
    $activities = [];
    if ($selectedCycle) {
        $activities = $activityModel->getByCycle($selectedCycle);
    } else {
        $activities = $activityModel->getByProject($selectedProject);
    }

    // Pagination logic
    $itemsPerPage = 15;
    $totalItems = count($activities);
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    if ($currentPage > $totalPages && $totalPages > 0) {
        $currentPage = $totalPages;
    }
    
    $offset = ($currentPage - 1) * $itemsPerPage;
    $paginatedActivities = array_slice($activities, $offset, $itemsPerPage);

    $timelineSummary = timelineProjectSummary($activities);
    $activityTiming = $timelineSummary['meta_by_id'];
?>

<div class="data-card" style="margin-top: 24px;">
    <div class="card-body" style="padding: 16px;">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
            <div style="padding: 12px; background: var(--bg-secondary); border-radius: var(--radius-md);">
                <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Current Process</label>
                <div style="font-weight: 700; margin-top: 4px;"><?php echo !empty($timelineSummary['current_activity']) ? htmlspecialchars($timelineSummary['current_activity']['step_name']) : 'All process completed'; ?></div>
            </div>
            <div style="padding: 12px; background: var(--bg-secondary); border-radius: var(--radius-md);">
                <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Remaining Process</label>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top: 4px;"><?php echo $timelineSummary['remaining_steps']; ?></div>
            </div>
            <div style="padding: 12px; background: var(--danger-bg); border-radius: var(--radius-md);">
                <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Overdue Process</label>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top: 4px; color: var(--danger);"><?php echo $timelineSummary['overdue_steps']; ?></div>
            </div>
            <div style="padding: 12px; background: var(--info-bg); border-radius: var(--radius-md);">
                <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Planned Process Days</label>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top: 4px; color: var(--info);"><?php echo $timelineSummary['total_planned_days']; ?></div>
            </div>
        </div>
    </div>
</div>

<div class="data-card" style="margin-top: 24px;">
    <div class="card-header">
        <h2><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($project['title']); ?> - Timeline Report Preview</h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($activities)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-calendar-times"></i></div>
            <h3>No activities found</h3>
            <p>This project or cycle has no activities.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Process</th>
                        <th>Activity Name</th>
                        <th>Planned Start</th>
                        <th>Planned End</th>
                        <th style="text-align: center;">Duration (Days)</th>
                        <th>Actual Completion</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Compliance</th>
                        <th>Documents</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedActivities as $activity): 
                        $docCount = $documentModel->getCountByActivity($activity['id']);
                    ?>
                    <tr>
                        <td><?php echo $activity['step_order']; ?></td>
                        <td>
                            <?php echo htmlspecialchars($activity['step_name']); ?>
                            <br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($activityTiming[$activity['id']]['timing_label'] ?? ''); ?></small>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($activity['planned_start_date'])); ?></td>
                        <td><?php echo date('M j, Y', strtotime($activity['planned_end_date'])); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($activityTiming[$activity['id']]['duration_label'] ?? '-'); ?></td>
                        <td>
                            <?php echo $activity['actual_completion_date'] 
                                ? date('M j, Y', strtotime($activity['actual_completion_date'])) 
                                : '-'; ?>
                        </td>
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
                            -
                            <?php endif; ?>
                        </td>
                        <td><?php echo $docCount; ?> file(s)</td>
                    </tr>
                    <?php if ($activity['compliance_remarks']): ?>
                    <tr>
                        <td colspan="8" style="background: var(--bg-secondary); font-size: 0.85rem; color: var(--text-secondary);">
                            <strong>Remarks:</strong> <?php echo htmlspecialchars($activity['compliance_remarks']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-footer" style="padding: 16px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <div class="pagination-info" style="color: var(--text-muted); font-size: 0.85rem;">
            Showing <?php echo $totalItems > 0 ? $offset + 1 : 0; ?> to <?php echo min($offset + $itemsPerPage, $totalItems); ?> of <?php echo $totalItems; ?> entries
        </div>
        <div class="pagination-controls" style="display: flex; gap: 8px;">
            <?php 
                $baseUrl = APP_URL . '/admin/reports.php?project=' . $selectedProject;
                if ($selectedCycle) $baseUrl .= '&cycle=' . $selectedCycle;
            ?>
            <a href="<?php echo $currentPage > 1 ? $baseUrl . '&page=' . ($currentPage - 1) : '#'; ?>" 
               class="btn btn-secondary <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>" 
               style="padding: 6px 14px; font-size: 0.85rem; <?php echo $currentPage <= 1 ? 'pointer-events: none; opacity: 0.5; background: var(--bg-secondary);' : 'background: white; border-color: var(--border-color); color: var(--text-primary);'; ?>">
                <i class="fas fa-chevron-left" style="margin-right: 4px; font-size: 0.75rem;"></i> Prev
            </a>
            <a href="<?php echo $currentPage < $totalPages ? $baseUrl . '&page=' . ($currentPage + 1) : '#'; ?>" 
               class="btn btn-secondary <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>"
               style="padding: 6px 14px; font-size: 0.85rem; <?php echo $currentPage >= $totalPages ? 'pointer-events: none; opacity: 0.5; background: var(--bg-secondary);' : 'background: white; border-color: var(--border-color); color: var(--text-primary);'; ?>">
                Next <i class="fas fa-chevron-right" style="margin-left: 4px; font-size: 0.75rem;"></i>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('projectSelect').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('reportForm').submit();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
