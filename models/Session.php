<?php
/**
 * Session Model
 * SDO-BACtrack - Token-based authentication sessions
 */

require_once __DIR__ . '/../config/database.php';

class Session {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    /**
     * Create a new session for a user.
     * @param int $userId
     * @param string $token
     * @param string|null $deviceInfo
     * @param string $expiresAt MySQL datetime
     * @return int Session ID
     */
    public function create($userId, $token, $deviceInfo, $expiresAt) {
        $this->db->query(
            "INSERT INTO sessions (user_id, token, device_info, expires_at) VALUES (?, ?, ?, ?)",
            [$userId, $token, $deviceInfo, $expiresAt]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Find session by token.
     * @param string $token
     * @return array|null Session row with user_id, or null
     */
    public function findByToken($token) {
        return $this->db->fetch(
            "SELECT * FROM sessions WHERE token = ? AND expires_at > NOW()",
            [$token]
        );
    }

    /**
     * Extend session expiry (sliding expiration). Call on each request so the user
     * is only logged out after $lifetimeSeconds of inactivity.
     * @param string $token
     * @param int $lifetimeSeconds Seconds from now until expiry
     * @return bool
     */
    public function extendExpiry($token, $lifetimeSeconds) {
        $stmt = $this->db->query(
            "UPDATE sessions SET expires_at = DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE token = ?",
            [$lifetimeSeconds, $token]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete session by token.
     * @param string $token
     * @return bool
     */
    public function deleteByToken($token) {
        return $this->db->query(
            "DELETE FROM sessions WHERE token = ?",
            [$token]
        );
    }

    /**
     * Delete expired sessions (cleanup).
     * @return int Rows deleted
     */
    public function deleteExpired() {
        $stmt = $this->db->query("DELETE FROM sessions WHERE expires_at <= NOW()");
        return $stmt->rowCount();
    }
}
