<?php
/**
 * Project Activity Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';

class ProjectActivity {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT pa.*, bc.project_id, bc.cycle_number, p.title as project_title
             FROM project_activities pa
             LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
             LEFT JOIN projects p ON bc.project_id = p.id
             WHERE pa.id = ?",
            [$id]
        );
    }

    public function getByCycle($cycleId) {
        return $this->db->fetchAll(
            "SELECT * FROM project_activities 
             WHERE bac_cycle_id = ? 
             ORDER BY step_order ASC",
            [$cycleId]
        );
    }

    public function getByProject($projectId) {
        return $this->db->fetchAll(
            "SELECT pa.*, bc.cycle_number 
             FROM project_activities pa
             LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
             WHERE bc.project_id = ?
             ORDER BY bc.cycle_number, pa.step_order ASC",
            [$projectId]
        );
    }

    public function create($data) {
        $this->db->query(
            "INSERT INTO project_activities 
             (bac_cycle_id, template_id, step_name, step_order, planned_start_date, planned_end_date, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $data['bac_cycle_id'],
                $data['template_id'] ?? null,
                $data['step_name'],
                $data['step_order'],
                $data['planned_start_date'],
                $data['planned_end_date'],
                $data['status'] ?? 'PENDING'
            ]
        );
        return $this->db->lastInsertId();
    }

    public function updateStatus($id, $status, $actualCompletionDate = null) {
        $sql = "UPDATE project_activities SET status = ?";
        $params = [$status];

        if ($actualCompletionDate !== null) {
            $sql .= ", actual_completion_date = ?";
            $params[] = $actualCompletionDate;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        return $this->db->query($sql, $params);
    }

    public function updateDates($id, $startDate, $endDate) {
        return $this->db->query(
            "UPDATE project_activities SET planned_start_date = ?, planned_end_date = ? WHERE id = ?",
            [$startDate, $endDate, $id]
        );
    }

    public function setCompliance($id, $status, $remarks = null) {
        return $this->db->query(
            "UPDATE project_activities SET compliance_status = ?, compliance_remarks = ? WHERE id = ?",
            [$status, $remarks, $id]
        );
    }

    public function markComplete($id, $completionDate = null) {
        $activity = $this->findById($id);
        if (!$activity) return false;

        $completionDate = $completionDate ?? date('Y-m-d');
        $plannedEnd = $activity['planned_end_date'];
        
        // Determine if delayed
        $status = ($completionDate > $plannedEnd) ? 'DELAYED' : 'COMPLETED';

        return $this->updateStatus($id, $status, $completionDate);
    }

    public function checkAndUpdateDelayed() {
        // Auto-mark activities as DELAYED if past due and not completed
        $today = date('Y-m-d');
        return $this->db->query(
            "UPDATE project_activities 
             SET status = 'DELAYED' 
             WHERE status IN ('PENDING', 'IN_PROGRESS') 
             AND planned_end_date < ? 
             AND actual_completion_date IS NULL",
            [$today]
        );
    }

    public function getUpcomingDeadlines($days = 2) {
        $today = date('Y-m-d');
        $futureDate = date('Y-m-d', strtotime("+{$days} days"));
        
        return $this->db->fetchAll(
            "SELECT pa.*, bc.project_id, p.title as project_title
             FROM project_activities pa
             LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
             LEFT JOIN projects p ON bc.project_id = p.id
             WHERE pa.status IN ('PENDING', 'IN_PROGRESS')
             AND pa.planned_end_date BETWEEN ? AND ?
             ORDER BY pa.planned_end_date ASC",
            [$today, $futureDate]
        );
    }

    public function getDelayedActivities() {
        return $this->db->fetchAll(
            "SELECT pa.*, bc.project_id, p.title as project_title
             FROM project_activities pa
             LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
             LEFT JOIN projects p ON bc.project_id = p.id
             WHERE pa.status = 'DELAYED'
             ORDER BY pa.planned_end_date ASC"
        );
    }

    public function getCalendarEvents($startDate, $endDate, $projectId = null) {
        $sql = "SELECT pa.*, bc.project_id, bc.cycle_number, p.title as project_title
                FROM project_activities pa
                LEFT JOIN bac_cycles bc ON pa.bac_cycle_id = bc.id
                LEFT JOIN projects p ON bc.project_id = p.id
                WHERE pa.planned_start_date <= ? AND pa.planned_end_date >= ?";
        $params = [$endDate, $startDate];

        if ($projectId) {
            $sql .= " AND bc.project_id = ?";
            $params[] = $projectId;
        }

        $sql .= " ORDER BY pa.planned_start_date, pa.step_order";

        return $this->db->fetchAll($sql, $params);
    }

    public function generateFromTemplate($cycleId, $procurementType, $startDate) {
        require_once __DIR__ . '/TimelineTemplate.php';
        $templateModel = new TimelineTemplate();
        $templates = $templateModel->getByProcurementType($procurementType);

        $currentDate = new DateTime($startDate);
        $activities = [];

        foreach ($templates as $template) {
            $plannedStart = $currentDate->format('Y-m-d');
            $duration = $template['default_duration_days'] - 1;
            $currentDate->modify("+{$duration} days");
            $plannedEnd = $currentDate->format('Y-m-d');

            $activityId = $this->create([
                'bac_cycle_id' => $cycleId,
                'template_id' => $template['id'],
                'step_name' => $template['step_name'],
                'step_order' => $template['step_order'],
                'planned_start_date' => $plannedStart,
                'planned_end_date' => $plannedEnd,
                'status' => 'PENDING'
            ]);

            $activities[] = $activityId;

            // Next step starts the day after
            $currentDate->modify('+1 day');
        }

        return $activities;
    }

    public function getStatistics() {
        $stats = [];

        $stats['total'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM project_activities"
        )['count'];

        $stats['by_status'] = [];
        $statusCounts = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM project_activities GROUP BY status"
        );
        foreach ($statusCounts as $row) {
            $stats['by_status'][$row['status']] = $row['count'];
        }

        $stats['compliance'] = [];
        $complianceCounts = $this->db->fetchAll(
            "SELECT compliance_status, COUNT(*) as count 
             FROM project_activities 
             WHERE compliance_status IS NOT NULL 
             GROUP BY compliance_status"
        );
        foreach ($complianceCounts as $row) {
            $stats['compliance'][$row['compliance_status']] = $row['count'];
        }

        return $stats;
    }
}
