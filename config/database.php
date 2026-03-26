<?php
/**
 * Database Configuration
 * SDO-BACtrack - BAC Procedural Timeline Tracking System
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'sdo_bac');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

            // Apply SQL migrations from database/updates once per file.
            $this->runPendingMigrations();
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    private function runPendingMigrations() {
        $this->connection->exec(
            "CREATE TABLE IF NOT EXISTS schema_migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL UNIQUE,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB"
        );

        $appliedRows = $this->connection
            ->query("SELECT filename FROM schema_migrations")
            ->fetchAll(PDO::FETCH_COLUMN);
        $applied = array_fill_keys($appliedRows ?: [], true);

        $updatesDir = dirname(__DIR__) . '/database/updates';
        if (!is_dir($updatesDir)) {
            return;
        }

        $files = glob($updatesDir . '/*.sql');
        if ($files === false || empty($files)) {
            return;
        }

        natsort($files);

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            if (isset($applied[$filename])) {
                continue;
            }

            $sql = file_get_contents($filePath);
            if ($sql === false) {
                throw new RuntimeException('Failed to read migration file: ' . $filename);
            }

            $statements = $this->splitSqlStatements($sql);

            try {
                foreach ($statements as $statement) {
                    $trimmed = trim($statement);
                    if ($trimmed === '') {
                        continue;
                    }
                    $this->connection->exec($trimmed);
                }

                $insert = $this->connection->prepare(
                    "INSERT INTO schema_migrations (filename) VALUES (?)"
                );
                $insert->execute([$filename]);
            } catch (Throwable $e) {
                // Some deployments already applied old updates manually.
                // If we hit an "already exists"-type error, mark file as applied and continue.
                if ($this->isIgnorableMigrationError($e)) {
                    $insert = $this->connection->prepare(
                        "INSERT IGNORE INTO schema_migrations (filename) VALUES (?)"
                    );
                    $insert->execute([$filename]);
                    continue;
                }

                throw $e;
            }
        }
    }

    private function isIgnorableMigrationError($exception) {
        $message = strtolower($exception->getMessage());
        $patterns = [
            'duplicate column',
            'already exists',
            'duplicate key name',
            'duplicate entry',
            'can\'t drop',
            'doesn\'t exist'
        ];

        foreach ($patterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function splitSqlStatements($sql) {
        $statements = [];
        $buffer = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if (!$inSingleQuote && !$inDoubleQuote) {
                // Skip single-line comments (-- ... and # ...)
                if ($char === '-' && $next === '-') {
                    while ($i < $length && $sql[$i] !== "\n") {
                        $i++;
                    }
                    continue;
                }
                if ($char === '#') {
                    while ($i < $length && $sql[$i] !== "\n") {
                        $i++;
                    }
                    continue;
                }
                // Skip block comments /* ... */
                if ($char === '/' && $next === '*') {
                    $i += 2;
                    while ($i < $length - 1 && !($sql[$i] === '*' && $sql[$i + 1] === '/')) {
                        $i++;
                    }
                    $i++;
                    continue;
                }
            }

            if ($char === "'" && !$inDoubleQuote) {
                $escaped = $i > 0 && $sql[$i - 1] === '\\';
                if (!$escaped) {
                    $inSingleQuote = !$inSingleQuote;
                }
            } elseif ($char === '"' && !$inSingleQuote) {
                $escaped = $i > 0 && $sql[$i - 1] === '\\';
                if (!$escaped) {
                    $inDoubleQuote = !$inDoubleQuote;
                }
            }

            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote) {
                $statements[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollBack();
    }
}

// Helper function to get database instance
function db() {
    return Database::getInstance();
}
