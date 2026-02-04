<?php
/**
 * BAC Cycle Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';

class BacCycle {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT bc.*, p.title as project_title, p.procurement_type
             FROM bac_cycles bc
             LEFT JOIN projects p ON bc.project_id = p.id
             WHERE bc.id = ?",
            [$id]
        );
    }

    public function getByProject($projectId) {
        return $this->db->fetchAll(
            "SELECT * FROM bac_cycles WHERE project_id = ? ORDER BY cycle_number",
            [$projectId]
        );
    }

    public function getActiveCycle($projectId) {
        return $this->db->fetch(
            "SELECT * FROM bac_cycles WHERE project_id = ? AND status = 'ACTIVE' ORDER BY cycle_number DESC LIMIT 1",
            [$projectId]
        );
    }

    public function create($projectId, $cycleNumber = 1) {
        $this->db->query(
            "INSERT INTO bac_cycles (project_id, cycle_number, status) VALUES (?, ?, 'ACTIVE')",
            [$projectId, $cycleNumber]
        );
        return $this->db->lastInsertId();
    }

    public function updateStatus($id, $status) {
        return $this->db->query(
            "UPDATE bac_cycles SET status = ? WHERE id = ?",
            [$status, $id]
        );
    }

    public function getNextCycleNumber($projectId) {
        $result = $this->db->fetch(
            "SELECT MAX(cycle_number) as max_cycle FROM bac_cycles WHERE project_id = ?",
            [$projectId]
        );
        return ($result['max_cycle'] ?? 0) + 1;
    }

    public function getStatistics($cycleId) {
        $stats = [];

        $stats['total_activities'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM project_activities WHERE bac_cycle_id = ?",
            [$cycleId]
        )['count'];

        $stats['by_status'] = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM project_activities 
             WHERE bac_cycle_id = ? GROUP BY status",
            [$cycleId]
        );

        $stats['completed'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM project_activities 
             WHERE bac_cycle_id = ? AND status = 'COMPLETED'",
            [$cycleId]
        )['count'];

        $stats['delayed'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM project_activities 
             WHERE bac_cycle_id = ? AND status = 'DELAYED'",
            [$cycleId]
        )['count'];

        return $stats;
    }
}
