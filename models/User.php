<?php
/**
 * User Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
    }

    public function findByEmail($email) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }

    public function create($data) {
        $this->db->query(
            "INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)",
            [
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'] ?? 'PROJECT_OWNER'
            ]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Self-registration: creates user with status PENDING (admin must approve) when status column exists.
     * Returns ['id' => ...] on success or ['error' => 'message'] on failure.
     */
    public function register($data) {
        $email = trim($data['email'] ?? '');
        $existing = $this->findByEmail($email);
        if ($existing) {
            if (isset($existing['role']) && $existing['role'] === 'PROCUREMENT') {
                return ['error' => 'BAC Member accounts cannot be created through self-registration.'];
            }
            return ['error' => 'An account with this email already exists.'];
        }
        $name = trim($data['full_name'] ?? $data['name'] ?? '');
        $password = $data['password'] ?? '';
        $employeeNo = trim($data['employee_no'] ?? '');
        $position = trim($data['employee_position'] ?? $data['position'] ?? '');
        $office = trim($data['office'] ?? '');
        $unitSection = trim($data['unit_section'] ?? '');

        $hasStatusColumn = $this->usersTableHasStatusColumn();

        $cols = ['name', 'email', 'password_hash', 'role'];
        $placeholders = ['?', '?', '?', '?'];
        $params = [$name, $email, password_hash($password, PASSWORD_DEFAULT), 'PROJECT_OWNER'];

        if ($hasStatusColumn) {
            $cols[] = 'status';
            $placeholders[] = '?';
            $params[] = 'PENDING';
        }
        if ($hasStatusColumn && $employeeNo !== '') {
            $cols[] = 'employee_no';
            $placeholders[] = '?';
            $params[] = $employeeNo;
        }
        if ($hasStatusColumn && $position !== '') {
            $cols[] = 'position';
            $placeholders[] = '?';
            $params[] = $position;
        }
        if ($hasStatusColumn && $office !== '') {
            $cols[] = 'office';
            $placeholders[] = '?';
            $params[] = $office;
        }
        if ($hasStatusColumn && $unitSection !== '') {
            $cols[] = 'unit_section';
            $placeholders[] = '?';
            $params[] = $unitSection;
        }

        $this->db->query(
            'INSERT INTO users (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')',
            $params
        );
        return ['id' => $this->db->lastInsertId()];
    }

    private function usersTableHasStatusColumn() {
        static $has = null;
        if ($has !== null) {
            return $has;
        }
        try {
            $rows = $this->db->fetchAll("SHOW COLUMNS FROM users LIKE 'status'");
            $has = !empty($rows);
        } catch (Exception $e) {
            $has = false;
        }
        return $has;
    }

    public function isApproved($user) {
        if (!isset($user['status'])) return true;
        return $user['status'] === 'APPROVED';
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $params[] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $params[] = $data['email'];
        }
        if (isset($data['password'])) {
            $fields[] = 'password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (isset($data['role'])) {
            $fields[] = 'role = ?';
            $params[] = $data['role'];
        }
        if (isset($data['status']) && in_array($data['status'], ['PENDING', 'APPROVED'], true)) {
            $fields[] = 'status = ?';
            $params[] = $data['status'];
        }

        if (empty($fields)) return false;

        $params[] = $id;
        return $this->db->query(
            "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?",
            $params
        );
    }

    public function delete($id) {
        return $this->db->query("DELETE FROM users WHERE id = ?", [$id]);
    }

    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
    }

    /**
     * Update user password.
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function updatePassword($userId, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->db->query(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [$hash, $userId]
        );
    }

    public function verifyPassword($email, $password) {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    public function isProcurement($userId) {
        $user = $this->findById($userId);
        return $user && $user['role'] === 'PROCUREMENT';
    }

    public function isProjectOwner($userId) {
        $user = $this->findById($userId);
        return $user && $user['role'] === 'PROJECT_OWNER';
    }
}
