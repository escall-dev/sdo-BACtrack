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

require_once __DIR__ . '/../includes/header.php';

$pendingRequests = $adjustmentModel->getPending();
?>

<p style="color: var(--text-muted); margin: 0 0 8px;"><?php echo count($pendingRequests); ?> pending request(s)</p>

<?php if (empty($pendingRequests)): ?>
<div class="data-card">
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-calendar-check"></i></div>
        <h3>No pending requests</h3>
        <p>All timeline adjustment requests have been processed.</p>
    </div>
</div>
<?php else: ?>
<div style="display: flex; flex-direction: column; gap: 16px;">
    <?php foreach ($pendingRequests as $request): ?>
    <div class="data-card" style="padding: 24px; border: 1px solid var(--border-color); border-left: 4px solid var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
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
                <div class="user-cell" style="margin-top: 4px;">
                    <?php if (!empty($request['requester_avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($request['requester_avatar']); ?>" alt="Avatar" class="user-avatar-sm">
                    <?php else: ?>
                    <div class="user-avatar-placeholder-sm">
                        <?php echo strtoupper(substr($request['requester_name'], 0, 1)); ?>
                    </div>
                    <?php endif; ?>
                    <span style="font-weight: 500;"><?php echo htmlspecialchars($request['requester_name']); ?></span>
                </div>
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

        <?php if ($auth->isBacSecretary()): ?>
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
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
