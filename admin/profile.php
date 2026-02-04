<?php
/**
 * User Profile
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User();
$user = $userModel->findById($auth->getUserId());

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Update profile
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($name) || empty($email)) {
            setFlashMessage('error', 'Name and email are required.');
        } else {
            // Check email uniqueness
            $existingUser = $userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] !== $user['id']) {
                setFlashMessage('error', 'Email address already exists.');
            } else {
                $userModel->update($user['id'], [
                    'name' => $name,
                    'email' => $email
                ]);
                setFlashMessage('success', 'Profile updated successfully.');
            }
        }
        if (!headers_sent()) {
            header('Location: ' . APP_URL . '/admin/profile.php');
            exit;
        }
    }

    // Change password
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            setFlashMessage('error', 'All password fields are required.');
        } elseif (empty($user['password']) || !password_verify($currentPassword, $user['password'])) {
            setFlashMessage('error', 'Current password is incorrect.');
        } elseif ($newPassword !== $confirmPassword) {
            setFlashMessage('error', 'New passwords do not match.');
        } elseif (strlen($newPassword) < 6) {
            setFlashMessage('error', 'New password must be at least 6 characters.');
        } else {
            $userModel->updatePassword($user['id'], $newPassword);
            setFlashMessage('success', 'Password changed successfully.');
        }
        if (!headers_sent()) {
            header('Location: ' . APP_URL . '/admin/profile.php');
            exit;
        }
    }
}

// Refresh user data
$user = $userModel->findById($auth->getUserId());
?>

<div class="page-header">
    <div>
        <h2 style="margin: 0;">My Profile</h2>
        <p style="color: var(--text-muted); margin: 4px 0 0;">Manage your account settings</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Profile Information -->
    <div class="data-card">
        <div class="card-header">
            <h2><i class="fas fa-user"></i> Profile Information</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="<?php echo USER_ROLES[$user['role']] ?? $user['role']; ?>" disabled>
                    <small style="color: var(--text-muted);">Contact administrator to change your role.</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Member Since</label>
                    <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" disabled>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="data-card">
        <div class="card-header">
            <h2><i class="fas fa-lock"></i> Change Password</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                    <small style="color: var(--text-muted);">Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
