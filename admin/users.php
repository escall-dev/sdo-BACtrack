<?php
/**
 * User Management
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/User.php';

// Require procurement role
$auth->requireProcurement();

$userModel = new User();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Create user
    if ($action === 'create') {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? 'PROJECT_OWNER'
        ];

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            setFlashMessage('error', 'All fields are required.');
        } elseif ($userModel->findByEmail($data['email'])) {
            setFlashMessage('error', 'Email address already exists.');
        } else {
            $userId = $userModel->create($data);
            setFlashMessage('success', 'User created successfully.');
        }
        if (!headers_sent()) {
            header('Location: ' . APP_URL . '/admin/users.php');
            exit;
        }
    }

    // Update user
    if ($action === 'update') {
        $userId = (int)$_POST['user_id'];
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => $_POST['role'] ?? 'PROJECT_OWNER',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Check email uniqueness
        $existingUser = $userModel->findByEmail($data['email']);
        if ($existingUser && $existingUser['id'] != $userId) {
            setFlashMessage('error', 'Email address already exists.');
        } else {
            $userModel->update($userId, $data);
            
            // Update password if provided
            if (!empty($_POST['password'])) {
                $userModel->updatePassword($userId, $_POST['password']);
            }
            
            setFlashMessage('success', 'User updated successfully.');
        }
        if (!headers_sent()) {
            header('Location: ' . APP_URL . '/admin/users.php');
            exit;
        }
    }

    // Delete user
    if ($action === 'delete') {
        $userId = (int)$_POST['user_id'];
        
        // Prevent self-deletion
        if ($userId === $auth->getUserId()) {
            setFlashMessage('error', 'You cannot delete your own account.');
        } else {
            $userModel->delete($userId);
            setFlashMessage('success', 'User deleted successfully.');
        }
        if (!headers_sent()) {
            header('Location: ' . APP_URL . '/admin/users.php');
            exit;
        }
    }
}

$users = $userModel->getAll();

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

    // Filter by status (1 = active, 0 = inactive)
    if ($filters['status'] !== '') {
        $isActive = !empty($user['is_active']);
        if ($filters['status'] === '1' && !$isActive) {
            return false;
        }
        if ($filters['status'] === '0' && $isActive) {
            return false;
        }
    }

    return true;
});

$displayUsers = array_values($filteredUsers);
?>

<div class="page-header">
    <div>
        <h2 style="margin: 0;">User Management</h2>
        <p style="color: var(--text-muted); margin: 4px 0 0;"><?php echo count($displayUsers); ?> user(s)</p>
    </div>
    <button class="btn btn-primary" onclick="openUserModal()">
        <i class="fas fa-plus"></i> Add User
    </button>
</div>

<!-- Filters -->
<div class="filter-bar">
    <form method="GET" class="filter-form">
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
                <option value="1" <?php echo $filters['status'] === '1' ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo $filters['status'] === '0' ? 'selected' : ''; ?>>Inactive</option>
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

<div class="data-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($displayUsers)): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state small">
                            <span class="empty-icon"><i class="fas fa-users"></i></span>
                            <h3>No users found</h3>
                            <p>Try adjusting your filters or add a new user.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($displayUsers as $user): ?>
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar-placeholder-sm">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
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
                    </td>
                    <td>
                        <span class="status-badge status-approved">
                            <?php echo USER_ROLES[$user['role']] ?? $user['role']; ?>
                        </span>
                    </td>
                    <td>
                        <?php $isActive = !empty($user['is_active']); ?>
                        <?php if ($isActive): ?>
                        <span class="status-badge status-active">Active</span>
                        <?php else: ?>
                        <span class="status-badge status-inactive">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-icon" title="Edit" onclick='editUser(<?php echo json_encode(array_merge($user, ["is_active" => (int)($user["is_active"] ?? 0)])); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($user['id'] !== $auth->getUserId()): ?>
                            <button class="btn btn-icon text-danger" title="Delete" 
                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="name" id="userName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span style="color: var(--danger);">*</span></label>
                        <input type="email" name="email" id="userEmail" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role <span style="color: var(--danger);">*</span></label>
                        <select name="role" id="userRole" class="form-control" required>
                            <option value="" disabled selected>-- Select Role --</option>
                            <?php foreach (USER_ROLES as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="statusGroup" style="display: none; align-self: flex-end;">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" id="userActive" value="1" checked>
                            <span>Account is active</span>
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password <span id="passwordRequired" style="color: var(--danger);">*</span></label>
                        <input type="password" name="password" id="userPassword" class="form-control" minlength="6">
                        <small id="passwordHelp" class="form-hint" style="display: none;">Leave blank to keep current password</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" id="userPasswordConfirm" class="form-control" minlength="6">
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

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 400px;">
        <form method="POST">
            <div class="modal-header">
                <h2>Delete User</h2>
                <button class="modal-close" type="button" onclick="closeDeleteModal()">&times;</button>
            </div>

            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="user_id" id="deleteUserId" value="">
            
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
                <p class="form-hint" style="color: var(--danger);">This action cannot be undone.</p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete User</button>
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

function deleteUser(id, name) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('deleteUserName').textContent = name;
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
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
