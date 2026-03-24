<?php
/**
 * User Management
 * SDO-BACtrack - BAC Members only
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/User.php';

$auth = auth();
$auth->requireSuperAdmin();

$userModel = new User();

// Handle form submissions — must run BEFORE header.php outputs any HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Create user
    if ($action === 'create') {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? 'ADMIN',
            'position' => trim($_POST['position'] ?? '')
        ];

        // Only superadmin can create superadmin users
        if ($data['role'] === 'SUPERADMIN' && !$auth->isSuperAdmin()) {
            $data['role'] = 'ADMIN';
        }

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            setFlashMessage('error', 'All fields are required.');
        } elseif ($userModel->findByEmail($data['email'])) {
            setFlashMessage('error', 'Email address already exists.');
        } else {
            $userModel->create($data);
            setFlashMessage('success', 'User created successfully.');
        }
        $auth->redirect(APP_URL . '/admin/users.php');
    }

    // Update user
    if ($action === 'update') {
        $userId = (int)$_POST['user_id'];
        $targetUser = $userModel->findById($userId);
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => $_POST['role'] ?? 'ADMIN',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'position' => trim($_POST['position'] ?? '')
        ];

        // Protect superadmin accounts from non-superadmins
        if ($targetUser && $targetUser['role'] === 'SUPERADMIN' && !$auth->isSuperAdmin()) {
            setFlashMessage('error', 'You cannot modify a Superadmin account.');
        } elseif ($data['role'] === 'SUPERADMIN' && !$auth->isSuperAdmin()) {
            setFlashMessage('error', 'Only a Superadmin can assign the Superadmin role.');
        } else {
            $existingUser = $userModel->findByEmail($data['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                setFlashMessage('error', 'Email address already exists.');
            } else {
                $userModel->update($userId, $data);
                if (!empty($_POST['password'])) {
                    $userModel->updatePassword($userId, $_POST['password']);
                }
                setFlashMessage('success', 'User updated successfully.');
            }
        }
        $auth->redirect(APP_URL . '/admin/users.php');
    }

    // Approve user (pending registration)
    if ($action === 'approve') {
        $userId = (int)$_POST['user_id'];
        $userModel->update($userId, ['status' => 'APPROVED', 'is_active' => 1]);
        setFlashMessage('success', 'User approved. They can now sign in.');
        $auth->redirect(APP_URL . '/admin/users.php');
    }

    // Activate user
    if ($action === 'activate') {
        $userId = (int)$_POST['user_id'];
        $targetUser = $userModel->findById($userId);

        if (!$targetUser) {
            setFlashMessage('error', 'User not found.');
        } elseif ($targetUser['role'] === 'SUPERADMIN' && !$auth->isSuperAdmin()) {
            setFlashMessage('error', 'You cannot modify a Superadmin account.');
        } elseif (isset($targetUser['is_active']) && (int)$targetUser['is_active'] === 1) {
            setFlashMessage('error', 'User is already active.');
        } else {
            $userModel->update($userId, ['is_active' => 1]);
            setFlashMessage('success', 'User activated successfully.');
        }
        $auth->redirect(APP_URL . '/admin/users.php');
    }

    // Deactivate user
    if ($action === 'deactivate') {
        $userId = (int)$_POST['user_id'];
        $targetUser = $userModel->findById($userId);

        if ($userId === $auth->getUserId()) {
            setFlashMessage('error', 'You cannot deactivate your own account.');
        } elseif (!$targetUser) {
            setFlashMessage('error', 'User not found.');
        } elseif ($targetUser['role'] === 'SUPERADMIN' && !$auth->isSuperAdmin()) {
            setFlashMessage('error', 'You cannot deactivate a Superadmin account.');
        } elseif (($targetUser['status'] ?? '') === 'PENDING') {
            setFlashMessage('error', 'Pending users cannot be deactivated. Approve them first.');
        } elseif (isset($targetUser['is_active']) && (int)$targetUser['is_active'] === 0) {
            setFlashMessage('error', 'User is already inactive.');
        } else {
            $userModel->update($userId, ['is_active' => 0]);
            setFlashMessage('success', 'User deactivated successfully.');
        }
        $auth->redirect(APP_URL . '/admin/users.php');
    }

    // Delete user
    if ($action === 'delete') {
        $userId = (int)$_POST['user_id'];
        $targetUser = $userModel->findById($userId);

        if ($userId === $auth->getUserId()) {
            setFlashMessage('error', 'You cannot delete your own account.');
        } elseif (!$targetUser) {
            setFlashMessage('error', 'User not found.');
        } elseif ($targetUser['role'] === 'SUPERADMIN' && !$auth->isSuperAdmin()) {
            setFlashMessage('error', 'You cannot delete a Superadmin account.');
        } else {
            try {
                $deleted = $userModel->delete($userId);
                if ($deleted) {
                    setFlashMessage('success', 'User deleted successfully.');
                } else {
                    setFlashMessage('error', 'Unable to delete user.');
                }
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'SQLSTATE[23000]') !== false) {
                    setFlashMessage('error', 'Cannot delete this user because they are linked to existing project records.');
                } else {
                    setFlashMessage('error', 'Unable to delete user right now. Please try again.');
                }
            }
        }
        $auth->redirect(APP_URL . '/admin/users.php');
    }
}

$users = $userModel->getAll();

require_once __DIR__ . '/../includes/header.php';

// Simple in-memory filters for UI (search / role / status)
$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'role' => $_GET['role'] ?? '',
    'status' => $_GET['status'] ?? '',
];

$filteredUsers = array_filter($users, function ($user) use ($filters) {
    // Search by name or email
    if ($filters['search'] !== '') {
        $haystack = strtolower(($user['name'] ?? '') . ' ' . ($user['email'] ?? ''));
        if (strpos($haystack, strtolower($filters['search'])) === false) {
            return false;
        }
    }

    // Filter by role
    if ($filters['role'] !== '' && isset($user['role']) && $user['role'] !== $filters['role']) {
        return false;
    }

    // Filter by status (PENDING, ACTIVE, INACTIVE)
    if ($filters['status'] !== '') {
        $approvalStatus = $user['status'] ?? 'APPROVED';
        $isActive = isset($user['is_active']) ? (int)$user['is_active'] === 1 : true;

        if ($filters['status'] === 'PENDING' && $approvalStatus !== 'PENDING') return false;
        if ($filters['status'] === 'ACTIVE' && !($approvalStatus === 'APPROVED' && $isActive)) return false;
        if ($filters['status'] === 'INACTIVE' && !($approvalStatus === 'APPROVED' && !$isActive)) return false;
    }

    return true;
});

$displayUsers = array_values($filteredUsers);
?>

<style>
/* Page-specific refinements moved to admin.css */
.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}
.cell-primary {
    font-weight: 600;
    color: var(--text-primary);
}
.cell-secondary {
    font-size: 0.8rem;
    color: var(--text-muted);
}
</style>

<div class="page-header">
    <div>
        <p style="color: var(--text-muted); margin: 4px 0 0;"><?php echo count($displayUsers); ?> user(s)</p>
    </div>
    <button class="btn btn-primary" onclick="openUserModal()">
        <i class="fas fa-plus"></i> Add User
    </button>
</div>

<!-- Filters -->
<div class="filter-bar calendar-filter-bar">
    <div class="calendar-filter-header">
        <div class="calendar-filter-right">
            <form method="GET" class="filter-form calendar-filter-form" onkeydown="if(event.key==='Enter'){event.preventDefault();this.submit();}">
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" class="filter-input"
                           placeholder="Name, email..."
                           value="<?php echo htmlspecialchars($filters['search']); ?>">
                </div>

                <div class="filter-group">
                    <label>Role</label>
                    <select name="role" class="filter-select">
                        <option value="">All Roles</option>
                        <?php foreach (USER_ROLES as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $filters['role'] === $key ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Status</label>
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="ACTIVE" <?php echo $filters['status'] === 'ACTIVE' ? 'selected' : ''; ?>>Active</option>
                        <option value="INACTIVE" <?php echo $filters['status'] === 'INACTIVE' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="PENDING" <?php echo $filters['status'] === 'PENDING' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="<?php echo APP_URL; ?>/admin/users.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="data-card">
    <?php if (empty($displayUsers)): ?>
    <div class="empty-state small">
        <span class="empty-icon"><i class="fas fa-users"></i></span>
        <h3>No users found</h3>
        <p>Try adjusting your filters or add a new user.</p>
    </div>
    <?php else: ?>
    <div class="list-container" data-paginate="15">
        <div class="list-header">
            <div class="list-header-main">
                <span class="list-header-label list-col" style="flex: 0 0 260px;">User</span>
                <div class="list-col" style="display: flex; align-items: center; gap: 14px; width: 100%;">
                    <div class="list-header-label list-col" style="text-align: center;">Position</div>
                    <div class="list-header-label list-col" style="text-align: center;">Role</div>
                    <div class="list-header-label list-col" style="text-align: center;">Status</div>
                    <div class="list-header-label list-col" style="text-align: center;">Date Created</div>
                </div>
            </div>
            <span class="list-header-label list-col-fixed" style="width: 120px; text-align: right;">Actions</span>
        </div>
        <?php foreach ($displayUsers as $user): ?>
        <?php
        $roleCssMap = [
            'ADMIN'         => 'role-procurement', // Reusing existing CSS or can add new
            'BAC_SECRETARY' => 'role-bac-secretary',
            'SUPERADMIN'    => 'role-superadmin',
        ];
        $roleCss = $roleCssMap[$user['role']] ?? 'role-procurement';
        $approvalStatus = $user['status'] ?? 'APPROVED';
        $isActive = isset($user['is_active']) ? (int)$user['is_active'] === 1 : true;
        $canEditThisUser = ($user['role'] !== 'SUPERADMIN' || $auth->isSuperAdmin());
        ?>
        <div class="list-row">
            <div class="list-row-main">
                <div class="user-cell list-col" style="flex: 0 0 260px;">
                    <?php if (!empty($user['avatar_url'])): ?>
                    <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Avatar" class="user-avatar-sm">
                    <?php else: ?>
                    <div class="user-avatar-placeholder-sm">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <?php endif; ?>
                    <div>
                        <div class="cell-primary">
                            <?php echo htmlspecialchars($user['name']); ?>
                            <?php if ($user['id'] === $auth->getUserId()): ?>
                            <span style="font-size: 0.75rem; color: var(--primary); margin-left: 4px;">(You)</span>
                            <?php endif; ?>
                        </div>
                        <div class="cell-secondary">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </div>
                    </div>
                </div>

                <div class="list-col" style="display: flex; align-items: center; gap: 14px; width: 100%;">
                    <div class="list-col" style="display: flex; justify-content: center;">
                        <span class="position-badge" style="color:var(--text-primary);background:var(--bg-secondary);border:1px solid var(--border-color);font-weight:600;" title="<?php echo htmlspecialchars($user['position'] ?? 'N/A'); ?>">
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($user['position'] ?? 'N/A'); ?></span>
                        </span>
                    </div>

                    <div class="list-col" style="display: flex; justify-content: center;">
                        <span class="role-badge <?php echo $roleCss; ?>">
                            <?php echo htmlspecialchars(USER_ROLES[$user['role']] ?? $user['role']); ?>
                        </span>
                    </div>

                    <div class="list-col" style="display: flex; justify-content: center;">
                        <?php if ($approvalStatus === 'PENDING'): ?>
                        <span class="status-badge status-pending">Pending</span>
                        <?php elseif (!$isActive): ?>
                        <span class="status-badge status-inactive">Inactive</span>
                        <?php else: ?>
                        <span class="status-badge status-active">Active</span>
                        <?php endif; ?>
                    </div>

                    <div class="list-col" style="display: flex; justify-content: center;">
                        <span class="position-badge" style="color:var(--text-muted);background:var(--bg-secondary);border:1px solid var(--border-color); font-weight: 500;">
                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="list-col-actions">
                <div class="action-buttons">
                    <?php if (($user['status'] ?? '') === 'PENDING'): ?>
                    <button type="button" class="btn btn-icon" title="Approve" onclick='openApproveModal(<?php echo (int)$user["id"]; ?>, <?php echo json_encode($user["name"]); ?>, <?php echo json_encode($user["email"]); ?>)'>
                        <i class="fas fa-check"></i>
                    </button>
                    <?php endif; ?>

                    <?php if ($canEditThisUser): ?>
                    <button class="btn btn-icon" title="Edit" onclick='editUser(<?php echo json_encode(array_merge($user, ["is_active" => (int)($user["is_active"] ?? 1)])); ?>)'>
                        <i class="fas fa-edit"></i>
                    </button>
                    <?php endif; ?>

                    <?php if ($user['id'] !== $auth->getUserId() && $canEditThisUser && $approvalStatus === 'APPROVED' && (int)($user['is_active'] ?? 1) === 1): ?>
                    <button class="btn btn-icon" title="Deactivate"
                            onclick='openDeactivateModal(<?php echo (int)$user["id"]; ?>, <?php echo json_encode($user["name"]); ?>, <?php echo json_encode($user["email"]); ?>)'>
                        <i class="fas fa-ban"></i>
                    </button>
                    <?php elseif ($user['id'] !== $auth->getUserId() && $canEditThisUser && $approvalStatus === 'APPROVED' && (int)($user['is_active'] ?? 1) === 0): ?>
                    <button class="btn btn-icon text-success" title="Activate"
                            onclick='openActivateModal(<?php echo (int)$user["id"]; ?>, <?php echo json_encode($user["name"]); ?>, <?php echo json_encode($user["email"]); ?>)'>
                        <i class="fas fa-check-circle"></i>
                    </button>
                    <?php endif; ?>

                    <?php if ($user['id'] !== $auth->getUserId() && $canEditThisUser): ?>
                    <button class='btn btn-icon text-danger' title='Delete'
                            onclick='deleteUser(<?php echo (int)$user["id"]; ?>, <?php echo json_encode($user["name"]); ?>, <?php echo json_encode($user["email"]); ?>)'>
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Activate Confirmation Modal -->
<div id="activateModal" class="modal-overlay">
    <div class="modal-container modal-confirm">
        <form method="POST" id="activateForm">
            <div class="modal-header">
                <h2><i class="fas fa-user-check" style="margin-right: 8px; color: var(--success);"></i>Activate User</h2>
                <button class="modal-close" type="button" onclick="closeActivateModal()">&times;</button>
            </div>

            <input type="hidden" name="action" value="activate">
            <input type="hidden" name="user_id" id="activateUserId" value="">

            <div class="modal-body">
                <p>Reactivate this account?</p>
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px; margin: 12px 0;">
                    <div style="font-weight: 700; color: var(--text-primary);" id="activateUserName">-</div>
                    <div style="color: var(--text-muted); margin-top: 2px;" id="activateUserEmail">-</div>
                </div>
                <p class="form-hint">This user will be able to sign in again.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeActivateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Activate</button>
            </div>
        </form>
    </div>
</div>

<!-- Deactivate Confirmation Modal -->
<div id="deactivateModal" class="modal-overlay">
    <div class="modal-container modal-confirm">
        <form method="POST" id="deactivateForm">
            <div class="modal-header">
                <h2><i class="fas fa-user-slash" style="margin-right: 8px;"></i>Deactivate User</h2>
                <button class="modal-close" type="button" onclick="closeDeactivateModal()">&times;</button>
            </div>

            <input type="hidden" name="action" value="deactivate">
            <input type="hidden" name="user_id" id="deactivateUserId" value="">

            <div class="modal-body">
                <p>Deactivate this account?</p>
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px; margin: 12px 0;">
                    <div style="font-weight: 700; color: var(--text-primary);" id="deactivateUserName">-</div>
                    <div style="color: var(--text-muted); margin-top: 2px;" id="deactivateUserEmail">-</div>
                </div>
                <p class="form-hint">Inactive users will no longer be able to sign in.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeactivateModal()">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-ban"></i> Deactivate</button>
            </div>
        </form>
    </div>
</div>

<!-- Add / Edit User Modal -->
<div id="userModal" class="modal-overlay">
    <div class="modal-container">
        <form method="POST" id="userForm">
            <div class="modal-header">
                <h2 id="modalTitle">Add User</h2>
                <button class="modal-close" type="button" onclick="closeUserModal()">&times;</button>
            </div>

            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="user_id" id="userId" value="">
            
            <div class="modal-body">
                <div class="form-section-title">Personal Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name <span style="color: var(--danger);">*</span></label>
                        <div class="input-group-custom">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" id="userName" class="form-control" placeholder="Enter full name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span style="color: var(--danger);">*</span></label>
                        <div class="input-group-custom">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" id="userEmail" class="form-control" placeholder="email@example.com" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 24px;">
                        <label class="form-label">Position</label>
                        <div class="input-group-custom">
                            <i class="fas fa-id-badge"></i>
                            <input type="text" name="position" id="userPosition" class="form-control" placeholder="e.g. IT Officer, Teacher III...">
                        </div>
                    </div>
                </div>

                <div class="form-section-title">Account Settings</div>
                <div class="form-row" style="align-items: flex-end;">
                    <div class="form-group">
                        <label class="form-label">Role <span style="color: var(--danger);">*</span></label>
                        <div class="input-group-custom">
                            <i class="fas fa-user-tag"></i>
                            <select name="role" id="userRole" class="form-control" required style="padding-left: 42px;">
                                <option value="" disabled selected>-- Select Role --</option>
                                <?php foreach (USER_ROLES as $key => $value): ?>
                                <?php if ($key === 'SUPERADMIN' && !$auth->isSuperAdmin()) continue; ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="statusGroup" style="display: none;">
                        <label class="toggle-wrapper" for="userActive">
                            <span class="switch">
                                <input type="checkbox" name="is_active" id="userActive" value="1" checked>
                                <span class="slider"></span>
                            </span>
                            <span class="toggle-label-text">Account is active</span>
                        </label>
                    </div>
                </div>

                <div class="form-section-title">Security</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password <span id="passwordRequired" style="color: var(--danger);">*</span></label>
                        <div class="input-group-custom">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="userPassword" class="form-control" minlength="6" placeholder="••••••••">
                        </div>
                        <small id="passwordHelp" class="form-hint" style="display: none;">Leave blank to keep current password</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group-custom">
                            <i class="fas fa-shield-alt"></i>
                            <input type="password" id="userPasswordConfirm" class="form-control" minlength="6" placeholder="••••••••">
                        </div>
                        <small class="form-hint">Re-enter password to confirm</small>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> <span id="submitBtnLabel">Create User</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Approve User Confirmation Modal -->
<div id="approveModal" class="modal-overlay">
    <div class="modal-container modal-confirm">
        <form method="POST" id="approveForm">
            <div class="modal-header">
                <h2><i class="fas fa-user-check" style="margin-right: 8px; color: var(--success);"></i>Approve User</h2>
                <button class="modal-close" type="button" onclick="closeApproveModal()" aria-label="Close">&times;</button>
            </div>
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="user_id" id="approveUserId" value="">
            <div class="modal-body">
                <p class="modal-confirm-message">Approve this user so they can sign in?</p>
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px; margin: 12px 0;">
                    <div style="font-weight: 700; color: var(--text-primary);" id="approveUserName">User Name</div>
                    <div style="color: var(--text-muted); margin-top: 2px;" id="approveUserEmail">user@example.com</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeApproveModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Approve
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-container modal-confirm">
        <form method="POST" id="deleteForm">
            <div class="modal-header">
                <h2><i class="fas fa-trash" style="margin-right: 8px;"></i>Delete User</h2>
                <button class="modal-close" type="button" onclick="closeDeleteModal()">&times;</button>
            </div>

            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="user_id" id="deleteUserId" value="">
            
            <div class="modal-body">
                <p>Are you sure you want to delete this account?</p>
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px; margin: 12px 0;">
                    <div style="font-weight: 700; color: var(--text-primary);" id="deleteUserName">-</div>
                    <div style="color: var(--text-muted); margin-top: 2px;" id="deleteUserEmail">-</div>
                </div>
                <p class="form-hint" style="color: var(--danger);">This action cannot be undone.</p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUserModal() {
    document.getElementById('modalTitle').textContent = 'Add User';
    document.getElementById('formAction').value = 'create';
    document.getElementById('userId').value = '';
    document.getElementById('userForm').reset();
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('passwordHelp').style.display = 'none';
    document.getElementById('statusGroup').style.display = 'none';
    document.getElementById('submitBtnLabel').textContent = 'Create User';
    document.getElementById('userModal').classList.add('show');
}

function editUser(user) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('formAction').value = 'update';
    document.getElementById('userId').value = user.id;
    document.getElementById('userName').value = user.name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userPosition').value = user.position || '';
    document.getElementById('userRole').value = user.role;
    document.getElementById('userActive').checked = user.is_active == 1;
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = false;
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('passwordHelp').style.display = 'block';
    document.getElementById('statusGroup').style.display = 'block';
    document.getElementById('submitBtnLabel').textContent = 'Update User';
    document.getElementById('userModal').classList.add('show');
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('show');
}

function openApproveModal(id, name, email) {
    document.getElementById('approveUserId').value = id;
    if(name) document.getElementById('approveUserName').textContent = name;
    if(email) document.getElementById('approveUserEmail').textContent = email;
    document.getElementById('approveModal').classList.add('show');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.remove('show');
}

function openActivateModal(id, name, email) {
    document.getElementById('activateUserId').value = id;
    document.getElementById('activateUserName').textContent = name;
    document.getElementById('activateUserEmail').textContent = email;
    document.getElementById('activateModal').classList.add('show');
}

function closeActivateModal() {
    document.getElementById('activateModal').classList.remove('show');
}

function openDeactivateModal(id, name, email) {
    document.getElementById('deactivateUserId').value = id;
    document.getElementById('deactivateUserName').textContent = name;
    document.getElementById('deactivateUserEmail').textContent = email;
    document.getElementById('deactivateModal').classList.add('show');
}

function closeDeactivateModal() {
    document.getElementById('deactivateModal').classList.remove('show');
}

function deleteUser(id, name, email) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteUserEmail').textContent = email;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

// Close overlay modals when clicking outside the card
document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.classList.remove('show');
        }
    });
});

// Close modals with Escape
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    if (document.getElementById('approveModal').classList.contains('show')) closeApproveModal();
    if (document.getElementById('activateModal').classList.contains('show')) closeActivateModal();
    if (document.getElementById('deactivateModal').classList.contains('show')) closeDeactivateModal();
    if (document.getElementById('deleteModal').classList.contains('show')) closeDeleteModal();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
