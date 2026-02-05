<?php
/**
 * Admin Logout
 * SDO-BACtrack - Invalidates current token only; other sessions remain active
 */

require_once __DIR__ . '/../includes/auth.php';

$auth = auth();
$auth->logout();

header('Location: ' . APP_URL . '/admin/login.php?logged_out=1');
exit;
