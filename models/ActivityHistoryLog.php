<?php
/**
 * Activity History Log Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';

class ActivityHistoryLog {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT ahl.*, u.name as changed_by_name
             FROM activity_history_logs ahl
             LEFT JOIN users u ON ahl.changed_by = u.id
             WHERE ahl.id = ?",
            [$id]
        );
    }

    public function getByActivity($activityId) {
        return $this->db->fetchAll(
            "SELECT ahl.*, u.name as changed_by_name
             FROM activity_history_logs ahl
             LEFT JOIN users u ON ahl.changed_by = u.id
             WHERE ahl.activity_id = ?
             ORDER BY ahl.changed_at DESC",
            [$activityId]
        );
    }

    public function create($data) {
        $this->db->query(
            "INSERT INTO activity_history_logs (activity_id, action_type, old_value, new_value, changed_by) 
             VALUES (?, ?, ?, ?, ?)",
            [
                $data['activity_id'],
                $data['action_type'],
                $data['old_value'] ?? null,
                $data['new_value'] ?? null,
                $data['changed_by']
            ]
        );
        return $this->db->lastInsertId();
    }

    public function logDateChange($activityId, $oldStart, $oldEnd, $newStart, $newEnd, $changedBy) {
        return $this->create([
            'activity_id' => $activityId,
            'action_type' => 'DATE_CHANGE',
            'old_value' => json_encode(['start' => $oldStart, 'end' => $oldEnd]),
            'new_value' => json_encode(['start' => $newStart, 'end' => $newEnd]),
            'changed_by' => $changedBy
        ]);
    }

    public function logStatusChange($activityId, $oldStatus, $newStatus, $changedBy) {
        return $this->create([
            'activity_id' => $activityId,
            'action_type' => 'STATUS_CHANGE',
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
            'changed_by' => $changedBy
        ]);
    }

    public function logComplianceTag($activityId, $oldCompliance, $newCompliance, $remarks, $changedBy) {
        return $this->create([
            'activity_id' => $activityId,
            'action_type' => 'COMPLIANCE_TAG',
            'old_value' => $oldCompliance,
            'new_value' => json_encode(['status' => $newCompliance, 'remarks' => $remarks]),
            'changed_by' => $changedBy
        ]);
    }

    public function getRecentLogs($limit = 50) {
        return $this->db->fetchAll(
            "SELECT ahl.*, u.name as changed_by_name, pa.step_name, p.title as project_title
             FROM activity_history_logs ahl
             LEFT JOIN users u ON ahl.changed_by = u.id
             LEFT JOIN project_activities pa ON ahl.activity_id = pa.id
             LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
             LEFT JOIN projects p ON bc.project_id = p.id
             ORDER BY ahl.changed_at DESC
             LIMIT ?",
            [$limit]
        );
    }
}
