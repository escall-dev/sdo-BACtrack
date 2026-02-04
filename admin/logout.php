<?php
/**
 * Admin Logout
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/auth.php';

$auth = auth();
$auth->logout();

header('Location: ' . APP_URL . '/admin/login.php');
exit;
