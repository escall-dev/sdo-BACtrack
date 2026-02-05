<?php
/**
 * One-time migration: add registration fields to users table.
 * Run this once in your browser (e.g. http://localhost/SDO-BACtrack/admin/migrate_registration_fields.php)
 * then delete or rename this file for security.
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><title>Migration</title></head><body><pre>';

$db = db()->getConnection();
$results = [];

try {
    $stmt = $db->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage()) . "\n";
    exit;
}

$toAdd = [
    'employee_no' => "ADD COLUMN employee_no VARCHAR(50) NULL AFTER role",
    'position'     => "ADD COLUMN position VARCHAR(100) NULL AFTER employee_no",
    'office'       => "ADD COLUMN office VARCHAR(100) NULL AFTER position",
    'unit_section' => "ADD COLUMN unit_section VARCHAR(100) NULL AFTER office",
    'status'       => "ADD COLUMN status ENUM('PENDING', 'APPROVED') NOT NULL DEFAULT 'APPROVED' AFTER unit_section",
];

foreach ($toAdd as $col => $sql) {
    if (in_array($col, $columns, true)) {
        $results[] = "Column '$col' already exists — skipped.";
        continue;
    }
    try {
        $db->exec("ALTER TABLE users " . $sql);
        $results[] = "Added column '$col'.";
    } catch (PDOException $e) {
        $results[] = "Column '$col': " . $e->getMessage();
    }
}

echo "Migration result:\n" . implode("\n", $results);
echo "\n\nDone. You can now use the registration form. Delete this file (migrate_registration_fields.php) for security.";
echo '</pre></body></html>';
