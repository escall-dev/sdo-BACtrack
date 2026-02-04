<?php
/**
 * Timeline Adjustment Request Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';

class AdjustmentRequest {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT tar.*, 
                    u1.name as requester_name, 
                    u2.name as reviewer_name,
                    pa.step_name, pa.planned_start_date, pa.planned_end_date,
                    p.title as project_title
             FROM timeline_adjustment_requests tar
             LEFT JOIN users u1 ON tar.requested_by = u1.id
             LEFT JOIN users u2 ON tar.reviewed_by = u2.id
             LEFT JOIN project_activities pa ON tar.activity_id = pa.id
             LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
             LEFT JOIN projects p ON bc.project_id = p.id
             WHERE tar.id = ?",
            [$id]
        );
    }

    public function getByActivity($activityId) {
        return $this->db->fetchAll(
            "SELECT tar.*, u1.name as requester_name, u2.name as reviewer_name
             FROM timeline_adjustment_requests tar
             LEFT JOIN users u1 ON tar.requested_by = u1.id
             LEFT JOIN users u2 ON tar.reviewed_by = u2.id
             WHERE tar.activity_id = ?
             ORDER BY tar.created_at DESC",
            [$activityId]
        );
    }

    public function getPending() {
        return $this->db->fetchAll(
            "SELECT tar.*, 
                    u1.name as requester_name,
                    pa.step_name,
                    p.title as project_title
             FROM timeline_adjustment_requests tar
             LEFT JOIN users u1 ON tar.requested_by = u1.id
             LEFT JOIN project_activities pa ON tar.activity_id = pa.id
             LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
             LEFT JOIN projects p ON bc.project_id = p.id
             WHERE tar.status = 'PENDING'
             ORDER BY tar.created_at ASC"
        );
    }

    public function create($data) {
        $this->db->query(
            "INSERT INTO timeline_adjustment_requests 
             (activity_id, requested_by, reason, new_start_date, new_end_date) 
             VALUES (?, ?, ?, ?, ?)",
            [
                $data['activity_id'],
                $data['requested_by'],
                $data['reason'],
                $data['new_start_date'],
                $data['new_end_date']
            ]
        );
        return $this->db->lastInsertId();
    }

    public function approve($id, $reviewerId, $notes = null) {
        $request = $this->findById($id);
        if (!$request) return false;

        $this->db->beginTransaction();

        try {
            // Update request status
            $this->db->query(
                "UPDATE timeline_adjustment_requests 
                 SET status = 'APPROVED', reviewed_by = ?, review_notes = ?, reviewed_at = NOW() 
                 WHERE id = ?",
                [$reviewerId, $notes, $id]
            );

            // Update activity dates
            require_once __DIR__ . '/ProjectActivity.php';
            require_once __DIR__ . '/ActivityHistoryLog.php';
            
            $activityModel = new ProjectActivity();
            $logModel = new ActivityHistoryLog();
            
            $activity = $activityModel->findById($request['activity_id']);
            
            // Log the date change
            $logModel->logDateChange(
                $request['activity_id'],
                $activity['planned_start_date'],
                $activity['planned_end_date'],
                $request['new_start_date'],
                $request['new_end_date'],
                $reviewerId
            );

            // Update dates
            $activityModel->updateDates(
                $request['activity_id'],
                $request['new_start_date'],
                $request['new_end_date']
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function reject($id, $reviewerId, $notes = null) {
        return $this->db->query(
            "UPDATE timeline_adjustment_requests 
             SET status = 'REJECTED', reviewed_by = ?, review_notes = ?, reviewed_at = NOW() 
             WHERE id = ?",
            [$reviewerId, $notes, $id]
        );
    }

    public function hasPendingRequest($activityId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM timeline_adjustment_requests 
             WHERE activity_id = ? AND status = 'PENDING'",
            [$activityId]
        );
        return $result['count'] > 0;
    }
}
