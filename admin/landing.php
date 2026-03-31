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
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
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
            margin-left: 90px;
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
        .nav-tab-btn {
            cursor: pointer;
            font-family: inherit;
        }
        .nav-tab-btn:focus-visible {
            outline: 2px solid var(--accent-light);
            outline-offset: -2px;
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

        .landing-tab-panel {
            display: none;
        }

        .landing-tab-panel.active {
            display: block;
        }

        #landing-calendar-container {
            min-height: 260px;
        }

        .landing-calendar-loading,
        .landing-calendar-error {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            padding: 24px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: var(--card-bg);
            font-size: 0.9rem;
        }

        .landing-calendar-error {
            color: #991b1b;
            border-color: #fca5a5;
            background: #fee2e2;
        }

        .top-panels {
            display: grid;
            grid-template-columns: minmax(640px, 1.8fr) minmax(360px, 1fr);
            gap: 20px;
            align-items: start;
        }

        .top-panels .data-card {
            width: 100%;
            max-width: none;
            margin: 0;
        }

        .top-panels .card-header {
            min-height: 50px;
            padding: 14px 20px;
            font-size: 0.9rem;
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
            max-width: none;
            margin: 0;
        }

        .projects-card {
            width: 100%;
            max-width: none;
            margin: 24px 0 0;
        }

        .estimator-card .card-body {
            padding: 10px 12px;
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

        .planner-highlight td {
            background: #fff7ed;
        }

        .planner-highlight td:first-child {
            font-weight: 800;
            color: #9a3412;
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

        .projects-pager {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .projects-count {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .pager-links {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .pager-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            border-radius: 9px;
            border: 1px solid var(--border-color);
            background: #fff;
            color: var(--primary);
            text-decoration: none;
            font-size: 0.83rem;
            font-weight: 700;
            transition: all var(--transition-base);
        }

        .pager-link:hover {
            border-color: var(--primary-light);
            background: var(--bg-secondary);
        }

        .pager-link.active {
            background: var(--primary-gradient);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 2px 8px rgba(15,76,117,0.24);
            pointer-events: none;
        }

        .pager-link.disabled {
            color: var(--text-muted);
            background: #f8fafc;
            pointer-events: none;
        }

        .project-open-link {
            color: #0f4c75;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            border: none;
            background: transparent;
            font: inherit;
            padding: 0;
        }

        .project-open-link:hover {
            color: #1b6ca8;
            text-decoration: underline;
        }

        .tracking-number-link {
            white-space: nowrap;
        }

        .project-row {
            transition: background var(--transition-base);
        }

        .project-row:hover {
            background: #f8fafc;
        }

        #bacProcessModal {
            padding: 20px 12px;
            overflow-y: auto;
        }

        .dark-modal.bac-modal {
            max-width: 1140px;
            width: min(98vw, 1140px);
            max-height: calc(100vh - 40px);
            background: #ffffff;
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.28);
            display: flex;
            flex-direction: column;
        }

        .dark-modal.bac-modal .modal-close-dark {
            background: rgba(15, 76, 117, 0.08);
            color: #334155;
        }

        .dark-modal.bac-modal .modal-close-dark:hover {
            background: rgba(15, 76, 117, 0.16);
            color: #0f172a;
        }

        .dark-modal.bac-modal .dark-modal-body {
            padding: 20px 22px 18px;
            overflow: auto;
        }

        .bac-modal-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1.3;
            color: var(--primary-dark);
        }

        .bac-modal-subtitle {
            margin-top: 6px;
            color: var(--text-secondary);
            font-size: 0.84rem;
            font-weight: 600;
        }

        .bac-modal-description {
            margin-top: 10px;
            margin-bottom: 2px;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: #f8fafc;
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.45;
        }

        .bac-desc-label {
            color: var(--primary-dark);
            font-weight: 700;
            margin-right: 6px;
        }

        .bac-desc-value {
            color: var(--text-secondary);
            white-space: pre-line;
        }

        .bac-table-wrap {
            margin-top: 14px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            overflow-y: auto;
            overflow-x: hidden;
            max-height: 62vh;
            background: #fff;
        }

        #calendarActivityModal {
            padding: 20px 12px;
            overflow-y: auto;
        }

        .dark-modal.calendar-activity-modal {
            max-width: 780px;
            width: min(95vw, 780px);
            max-height: calc(100vh - 40px);
            background: #ffffff;
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.28);
            display: flex;
            flex-direction: column;
        }

        .dark-modal.calendar-activity-modal .modal-close-dark {
            background: rgba(15, 76, 117, 0.08);
            color: #334155;
        }

        .dark-modal.calendar-activity-modal .modal-close-dark:hover {
            background: rgba(15, 76, 117, 0.16);
            color: #0f172a;
        }

        .calendar-activity-body {
            padding: 20px 22px 18px;
            overflow: auto;
        }

        .calendar-activity-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1.3;
            color: var(--primary-dark);
        }

        .calendar-activity-subtitle {
            margin-top: 6px;
            color: var(--text-secondary);
            font-size: 0.84rem;
            font-weight: 600;
        }

        .calendar-activity-card {
            margin-top: 14px;
            padding: 16px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: #f8fafc;
        }

        .calendar-activity-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .calendar-activity-step {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }

        .calendar-activity-project {
            margin-top: 4px;
            font-size: 0.84rem;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .calendar-status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: 1px solid transparent;
        }

        .calendar-status-pill.pending { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
        .calendar-status-pill.in-progress { background: #dbeafe; color: #1d4ed8; border-color: #93c5fd; }
        .calendar-status-pill.completed { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
        .calendar-status-pill.delayed { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }

        .calendar-activity-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .calendar-activity-cell {
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-sm);
            background: #ffffff;
        }

        .calendar-activity-label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.72rem;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .calendar-activity-value {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.4;
        }

        .calendar-activity-meta {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .calendar-activity-meta-item {
            font-size: 0.82rem;
            color: var(--text-secondary);
        }

        .calendar-activity-meta-item strong {
            color: #0f172a;
        }

        .calendar-activity-actions {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .calendar-activity-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            font-weight: 700;
            padding: 10px 14px;
            border-radius: var(--radius-md);
        }

        .calendar-activity-link.secondary {
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            background: #ffffff;
        }

        .calendar-activity-link.secondary:hover {
            border-color: #cbd5e1;
            color: #0f172a;
            background: #f8fafc;
        }

        .calendar-activity-link.primary {
            border: 1px solid transparent;
            color: #ffffff;
            background: var(--primary-gradient);
            box-shadow: 0 4px 14px rgba(15, 76, 117, 0.3);
        }

        .calendar-activity-link.primary:hover {
            background: linear-gradient(135deg, #1b6ca8 0%, #2578ba 100%);
            color: #ffffff;
        }

        @media (max-width: 720px) {
            .calendar-activity-grid,
            .calendar-activity-meta {
                grid-template-columns: 1fr;
            }
        }

        .bac-process-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            table-layout: fixed;
        }

        .bac-process-table th,
        .bac-process-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 9px;
            text-align: left;
            vertical-align: top;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .bac-process-table th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .bac-process-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .bac-empty {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: var(--radius-md);
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.86rem;
        }

        .bac-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: 1px solid transparent;
        }

        .bac-status.completed { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
        .bac-status.in_progress { background: #dbeafe; color: #1d4ed8; border-color: #93c5fd; }
        .bac-status.pending { background: #e2e8f0; color: #475569; border-color: #cbd5e1; }
        .bac-status.delayed { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }

        .bac-loading {
            margin-top: 16px;
            color: #475569;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

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
            .dark-modal.bac-modal .dark-modal-body { padding: 18px 14px 14px; }
            .bac-process-table { font-size: 0.8rem; }
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

        @media (max-width: 1100px) {
            .top-panels {
                grid-template-columns: 1fr;
            }
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
                <button type="button" id="landing-home-tab" class="nav-link nav-tab-btn active" onclick="switchLandingTab('home')" role="tab" aria-selected="true" aria-controls="landing-home-panel"><i class="fas fa-home"></i> Home</button>
                <button type="button" id="landing-calendar-tab" class="nav-link nav-tab-btn" onclick="switchLandingTab('calendar')" role="tab" aria-selected="false" aria-controls="landing-calendar-panel"><i class="fas fa-calendar-alt"></i> Calendar</button>
                <a href="#" class="nav-link" onclick="openContactModal(); return false;"><i class="fas fa-phone"></i> Contacts</a>
            </div>
            <button class="btn-login" onclick="openLoginModal()">
                <i class="fas fa-sign-in-alt"></i> LOGIN
            </button>
        </div>
    </header>





    <!-- ── Main Content ── -->
    <main class="main-content">
        <div class="content-wrap">
            <section id="landing-home-panel" class="landing-tab-panel active" role="tabpanel" aria-labelledby="landing-home-tab">

            <div class="top-panels">

            <!-- Detailed Procurement Timeline Planner (table) -->
            <div class="data-card estimator-card">
                <div class="card-header">
                    <i class="fas fa-table"></i> Procurement Timeline Estimator
                </div>
                <div class="card-body">
                    <?php
                        $procCfg = procurementConfig();
                        $workflowKeys = array_keys($procCfg['workflows'] ?? []);
                        $estimatorWhitelist = [
                            'COMPETITIVE_BIDDING' => 'Competitive Bidding',
                            'SMALL_VALUE_PROCUREMENT' => 'Small Value Procurement (200k and below)',
                            'SMALL_VALUE_PROCUREMENT_200K' => 'Small Value Procurement (200k and above)',
                            'DIRECT_ACQUISITION' => 'Direct Acquisition',
                        ];

                        $estimatorTypes = [];
                        foreach ($estimatorWhitelist as $key => $label) {
                            if (in_array($key, $workflowKeys, true)) {
                                $estimatorTypes[$key] = $label;
                            }
                        }
                    ?>
                    <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;flex-wrap:wrap;">
                        <label style="font-weight:700;margin-right:6px;">Mode of Procurement:</label>
                        <select id="estProcurementType" class="search-input" style="max-width:360px;">
                            <option value="" selected>Select Mode of Procurement</option>
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
                                <th style="padding:5px 6px;border:1px solid var(--border-color);width:46%;text-align:left;">Procurement Stage</th>
                                <th style="padding:5px 6px;border:1px solid var(--border-color);width:27%;">Start Date</th>
                                <th style="padding:5px 6px;border:1px solid var(--border-color);width:27%;">End Date</th>
                            </tr>
                        </thead>
                        <tbody id="plannerBody">
                            <!-- rows injected by JS -->
                        </tbody>
                    </table>

                    <div style="display:flex;gap:8px;align-items:center;margin-top:10px;flex-wrap:wrap;justify-content:flex-end;">
                        <label style="font-weight:800;color:var(--text-secondary);font-size:0.84rem;">Latest Allowable Date:</label>
                        <input type="date" id="latestAllowableDate" class="search-input" style="max-width:160px;" readonly />
                    </div>

                    <div style="text-align:center;font-weight:700;font-size:0.82rem;margin-top:10px;">
                            <a href="https://192.168.11.1/icthelpdesk/login.php" target="_blank" rel="noopener noreferrer" style="color:var(--text-muted);text-decoration:none;">
                                User's Guide | Found errors? Tell us.
                            </a>
                    </div>

             
                </div>
            </div>
            </div>

            <!-- Projects List -->
            <?php
            require_once __DIR__ . '/../models/Project.php';
            $projectModel = new Project();
            $allProjects = $projectModel->getAll([]);

            $projectsPerPage = 10;
            $totalProjects = count($allProjects);
            $totalPages = max(1, (int) ceil($totalProjects / $projectsPerPage));

            $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            if ($currentPage < 1) {
                $currentPage = 1;
            }
            if ($currentPage > $totalPages) {
                $currentPage = $totalPages;
            }

            $offset = ($currentPage - 1) * $projectsPerPage;
            $projects = array_slice($allProjects, $offset, $projectsPerPage);

            $queryParams = $_GET;
            unset($queryParams['page']);

            $makePageUrl = function($page) use ($queryParams) {
                $params = $queryParams;
                if ($page > 1) {
                    $params['page'] = $page;
                }
                $query = http_build_query($params);
                return 'landing.php' . ($query !== '' ? ('?' . $query) : '');
            };

            $startRow = $totalProjects === 0 ? 0 : ($offset + 1);
            $endRow = $totalProjects === 0 ? 0 : min($offset + $projectsPerPage, $totalProjects);
            ?>
            <div class="data-card projects-card">
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
                                    <th style="text-align:center; width: 150px; border:1px solid #e2e8f0; padding:12px 8px;">Tracking Number</th>
                                    <th style="text-align:center; border:1px solid #e2e8f0; padding:12px 8px;">Project Title</th>
                                    <th style="text-align:center; width: 220px; border:1px solid #e2e8f0; padding:12px 8px;">Procurement Type</th>
                                    <th style="text-align:center; width: 170px; border:1px solid #e2e8f0; padding:12px 8px;">Implementation Date</th>
                                    <th style="text-align:center; width: 190px; border:1px solid #e2e8f0; padding:12px 8px;">Project Owner</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>
                                <?php
                                    $procTypeKey = $project['procurement_type'] ?? '';
                                    $procTypeLabel = PROCUREMENT_TYPES[$procTypeKey] ?? $procTypeKey;
                                    $implementationDate = !empty($project['project_start_date'])
                                        ? date('M d, Y', strtotime($project['project_start_date']))
                                        : 'Not set';
                                    $projectOwner = !empty($project['creator_name'])
                                        ? $project['creator_name']
                                        : 'Unassigned';
                                ?>
                                <tr class="project-row" style="background:#fff;">
                                    <td style="text-align:center;font-weight:600;vertical-align:middle;border:1px solid #e2e8f0; padding:12px 8px;">
                                        <button
                                            type="button"
                                            class="project-open-link tracking-number-link"
                                            onclick="openBacProcessModal(<?php echo (int)$project['id']; ?>, <?php echo htmlspecialchars(json_encode($project['title']), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode($project['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>);"
                                            aria-label="Open BAC process for project <?php echo (int)$project['id']; ?>"
                                        >
                                            <?php echo htmlspecialchars($project['bactrack_id'] ?? sprintf('PR-%04d', $project['id'])); ?>
                                        </button>
                                    </td>
                                    <td style="text-align:center;vertical-align:middle;border:1px solid #e2e8f0; padding:12px 8px;">
                                        <button
                                            type="button"
                                            class="project-open-link"
                                            onclick="openBacProcessModal(<?php echo (int)$project['id']; ?>, <?php echo htmlspecialchars(json_encode($project['title']), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode($project['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>);"
                                        >
                                            <?php echo htmlspecialchars($project['title']); ?>
                                        </button>
                                    </td>
                                    <td style="text-align:center;vertical-align:middle;border:1px solid #e2e8f0; padding:12px 8px;">
                                        <?php echo htmlspecialchars($procTypeLabel); ?>
                                    </td>
                                    <td style="text-align:center;vertical-align:middle;border:1px solid #e2e8f0; padding:12px 8px;">
                                        <?php echo htmlspecialchars($implementationDate); ?>
                                    </td>
                                    <td style="text-align:center;vertical-align:middle;border:1px solid #e2e8f0; padding:12px 8px;">
                                        <?php echo htmlspecialchars($projectOwner); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                    <div class="projects-pager">
                        <div class="projects-count">
                            Showing <?php echo $startRow; ?>-<?php echo $endRow; ?> of <?php echo $totalProjects; ?> projects
                        </div>
                        <div class="pager-links">
                            <?php if ($currentPage > 1): ?>
                                <a class="pager-link" href="<?php echo htmlspecialchars($makePageUrl($currentPage - 1)); ?>">Prev</a>
                            <?php else: ?>
                                <span class="pager-link disabled">Prev</span>
                            <?php endif; ?>

                            <?php
                            $windowStart = max(1, $currentPage - 2);
                            $windowEnd = min($totalPages, $currentPage + 2);

                            if ($windowStart > 1) {
                                echo '<a class="pager-link" href="' . htmlspecialchars($makePageUrl(1)) . '">1</a>';
                                if ($windowStart > 2) {
                                    echo '<span class="pager-link disabled">...</span>';
                                }
                            }

                            for ($i = $windowStart; $i <= $windowEnd; $i++) {
                                if ($i === $currentPage) {
                                    echo '<span class="pager-link active">' . $i . '</span>';
                                } else {
                                    echo '<a class="pager-link" href="' . htmlspecialchars($makePageUrl($i)) . '">' . $i . '</a>';
                                }
                            }

                            if ($windowEnd < $totalPages) {
                                if ($windowEnd < $totalPages - 1) {
                                    echo '<span class="pager-link disabled">...</span>';
                                }
                                echo '<a class="pager-link" href="' . htmlspecialchars($makePageUrl($totalPages)) . '">' . $totalPages . '</a>';
                            }
                            ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a class="pager-link" href="<?php echo htmlspecialchars($makePageUrl($currentPage + 1)); ?>">Next</a>
                            <?php else: ?>
                                <span class="pager-link disabled">Next</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                </div>
            </div>

            </section>

            <section id="landing-calendar-panel" class="landing-tab-panel" role="tabpanel" aria-labelledby="landing-calendar-tab" aria-hidden="true">
                <div id="landing-calendar-container" aria-live="polite"></div>
            </section>

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

    <!-- ── BAC Process Modal ── -->
    <div class="modal-overlay" id="bacProcessModal" onclick="if(event.target===this) closeBacProcessModal()">
        <div class="dark-modal bac-modal">
            <button type="button" class="modal-close-dark" onclick="closeBacProcessModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="dark-modal-body">
                <h2 class="bac-modal-title" id="bacModalProjectTitle">BAC Process</h2>
                <p class="bac-modal-subtitle" id="bacModalProjectStatus">Loading status...</p>
                <p class="bac-modal-description"><span class="bac-desc-label">Project Description:</span><span class="bac-desc-value" id="bacModalProjectDescription">Loading project description...</span></p>
                <div id="bacModalContent" class="bac-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Loading BAC process...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Calendar Activity Modal ── -->
    <div class="modal-overlay" id="calendarActivityModal" onclick="if(event.target===this) closeLandingCalendarActivityModal()">
        <div class="dark-modal calendar-activity-modal">
            <button type="button" class="modal-close-dark" onclick="closeLandingCalendarActivityModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="calendar-activity-body">
                <h2 class="calendar-activity-title" id="calendarActivityTitle">Process Details</h2>
                <p class="calendar-activity-subtitle" id="calendarActivitySubtitle">Select a calendar event to view details.</p>
                <div id="calendarActivityContent" class="bac-loading">
                    <i class="fas fa-info-circle"></i>
                    <span>Process details will appear here.</span>
                </div>
                <div class="calendar-activity-actions">
                    <button type="button" class="calendar-activity-link secondary" onclick="closeLandingCalendarActivityModal()">
                        <i class="fas fa-xmark"></i> Close
                    </button>
                </div>
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

        let landingCalendarLoaded = false;
        let landingCalendarLoading = false;
        let landingCalendarInstance = null;
        let landingCalendarSelectedProject = '';
        let landingCalendarFocusedProject = '';
        let landingCalendarFocusRequestId = 0;
        const LANDING_CALENDAR_WIDGET_URL = 'calendar-widget.php';
        const LANDING_CALENDAR_VIEW_KEY = 'landing_calendar_view';

        function switchLandingTab(tab) {
            const showCalendar = tab === 'calendar';
            const homeTab = document.getElementById('landing-home-tab');
            const calendarTab = document.getElementById('landing-calendar-tab');
            const homePanel = document.getElementById('landing-home-panel');
            const calendarPanel = document.getElementById('landing-calendar-panel');

            if (homeTab) {
                homeTab.classList.toggle('active', !showCalendar);
                homeTab.setAttribute('aria-selected', showCalendar ? 'false' : 'true');
            }
            if (calendarTab) {
                calendarTab.classList.toggle('active', showCalendar);
                calendarTab.setAttribute('aria-selected', showCalendar ? 'true' : 'false');
            }
            if (homePanel) {
                homePanel.classList.toggle('active', !showCalendar);
                homePanel.setAttribute('aria-hidden', showCalendar ? 'true' : 'false');
            }
            if (calendarPanel) {
                calendarPanel.classList.toggle('active', showCalendar);
                calendarPanel.setAttribute('aria-hidden', showCalendar ? 'false' : 'true');
            }

            if (showCalendar && !landingCalendarLoaded && !landingCalendarLoading) {
                loadLandingCalendarWidget();
            }
        }

        function loadLandingCalendarWidget() {
            const container = document.getElementById('landing-calendar-container');
            if (!container) {
                return;
            }

            landingCalendarLoading = true;
            container.innerHTML = '<div class="landing-calendar-loading"><i class="fas fa-spinner fa-spin"></i><span>Loading calendar...</span></div>';

            fetch(LANDING_CALENDAR_WIDGET_URL, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Failed to load calendar widget');
                    }
                    return response.text();
                })
                .then((html) => {
                    container.innerHTML = html;
                    landingCalendarLoaded = true;
                    initLandingCalendarWidget();
                })
                .catch(() => {
                    container.innerHTML = '<div class="landing-calendar-error"><i class="fas fa-exclamation-circle"></i><span>Unable to load the calendar panel right now.</span></div>';
                })
                .finally(() => {
                    landingCalendarLoading = false;
                });
        }

        function initLandingCalendarWidget() {
            const projectSelect = document.getElementById('landingCalendarProjectFilter');
            const trackingSearch = document.getElementById('landingCalendarTrackingSearch');
            const searchEmpty = document.getElementById('landingCalendarSearchEmpty');
            const prompt = document.getElementById('landingCalendarPrompt');
            const shell = document.getElementById('landingCalendarShell');
            const calendarEl = document.getElementById('landingCalendar');

            if (!projectSelect || !prompt || !shell || !calendarEl) {
                return;
            }

            const defaultOptionText = projectSelect.options.length > 0
                ? String(projectSelect.options[0].textContent || 'Select a project first')
                : 'Select a project first';

            const normalizeTrackingTerm = (value) => String(value || '')
                .toLowerCase()
                .replace(/[^a-z0-9]/g, '');

            const projectOptionRecords = Array.from(projectSelect.options)
                .filter((option) => String(option.value || '').trim() !== '')
                .map((option) => {
                    const value = String(option.value || '').trim();
                    const bactrackId = String(option.dataset.bactrackId || '').trim();
                    const projectTitle = String(option.dataset.projectTitle || option.textContent || '').trim();
                    return {
                        value,
                        text: String(option.textContent || '').trim(),
                        bactrackId,
                        projectTitle,
                        bactrackIdNormalized: normalizeTrackingTerm(bactrackId)
                    };
                });

            function ensureCalendarInstance() {
                if (landingCalendarInstance || typeof FullCalendar === 'undefined') {
                    return;
                }

                const savedView = localStorage.getItem(LANDING_CALENDAR_VIEW_KEY) || 'dayGridMonth';
                landingCalendarInstance = new FullCalendar.Calendar(calendarEl, {
                    initialView: savedView,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth'
                    },
                    events: function(info, successCallback, failureCallback) {
                        if (!landingCalendarSelectedProject) {
                            successCallback([]);
                            return;
                        }

                        const url = 'api/calendar-events.php?start=' + encodeURIComponent(info.startStr)
                            + '&end=' + encodeURIComponent(info.endStr)
                            + '&project=' + encodeURIComponent(landingCalendarSelectedProject);

                        fetch(url)
                            .then((response) => response.json())
                            .then((data) => {
                                if (Array.isArray(data)) {
                                    successCallback(data.map(function(eventItem) {
                                        if (!eventItem || typeof eventItem !== 'object') {
                                            return eventItem;
                                        }
                                        var normalizedEvent = Object.assign({}, eventItem);
                                        delete normalizedEvent.url;
                                        return normalizedEvent;
                                    }));
                                    return;
                                }
                                if (data && data.success && Array.isArray(data.events)) {
                                    successCallback(data.events.map(function(eventItem) {
                                        if (!eventItem || typeof eventItem !== 'object') {
                                            return eventItem;
                                        }
                                        var normalizedEvent = Object.assign({}, eventItem);
                                        delete normalizedEvent.url;
                                        return normalizedEvent;
                                    }));
                                    return;
                                }
                                failureCallback(data && data.error ? data.error : 'Unable to load events');
                            })
                            .catch((error) => {
                                failureCallback(error);
                            });
                    },
                    eventDidMount: function(info) {
                        const status = String(info.event.extendedProps.status || '').toLowerCase();
                        if (status !== '') {
                            info.el.classList.add('status-' + status);
                        }
                        if (info.el && info.el.hasAttribute('href')) {
                            info.el.removeAttribute('href');
                        }
                    },
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();
                        if (!info.event || !info.event.id) {
                            return;
                        }
                        openLandingCalendarActivityModal(info.event.id, info.event);
                    },
                    datesSet: function(info) {
                        localStorage.setItem(LANDING_CALENDAR_VIEW_KEY, info.view.type);
                    },
                    height: 'auto',
                    contentHeight: 'auto',
                    expandRows: false,
                    fixedWeekCount: false,
                    showNonCurrentDates: true,
                    dayMaxEvents: 1,
                    dayMaxEventRows: 1
                });

                landingCalendarInstance.render();
            }

            function applyProjectSelection() {
                landingCalendarSelectedProject = String(projectSelect.value || '').trim();

                if (!landingCalendarSelectedProject) {
                    prompt.style.display = '';
                    shell.style.display = 'none';
                    landingCalendarFocusedProject = '';
                    landingCalendarFocusRequestId += 1;
                    return;
                }

                prompt.style.display = 'none';
                shell.style.display = '';
                ensureCalendarInstance();

                if (!landingCalendarInstance) {
                    return;
                }

                const selectedProjectId = landingCalendarSelectedProject;
                const searchQuery = trackingSearch ? normalizeTrackingTerm(trackingSearch.value) : '';
                const forceRefocus = searchQuery !== '';

                if (!forceRefocus && landingCalendarFocusedProject === selectedProjectId) {
                    landingCalendarInstance.refetchEvents();
                    return;
                }

                landingCalendarFocusedProject = selectedProjectId;
                const requestId = ++landingCalendarFocusRequestId;

                fetch('api/calendar-events.php?project=' + encodeURIComponent(selectedProjectId))
                    .then((response) => response.json())
                    .then((data) => {
                        if (!landingCalendarInstance) {
                            return;
                        }
                        if (requestId !== landingCalendarFocusRequestId) {
                            return;
                        }
                        if (landingCalendarSelectedProject !== selectedProjectId) {
                            return;
                        }

                        const events = Array.isArray(data)
                            ? data
                            : (data && data.success && Array.isArray(data.events) ? data.events : []);

                        if (events.length > 0) {
                            const firstValidEvent = events.find((eventItem) => {
                                const rawDate = String((eventItem && eventItem.start) || '').trim();
                                if (!/^\d{4}-\d{2}-\d{2}$/.test(rawDate)) {
                                    return false;
                                }
                                return rawDate !== '0000-00-00';
                            });

                            if (firstValidEvent) {
                                const firstEventDate = String(firstValidEvent.start || '').trim();
                                landingCalendarInstance.gotoDate(firstEventDate);
                                landingCalendarInstance.refetchEvents();
                                return;
                            }
                        }

                        landingCalendarInstance.refetchEvents();
                    })
                    .catch(() => {
                        if (!landingCalendarInstance) {
                            return;
                        }
                        if (requestId !== landingCalendarFocusRequestId) {
                            return;
                        }
                        if (landingCalendarSelectedProject !== selectedProjectId) {
                            return;
                        }
                        landingCalendarInstance.refetchEvents();
                    });
            }

            function rebuildProjectOptions() {
                const currentValue = String(projectSelect.value || '').trim();
                const query = trackingSearch ? normalizeTrackingTerm(trackingSearch.value) : '';

                projectSelect.innerHTML = '';

                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.textContent = defaultOptionText;
                projectSelect.appendChild(placeholderOption);

                let matchCount = 0;
                let hasCurrent = false;
                let singleMatchValue = '';
                let exactMatchValue = '';

                projectOptionRecords.forEach((record) => {
                    const matches = query === '' || record.bactrackIdNormalized.indexOf(query) !== -1;
                    if (!matches) {
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = record.value;
                    option.textContent = record.text;
                    option.dataset.bactrackId = record.bactrackId;
                    option.dataset.projectTitle = record.projectTitle;
                    projectSelect.appendChild(option);
                    matchCount += 1;
                    singleMatchValue = record.value;

                    if (query !== '' && record.bactrackIdNormalized === query) {
                        exactMatchValue = record.value;
                    }

                    if (record.value === currentValue) {
                        hasCurrent = true;
                    }
                });

                projectSelect.disabled = matchCount === 0;

                if (exactMatchValue !== '') {
                    projectSelect.value = exactMatchValue;
                } else if (query !== '' && matchCount === 1 && singleMatchValue !== '') {
                    projectSelect.value = singleMatchValue;
                } else if (hasCurrent) {
                    projectSelect.value = currentValue;
                } else {
                    projectSelect.value = '';
                }

                if (searchEmpty) {
                    searchEmpty.style.display = query !== '' && matchCount === 0 ? '' : 'none';
                }

                applyProjectSelection();
            }

            projectSelect.addEventListener('change', applyProjectSelection);

            if (trackingSearch) {
                trackingSearch.addEventListener('input', rebuildProjectOptions);
                trackingSearch.addEventListener('search', rebuildProjectOptions);
            }

            rebuildProjectOptions();
        }

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

        function closeBacProcessModal() {
            document.getElementById('bacProcessModal').style.display = 'none';
        }

        function toReadableStatus(status) {
            return String(status || '')
                .replace(/_/g, ' ')
                .trim()
                .toLowerCase()
                .replace(/\b\w/g, function(char) { return char.toUpperCase(); });
        }

        function toStatusClass(status) {
            const key = String(status || '').toLowerCase().replace(/_/g, '-');
            if (key === 'in-progress') return 'in-progress';
            if (key === 'completed') return 'completed';
            if (key === 'delayed') return 'delayed';
            return 'pending';
        }

        function formatLongDate(value) {
            const raw = String(value || '').trim();
            if (!raw || raw === '0000-00-00') {
                return 'N/A';
            }

            const parsed = new Date(raw + 'T00:00:00');
            if (Number.isNaN(parsed.getTime())) {
                return raw;
            }

            return parsed.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        function buildCalendarActivityFallback(activityId, calendarEvent) {
            const props = (calendarEvent && calendarEvent.extendedProps) ? calendarEvent.extendedProps : {};
            return {
                id: Number(activityId) || 0,
                step_name: String((calendarEvent && calendarEvent.title) || 'Process Details'),
                project_title: String(props.project_title || 'Unknown project'),
                step_order: props.step_order != null ? props.step_order : '-',
                status: String(props.status || 'PENDING'),
                status_label: toReadableStatus(props.status || 'PENDING'),
                planned_start_date: String(props.planned_start_date || ''),
                planned_end_date: String(props.planned_end_date || ''),
                planned_start_date_formatted: formatLongDate(props.planned_start_date || ''),
                planned_end_date_formatted: formatLongDate(props.planned_end_date || ''),
                timing_label: 'Pending validation',
                compliance_label: null
            };
        }

        function renderLandingCalendarActivity(activity) {
            const titleEl = document.getElementById('calendarActivityTitle');
            const subtitleEl = document.getElementById('calendarActivitySubtitle');
            const contentEl = document.getElementById('calendarActivityContent');

            if (!titleEl || !subtitleEl || !contentEl) {
                return;
            }

            const stepName = String(activity.step_name || 'Process Details');
            const projectTitle = String(activity.project_title || 'Unknown project');
            const stepOrder = activity.step_order != null ? String(activity.step_order) : '-';
            const statusLabel = String(activity.status_label || toReadableStatus(activity.status || 'PENDING') || 'Pending');
            const statusClass = toStatusClass(activity.status || 'PENDING');
            const plannedStart = String(activity.planned_start_date_formatted || formatLongDate(activity.planned_start_date || ''));
            const plannedEnd = String(activity.planned_end_date_formatted || formatLongDate(activity.planned_end_date || ''));
            const timelineLabel = String(activity.timing_label || 'N/A');
            const complianceLabel = String(activity.compliance_label || 'Not set');

            titleEl.textContent = stepName;
            subtitleEl.textContent = projectTitle + ' | Process ' + stepOrder;

            contentEl.className = '';
            contentEl.innerHTML = `
                <div class="calendar-activity-card">
                    <div class="calendar-activity-top">
                        <div>
                            <h3 class="calendar-activity-step">${escapeHtml(stepName)}</h3>
                            <p class="calendar-activity-project">${escapeHtml(projectTitle)} | Process ${escapeHtml(stepOrder)}</p>
                        </div>
                        <span class="calendar-status-pill ${escapeHtml(statusClass)}">${escapeHtml(statusLabel)}</span>
                    </div>

                    <div class="calendar-activity-grid">
                        <div class="calendar-activity-cell">
                            <span class="calendar-activity-label">Planned Start</span>
                            <div class="calendar-activity-value">${escapeHtml(plannedStart)}</div>
                        </div>
                        <div class="calendar-activity-cell">
                            <span class="calendar-activity-label">Planned End</span>
                            <div class="calendar-activity-value">${escapeHtml(plannedEnd)}</div>
                        </div>
                        <div class="calendar-activity-cell">
                            <span class="calendar-activity-label">Timeline Status</span>
                            <div class="calendar-activity-value">${escapeHtml(timelineLabel)}</div>
                        </div>
                    </div>

                    <div class="calendar-activity-meta">
                        <div class="calendar-activity-meta-item">Compliance: <strong>${escapeHtml(complianceLabel)}</strong></div>
                    </div>
                </div>
            `;
        }

        function openLandingCalendarActivityModal(activityId, calendarEvent) {
            const modalEl = document.getElementById('calendarActivityModal');
            const titleEl = document.getElementById('calendarActivityTitle');
            const subtitleEl = document.getElementById('calendarActivitySubtitle');
            const contentEl = document.getElementById('calendarActivityContent');
            const fallbackData = buildCalendarActivityFallback(activityId, calendarEvent);

            if (!modalEl || !titleEl || !subtitleEl || !contentEl) {
                return;
            }

            titleEl.textContent = fallbackData.step_name;
            subtitleEl.textContent = 'Loading process details...';
            contentEl.className = 'bac-loading';
            contentEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Loading process details...</span>';
            modalEl.style.display = 'flex';

            fetch('api/activity-detail.php?id=' + encodeURIComponent(String(activityId)))
                .then(function(response) {
                    return response.json().then(function(payload) {
                        return {
                            ok: response.ok,
                            payload: payload
                        };
                    });
                })
                .then(function(result) {
                    if (result.ok && result.payload && result.payload.id) {
                        renderLandingCalendarActivity(result.payload);
                        return;
                    }
                    renderLandingCalendarActivity(fallbackData);
                })
                .catch(function() {
                    renderLandingCalendarActivity(fallbackData);
                });
        }

        function closeLandingCalendarActivityModal() {
            const modalEl = document.getElementById('calendarActivityModal');
            if (modalEl) {
                modalEl.style.display = 'none';
            }
        }

        function statusClass(status) {
            const key = String(status || '').toLowerCase();
            if (key === 'completed') return 'completed';
            if (key === 'in_progress') return 'in_progress';
            if (key === 'delayed') return 'delayed';
            return 'pending';
        }

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        }

        function formatDate(value) {
            if (!value) return '-';
            return value;
        }

        function renderBacProcess(project) {
            const titleEl = document.getElementById('bacModalProjectTitle');
            const statusEl = document.getElementById('bacModalProjectStatus');
            const descEl = document.getElementById('bacModalProjectDescription');
            const contentEl = document.getElementById('bacModalContent');

            titleEl.textContent = `PR-${String(project.id).padStart(4, '0')} - ${project.title}`;
            statusEl.textContent = `Current Status: ${project.timeline_status || 'N/A'}`;
            const description = String(project.description || '').trim();
            descEl.textContent = description !== ''
                ? description
                : 'No project description provided.';

            if (!project.activities || project.activities.length === 0) {
                contentEl.className = 'bac-empty';
                contentEl.innerHTML = 'No BAC process activities are available for this project yet.';
                return;
            }

            const rows = project.activities.map((act) => {
                const cls = statusClass(act.status);
                const statusLabel = String(act.status || '').replace(/_/g, ' ');
                return `
                    <tr>
                        <td>${escapeHtml(act.step)}</td>
                        <td>${escapeHtml(act.name)}</td>
                        <td>${escapeHtml(formatDate(act.planned_start))}</td>
                        <td>${escapeHtml(formatDate(act.planned_end))}</td>
                        <td><span class="bac-status ${cls}">${escapeHtml(statusLabel)}</span></td>
                    </tr>
                `;
            }).join('');

            contentEl.className = 'bac-table-wrap';
            contentEl.innerHTML = `
                <table class="bac-process-table">
                    <colgroup>
                        <col style="width:8%;">
                        <col style="width:40%;">
                        <col style="width:17%;">
                        <col style="width:17%;">
                        <col style="width:18%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Step</th>
                            <th>Activity</th>
                            <th>Planned Start</th>
                            <th>Planned End</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            `;
        }

        function openBacProcessModal(projectId, projectTitle, projectDescription) {
            const modal = document.getElementById('bacProcessModal');
            const titleEl = document.getElementById('bacModalProjectTitle');
            const statusEl = document.getElementById('bacModalProjectStatus');
            const descEl = document.getElementById('bacModalProjectDescription');
            const contentEl = document.getElementById('bacModalContent');

            titleEl.textContent = `Loading project #${projectId}...`;
            statusEl.textContent = 'Fetching BAC process...';
            const preloadDescription = String(projectDescription || '').trim();
            descEl.textContent = preloadDescription !== ''
                ? preloadDescription
                : 'Loading project description...';
            contentEl.className = 'bac-loading';
            contentEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Loading BAC process...</span>';
            modal.style.display = 'flex';

            fetch('api/track-project.php?q=' + encodeURIComponent(projectId))
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.data || res.data.length === 0) {
                        titleEl.textContent = projectTitle ? String(projectTitle) : `Project #${projectId}`;
                        statusEl.textContent = 'Unable to load BAC process';
                        descEl.textContent = preloadDescription !== ''
                            ? preloadDescription
                            : 'No project description available.';
                        contentEl.className = 'bac-empty';
                        contentEl.textContent = 'No BAC process data found for this project.';
                        return;
                    }
                    renderBacProcess(res.data[0]);
                })
                .catch(() => {
                    titleEl.textContent = projectTitle ? String(projectTitle) : `Project #${projectId}`;
                    statusEl.textContent = 'Unable to load BAC process';
                    descEl.textContent = preloadDescription !== ''
                        ? preloadDescription
                        : 'No project description available.';
                    contentEl.className = 'bac-empty';
                    contentEl.textContent = 'Error fetching BAC process. Please try again.';
                });
        }

        /* Clear stale session token */
        try { sessionStorage.removeItem('auth_token'); } catch (e) {}

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
                if (type === 'COMPETITIVE_BIDDING' && String(s.key || '') === 'eligibility_submission_opening') {
                    tr.classList.add('planner-highlight');
                }
                tr.innerHTML = `
                    <td style="padding:4px 6px;border:1px solid var(--border-color);">
                        ${s.name}
                        <div style="font-size:0.75rem;color:var(--text-muted);">Base: ${Number(s.days) || 0} day(s)</div>
                    </td>
                    <td style="padding:4px 6px;border:1px solid var(--border-color);text-align:center;"><input type="date" id="start-${idx}" class="search-input" style="width:100%;max-width:120px;" readonly /></td>
                    <td style="padding:4px 6px;border:1px solid var(--border-color);text-align:center;"><input type="date" id="end-${idx}" class="search-input" style="width:100%;max-width:120px;" readonly /></td>
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
                const effectiveDays = baseDays;

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

        function computeLatest() {
            // Same estimator logic; this button now just recomputes.
            computeLatestAllowableSchedule();
        }

        // initialize planner on load
        document.addEventListener('DOMContentLoaded', function() {
            switchLandingTab('home');
            renderPlannerRows();

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeLandingCalendarActivityModal();
                }
            });

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
                // no-op: removed "Add days" controls
            }

            validateBudgetRealtime();
        });
    </script>
</body>
</html>
