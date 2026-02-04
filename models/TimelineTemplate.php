<?php
/**
 * Timeline Template Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';

class TimelineTemplate {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT * FROM timeline_templates WHERE id = ?",
            [$id]
        );
    }

    public function getByProcurementType($type) {
        return $this->db->fetchAll(
            "SELECT * FROM timeline_templates 
             WHERE procurement_type = ? 
             ORDER BY step_order ASC",
            [$type]
        );
    }

    public function getAll() {
        return $this->db->fetchAll(
            "SELECT * FROM timeline_templates ORDER BY procurement_type, step_order"
        );
    }

    public function create($data) {
        $this->db->query(
            "INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days) 
             VALUES (?, ?, ?, ?)",
            [
                $data['procurement_type'],
                $data['step_name'],
                $data['step_order'],
                $data['default_duration_days']
            ]
        );
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['step_name'])) {
            $fields[] = 'step_name = ?';
            $params[] = $data['step_name'];
        }
        if (isset($data['step_order'])) {
            $fields[] = 'step_order = ?';
            $params[] = $data['step_order'];
        }
        if (isset($data['default_duration_days'])) {
            $fields[] = 'default_duration_days = ?';
            $params[] = $data['default_duration_days'];
        }

        if (empty($fields)) return false;

        $params[] = $id;
        return $this->db->query(
            "UPDATE timeline_templates SET " . implode(', ', $fields) . " WHERE id = ?",
            $params
        );
    }

    public function delete($id) {
        return $this->db->query("DELETE FROM timeline_templates WHERE id = ?", [$id]);
    }

    public function getTotalDuration($procurementType) {
        $result = $this->db->fetch(
            "SELECT SUM(default_duration_days) as total FROM timeline_templates WHERE procurement_type = ?",
            [$procurementType]
        );
        return $result['total'] ?? 0;
    }
}
