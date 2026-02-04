<?php
/**
 * Admin Login Page
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/auth.php';

$auth = auth();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    $redirect = $_SESSION['redirect_after_login'] ?? APP_URL . '/admin/';
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . $redirect);
    exit;
}

$error = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        if ($auth->login($email, $password)) {
            $redirect = $_SESSION['redirect_after_login'] ?? APP_URL . '/admin/';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
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
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            margin: auto;
        }
        
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px 28px;
            backdrop-filter: blur(20px);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }
        
        .logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 110px;
            height: 110px;
            border-radius: 999px;
            padding: 6px;
            
           
            margin-bottom: 18px;
            position: relative;
            overflow: hidden;
        }
        
        .logo-badge::after {
            content: '';
            position: absolute;
            inset: -10px;
            border-radius: inherit;
            border: 1px solid rgba(212, 175, 55, 0.35);
            pointer-events: none;
            box-shadow: 0 0 25px rgba(212, 175, 55, 0.45);
        }
        
        .logo-badge img {
            width: 100%;
            height: 100%;
            border-radius: 999px;
            object-fit: cover;
        }
        
        .login-header h1 {
            color: var(--text);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            color: var(--text);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 6px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 14px;
            font-size: 0.95rem;
            font-family: inherit;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            transition: all 0.2s ease;
        }
        
        .form-control::placeholder {
            color: var(--text-muted);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            background: rgba(0, 0, 0, 0.4);
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
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
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 76, 117, 0.4);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        
        .login-footer p {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .credentials-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
            padding: 12px 16px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 0.8rem;
        }

        .credentials-info strong {
            display: block;
            margin-bottom: 8px;
            color: #60a5fa;
        }

        .credentials-info code {
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-badge">
                    <img src="/SDO-BACtrack/sdo-template/logo-imgs/sdo-logo.jpg" alt="SDO San Pedro Logo">
                </div>
                <h1><?php echo APP_NAME; ?></h1>
                <p><?php echo APP_SUBTITLE; ?></p>
            </div>
            
            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>

            <div class="credentials-info">
                <strong><i class="fas fa-info-circle"></i> Default Credentials</strong>
                Procurement: <code>procurement@sdo.edu.ph</code> / <code>password</code><br>
                Project Owner: <code>owner@sdo.edu.ph</code> / <code>password</code>
            </div>
            
            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> SDO-BACtrack<br>BAC Procedural Timeline Tracking System</p>
            </div>
        </div>
    </div>
</body>
</html>
