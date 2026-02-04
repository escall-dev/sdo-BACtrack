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
        return $this->db->fetchAll("SELECT * FROM users ORDER BY name");
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
