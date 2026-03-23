<?php
/**
 * Adjustment Requests Management
 * SDO-BACtrack - BAC Members only
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/AdjustmentRequest.php';
require_once __DIR__ . '/../models/Notification.php';

$auth = auth();
$auth->requireProcurement();

$adjustmentModel = new AdjustmentRequest();

// Handle approval/rejection (must run before header.php outputs HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $auth->isBacSecretary()) {
    $requestId = (int)$_POST['request_id'];
    $action = $_POST['action'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($notes)) {
        setFlashMessage('danger', 'Review notes are required for this action.');
        $auth->redirect(APP_URL . '/admin/adjustments.php');
    }

    $request = $adjustmentModel->findById($requestId);

    if ($request && $request['status'] === 'PENDING') {
        $notificationModel = new Notification();

        if ($action === 'approve') {
            $adjustmentModel->approve($requestId, $auth->getUserId(), $notes);
            $notificationModel->notifyAdjustmentResponse($requestId, $request['step_name'], $request['project_title'], 'APPROVED', $request['requested_by']);
            setFlashMessage('success', 'Adjustment request approved. Timeline updated.');
        } elseif ($action === 'reject') {
            $adjustmentModel->reject($requestId, $auth->getUserId(), $notes);
            $notificationModel->notifyAdjustmentResponse($requestId, $request['step_name'], $request['project_title'], 'REJECTED', $request['requested_by']);
            setFlashMessage('success', 'Adjustment request rejected.');
        }
    }

    $auth->redirect(APP_URL . '/admin/adjustments.php');
}

// Get filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? ''
];

require_once __DIR__ . '/../includes/header.php';

$requests = $adjustmentModel->getAll($filters);
?>

<style>
.filter-bar {
    background: var(--bg-card);
    padding: 16px;
    border-radius: var(--radius-md);
    margin-bottom: 24px;
    border: 1px solid var(--border-color);
}
.filter-form {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    align-items: flex-end;
}
.filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.filter-group label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
}
.filter-select, .filter-input {
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    outline: none;
    background: var(--bg-primary);
    color: var(--text-primary);
    min-width: 150px;
}
.filter-input {
    min-width: 250px;
}

.request-reason {
    font-size: 0.85rem;
    color: var(--text-secondary);
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.action-cell {
    min-width: 250px;
}
</style>

<p style="color: var(--text-muted); margin: 0 0 8px;"><?php echo count($requests); ?> request(s) found</p>

<div class="filter-bar">
    <form class="filter-form" method="GET">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" class="filter-input" placeholder="Project, step, or requester..." value="<?php echo htmlspecialchars($filters['search']); ?>">
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="PENDING" <?php echo $filters['status'] === 'PENDING' ? 'selected' : ''; ?>>Pending</option>
                <option value="APPROVED" <?php echo $filters['status'] === 'APPROVED' ? 'selected' : ''; ?>>Approved</option>
                <option value="REJECTED" <?php echo $filters['status'] === 'REJECTED' ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?php echo APP_URL; ?>/admin/adjustments.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="data-card">
    <?php if (empty($requests)): ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-calendar-check"></i></div>
        <h3>No requests found</h3>
        <p>Try adjusting your filters or check back later.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Process</th>
                    <th>New Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th style="text-align: center;">Status</th>
                    <th>Requested By</th>
                    <?php if ($auth->isBacSecretary()): ?>
                    <th style="text-align: center;">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                <tr>
                    <td>
                        <div style="font-weight: 600;">
                            <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $request['activity_id']; ?>" style="color: var(--primary); text-decoration: none;">
                                <?php echo htmlspecialchars($request['step_name']); ?>
                            </a>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            <?php echo htmlspecialchars($request['project_title']); ?>
                        </div>
                    </td>
                    <td class="date-cell">
                        <?php echo date('M j, Y', strtotime($request['new_start_date'])); ?>
                    </td>
                    <td class="date-cell">
                        <?php echo date('M j, Y', strtotime($request['new_end_date'])); ?>
                    </td>
                    <td>
                        <div class="request-reason" title="<?php echo htmlspecialchars($request['reason']); ?>">
                            <?php echo htmlspecialchars($request['reason']); ?>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                            <?php echo $request['status']; ?>
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.85rem; font-weight: 500;"><?php echo htmlspecialchars($request['requester_name']); ?></span>
                    </td>
                    <?php if ($auth->isBacSecretary()): ?>
                    <td class="action-cell">
                        <?php if ($request['status'] === 'PENDING'): ?>
                        <form method="POST" style="display: flex; gap: 8px; width: 100%; justify-content: center;">
                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Reason/Notes..." required style="flex: 1; max-width: 250px; min-width: 150px;">
                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        <?php else: ?>
                            <div style="text-align: center; font-size: 0.8rem; color: var(--text-muted);">
                                <?php echo $request['reviewed_at'] ? date('M j, Y', strtotime($request['reviewed_at'])) : '-'; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
