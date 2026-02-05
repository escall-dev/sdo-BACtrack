<?php
/**
 * Reports Page
 * SDO-BACtrack
 */

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

<div class="page-header">
    <div>
        <h2 style="margin: 0;">Generate Timeline Report</h2>
        <p style="color: var(--text-muted); margin: 4px 0 0;">Select a project and cycle to generate a printable report</p>
    </div>
</div>

<div class="data-card">
    <div class="card-body">
        <form method="GET" id="reportForm">
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Select Project</label>
                    <select name="project" class="form-control" id="projectSelect" required>
                        <option value="">Choose a project...</option>
                        <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project['id']; ?>" <?php echo $selectedProject == $project['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($project['title']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Select Cycle</label>
                    <select name="cycle" class="form-control" id="cycleSelect" <?php echo empty($cycles) ? 'disabled' : ''; ?>>
                        <option value="">All Cycles</option>
                        <?php foreach ($cycles as $cycle): ?>
                        <option value="<?php echo $cycle['id']; ?>" <?php echo $selectedCycle == $cycle['id'] ? 'selected' : ''; ?>>
                            Cycle <?php echo $cycle['cycle_number']; ?> (<?php echo $cycle['status']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Load
                    </button>
                    <?php if ($selectedProject): ?>
                    <a href="<?php echo APP_URL; ?>/admin/report-print.php?project=<?php echo $selectedProject; ?><?php echo $selectedCycle ? '&cycle=' . $selectedCycle : ''; ?>" 
                       class="btn btn-success" target="_blank">
                        <i class="fas fa-print"></i> Print Report
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
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
?>

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
                        <th>Step</th>
                        <th>Activity Name</th>
                        <th>Planned Start</th>
                        <th>Planned End</th>
                        <th>Duration (Days)</th>
                        <th>Actual Completion</th>
                        <th>Status</th>
                        <th>Compliance</th>
                        <th>Documents</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): 
                        $docCount = $documentModel->getCountByActivity($activity['id']);
                    ?>
                    <tr>
                        <td><?php echo $activity['step_order']; ?></td>
                        <td><?php echo htmlspecialchars($activity['step_name']); ?></td>
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
                            <?php echo $activity['actual_completion_date'] 
                                ? date('M j, Y', strtotime($activity['actual_completion_date'])) 
                                : '-'; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace('_', '-', $activity['status'])); ?>">
                                <?php echo $activity['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($activity['compliance_status']): ?>
                            <span class="compliance-badge compliance-<?php echo strtolower(str_replace('_', '-', $activity['compliance_status'])); ?>">
                                <?php echo $activity['compliance_status']; ?>
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
