<?php
/**
 * Main Entry Point / Redirect
 * SDO-BACtrack
 * 
 * Redirects to admin login page
 */

require_once __DIR__ . '/config/app.php';

header('Location: ' . APP_URL . '/admin/login.php');
exit;
