<?php
/**
 * Adjustment Requests Management
 * SDO-BACtrack - BAC Members only
 */

require_once __DIR__ . '/../includes/auth.php';
$auth = auth();
$auth->requireProcurement();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/AdjustmentRequest.php';
require_once __DIR__ . '/../models/Notification.php';

$adjustmentModel = new AdjustmentRequest();

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = (int)$_POST['request_id'];
    $action = $_POST['action'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

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

    header('Location: ' . APP_URL . '/admin/adjustments.php');
    exit;
}

$pendingRequests = $adjustmentModel->getPending();
?>

<div class="page-header">
    <div>
        <h2 style="margin: 0;">Timeline Adjustment Requests</h2>
        <p style="color: var(--text-muted); margin: 4px 0 0;"><?php echo count($pendingRequests); ?> pending request(s)</p>
    </div>
</div>

<div class="data-card">
    <?php if (empty($pendingRequests)): ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-calendar-check"></i></div>
        <h3>No pending requests</h3>
        <p>All timeline adjustment requests have been processed.</p>
    </div>
    <?php else: ?>
    <?php foreach ($pendingRequests as $request): ?>
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
            <div>
                <h3 style="font-size: 1.1rem; margin: 0;">
                    <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $request['activity_id']; ?>" style="color: var(--primary); text-decoration: none;">
                        <?php echo htmlspecialchars($request['step_name']); ?>
                    </a>
                </h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 4px 0 0;">
                    <?php echo htmlspecialchars($request['project_title']); ?>
                </p>
            </div>
            <span class="status-badge status-pending">PENDING</span>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Requested By</label>
                <p style="font-weight: 500;"><?php echo htmlspecialchars($request['requester_name']); ?></p>
            </div>
            <div>
                <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">New Start Date</label>
                <p style="font-weight: 500;"><?php echo date('M j, Y', strtotime($request['new_start_date'])); ?></p>
            </div>
            <div>
                <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">New End Date</label>
                <p style="font-weight: 500;"><?php echo date('M j, Y', strtotime($request['new_end_date'])); ?></p>
            </div>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Reason</label>
            <p style="background: var(--bg-secondary); padding: 12px; border-radius: var(--radius-md); margin-top: 4px;">
                <?php echo nl2br(htmlspecialchars($request['reason'])); ?>
            </p>
        </div>

        <form method="POST" style="display: flex; gap: 12px; align-items: flex-end;">
            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
            <div style="flex: 1;">
                <label class="form-label">Review Notes (Optional)</label>
                <input type="text" name="notes" class="form-control" placeholder="Add notes for this decision...">
            </div>
            <button type="submit" name="action" value="approve" class="btn btn-success">
                <i class="fas fa-check"></i> Approve
            </button>
            <button type="submit" name="action" value="reject" class="btn btn-danger">
                <i class="fas fa-times"></i> Reject
            </button>
        </form>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
