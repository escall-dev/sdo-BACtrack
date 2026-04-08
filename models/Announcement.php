<?php
/**
 * Announcement Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';

class Announcement {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT a.*, u.name AS creator_name
             FROM announcements a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.id = ?",
            [(int)$id]
        );
    }

    public function getActive($limit = 8) {
        $limit = max(1, (int)$limit);
        return $this->db->fetchAll(
            "SELECT a.*, u.name AS creator_name
             FROM announcements a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.is_active = 1
               AND (a.starts_at IS NULL OR a.starts_at = '' OR a.starts_at <= NOW())
               AND (a.ends_at   IS NULL OR a.ends_at   = '' OR a.ends_at   >= NOW())
             ORDER BY COALESCE(a.starts_at, a.created_at) DESC, a.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function listAll() {
        return $this->db->fetchAll(
            "SELECT a.*, u.name AS creator_name
             FROM announcements a
             LEFT JOIN users u ON u.id = a.created_by
             ORDER BY a.id DESC"
        );
    }

    public function create($data, $createdBy) {
        $title = trim((string)($data['title'] ?? ''));
        $body = trim((string)($data['body'] ?? ''));
        $linkUrl = trim((string)($data['link_url'] ?? ''));
        $isActive = !empty($data['is_active']) ? 1 : 0;
        $startsAt = $this->normalizeDateTime($data['starts_at'] ?? null);
        $endsAt = $this->normalizeDateTime($data['ends_at'] ?? null);

        $this->db->query(
            "INSERT INTO announcements (title, body, link_url, is_active, starts_at, ends_at, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$title, $body, ($linkUrl !== '' ? $linkUrl : null), $isActive, $startsAt, $endsAt, (int)$createdBy]
        );

        return (int)$this->db->lastInsertId();
    }

    public function update($id, $data) {
        $title = trim((string)($data['title'] ?? ''));
        $body = trim((string)($data['body'] ?? ''));
        $linkUrl = trim((string)($data['link_url'] ?? ''));
        $isActive = !empty($data['is_active']) ? 1 : 0;
        $startsAt = $this->normalizeDateTime($data['starts_at'] ?? null);
        $endsAt = $this->normalizeDateTime($data['ends_at'] ?? null);

        $this->db->query(
            "UPDATE announcements
             SET title = ?, body = ?, link_url = ?, is_active = ?, starts_at = ?, ends_at = ?
             WHERE id = ?",
            [$title, $body, ($linkUrl !== '' ? $linkUrl : null), $isActive, $startsAt, $endsAt, (int)$id]
        );
        return true;
    }

    public function delete($id) {
        $this->db->query("DELETE FROM announcements WHERE id = ?", [(int)$id]);
        return true;
    }

    private function normalizeDateTime($val) {
        if ($val === null) return null;
        $s = trim((string)$val);
        if ($s === '') return null;

        // Accept HTML datetime-local (YYYY-MM-DDTHH:MM) and date (YYYY-MM-DD)
        $s = str_replace('T', ' ', $s);

        $ts = strtotime($s);
        if ($ts === false) {
            return null;
        }
        return date('Y-m-d H:i:s', $ts);
    }
}

