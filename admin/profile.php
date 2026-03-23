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
        ['id' => 'Personnel',                                'name' => 'Personnel'],
        ['id' => 'Property and Supply',                      'name' => 'Property and Supply'],
        ['id' => 'Records',                                  'name' => 'Records'],
        ['id' => 'Procurement',                              'name' => 'Procurement'],
        ['id' => 'General Services',                         'name' => 'General Services'],
        ['id' => 'Legal',                                    'name' => 'Legal'],
        ['id' => 'Information and Communication Technology', 'name' => 'Information and Communication Technology'],
        ['id' => 'Cash',                                     'name' => 'Cash'],
        ['id' => 'Finance (Accounting)',                     'name' => 'Finance (Accounting)'],
        ['id' => 'Finance (Budget)',                         'name' => 'Finance (Budget)'],
        ['id' => 'Administrative',                           'name' => 'Administrative']
    ],
    'SGOD' => [
        ['id' => 'School Management Monitoring and Evaluation', 'name' => 'School Management Monitoring and Evaluation'],
        ['id' => 'Human Resource Development',                  'name' => 'Human Resource Development'],
        ['id' => 'Social Mobilization and Networking',          'name' => 'Social Mobilization and Networking'],
        ['id' => 'Planning and Research',                       'name' => 'Planning and Research'],
        ['id' => 'Disaster Risk Reduction and Management',      'name' => 'Disaster Risk Reduction and Management'],
        ['id' => 'Education Facilities',                        'name' => 'Education Facilities'],
        ['id' => 'School Health and Nutrition',                 'name' => 'School Health and Nutrition'],
        ['id' => 'School Health and Nutrition (Dental)',        'name' => 'School Health and Nutrition (Dental)'],
        ['id' => 'School Health and Nutrition (Medical)',       'name' => 'School Health and Nutrition (Medical)']
    ],
    'CID' => [
        ['id' => 'Instructional Management',         'name' => 'Instructional Management'],
        ['id' => 'Learning Resource Management',     'name' => 'Learning Resource Management'],
        ['id' => 'Alternative Learning System',      'name' => 'Alternative Learning System'],
        ['id' => 'District Instructional Supervision', 'name' => 'District Instructional Supervision']
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
        $newEmail = trim($_POST['email'] ?? '');
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

            // Only superadmins can update email
            if ($user['role'] === 'SUPERADMIN' && !empty($newEmail) && $newEmail !== $user['email']) {
                if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                    setFlashMessage('error', 'Please enter a valid email address.');
                    if (!headers_sent()) {
                        header('Location: ' . APP_URL . '/admin/profile.php');
                        exit;
                    }
                } else {
                    $updateData['email'] = $newEmail;
                }
            }

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
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" <?php echo ($user['role'] !== 'SUPERADMIN') ? 'disabled' : ''; ?>>

                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div class="form-group">
                            <label class="form-label">Employee ID</label>
                            <input type="text" name="employee_no" class="form-control" value="<?php echo htmlspecialchars($user['employee_no'] ?? ''); ?>" placeholder="Enter employee ID">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Position/Designation</label>
                            <input type="text" name="position" class="form-control" value="<?php echo htmlspecialchars($user['position'] ?? ''); ?>" placeholder="Enter position">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div class="form-group">
                            <label class="form-label">Office/Division</label>
                            <select name="office" id="profileOffice" class="form-control">
                                <option value="">Select Office</option>
                                <?php foreach ($topOffices as $o): ?>
                                <option value="<?php echo htmlspecialchars($o['code']); ?>" <?php echo (($user['office'] ?? '') === $o['code']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($o['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit/Section</label>
                            <select name="unit_section" id="profileUnit" class="form-control" disabled>
                                <option value="">Select Unit/Section</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h3><i class="fas fa-lock"></i> Change Password</h3>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" minlength="6" placeholder="Leave blank to keep current">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter new password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 8px;">
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
        <button class="profile-modal-close" id="avatarModalClose" aria-label="Close">&times;</button>
        <div class="profile-modal-avatar">
            <?php if ($hasAvatar): ?>
            <img src="<?php echo $avatarUrl; ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
            <?php else: ?>
            <div class="profile-avatar-initial" style="width:120px;height:120px;font-size:3rem;"><?php echo $userInitial; ?></div>
            <?php endif; ?>
        </div>
        <h3 class="profile-modal-name"><?php echo htmlspecialchars($user['name']); ?></h3>
        <p class="profile-modal-subtitle">Manage your profile picture</p>
        <div class="profile-modal-actions">
            <?php if ($hasAvatar): ?>
            <button type="button" class="profile-modal-action" id="viewAvatarBtn">
                <i class="fas fa-eye"></i>
                <span>View Full Size</span>
            </button>
            <?php endif; ?>
            <button type="button" class="profile-modal-action" id="changeAvatarBtn">
                <i class="fas fa-camera"></i>
                <span><?php echo $hasAvatar ? 'Change Photo' : 'Upload Photo'; ?></span>
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
    'use strict';
    
    // Office / Unit cascading dropdown
    var unitsByOffice = <?php echo json_encode($unitsByOffice); ?>;
    var selOffice = document.getElementById('profileOffice');
    var selUnit = document.getElementById('profileUnit');
    var savedUnit = <?php echo json_encode($user['unit_section'] ?? ''); ?>;

    function fillUnits() {
        var code = selOffice.value;
        selUnit.innerHTML = '<option value="">Select Unit/Section</option>';
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
    
    selOffice.addEventListener('change', function() {
        savedUnit = '';
        fillUnits();
    });
    
    fillUnits();

    // Avatar modal
    var modal = document.getElementById('avatarModal');
    var trigger = document.getElementById('avatarTrigger');
    var closeBtn = document.getElementById('avatarModalClose');
    var changeBtn = document.getElementById('changeAvatarBtn');
    var fileInput = document.getElementById('avatarFileInput');
    var form = document.getElementById('avatarForm');

    trigger.addEventListener('click', function() {
        modal.classList.add('active');
    });
    
    closeBtn.addEventListener('click', function() {
        modal.classList.remove('active');
    });
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });

    changeBtn.addEventListener('click', function() {
        fileInput.click();
    });
    
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
        
        lightboxClose.addEventListener('click', function() {
            lightbox.classList.remove('active');
        });
        
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                lightbox.classList.remove('active');
            }
        });
    }
})();
</script>

<style>
/* Premium Profile Page Styles */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body {
    overflow-x: hidden;
    font-family: 'Inter', sans-serif;
    background-color: #f3f4f6;
}

.profile-page {
    display: flex;
    flex-wrap: nowrap;
    gap: 32px;
    justify-content: center;
    align-items: flex-start;
    padding: 40px 2vw;
    min-height: 100vh;
    width: 100%;
    margin: 0 auto;
    box-sizing: border-box;
    font-family: 'Inter', sans-serif;
}

.profile-left {
    flex: 1.5 1 0;
    min-width: 320px;
    width: 100%;
    animation: slideUp 0.6s ease-out;
}

.profile-right {
    flex: 1 1 0;
    min-width: 320px;
    width: 100%;
    animation: slideUp 0.6s ease-out 0.1s both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.data-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    margin-bottom: 24px;
    padding: 32px;
    border: 1px solid rgba(0,0,0,0.04);
    position: relative;
    overflow: hidden;
}
.data-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(to right, #154c79, #2b7bc0);
}

.profile-left .data-card::before {
    display: none;
}


.card-header {
    margin-bottom: 24px;
    border-bottom: 2px solid #f3f4f6;
    padding-bottom: 16px;
    position: relative;
}

.card-header::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 60px;
    height: 2px;
    background: #154c79;
    border-radius: 2px;
}

.card-header h2 {
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-header h2 i {
    color: #154c79; /* Theme primary */
}

.card-body {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-label {
    font-size: 0.95rem;
    color: #374151;
    font-weight: 600;
}

.form-control {
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 1rem;
    background: #f9fafb;
    transition: all 0.2s ease;
    color: #111827;
}

.form-control:focus {
    border-color: #154c79;
    background: #ffffff;
    outline: none;
    box-shadow: 0 0 0 4px rgba(21, 76, 121, 0.1);
}

.form-control:disabled {
    background: #f3f4f6;
    color: #6b7280;
    cursor: not-allowed;
}

.btn.btn-primary {
    background: #154c79;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 12px 24px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 6px -1px rgba(21, 76, 121, 0.2), 0 2px 4px -1px rgba(21, 76, 121, 0.1);
}

.btn.btn-primary:hover {
    background: #0f3a5e;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(21, 76, 121, 0.3), 0 4px 6px -1px rgba(21, 76, 121, 0.2);
}

.btn.btn-primary:active {
    transform: translateY(0);
    box-shadow: none;
}

.profile-card {
    background: linear-gradient(135deg, #154c79 0%, #0a2e4a 100%);
    border-radius: 16px;
    box-shadow: 0 15px 35px rgba(21, 76, 121, 0.3);
    padding: 40px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.1);
}

.profile-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 100%);
    pointer-events: none;
    z-index: 0;
}

.profile-card::after {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
    pointer-events: none;
    z-index: 0;
}

.profile-card-bg {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    position: relative;
    z-index: 1;
}

.profile-avatar-wrapper {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    cursor: pointer;
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    margin-bottom: 8px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 4px solid rgba(255,255,255,0.2);
}

.profile-avatar-wrapper:hover {
    transform: scale(1.05) translateY(-2px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.25);
    border-color: rgba(255,255,255,0.4);
}

.profile-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.profile-avatar-initial {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
    color: #154c79;
    font-size: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.profile-avatar-camera {
    position: absolute;
    bottom: 0;
    right: 0;
    background: #ffffff;
    border-radius: 50%;
    padding: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    color: #154c79;
    font-size: 1rem;
    transition: transform 0.2s ease;
}

.profile-card-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-card-role {
    font-size: 0.9rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9);
    margin-top: 2px;
    background: rgba(0,0,0,0.15);
    padding: 4px 12px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.account-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 12px;
    border-bottom: 1px solid #f3f4f6;
    border-radius: 8px;
    margin: 0 -12px;
}

.account-info-item:last-child {
    border-bottom: none;
}

.account-info-label {
    color: #6b7280;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.account-info-value {
    color: #111827;
    font-size: 1rem;
    font-weight: 600;
    text-align: right;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: capitalize;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.status-approved {
    background: #def7ec;
    color: #03543f;
    border: 1px solid #84e1bc;
}

.status-pending {
    background: #fdf6b2;
    color: #723b13;
    border: 1px solid #faca15;
}

.status-rejected {
    background: #fde8e8;
    color: #9b1c1c;
    border: 1px solid #f8b4b4;
}

.lightbox-overlay, .profile-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(17, 24, 39, 0.75);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.lightbox-overlay.active, .profile-modal-overlay.active {
    display: flex;
    opacity: 1;
}

.profile-modal {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    padding: 40px;
    min-width: 360px;
    max-width: 90vw;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    transform: scale(0.95);
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.profile-modal-overlay.active .profile-modal {
    transform: scale(1);
}

.profile-modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #f3f4f6;
    border: none;
    border-radius: 50%;
    font-size: 1.5rem;
    color: #6b7280;
    width: 40px;
    height: 40px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.profile-modal-close:hover {
    background: #e5e7eb;
    color: #111827;
}

.profile-modal-avatar img, .profile-modal-avatar .profile-avatar-initial {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 16px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.profile-modal-name {
    font-size: 1.4rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.profile-modal-subtitle {
    color: #6b7280;
    font-size: 1rem;
    margin-bottom: 24px;
}

.profile-modal-actions {
    display: flex;
    gap: 16px;
    margin-bottom: 8px;
}

.profile-modal-action {
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 0.95rem;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.profile-modal-action:hover {
    background: #154c79;
    border-color: #154c79;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(21, 76, 121, 0.2);
}

.lightbox-img {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    border: 4px solid #ffffff;
}

.lightbox-close {
    position: absolute;
    top: 40px;
    right: 40px;
    background: rgba(255,255,255,0.2);
    border: 2px solid #ffffff;
    border-radius: 50%;
    font-size: 2rem;
    color: #ffffff;
    width: 50px;
    height: 50px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    z-index: 1001;
    backdrop-filter: blur(4px);
}

.lightbox-close:hover {
    background: #ffffff;
    color: #111827;
    transform: scale(1.1);
}

hr {
    border: 0;
    height: 1px;
    background: #e5e7eb;
    margin: 32px 0 24px 0;
}

@media (max-width: 992px) {
    .profile-page {
        flex-direction: column;
        padding: 24px 4vw;
        gap: 24px;
        max-width: 100vw;
    }
    
    .profile-left {
        max-width: 100%;
        order: 2; /* Put form below profile card on mobile */
    }
    
    .profile-right {
        max-width: 100%;
        order: 1; /* Profile card on top */
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
