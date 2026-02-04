<?php
/**
 * Admin Panel Header
 * SDO-BACtrack
 */

// Start output buffering so that any redirects or header() calls
// made before the final response is sent will not trigger
// "headers already sent" warnings even if some output happened.
if (ob_get_level() === 0) {
    ob_start();
}

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/../models/Notification.php';

$auth = auth();
$auth->requireLogin();

$currentUser = $auth->getUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Get notification counts
$notificationModel = new Notification();
$unreadNotifications = $notificationModel->getUnreadCount($auth->getUserId());
$notifications = $notificationModel->getByUser($auth->getUserId(), 10);

// Get page title
$pageTitles = [
    'index' => 'Dashboard',
    'calendar' => 'Calendar',
    'projects' => 'Projects',
    'project-view' => 'View Project',
    'project-create' => 'Create Project',
    'activities' => 'Activities',
    'activity-view' => 'View Activity',
    'documents' => 'Documents',
    'adjustments' => 'Adjustment Requests',
    'reports' => 'Reports',
    'report-print' => 'Print Report',
    'users' => 'User Management',
    'profile' => 'My Profile'
];

$pageTitle = $pageTitles[$currentPage] ?? 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/admin.css">
    <?php if ($currentPage === 'calendar'): ?>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon-wrapper">
                        <div class="logo-badge">
                            <img src="/SDO-BACtrack/sdo-template/logo-imgs/sdo-logo.jpg" alt="SDO San Pedro Logo">
                        </div>
                    </div>
                    <div class="logo-text">
                        <span class="logo-title"><?php echo APP_NAME; ?></span>
                        <span class="logo-subtitle"><?php echo APP_SUBTITLE; ?></span>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="<?php echo APP_URL; ?>/admin/" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>" data-tooltip="Dashboard">
                    <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
                    <span class="nav-text">Dashboard</span>
                </a>
                
                <a href="<?php echo APP_URL; ?>/admin/calendar.php" class="nav-item <?php echo $currentPage === 'calendar' ? 'active' : ''; ?>" data-tooltip="Calendar">
                    <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
                    <span class="nav-text">Calendar</span>
                </a>
                
                <a href="<?php echo APP_URL; ?>/admin/projects.php" class="nav-item <?php echo in_array($currentPage, ['projects', 'project-view', 'project-create']) ? 'active' : ''; ?>" data-tooltip="Projects">
                    <span class="nav-icon"><i class="fas fa-folder-open"></i></span>
                    <span class="nav-text">Projects</span>
                </a>
                
                <a href="<?php echo APP_URL; ?>/admin/activities.php" class="nav-item <?php echo in_array($currentPage, ['activities', 'activity-view']) ? 'active' : ''; ?>" data-tooltip="Activities">
                    <span class="nav-icon"><i class="fas fa-tasks"></i></span>
                    <span class="nav-text">Activities</span>
                </a>
                
                <?php if ($auth->isProcurement()): ?>
                <a href="<?php echo APP_URL; ?>/admin/adjustments.php" class="nav-item <?php echo $currentPage === 'adjustments' ? 'active' : ''; ?>" data-tooltip="Adjustment Requests">
                    <span class="nav-icon"><i class="fas fa-clock"></i></span>
                    <span class="nav-text">Adjustments</span>
                </a>
                <?php endif; ?>
                
                <a href="<?php echo APP_URL; ?>/admin/reports.php" class="nav-item <?php echo in_array($currentPage, ['reports', 'report-print']) ? 'active' : ''; ?>" data-tooltip="Reports">
                    <span class="nav-icon"><i class="fas fa-file-alt"></i></span>
                    <span class="nav-text">Reports</span>
                </a>
                
                <?php if ($auth->isProcurement()): ?>
                <div class="nav-divider"></div>
                <a href="<?php echo APP_URL; ?>/admin/users.php" class="nav-item <?php echo $currentPage === 'users' ? 'active' : ''; ?>" data-tooltip="Users">
                    <span class="nav-icon"><i class="fas fa-users"></i></span>
                    <span class="nav-text">Users</span>
                </a>
                <?php endif; ?>
                
                <a href="<?php echo APP_URL; ?>/admin/profile.php" class="nav-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?>" data-tooltip="My Profile">
                    <span class="nav-icon"><i class="fas fa-user-cog"></i></span>
                    <span class="nav-text">My Profile</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar-placeholder">
                        <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                        <span class="user-role"><?php echo USER_ROLES[$currentUser['role']] ?? $currentUser['role']; ?></span>
                    </div>
                </div>
                <a href="<?php echo APP_URL; ?>/admin/logout.php" class="logout-btn-new" title="Logout">
                    <i class="bx bx-log-out"></i>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <div class="top-bar-left">
                    <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fas fa-bars"></i></button>
                    <button class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
                        <i class="fas fa-columns toggle-icon"></i>
                    </button>
                    <h1 class="page-title"><?php echo $pageTitle; ?></h1>
                </div>
                <div class="top-bar-right">
                    <!-- Notifications Dropdown -->
                    <div class="notification-dropdown">
                        <button class="notification-btn" id="notificationBtn">
                            <i class="fas fa-bell"></i>
                            <?php if ($unreadNotifications > 0): ?>
                            <span class="notification-badge"><?php echo $unreadNotifications > 99 ? '99+' : $unreadNotifications; ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="notification-panel" id="notificationPanel">
                            <div class="notification-header">
                                <h3>Notifications</h3>
                                <?php if ($unreadNotifications > 0): ?>
                                <a href="<?php echo APP_URL; ?>/admin/api/mark-notifications-read.php" class="mark-read-btn">Mark all read</a>
                                <?php endif; ?>
                            </div>
                            <div class="notification-list">
                                <?php if (empty($notifications)): ?>
                                <div class="notification-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No notifications</p>
                                </div>
                                <?php else: ?>
                                <?php foreach ($notifications as $notification): ?>
                                <a href="<?php echo APP_URL; ?>/admin/activity-view.php?id=<?php echo $notification['reference_id']; ?>" 
                                   class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                    <div class="notification-icon <?php echo strtolower($notification['type']); ?>">
                                        <?php
                                        $icon = 'bell';
                                        switch ($notification['type']) {
                                            case 'DEADLINE_WARNING': $icon = 'clock'; break;
                                            case 'ACTIVITY_DELAYED': $icon = 'exclamation-triangle'; break;
                                            case 'DOCUMENT_UPLOADED': $icon = 'file-upload'; break;
                                            case 'ADJUSTMENT_REQUEST': $icon = 'calendar-plus'; break;
                                            case 'ADJUSTMENT_RESPONSE': $icon = 'calendar-check'; break;
                                        }
                                        ?>
                                        <i class="fas fa-<?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <strong><?php echo htmlspecialchars($notification['title']); ?></strong>
                                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <span class="notification-time"><?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?></span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <span class="current-date"><?php echo date('l, F j, Y'); ?></span>
                </div>
            </header>
            
            <div class="content-wrapper">
                <?php echo displayFlashMessages(); ?>
