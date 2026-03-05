<?php
/**
 * User Profile
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User();
$user = $userModel->findById($auth->getUserId());

// Office / Unit cascading dropdown data (same as register.php)
$topOffices = [
    ['code' => 'OSDS', 'name' => 'Office of the Schools Division Superintendent Staff (OSDS)'],
    ['code' => 'SGOD', 'name' => 'Schools Governance and Operations Division (SGOD)'],
    ['code' => 'CID',  'name' => 'Curriculum and Instruction Division (CID)']
];
$unitsByOffice = [
    'OSDS' => [
        ['id' => 'Information and Communication Technology', 'name' => 'Information and Communication Technology'],
        ['id' => 'Administrative Unit',   'name' => 'Administrative Unit'],
        ['id' => 'Budget Section',        'name' => 'Budget Section'],
        ['id' => 'Human Resource',        'name' => 'Human Resource'],
        ['id' => 'Procurement',           'name' => 'Procurement'],
        ['id' => 'Other',                 'name' => 'Other']
    ],
    'SGOD' => [
        ['id' => 'School Management',          'name' => 'School Management'],
        ['id' => 'Education Facilities',       'name' => 'Education Facilities'],
        ['id' => 'School District Supervision','name' => 'School District Supervision'],
        ['id' => 'Other',                      'name' => 'Other']
    ],
    'CID' => [
        ['id' => 'Learning Resource Management', 'name' => 'Learning Resource Management'],
        ['id' => 'Education Technology',         'name' => 'Education Technology'],
        ['id' => 'Programs Section',             'name' => 'Programs Section'],
        ['id' => 'Other',                        'name' => 'Other']
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Update profile (merged: profile fields + optional password change)
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $employeeNo = trim($_POST['employee_no'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $office = trim($_POST['office'] ?? '');
        $unitSection = trim($_POST['unit_section'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($name)) {
            setFlashMessage('error', 'Full Name is required.');
        } else {
            // Update profile fields
            $updateData = [
                'name' => $name,
                'employee_no' => $employeeNo,
                'position' => $position,
                'office' => $office,
                'unit_section' => $unitSection
            ];

            // Only update password if user filled in the password fields
            if (!empty($newPassword) || !empty($confirmPassword)) {
                if ($newPassword !== $confirmPassword) {
                    setFlashMessage('error', 'New passwords do not match.');
                    if (!headers_sent()) {
                        header('Location: ' . APP_URL . '/admin/profile.php');
                        exit;
                    }
                } elseif (strlen($newPassword) < 6) {
                    setFlashMessage('error', 'New password must be at least 6 characters.');
                    if (!headers_sent()) {
                        header('Location: ' . APP_URL . '/admin/profile.php');
                        exit;
                    }
                } else {
                    $updateData['password'] = $newPassword;
                }
            }

            $userModel->update($user['id'], $updateData);
            setFlashMessage('success', 'Profile updated successfully.');
        }
        if (!headers_sent()) {
            header('Location: ' . APP_URL . '/admin/profile.php');
            exit;
        }
    }

    // Upload avatar
    if ($action === 'upload_avatar') {
        $avatarDir = __DIR__ . '/../uploads/avatars/';
        $maxSize = 2 * 1024 * 1024; // 2 MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            setFlashMessage('error', 'Please select an image to upload.');
        } else {
            $file = $_FILES['avatar'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($ext, $allowedExts, true) || !in_array($mimeType, $allowedTypes, true)) {
                setFlashMessage('error', 'Only JPG, PNG, and GIF images are allowed.');
            } elseif ($file['size'] > $maxSize) {
                setFlashMessage('error', 'Avatar image must be under 2 MB.');
            } else {
                // Build filename: sanitized_username_YYYY-MM-DD.ext
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', str_replace(' ', '_', $user['name']));
                $filename = $safeName . '_' . date('Y-m-d') . '.' . $ext;

                // Delete old avatar file if exists
                if (!empty($user['avatar_url'])) {
                    $oldFile = __DIR__ . '/..' . parse_url($user['avatar_url'], PHP_URL_PATH);
                    // Ensure old file is within the avatars directory
                    $oldReal = realpath(dirname($oldFile));
                    $avatarReal = realpath($avatarDir);
                    if ($oldReal && $avatarReal && strpos($oldReal, $avatarReal) === 0 && is_file($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $destination = $avatarDir . $filename;
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $avatarUrl = APP_URL . '/uploads/avatars/' . $filename;
                    $userModel->update($user['id'], ['avatar_url' => $avatarUrl]);
                    setFlashMessage('success', 'Profile picture updated successfully.');
                } else {
                    setFlashMessage('error', 'Failed to upload image. Please try again.');
                }
            }
        }
        if (!headers_sent()) {
            header('Location: ' . APP_URL . '/admin/profile.php');
            exit;
        }
    }
}

// Refresh user data
$user = $userModel->findById($auth->getUserId());

// Get last login from sessions table
$db = db();
$lastSession = $db->fetch(
    "SELECT login_time FROM sessions WHERE user_id = ? ORDER BY login_time DESC LIMIT 1",
    [$user['id']]
);
$lastLogin = $lastSession ? date('F j, Y – g:i A', strtotime($lastSession['login_time'])) : 'N/A';

// Avatar helpers
$hasAvatar = !empty($user['avatar_url']);
$avatarUrl = $hasAvatar ? htmlspecialchars($user['avatar_url']) : '';
$userInitial = strtoupper(substr($user['name'], 0, 1));
?>

<div class="profile-page">
    <!-- Left Column: Edit Profile -->
    <div class="profile-left">
        <div class="data-card">
            <div class="card-header">
                <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-group">
                        <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <small style="color: var(--text-muted);">Email cannot be changed</small>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label">Employee Number</label>
                            <input type="text" name="employee_no" class="form-control" value="<?php echo htmlspecialchars($user['employee_no'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Position/Designation</label>
                            <input type="text" name="position" class="form-control" value="<?php echo htmlspecialchars($user['position'] ?? ''); ?>">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label">Office/Division</label>
                            <select name="office" id="profileOffice" class="form-control">
                                <option value="">-- Select Office --</option>
                                <?php foreach ($topOffices as $o): ?>
                                <option value="<?php echo htmlspecialchars($o['code']); ?>" <?php echo (($user['office'] ?? '') === $o['code']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($o['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color: var(--text-muted);">OSDS, SGOD, or CID</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit/Section</label>
                            <select name="unit_section" id="profileUnit" class="form-control" disabled>
                                <option value="">-- Select Unit/Section --</option>
                            </select>
                            <small style="color: var(--text-muted);">Select an Office first</small>
                        </div>
                    </div>

                    <hr style="border:none; border-top:1px solid var(--border-color); margin:24px 0;">
                    <h3 style="margin:0 0 16px; font-size:1rem; font-weight:600;">Change Password</h3>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" minlength="6" placeholder="Leave blank to keep current">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter new password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Profile Card + Account Info -->
    <div class="profile-right">
        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-card-bg">
                <div class="profile-avatar-wrapper" id="avatarTrigger" title="Click to view or change profile picture">
                    <?php if ($hasAvatar): ?>
                    <img src="<?php echo $avatarUrl; ?>" alt="Profile Picture" class="profile-avatar-img">
                    <?php else: ?>
                    <div class="profile-avatar-initial"><?php echo $userInitial; ?></div>
                    <?php endif; ?>
                    <div class="profile-avatar-camera">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <h3 class="profile-card-name"><?php echo htmlspecialchars($user['name']); ?></h3>
                <span class="profile-card-role"><?php echo USER_ROLES[$user['role']] ?? $user['role']; ?></span>
            </div>
        </div>

        <!-- Account Info -->
        <div class="data-card" style="margin-top:20px;">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Account Info</h2>
            </div>
            <div class="card-body">
                <div class="account-info-item">
                    <span class="account-info-label">ROLE</span>
                    <span class="account-info-value" style="font-weight:700; text-transform:uppercase;"><?php echo htmlspecialchars($user['role']); ?></span>
                </div>
                <div class="account-info-item">
                    <span class="account-info-label">ACCOUNT STATUS</span>
                    <span class="account-info-value">
                        <span class="status-badge status-<?php echo strtolower($user['status'] ?? 'approved'); ?>"><?php echo htmlspecialchars(ucfirst(strtolower($user['status'] ?? 'Approved'))); ?></span>
                    </span>
                </div>
                <div class="account-info-item">
                    <span class="account-info-label">MEMBER SINCE</span>
                    <span class="account-info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                </div>
                <div class="account-info-item">
                    <span class="account-info-label">LAST LOGIN</span>
                    <span class="account-info-value"><?php echo $lastLogin; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Lightbox Viewer -->
<div class="lightbox-overlay" id="lightboxOverlay">
    <button class="lightbox-close" id="lightboxClose">&times;</button>
    <img src="<?php echo $avatarUrl; ?>" alt="Profile Picture" class="lightbox-img" id="lightboxImg">
</div>

<!-- Profile Picture Modal -->
<div class="profile-modal-overlay" id="avatarModal">
    <div class="profile-modal">
        <button class="profile-modal-close" id="avatarModalClose">&times;</button>
        <div class="profile-modal-avatar">
            <?php if ($hasAvatar): ?>
            <img src="<?php echo $avatarUrl; ?>" alt="Profile Picture">
            <?php else: ?>
            <div class="profile-avatar-initial" style="width:120px;height:120px;font-size:3rem;"><?php echo $userInitial; ?></div>
            <?php endif; ?>
        </div>
        <h3 class="profile-modal-name"><?php echo htmlspecialchars($user['name']); ?></h3>
        <p class="profile-modal-subtitle">Profile Picture</p>
        <div class="profile-modal-actions">
            <?php if ($hasAvatar): ?>
            <button type="button" class="profile-modal-action" id="viewAvatarBtn">
                <i class="fas fa-eye"></i> View Profile Picture
            </button>
            <?php endif; ?>
            <button type="button" class="profile-modal-action" id="changeAvatarBtn">
                <i class="fas fa-camera"></i> Change Profile Picture
            </button>
        </div>
        <!-- Hidden upload form -->
        <form method="POST" enctype="multipart/form-data" id="avatarForm" style="display:none;">
            <input type="hidden" name="action" value="upload_avatar">
            <input type="file" name="avatar" id="avatarFileInput" accept="image/jpeg,image/png,image/gif">
        </form>
    </div>
</div>

<script>
(function() {
    // Office / Unit cascading dropdown
    var unitsByOffice = <?php echo json_encode($unitsByOffice); ?>;
    var selOffice = document.getElementById('profileOffice');
    var selUnit = document.getElementById('profileUnit');
    var savedUnit = <?php echo json_encode($user['unit_section'] ?? ''); ?>;

    function fillUnits() {
        var code = selOffice.value;
        selUnit.innerHTML = '<option value="">-- Select Unit/Section --</option>';
        selUnit.disabled = true;
        if (code && unitsByOffice[code]) {
            unitsByOffice[code].forEach(function(u) {
                var opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.name;
                if (u.id === savedUnit) opt.selected = true;
                selUnit.appendChild(opt);
            });
            selUnit.disabled = false;
        }
    }
    selOffice.addEventListener('change', function() { savedUnit = ''; fillUnits(); });
    fillUnits();

    // Avatar modal
    var modal = document.getElementById('avatarModal');
    var trigger = document.getElementById('avatarTrigger');
    var closeBtn = document.getElementById('avatarModalClose');
    var changeBtn = document.getElementById('changeAvatarBtn');
    var fileInput = document.getElementById('avatarFileInput');
    var form = document.getElementById('avatarForm');

    trigger.addEventListener('click', function() { modal.classList.add('active'); });
    closeBtn.addEventListener('click', function() { modal.classList.remove('active'); });
    modal.addEventListener('click', function(e) { if (e.target === modal) modal.classList.remove('active'); });

    changeBtn.addEventListener('click', function() { fileInput.click(); });
    fileInput.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            form.submit();
        }
    });

    // Lightbox viewer
    var viewBtn = document.getElementById('viewAvatarBtn');
    var lightbox = document.getElementById('lightboxOverlay');
    var lightboxClose = document.getElementById('lightboxClose');
    if (viewBtn && lightbox) {
        viewBtn.addEventListener('click', function() {
            modal.classList.remove('active');
            lightbox.classList.add('active');
        });
        lightboxClose.addEventListener('click', function() { lightbox.classList.remove('active'); });
        lightbox.addEventListener('click', function(e) { if (e.target === lightbox) lightbox.classList.remove('active'); });
    }
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
