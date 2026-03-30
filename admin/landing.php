<?php
/**
 * Admin Login Page
 * SDO-BACtrack (Premium Design)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/procurement.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/User.php';

$auth = auth();

// Always show the login form when visiting landing.php directly.
// Do NOT auto-redirect even if user has an active session (cookie/token).
// This allows users to log in as a different account.

$error = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $token = $auth->login($email, $password);
        if ($token !== false) {
            // Token is passed via URL parameter - JavaScript will store it in sessionStorage
            // and set a tab-specific cookie for refresh support
            $redirect = $_SESSION['redirect_after_login'] ?? APP_URL . '/admin/';
            unset($_SESSION['redirect_after_login']);
            // Strip any stale auth_token params from redirect URL before appending the new one
            $redirect = preg_replace('/([?&])' . preg_quote(AUTH_TOKEN_PARAM, '/') . '=[^&]*(&|$)/', '$1', $redirect);
            $redirect = rtrim($redirect, '?&');
            $sep = strpos($redirect, '?') !== false ? '&' : '?';
            $redirect .= $sep . AUTH_TOKEN_PARAM . '=' . urlencode($token);
            header('Location: ' . $redirect);
            exit;
        } else {
            $user = (new User())->findByEmail($email);
            if ($user && isset($user['status']) && $user['status'] === 'PENDING') {
                $error = 'Your account is pending administrator approval.';
            } elseif ($user && isset($user['is_active']) && (int)$user['is_active'] !== 1) {
                $error = 'Your account is inactive. Please contact the administrator.';
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* ─── Design Tokens (mirrors admin.css) ─── */
        :root {
            --primary:          #0f4c75;
            --primary-light:    #1b6ca8;
            --primary-dark:     #0a2f4a;
            --primary-gradient: linear-gradient(135deg, #0f4c75 0%, #1b6ca8 100%);

            --accent:           #d4af37;
            --accent-light:     #e5c158;

            --success:          #10b981;
            --success-bg:       #d1fae5;
            --danger:           #ef4444;
            --danger-bg:        #fee2e2;
            --info:             #3b82f6;
            --info-bg:          #dbeafe;

            --bg-primary:       #f8fafc;
            --bg-secondary:     #f1f5f9;
            --card-bg:          #ffffff;

            --text-primary:     #0f172a;
            --text-secondary:   #475569;
            --text-muted:       #94a3b8;
            --text-light:       #ffffff;

            --border-color:     #e2e8f0;

            --shadow-sm:  0 2px 4px rgba(0,0,0,0.08);
            --shadow-md:  0 4px 12px rgba(0,0,0,0.12);
            --shadow-lg:  0 8px 24px rgba(0,0,0,0.16);
            --shadow-xl:  0 12px 32px rgba(0,0,0,0.20);

            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;

            --transition-base: 200ms ease;
            --transition-slow: 300ms ease;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary) url('../assets/img/sdo-bg.jpg') center center / cover no-repeat fixed;
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─── Header ─── */
        .site-header {
            background: rgba(15, 76, 117, 0.55); /* lighter fallback for browsers without backdrop-filter */
            background: linear-gradient(135deg, rgba(15,76,117,0.55) 0%, rgba(27,108,168,0.55) 100%);
            backdrop-filter: blur(10px) saturate(160%);
            -webkit-backdrop-filter: blur(10px) saturate(160%);
            padding: 0 0 0 0;
            min-height: 100px;
            box-shadow: var(--shadow-md);
            /* keep header visible while scrolling */
            position: fixed;
            left: 0;
            right: 0;
            top: 0;
            z-index: 1200;
            overflow: hidden;
        }
        .site-header::before {
            content: '';
            position: absolute;
            top: -40px; right: -60px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            pointer-events: none;
        }
        .site-header::after {
            content: '';
            position: absolute;
            bottom: -50px; left: -30px;
            width: 180px; height: 180px;
            border-radius: 50%;
            background: rgba(212,175,55,0.08);
            pointer-events: none;
        }
        .header-inner {
            max-width: 1140px;
            margin: 0;
            padding: 24px 32px;
            display: flex;
            align-items: center;
            gap: 24px;
            justify-content: flex-start;
            z-index: 1;
            height: 100px;
        }
        .header-spacer {
            flex: 1 1 0;
        }
        .header-logo-wrap {
            width: 64px; height: 64px;
            border-radius: 50%;
            border: 2px solid rgba(212,175,55,0.55);
            box-shadow: 0 0 0 4px rgba(212,175,55,0.15);
            overflow: hidden;
            flex-shrink: 0;
            background: #fff;
        }
        .header-logo-wrap img {
            width: 100%; height: 100%;
            object-fit: cover;
        }
        .header-text-group { color: #fff; }
        .header-text-group h1 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.3px;
            line-height: 1.2;
        }
        .header-text-group p {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.65);
            margin-top: 2px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .header-flag {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 24px;
            justify-content: flex-start;
            font-size: 0.78rem;
            font-weight: 500;
            flex: 1 1 0;
            text-transform: uppercase;
        .btn-login {
            flex-shrink: 0;
        }
        }
        .header-flag i { color: var(--accent); }

        /* ─── Navbar ─── */
        .navbar {
            background: var(--primary-dark);
            border-bottom: 2px solid var(--accent);
        }
        .navbar-inner {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 46px;
        }
        .navbar-links {
            display: flex;
            align-items: center;
            height: 100%;
            justify-content: center;
            background: none;
            z-index: 2;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            gap: 8px;
        }
        .btn-login {
            margin-left: 18px;
            margin-right: 0;
        }
        .nav-link {
            color: #fff;
            text-decoration: none;
            padding: 0 18px;
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 1rem;
            font-weight: 700;
            height: 62px;
            border: none;
            background: none;
            position: relative;
            transition: color var(--transition-base);
            letter-spacing: 0.01em;
        }
        .nav-link:not(:last-child) {
            border-right: 1px solid rgba(255,255,255,0.18);
        }
        .nav-link.active {
            color: #fff;
            background: none;
        }
        .nav-link.active {
            font-weight: 800;
        }
        .nav-link.active::after {
            content: '';
            display: block;
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 3px;
            background: var(--accent);
            border-radius: 2px 2px 0 0;
        }
        .nav-link:hover {
            color: var(--accent-light);
        }
        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 0 18px;
            height: 38px;
            background: none;
            border: 1.5px solid rgba(255,255,255,0.35);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 999px;
            cursor: pointer;
            letter-spacing: 0.06em;
            transition: all var(--transition-base);
            margin-left: 18px;
            box-shadow: 0 2px 8px rgba(15,76,117,0.10);
            position: absolute;
            right: 32px;
            top: 50%;
            transform: translateY(-50%);
        }
        .btn-login:hover {
            background: rgba(255,255,255,0.08);
            color: var(--accent-light);
            border-color: var(--accent-light);
        }

        /* ─── Hero strip ─── */
        .hero-strip {
            background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary) 60%, var(--primary-light) 100%);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
        }
        .hero-strip-inner {
            max-width: 1140px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .hero-tagline {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.75);
            font-weight: 500;
        }
        .hero-tagline strong { color: var(--accent-light); }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(212,175,55,0.15);
            border: 1px solid rgba(212,175,55,0.35);
            color: var(--accent-light);
            border-radius: 999px;
            padding: 3px 12px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        /* ─── Main Content ─── */
        .main-content {
            flex: 1;
            /* leave space for the fixed header */
            padding: 160px 20px 32px;
        }
        .content-wrap {
            max-width: 1120px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 24px;
          
            border-radius: var(--radius-lg);
          
            padding: 32px 32px;
        }

        /* ─── Cards ─── */
        .data-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            scroll-margin-top: 150px;
        }
        .card-header {
            background: var(--primary-gradient);
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .card-header i { font-size: 0.95rem; opacity: 0.85; }
        .card-body { padding: 20px; }

        .estimator-card {
            width: 100%;
            max-width: 860px;
            margin: 0 auto 18px;
        }

        .content-wrap > .data-card:not(.estimator-card) {
            width: 100%;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .estimator-card .card-body {
            padding: 10px 12px;
        }

        .estimator-card .card-header {
            padding: 10px 14px;
            font-size: 0.82rem;
        }

        .estimator-card .search-input {
            padding: 6px 8px;
            font-size: 0.82rem;
        }

        .estimator-card .btn-search {
            padding: 6px 10px;
            font-size: 0.8rem;
            gap: 5px;
        }

        .estimator-card table th,
        .estimator-card table td {
            font-size: 0.82rem;
            line-height: 1.2;
        }

        /* ─── Search / Tracker ─── */
        .search-row {
            display: flex;
            gap: 10px;
        }
        .search-input {
            flex: 1;
            padding: 10px 14px;
            font-family: inherit;
            font-size: 0.92rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: var(--bg-secondary);
            color: var(--text-primary);
            transition: all var(--transition-base);
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary-light);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(15,76,117,0.1);
        }
        .search-input::placeholder { color: var(--text-muted); }
        .btn-search {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 20px;
            background: var(--primary-gradient);
            color: #fff;
            font-size: 0.88rem;
            font-weight: 700;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-base);
            box-shadow: 0 3px 8px rgba(15,76,117,0.35);
            white-space: nowrap;
        }
        .btn-search:hover {
            background: linear-gradient(135deg, #1b6ca8 0%, #2578ba 100%);
            transform: translateY(-1px);
        }
        #trackerResults { margin-top: 16px; }

        /* tracker result cards */
        .result-card {
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            margin-bottom: 12px;
            overflow: hidden;
            background: var(--card-bg);
            box-shadow: var(--shadow-sm);
            transition: box-shadow var(--transition-base), border-color var(--transition-base);
        }
        .result-card:hover { box-shadow: var(--shadow-md); border-color: var(--primary-light); }
        .result-card-header {
            padding: 12px 16px;
            background: var(--bg-secondary);
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            border-bottom: 1px solid var(--border-color);
            gap: 10px;
        }
        .result-card-header:hover { background: var(--bg-primary); }
        .result-title {
            font-weight: 700;
            color: var(--primary);
            font-size: 0.92rem;
        }
        .result-status {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 3px;
        }
        .result-status strong { color: var(--primary-dark); }
        .result-body {
            padding: 12px 16px;
            display: none;
            animation: fadeIn 0.25s ease;
        }
        .activity-table {
            width: 100%;
            font-size: 0.83rem;
            border-collapse: collapse;
        }
        .activity-table td {
            padding: 6px 8px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
        }
        .activity-table tr:last-child td { border-bottom: none; }
        .activity-table td:last-child {
            text-align: right;
            font-weight: 700;
        }
        .act-completed { color: #10b981; }
        .act-in_progress { color: #3b82f6; }
        .act-delayed { color: #ef4444; }
        .act-pending { color: #94a3b8; }

        /* ─── Announcements ─── */
        .announcement-item {
            padding: 16px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .announcement-item:first-child { padding-top: 0; }
        .announcement-item:last-child { border-bottom: none; padding-bottom: 0; }
        .ann-date {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 5px;
        }
        .ann-date i { color: var(--accent); }
        .ann-title {
            display: block;
            color: var(--primary);
            font-size: 0.97rem;
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 5px;
            transition: color var(--transition-base);
        }
        .ann-title:hover { color: var(--primary-light); text-decoration: underline; }
        .ann-desc {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.55;
        }

        /* ─── Alerts ─── */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            line-height: 1.5;
            margin-bottom: 0;
        }
        .alert-error  { background: var(--danger-bg);  color: #991b1b; border: 1px solid #fca5a5; }
        .alert-success{ background: var(--success-bg); color: #065f46; border: 1px solid #6ee7b7; }
        .alert i { margin-top: 1px; flex-shrink: 0; }

        /* ─── Login Modal (dark, premium) ─── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
            animation: fadeIn 0.2s ease;
        }
        .dark-modal {
            background: #1e2d3d;
            border-radius: var(--radius-xl);
            box-shadow: 0 20px 60px rgba(0,0,0,0.45);
            max-width: 420px;
            width: 92%;
            position: relative;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.08);
            animation: slideDown 0.25s ease;
        }
        .modal-close-dark {
            position: absolute;
            top: 14px; right: 14px;
            background: rgba(255,255,255,0.07);
            border: none;
            color: #9ca3af;
            width: 30px; height: 30px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all var(--transition-base);
        }
        .modal-close-dark:hover { background: rgba(255,255,255,0.15); color: #fff; }
        .dark-modal-body { padding: 36px 30px 30px; }
        .dark-modal-header { text-align: center; margin-bottom: 26px; }
        .dark-modal-logo {
            width: 104px; height: 104px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(212,175,55,0.5);
            box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
            margin-bottom: 14px;
        }
        .dark-modal-header h2 {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.3px;
            margin: 0 0 5px;
        }
        .dark-modal-header p {
            font-size: 0.8rem;
            color: #6b7280;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 600;
        }
        .dark-form-group { margin-bottom: 16px; }
        .dark-form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #d1d5db;
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .dark-form-control {
            width: 100%;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 11px 14px;
            border-radius: var(--radius-md);
            color: #fff;
            font-family: inherit;
            font-size: 0.93rem;
            transition: border-color var(--transition-base), box-shadow var(--transition-base);
        }
        .dark-form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(27,108,168,0.25);
        }
        .dark-form-control::placeholder { color: #4b5563; }
        .dark-forgot-link {
            display: block;
            text-align: right;
            font-size: 0.8rem;
            color: #9ca3af;
            text-decoration: none;
            margin-top: -6px;
            margin-bottom: 20px;
            font-weight: 600;
            transition: color var(--transition-base);
        }
        .dark-forgot-link:hover { color: #e5e7eb; }
        .dark-btn-primary {
            width: 100%;
            background: var(--primary-gradient);
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            transition: all var(--transition-base);
            box-shadow: 0 4px 15px rgba(15,76,117,0.4);
            letter-spacing: 0.03em;
        }
        .dark-btn-primary:hover {
            background: linear-gradient(135deg, #1b6ca8 0%, #2578ba 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(15,76,117,0.5);
        }
        .dark-divider {
            display: flex;
            align-items: center;
            margin: 22px 0 18px;
            gap: 12px;
            color: #9ca3af;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.05em;
        }
        .dark-divider::before, .dark-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .dark-btn-secondary {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            width: 100%;
            background: rgba(255,255,255,0.06);
            color: #d1d5db;
            border: 1px solid rgba(255,255,255,0.1);
            padding: 11px;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all var(--transition-base);
        }
        .dark-btn-secondary:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .dark-help-link {
            text-align: center;
            margin-top: 22px;
            font-size: 0.82rem;
            color: #9ca3af;
        }
        .dark-help-link a { color: #d1d5db; font-weight: 700; text-decoration: none; }
        .dark-help-link a:hover { color: #ffffff; text-decoration: underline; }

        /* modal alerts */
        .dark-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 14px;
            border-radius: var(--radius-md);
            font-size: 0.86rem;
            line-height: 1.5;
            margin-bottom: 18px;
        }
        .dark-alert-error   { background: rgba(239,68,68,0.12); color: #fca5a5; border: 1px solid rgba(239,68,68,0.25); }
        .dark-alert-success { background: rgba(16,185,129,0.12); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.25); }

        /* ─── Footer ─── */
        .site-footer {
            background: var(--primary-dark);
            border-top: 2px solid var(--accent);
            padding: 22px 20px;
            margin-top: auto;
        }
        .footer-inner {
            max-width: 1140px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .footer-left, .footer-right {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.45);
            line-height: 1.6;
        }
        .footer-left {
            text-align: center;
            margin: 0 auto;
        }
        .footer-left strong, .footer-right strong { color: rgba(255,255,255,0.75); }
        .footer-right { text-align: right; }

        /* ─── Animations ─── */
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes slideDown {
            from { transform: translateY(-14px); opacity: 0; }
            to   { transform: translateY(0);     opacity: 1; }
        }

        /* ─── Responsive ─── */
        @media (max-width: 600px) {
            .search-row { flex-direction: column; }
            .btn-search { width: 100%; justify-content: center; }
            .header-flag { display: none; }
            .footer-right { text-align: left; }
            .dark-modal-body { padding: 28px 20px 22px; }
        }

        @media (max-width: 800px) {
            /* On smaller screens, restore normal flow for header elements */
            .site-header { position: relative; }
            .navbar-links {
                position: static;
                transform: none;
                left: auto;
                gap: 12px;
                justify-content: center;
                width: 100%;
                margin: 8px 0 0 0;
            }
            .btn-login {
                position: static;
                right: auto;
                top: auto;
                transform: none;
                margin-left: 0;
            }
            .header-inner { height: auto; padding: 16px 18px; flex-wrap: wrap; }
        }
    </style>
</head>
<body>

    <!-- ── Main Header ── -->

    <header class="site-header">
        <div class="header-inner">
            <div class="header-logo-wrap">
                <img src="/SDO-BACtrack/sdo-template/logo-imgs/sdo-logo.jpg" alt="SDO Logo">
            </div>
            <div class="header-text-group">
                <h1><?php echo APP_NAME; ?></h1>
                <p><?php echo APP_SUBTITLE; ?></p>
            </div>
            <div class="header-spacer"></div>
            <div class="navbar-links">
                <a href="landing.php" class="nav-link active"><i class="fas fa-home"></i> Home</a>
                <a href="#" class="nav-link" onclick="openContactModal(); return false;">Contacts</a>
            </div>
            <button class="btn-login" onclick="openLoginModal()">
                <i class="fas fa-sign-in-alt"></i> LOGIN
            </button>
        </div>
    </header>





    <!-- ── Main Content ── -->
    <main class="main-content">
        <div class="content-wrap">

            <!-- Detailed Procurement Timeline Planner (table) -->
            <div class="data-card estimator-card">
                <div class="card-header">
                    <i class="fas fa-table"></i> Procurement Timeline Estimator
                </div>
                <div class="card-body">
                    <?php
                        $procCfg = procurementConfig();
                        $workflowKeys = array_keys($procCfg['workflows'] ?? []);
                        $estimatorTypes = [];
                        foreach (PROCUREMENT_TYPES as $key => $label) {
                            if (in_array($key, $workflowKeys, true)) {
                                $estimatorTypes[$key] = $label;
                            }
                        }
                    ?>
                    <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;flex-wrap:wrap;">
                        <label style="font-weight:700;margin-right:6px;">Procurement Type / Mode of Procurement:</label>
                        <select id="estProcurementType" class="search-input" style="max-width:360px;">
                            <option value="" selected>Select procurement type / mode</option>
                            <?php foreach ($estimatorTypes as $k => $lbl): ?>
                                <option value="<?php echo htmlspecialchars($k); ?>"><?php echo htmlspecialchars($lbl); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <input type="number" id="estBudget" class="search-input" min="0" step="0.01" placeholder="Estimated Budget (PHP)" style="max-width:220px;" />

                        <label style="font-weight:700;margin-left:6px;">Implementation date:</label>
                        <input type="date" id="plannerStart" class="search-input" style="max-width:170px;" />

                        <button class="btn-search" onclick="computeEarliest()">Compute / Reset</button>
                        <button class="btn-search" style="background:#ddd;color:#333;box-shadow:none;" onclick="startOver()">Start Over</button>
                    </div>

                    <div id="svpBudgetWarning" style="display:none;margin:8px 0;padding:8px 10px;border:1px solid var(--danger);background:var(--danger-bg);color:#7f1d1d;border-radius:10px;font-weight:600;font-size:0.84rem;"></div>

                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:var(--bg-secondary);">
                                <th style="padding:5px 6px;border:1px solid var(--border-color);width:38%;text-align:left;">Procurement Stage</th>
                                <th style="padding:5px 6px;border:1px solid var(--border-color);width:16%;">Start Date</th>
                                <th style="padding:5px 6px;border:1px solid var(--border-color);width:16%;">End Date</th>
                                <th style="padding:5px 6px;border:1px solid var(--border-color);width:12%;">Add days</th>
                                <th style="padding:5px 6px;border:1px solid var(--border-color);width:18%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="plannerBody">
                            <!-- rows injected by JS -->
                        </tbody>
                    </table>

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;gap:8px;flex-wrap:wrap;">
                        <button class="btn-search" onclick="computeLatest()">Compute for the Latest Allowable Time</button>
                        <div style="text-align:center;font-weight:700;font-size:0.82rem;">
                            <a href="https://192.168.11.1/icthelpdesk/login.php" target="_blank" rel="noopener noreferrer" style="color:var(--text-muted);text-decoration:none;">
                                User's Guide | Found errors? Tell us.
                            </a>
                        </div>
                    </div>

                    <div style="display:flex;gap:8px;align-items:center;margin-top:10px;flex-wrap:wrap;justify-content:flex-end;">
                        <label style="font-weight:800;color:var(--text-secondary);font-size:0.84rem;">Latest Allowable Date:</label>
                        <input type="date" id="latestAllowableDate" class="search-input" style="max-width:170px;" readonly />
                    </div>
                </div>
            </div>
            <div class="data-card">
                <div class="card-header">
                    <i class="fas fa-search"></i> Project Tracker
                </div>
                <div class="card-body">
                    <form id="trackerForm" onsubmit="event.preventDefault(); searchContent();">
                        <div class="search-row">
                            <input type="text" id="trackInput" class="search-input"
                                   placeholder="Enter Project Number or Title…" required>
                            <button type="submit" class="btn-search">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                    <div id="trackerResults"></div>
                </div>
            </div>

            <!-- Projects List -->
            <?php
            require_once __DIR__ . '/../models/Project.php';
            $projectModel = new Project();
            $projects = $projectModel->getAll([]);
            ?>
            <div class="data-card">
                <div class="card-header">
                    <i class="fas fa-folder-open"></i> Projects List
                </div>
                <div class="card-body">
                <?php if (empty($projects)): ?>
                    <div class="empty-state" style="text-align:center;padding:32px 0;">
                        <div class="empty-icon" style="font-size:2.5rem;color:var(--text-muted);"><i class="fas fa-folder-plus"></i></div>
                        <h3 style="margin:12px 0 6px;">No projects found</h3>
                        <p style="color:var(--text-muted);">No BAC projects have been created yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" style="width:100%;font-size:0.97rem;border-collapse:collapse;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                            <thead style="background: #f1f5f9;">
                                <tr>
                                    <th style="text-align:center; width: 180px; border:1px solid #e2e8f0; padding:12px 0;">Project Number</th>
                                    <th style="text-align:center; border:1px solid #e2e8f0; padding:12px 0;">Project Title</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>
                                <tr style="background:#fff;">
                                    <td style="text-align:center;font-weight:600;vertical-align:middle;border:1px solid #e2e8f0; padding:12px 0;">
                                        <?php printf('PR-%04d', $project['id']); ?>
                                    </td>
                                    <td style="text-align:center;vertical-align:middle;border:1px solid #e2e8f0; padding:12px 0;">
                                        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $project['id']; ?>" style="color: #0f4c75; font-weight: 600; text-decoration: none; display:inline-block;">
                                            <?php echo htmlspecialchars($project['title']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <!-- ── Contact Modal ── -->
    <div class="modal-overlay" id="contactModal" onclick="if(event.target===this) closeContactModal()">
        <div class="dark-modal">
            <button type="button" class="modal-close-dark" onclick="closeContactModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="dark-modal-body" style="text-align:center;">
                <div class="dark-modal-header" style="margin-bottom:18px;">
                    <i class="fas fa-headset" style="font-size:2.2rem;color:var(--accent-light);margin-bottom:10px;"></i>
                    <h2 style="margin-bottom:4px;">ICT Helpdesk Support</h2>
                    <p>Contact and Assistance</p>
                </div>
                <p style="margin:0 auto 20px;color:#cbd5e1;line-height:1.65;max-width:520px;">
                    If you are experiencing technical difficulties or have questions about this system,
                    you may reach out to our ICT Helpdesk portal.
                </p>
                <a href="https://192.168.11.1/icthelpdesk/login.php" target="_blank" rel="noopener noreferrer" class="dark-btn-primary" style="text-decoration:none;">
                    <i class="fas fa-external-link-alt"></i> Connect with Us
                </a>
            </div>
        </div>
    </div>

    <!-- ── Login Modal ── -->
    <div class="modal-overlay" id="loginModal" onclick="if(event.target===this) closeLoginModal()">
        <div class="dark-modal">
            <button type="button" class="modal-close-dark" onclick="closeLoginModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="dark-modal-body">
                <div class="dark-modal-header">
                    <img src="/SDO-BACtrack/sdo-template/logo-imgs/sdo-logo.jpg" alt="SDO Logo" class="dark-modal-logo">
                    <h2><?php echo APP_NAME; ?></h2>
                    <p><?php echo APP_SUBTITLE; ?></p>
                </div>

                <?php if (isset($_GET['registered'])): ?>
                <div class="dark-alert dark-alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>Registration successful. Your account will need to be approved by an administrator before you can sign in.</div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() { openLoginModal(); });
                </script>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                <div class="dark-alert dark-alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() { openLoginModal(); });
                </script>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="dark-form-group">
                        <label class="dark-form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="dark-form-control"
                               placeholder="your.email@deped.gov.ph"
                               value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    </div>
                    <div class="dark-form-group">
                        <label class="dark-form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="dark-form-control"
                               placeholder="Enter your password" required>
                    </div>

                    <a href="#" class="dark-forgot-link"><i class="fas fa-key"></i> Forgot Password?</a>

                    <button type="submit" class="dark-btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="dark-help-link">
                    Need help? Contact <a href="#"><strong>ICT Helpdesk</strong></a>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Footer ── -->
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-left">
                <strong>DepEd - Schools Division Office of San Pedro City</strong><br>
                <span>&copy; <?php echo date('Y'); ?> ICT Unit </span>
            </div>
           
        </div>
    </footer>

    <script>
        const ESTIMATOR_BACKWARD_STAGES = <?php
            $workflows = procurementConfig()['workflows'] ?? [];
            $backwardOnly = [];
            foreach ($workflows as $key => $wf) {
                $backwardOnly[$key] = $wf['backward_timeline_stages'] ?? [];
            }
            echo json_encode($backwardOnly, JSON_UNESCAPED_SLASHES);
        ?>;

        /* ── Modal ── */
        function openLoginModal() {
            document.getElementById('loginModal').style.display = 'flex';
        }
        function closeLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
        }
        function openContactModal() {
            document.getElementById('contactModal').style.display = 'flex';
        }
        function closeContactModal() {
            document.getElementById('contactModal').style.display = 'none';
        }

        /* Clear stale session token */
        try { sessionStorage.removeItem('auth_token'); } catch (e) {}

        /* ── Project Tracker ── */
        function searchContent() {
            const query = document.getElementById('trackInput').value;
            const box   = document.getElementById('trackerResults');
            box.innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:14px;font-size:0.88rem;"><i class="fas fa-spinner fa-spin"></i> Searching…</div>';

            fetch('api/track-project.php?q=' + encodeURIComponent(query))
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        box.innerHTML = `<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><div>${res.error}</div></div>`;
                        return;
                    }
                    if (res.data.length === 0) {
                        box.innerHTML = `<div class="alert alert-error"><i class="fas fa-info-circle"></i><div>No projects found matching your query.</div></div>`;
                        return;
                    }

                    let html = '';
                    res.data.forEach((project, idx) => {
                        let rows = '';
                        if (project.activities && project.activities.length > 0) {
                            project.activities.forEach(act => {
                                const cls = 'act-' + act.status.toLowerCase();
                                rows += `<tr>
                                    <td>Step ${act.step}: ${act.name}</td>
                                    <td class="${cls}">${act.status.replace(/_/g,' ')}</td>
                                </tr>`;
                            });
                        } else {
                            rows = '<tr><td colspan="2" style="color:var(--text-muted)">No timeline activities available.</td></tr>';
                        }

                        html += `
                        <div class="result-card">
                            <div class="result-card-header" onclick="toggleResult(${idx})">
                                <div>
                                    <div class="result-title">#${project.id} — ${project.title}</div>
                                    <div class="result-status">Status: <strong>${project.timeline_status}</strong></div>
                                </div>
                                <i class="fas fa-chevron-down" id="chev-${idx}" style="color:var(--text-muted);font-size:0.8rem;transition:transform 0.2s;"></i>
                            </div>
                            <div class="result-body" id="result-body-${idx}">
                                <table class="activity-table">${rows}</table>
                            </div>
                        </div>`;
                    });
                    box.innerHTML = html;
                })
                .catch(() => {
                    box.innerHTML = `<div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i><div>Error fetching data. Please try again.</div></div>`;
                });
        }

        function toggleResult(idx) {
            const body = document.getElementById('result-body-' + idx);
            const chev = document.getElementById('chev-' + idx);
            const open = body.style.display === 'block';
            body.style.display = open ? 'none' : 'block';
            chev.style.transform = open ? '' : 'rotate(180deg)';
        }

        function parseMoney(val) {
            const n = Number(val);
            return Number.isFinite(n) ? n : NaN;
        }

        function showBudgetWarning(message) {
            const box = document.getElementById('svpBudgetWarning');
            if (!message) {
                box.style.display = 'none';
                box.textContent = '';
                return;
            }
            box.style.display = 'block';
            box.textContent = message;
        }

        function validateBudgetRealtime() {
            const type = (document.getElementById('estProcurementType')?.value || '').trim();
            const budgetRaw = document.getElementById('estBudget')?.value ?? '';
            const budget = parseMoney(budgetRaw);

            // If empty, don't warn.
            if (budgetRaw === '' || Number.isNaN(budget)) {
                showBudgetWarning('');
                return;
            }

            if (type === 'SMALL_VALUE_PROCUREMENT') {
                if (budget >= 200000.0) {
                    showBudgetWarning('The budget for Small Value Procurement (200k and below) must not exceed 199,999.99.');
                    return;
                }
            }

            if (type === 'SMALL_VALUE_PROCUREMENT_200K') {
                if (budget < 200000.0) {
                    showBudgetWarning('The minimum budget for this procurement type is 200,000.00.');
                    return;
                }
                if (budget >= 2000000.0) {
                    showBudgetWarning('The maximum budget for this procurement type is 1,999,999.99.');
                    return;
                }
            }

            showBudgetWarning('');
        }

        /* ── Detailed Planner logic ── */
        function getSelectedBackwardStages() {
            const type = (document.getElementById('estProcurementType')?.value || '').trim();
            const stages = type ? (ESTIMATOR_BACKWARD_STAGES[type] || []) : [];
            return { type, stages };
        }

        function renderPlannerRows() {
            const tbody = document.getElementById('plannerBody');
            tbody.innerHTML = '';
            const { type, stages } = getSelectedBackwardStages();
            if (!type) {
                return;
            }
            stages.forEach((s, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding:4px 6px;border:1px solid var(--border-color);">
                        ${s.name}
                        <div style="font-size:0.75rem;color:var(--text-muted);">Base: ${Number(s.days) || 0} day(s)</div>
                    </td>
                    <td style="padding:4px 6px;border:1px solid var(--border-color);text-align:center;"><input type="date" id="start-${idx}" class="search-input" style="width:100%;max-width:120px;" readonly /></td>
                    <td style="padding:4px 6px;border:1px solid var(--border-color);text-align:center;"><input type="date" id="end-${idx}" class="search-input" style="width:100%;max-width:120px;" readonly /></td>
                    <td style="padding:4px 6px;border:1px solid var(--border-color);text-align:center;"><input type="number" id="add-${idx}" class="search-input" style="width:100%;max-width:64px;" value="0" /></td>
                    <td style="padding:4px 6px;border:1px solid var(--border-color);text-align:center;">
                        <button class="btn-search" onclick="updateRow(${idx})" style="padding:4px 8px;font-size:0.76rem;">Update</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function parseDateInput(val) {
            return val ? new Date(val + 'T00:00:00') : null;
        }

        function addDays(date, days) {
            const d = new Date(date);
            d.setDate(d.getDate() + days);
            return d;
        }

        function setLatestAllowableDate(val) {
            const el = document.getElementById('latestAllowableDate');
            if (!el) return;
            el.value = val || '';
        }

        function computeLatestAllowableSchedule() {
            const { type, stages } = getSelectedBackwardStages();
            if (!type || stages.length === 0) {
                setLatestAllowableDate('');
                return;
            }

            const implementationVal = document.getElementById('plannerStart')?.value || '';
            if (!implementationVal) {
                setLatestAllowableDate('');
                const tbody = document.getElementById('plannerBody');
                if (tbody) {
                    const inputs = tbody.querySelectorAll('input[type="date"]');
                    inputs.forEach(i => { i.value = ''; });
                }
                return;
            }

            const implementationDate = parseDateInput(implementationVal);
            if (!implementationDate) return;

            const lastIndex = stages.length - 1;

            // Cursor ends the day before implementation date.
            let cursor = addDays(implementationDate, -1);

            // We compute from last to first (same anchor behavior as backend engine).
            for (let i = lastIndex; i >= 0; i--) {
                const stage = stages[i];
                const baseDays = Math.max(0, parseInt(stage.days ?? 0, 10) || 0);
                const add = parseInt(document.getElementById(`add-${i}`)?.value || '0', 10) || 0;
                const effectiveDays = Math.max(0, baseDays + add);

                let plannedEnd;
                let plannedStart;

                if (effectiveDays === 0) {
                    plannedStart = addDays(cursor, 1);
                    plannedEnd = plannedStart;
                } else {
                    plannedEnd = new Date(cursor);
                    plannedStart = addDays(plannedEnd, -(effectiveDays - 1));
                    cursor = addDays(plannedStart, -1);
                }

                const startEl = document.getElementById(`start-${i}`);
                const endEl = document.getElementById(`end-${i}`);
                if (startEl) startEl.value = plannedStart.toISOString().slice(0, 10);
                if (endEl) endEl.value = plannedEnd.toISOString().slice(0, 10);
            }

            // Latest Allowable Date = END date of the first backward step.
            const firstEnd = document.getElementById('end-0')?.value || '';
            setLatestAllowableDate(firstEnd);
        }

        function computeEarliest() {
            // In this estimator, “Compute/Reset” means compute the latest allowable schedule backward from implementation date.
            computeLatestAllowableSchedule();
        }

        function startOver() {
            const typeEl = document.getElementById('estProcurementType');
            if (typeEl) {
                typeEl.value = '';
            }
            document.getElementById('plannerStart').value = '';
            setLatestAllowableDate('');
            renderPlannerRows();
            validateBudgetRealtime();
        }

        function updateRow(idx) {
            computeLatestAllowableSchedule();
        }

        function computeLatest() {
            // Same estimator logic; this button now just recomputes.
            computeLatestAllowableSchedule();
        }

        // initialize planner on load
        document.addEventListener('DOMContentLoaded', function() {
            renderPlannerRows();

            const typeEl = document.getElementById('estProcurementType');
            const budgetEl = document.getElementById('estBudget');
            const implEl = document.getElementById('plannerStart');
            const tbody = document.getElementById('plannerBody');

            if (typeEl) {
                typeEl.addEventListener('change', function() {
                    renderPlannerRows();
                    validateBudgetRealtime();
                    computeLatestAllowableSchedule();
                });
            }
            if (budgetEl) {
                budgetEl.addEventListener('input', validateBudgetRealtime);
                budgetEl.addEventListener('change', validateBudgetRealtime);
            }
            if (implEl) {
                implEl.addEventListener('change', computeLatestAllowableSchedule);
                implEl.addEventListener('input', computeLatestAllowableSchedule);
            }
            if (tbody) {
                tbody.addEventListener('input', function(e) {
                    const t = e.target;
                    if (t && t.id && t.id.startsWith('add-')) {
                        computeLatestAllowableSchedule();
                    }
                });
            }

            validateBudgetRealtime();
        });
    </script>
</body>
</html>
