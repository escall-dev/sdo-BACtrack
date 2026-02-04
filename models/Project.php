<?php
/**
 * Project Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';

class Project {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT p.*, u.name as creator_name 
             FROM projects p 
             LEFT JOIN users u ON p.created_by = u.id 
             WHERE p.id = ?",
            [$id]
        );
    }

    public function getAll($filters = []) {
        $sql = "SELECT p.*, u.name as creator_name,
                (SELECT COUNT(*) FROM bac_cycles WHERE project_id = p.id) as cycle_count
                FROM projects p 
                LEFT JOIN users u ON p.created_by = u.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['procurement_type'])) {
            $sql .= " AND p.procurement_type = ?";
            $params[] = $filters['procurement_type'];
        }

        if (!empty($filters['created_by'])) {
            $sql .= " AND p.created_by = ?";
            $params[] = $filters['created_by'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function create($data) {
        $this->db->query(
            "INSERT INTO projects (title, description, procurement_type, created_by) 
             VALUES (?, ?, ?, ?)",
            [
                $data['title'],
                $data['description'] ?? '',
                $data['procurement_type'] ?? 'PUBLIC_BIDDING',
                $data['created_by']
            ]
        );
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['title'])) {
            $fields[] = 'title = ?';
            $params[] = $data['title'];
        }
        if (isset($data['description'])) {
            $fields[] = 'description = ?';
            $params[] = $data['description'];
        }
        if (isset($data['procurement_type'])) {
            $fields[] = 'procurement_type = ?';
            $params[] = $data['procurement_type'];
        }

        if (empty($fields)) return false;

        $params[] = $id;
        return $this->db->query(
            "UPDATE projects SET " . implode(', ', $fields) . " WHERE id = ?",
            $params
        );
    }

    public function delete($id) {
        return $this->db->query("DELETE FROM projects WHERE id = ?", [$id]);
    }

    public function getStatistics() {
        $stats = [];
        
        $stats['total'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM projects"
        )['count'];

        $stats['by_type'] = $this->db->fetchAll(
            "SELECT procurement_type, COUNT(*) as count 
             FROM projects GROUP BY procurement_type"
        );

        $stats['this_month'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM projects 
             WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
             AND YEAR(created_at) = YEAR(CURRENT_DATE())"
        )['count'];

        return $stats;
    }
}
