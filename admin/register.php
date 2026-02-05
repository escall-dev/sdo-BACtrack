<?php
/**
 * Employee Self-Registration Page
 * SDO-BACtrack - BAC Procedural Timeline Tracking System
 */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';

$auth = auth();
if ($auth->isLoggedIn()) {
    header('Location: ' . APP_URL . '/admin/');
    exit;
}

$error = '';
$formData = [
    'full_name' => '',
    'email' => '',
    'employee_no' => '',
    'employee_position' => '',
    'office' => '',
    'unit_section' => ''
];

// Static SDO offices (OSDS, SGOD, CID) and units per office for cascading dropdown
$topOffices = [
    ['code' => 'OSDS', 'name' => 'Office of the Schools Division Superintendent (OSDS)'],
    ['code' => 'SGOD', 'name' => 'Schools Governance and Operations Division (SGOD)'],
    ['code' => 'CID', 'name' => 'Curriculum and Instruction Division (CID)']
];
$unitsByOffice = [
    'OSDS' => [
        ['id' => 'OSDS-Admin', 'name' => 'Administrative Unit'],
        ['id' => 'OSDS-Budget', 'name' => 'Budget Section'],
        ['id' => 'OSDS-HR', 'name' => 'Human Resource'],
        ['id' => 'OSDS-Procurement', 'name' => 'Procurement'],
        ['id' => 'OSDS-Other', 'name' => 'Other']
    ],
    'SGOD' => [
        ['id' => 'SGOD-SSM', 'name' => 'School Management'],
        ['id' => 'SGOD-EF', 'name' => 'Education Facilities'],
        ['id' => 'SGOD-SDS', 'name' => 'School District Supervision'],
        ['id' => 'SGOD-Other', 'name' => 'Other']
    ],
    'CID' => [
        ['id' => 'CID-LRM', 'name' => 'Learning Resource Management'],
        ['id' => 'CID-EduTech', 'name' => 'Education Technology'],
        ['id' => 'CID-Programs', 'name' => 'Programs Section'],
        ['id' => 'CID-Other', 'name' => 'Other']
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'employee_no' => trim($_POST['employee_no'] ?? ''),
        'employee_position' => trim($_POST['employee_position'] ?? ''),
        'office' => $_POST['office'] ?? '',
        'unit_section' => trim($_POST['unit_section'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? ''
    ];

    if (empty($formData['full_name'])) {
        $error = 'Full name is required.';
    } elseif (empty($formData['email'])) {
        $error = 'Email address is required.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($formData['password'])) {
        $error = 'Password is required.';
    } elseif (strlen($formData['password']) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($formData['password'] !== $formData['password_confirm']) {
        $error = 'Passwords do not match.';
    } else {
        $userModel = new User();
        $result = $userModel->register($formData);

        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            header('Location: ' . APP_URL . '/admin/login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #0f4c75;
            --primary-light: #1b6ca8;
            --primary-dark: #0a2f4a;
            --accent: #bbe1fa;
            --gold: #d4af37;
            --bg-dark: #0a1628;
            --bg-card: #111d2e;
            --text: #e8f1f8;
            --text-muted: #7a9bb8;
            --border: rgba(187, 225, 250, 0.1);
            --error: #ef4444;
            --success: #10b981;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container { width: 100%; max-width: 480px; }
        .register-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px 28px;
        }
        .register-header { text-align: center; margin-bottom: 24px; }
        .register-header h1 {
            color: var(--text);
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .register-header p { color: var(--text-muted); font-size: 0.85rem; }
        .form-group { margin-bottom: 16px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .form-label {
            display: block;
            color: var(--text);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .form-label .required { color: var(--error); }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            font-size: 0.9rem;
            font-family: inherit;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            transition: all 0.2s ease;
        }
        .form-control::placeholder { color: var(--text-muted); }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            background: rgba(0, 0, 0, 0.4);
        }
        select.form-control { cursor: pointer; }
        .form-control:disabled { opacity: 0.7; cursor: not-allowed; }
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success-message {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }
        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.8rem;
        }
        .info-box i { margin-right: 8px; }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 20px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
            margin-top: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 76, 117, 0.4);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            border: 1px solid var(--border);
            margin-top: 12px;
        }
        .btn-secondary:hover { background: rgba(255, 255, 255, 0.1); }
        .form-hint { font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; }
        @media (max-width: 480px) {
            .form-row { grid-template-columns: 1fr; }
            .register-card { padding: 24px 20px; }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1><i class="fas fa-user-plus"></i> Create Account</h1>
                <p>Register as an SDO Employee to use BAC Timeline Tracker</p>
            </div>

            <?php if (isset($_GET['registered'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> Registration successful. Your account will need to be approved by an administrator before you can sign in.
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                Your account will need to be approved by an administrator before you can login.
            </div>

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name"
                               value="<?php echo htmlspecialchars($formData['full_name']); ?>"
                               placeholder="Juan Dela Cruz" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email <span class="required">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?php echo htmlspecialchars($formData['email']); ?>"
                               placeholder="user@deped.gov.ph" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="reg_office">Office</label>
                        <select class="form-control" id="reg_office" name="office" aria-label="Select office to enable Unit/Section">
                            <option value="">-- Select Office --</option>
                            <?php foreach ($topOffices as $o): ?>
                            <option value="<?php echo htmlspecialchars($o['code']); ?>" <?php echo ($formData['office'] === $o['code']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($o['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-hint">OSDS, SGOD, or CID</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="reg_unit_id">Unit/Section</label>
                        <select class="form-control" id="reg_unit_id" name="unit_section" disabled>
                            <option value="">-- Select Unit/Section --</option>
                        </select>
                        <span class="form-hint">Select an Office first</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Password <span class="required">*</span></label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Min. 8 characters" required>
                        <span class="form-hint">Minimum 8 characters</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirm">Confirm Password <span class="required">*</span></label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                               placeholder="Re-enter password" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="employee_no">Employee No. (optional)</label>
                        <input type="text" class="form-control" id="employee_no" name="employee_no"
                               value="<?php echo htmlspecialchars($formData['employee_no']); ?>"
                               placeholder="E-12345">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="employee_position">Position (optional)</label>
                        <input type="text" class="form-control" id="employee_position" name="employee_position"
                               value="<?php echo htmlspecialchars($formData['employee_position']); ?>"
                               placeholder="Teacher I">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Register
                </button>

                <a href="<?php echo APP_URL; ?>/admin/login.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </form>
        </div>
    </div>
    <script>
    (function() {
        var unitsByOffice = <?php echo json_encode($unitsByOffice); ?>;
        var selOffice = document.getElementById('reg_office');
        var selUnit = document.getElementById('reg_unit_id');
        var savedUnit = <?php echo json_encode($formData['unit_section']); ?>;
        if (!selOffice || !selUnit) return;
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
        selOffice.addEventListener('change', fillUnits);
        fillUnits();
    })();
    </script>
</body>
</html>
