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

    if ($action === 'reject' && empty($notes)) {
        setFlashMessage('danger', 'Review notes (reason) are required when rejecting a request.');
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
    width: 120px;
}
.action-wrapper {
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
    backdrop-filter: blur(2px);
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background-color: var(--card-bg);
    margin: 10% auto;
    width: 90%;
    max-width: 500px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

.modal-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-header h3 i {
    color: var(--danger);
    font-size: 1.2rem;
}

.close-btn {
    background: #f1f5f9;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    cursor: pointer;
    color: var(--text-muted);
    transition: all 0.2s;
}

.close-btn:hover {
    background: var(--danger-bg);
    color: var(--danger);
    transform: rotate(90deg);
}

.modal-body {
    padding: 24px;
    background: #fafafa;
}

#modal-notes {
    border: 1.5px solid var(--border-color);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    resize: none;
}

#modal-notes:focus {
    border-color: var(--danger);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1), inset 0 2px 4px rgba(0,0,0,0.02);
    outline: none;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: #ffffff;
}

.btn-modal {
    padding: 10px 20px;
    font-weight: 600;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-modal-cancel {
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
}

.btn-modal-cancel:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

.btn-modal-reject {
    background: var(--danger);
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
}

.btn-modal-reject:hover {
    background: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 6px 15px rgba(239, 68, 68, 0.35);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
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
        <table class="data-table" data-paginate="15">
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
                        <div class="action-wrapper">
                            <?php if ($request['status'] === 'PENDING'): ?>
                                <form method="POST" style="display: flex; gap: 8px;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="openRejectModal(<?php echo $request['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <div style="text-align: center; font-size: 0.8rem; color: var(--text-muted);">
                                    <div><?php echo $request['reviewed_at'] ? date('M j, Y', strtotime($request['reviewed_at'])) : '-'; ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-circle"></i> Rejection Reason</h3>
            <button type="button" class="close-btn" onclick="closeRejectModal()">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="request_id" id="modal-request-id">
            <input type="hidden" name="action" value="reject">
            <div class="modal-body">
                <label for="modal-notes" style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 10px;">
                    REASON FOR REJECTION
                </label>
                <textarea name="notes" class="form-control" rows="4" placeholder="Type your reason here..." required id="modal-notes" style="width: 100%; border-radius: var(--radius-md); padding: 12px; font-size: 0.95rem;"></textarea>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px;">
                    <i class="fas fa-info-circle"></i> This note will be visible to the requester.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-cancel" onclick="closeRejectModal()">
                    <i class="fas fa-undo"></i> Cancel
                </button>
                <button type="submit" class="btn-modal btn-modal-reject">
                    <i class="fas fa-minus-circle"></i> Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id) {
    document.getElementById('modal-request-id').value = id;
    document.getElementById('rejectModal').style.display = 'block';
    setTimeout(() => {
        document.getElementById('modal-notes').focus();
    }, 100);
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

// Close on click outside
window.onclick = function(event) {
    const modal = document.getElementById('rejectModal');
    if (event.target == modal) {
        closeRejectModal();
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
