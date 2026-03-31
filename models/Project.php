<?php
/**
 * Project Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';

class Project {
    private $db;
    private $hasProjectOwnerNameColumn = null;
    private $hasBactrackIdColumn = null;

    public function __construct() {
        $this->db = db();
    }

    private function projectsTableHasProjectOwnerNameColumn() {
        if ($this->hasProjectOwnerNameColumn !== null) {
            return $this->hasProjectOwnerNameColumn;
        }

        try {
            $rows = $this->db->fetchAll("SHOW COLUMNS FROM projects LIKE 'project_owner_name'");
            $this->hasProjectOwnerNameColumn = !empty($rows);
        } catch (Exception $e) {
            $this->hasProjectOwnerNameColumn = false;
        }

        return $this->hasProjectOwnerNameColumn;
    }

    private function projectsTableHasBactrackIdColumn() {
        if ($this->hasBactrackIdColumn !== null) {
            return $this->hasBactrackIdColumn;
        }

        try {
            $rows = $this->db->fetchAll("SHOW COLUMNS FROM projects LIKE 'bactrack_id'");
            $this->hasBactrackIdColumn = !empty($rows);
        } catch (Exception $e) {
            $this->hasBactrackIdColumn = false;
        }

        return $this->hasBactrackIdColumn;
    }

    private function ensureBactrackIdColumn() {
        if ($this->projectsTableHasBactrackIdColumn()) {
            return;
        }

        try {
            $this->db->query("ALTER TABLE projects ADD COLUMN bactrack_id VARCHAR(32) NULL AFTER title");
            try {
                $this->db->query("CREATE UNIQUE INDEX uq_projects_bactrack_id ON projects (bactrack_id)");
            } catch (Exception $e) {
                // Ignore when index already exists.
            }
            $this->hasBactrackIdColumn = true;
        } catch (Exception $e) {
            $message = strtolower($e->getMessage());
            if (strpos($message, 'duplicate column') !== false || strpos($message, 'already exists') !== false) {
                $this->hasBactrackIdColumn = true;
                return;
            }
            throw $e;
        }
    }

    private function generateBactrackId() {
        $monthKey = date('Ym');
        $lockName = 'projects_bactrack_id_' . $monthKey;
        $lockAcquired = false;

        try {
            $lockRow = $this->db->fetch("SELECT GET_LOCK(?, 10) AS lck", [$lockName]);
            $lockAcquired = isset($lockRow['lck']) && (int)$lockRow['lck'] === 1;

            if (!$lockAcquired) {
                throw new RuntimeException('Unable to lock BACTrack ID generator. Please retry.');
            }

            $seriesRow = $this->db->fetch(
                "SELECT COALESCE(MAX(CAST(RIGHT(bactrack_id, 3) AS UNSIGNED)), 0) AS max_series
                 FROM projects
                 WHERE bactrack_id LIKE ?",
                ['BT___-' . $monthKey . '-%']
            );

            $nextSeries = ((int)($seriesRow['max_series'] ?? 0)) + 1;
            if ($nextSeries > 999) {
                throw new RuntimeException('Monthly BACTrack ID series limit reached (999).');
            }

            $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $alphabetLength = strlen($alphabet);
            $seriesPart = str_pad((string)$nextSeries, 3, '0', STR_PAD_LEFT);

            for ($attempt = 0; $attempt < 15; $attempt++) {
                $randPart = '';
                for ($i = 0; $i < 3; $i++) {
                    $idx = random_int(0, $alphabetLength - 1);
                    $randPart .= $alphabet[$idx];
                }

                $candidate = 'BT' . $randPart . '-' . $monthKey . '-' . $seriesPart;
                $exists = $this->db->fetch("SELECT id FROM projects WHERE bactrack_id = ? LIMIT 1", [$candidate]);
                if (!$exists) {
                    return $candidate;
                }
            }

            throw new RuntimeException('Unable to generate a unique BACTrack ID. Please retry.');
        } finally {
            if ($lockAcquired) {
                $this->db->fetch("SELECT RELEASE_LOCK(?)", [$lockName]);
            }
        }
    }

    public function findById($id) {
        $ownerNameSql = $this->projectsTableHasProjectOwnerNameColumn()
            ? "COALESCE(NULLIF(p.project_owner_name, ''), u.name)"
            : "u.name";

        return $this->db->fetch(
            "SELECT p.*, {$ownerNameSql} as creator_name, u.avatar_url as creator_avatar,
                    rej.name as rejected_by_name
             FROM projects p 
             LEFT JOIN users u ON p.created_by = u.id 
             LEFT JOIN users rej ON p.rejected_by = rej.id 
             WHERE p.id = ?",
            [$id]
        );
    }

    /**
     * Get distinct project owners (bidders) who have created projects, for filter dropdowns.
     * @return array [['id' => ..., 'name' => ...], ...]
     */
    public function getProjectOwners() {
        return $this->db->fetchAll(
            "SELECT DISTINCT u.id, u.name 
             FROM users u 
             INNER JOIN projects p ON p.created_by = u.id 
             WHERE u.role = 'ADMIN' OR u.role = 'SUPERADMIN'
             ORDER BY u.name ASC"
        );
    }

    /**
     * Approve a project (BAC only). Sets approval_status to APPROVED.
     * @param int $id Project ID
     * @return bool
     */
    public function approve($id) {
        return $this->db->query(
            "UPDATE projects SET approval_status = 'APPROVED', rejection_remarks = NULL, rejected_by = NULL, rejected_at = NULL WHERE id = ? AND approval_status = 'PENDING_APPROVAL'",
            [$id]
        );
    }

    /**
     * Reject a project (BAC only). Requires remarks. Sets approval_status to REJECTED.
     * @param int $id Project ID
     * @param string $remarks Required reason for rejection
     * @param int $rejectedBy User ID of BAC member who rejected
     * @return bool
     */
    public function reject($id, $remarks, $rejectedBy) {
        $remarks = trim($remarks);
        if (empty($remarks)) return false;
        return $this->db->query(
            "UPDATE projects SET approval_status = 'REJECTED', rejection_remarks = ?, rejected_by = ?, rejected_at = NOW() WHERE id = ? AND approval_status = 'PENDING_APPROVAL'",
            [$remarks, $rejectedBy, $id]
        );
    }

    public function getAll($filters = []) {
        $ownerNameSql = $this->projectsTableHasProjectOwnerNameColumn()
            ? "COALESCE(NULLIF(p.project_owner_name, ''), u.name)"
            : "u.name";

        $sql = "SELECT p.*, {$ownerNameSql} as creator_name, u.avatar_url as creator_avatar,
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

        if (!empty($filters['approval_status'])) {
            $sql .= " AND p.approval_status = ?";
            $params[] = $filters['approval_status'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        $projects = $this->db->fetchAll($sql, $params);

        foreach ($projects as &$project) {
            $project['timeline_status'] = $this->getCurrentTimelineStatus(
                (int) $project['id'],
                $project['approval_status'] ?? 'APPROVED'
            );
        }
        unset($project);

        return $projects;
    }

    private function getCurrentTimelineStatus($projectId, $fallbackStatus = 'APPROVED') {
        $cycle = $this->db->fetch(
            "SELECT id FROM bac_cycles WHERE project_id = ? ORDER BY id DESC LIMIT 1",
            [$projectId]
        );

        if (!$cycle) {
            return $fallbackStatus;
        }

        $currentStep = $this->db->fetch(
            "SELECT step_order, step_name, status
             FROM project_activities
             WHERE bac_cycle_id = ?
               AND status IN ('PENDING', 'IN_PROGRESS', 'DELAYED')
             ORDER BY step_order ASC
             LIMIT 1",
            [$cycle['id']]
        );

        if ($currentStep) {
            return 'Step ' . $currentStep['step_order'] . ': ' . $currentStep['step_name'] . ' (' . $currentStep['status'] . ')';
        }

        $counts = $this->db->fetch(
            "SELECT COUNT(*) AS total_steps,
                    SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) AS completed_steps
             FROM project_activities
             WHERE bac_cycle_id = ?",
            [$cycle['id']]
        );

        $totalSteps = (int) ($counts['total_steps'] ?? 0);
        $completedSteps = (int) ($counts['completed_steps'] ?? 0);

        if ($totalSteps > 0 && $totalSteps === $completedSteps) {
            return 'COMPLETED';
        }

        return $fallbackStatus;
    }

    public function create($data) {
        $approvalStatus = $data['approval_status'] ?? 'APPROVED';
        $startDate = !empty($data['project_start_date']) ? $data['project_start_date'] : null;
        $this->ensureBactrackIdColumn();
        $bactrackId = $this->generateBactrackId();

        if ($this->projectsTableHasProjectOwnerNameColumn()) {
            $this->db->query(
                "INSERT INTO projects (title, bactrack_id, description, procurement_type, project_start_date, project_owner_name, created_by, approval_status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['title'],
                    $bactrackId,
                    $data['description'] ?? '',
                    $data['procurement_type'] ?? 'PUBLIC_BIDDING',
                    $startDate,
                    trim((string)($data['project_owner_name'] ?? '')),
                    $data['created_by'],
                    $approvalStatus
                ]
            );
        } else {
            $this->db->query(
                "INSERT INTO projects (title, bactrack_id, description, procurement_type, project_start_date, created_by, approval_status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['title'],
                    $bactrackId,
                    $data['description'] ?? '',
                    $data['procurement_type'] ?? 'PUBLIC_BIDDING',
                    $startDate,
                    $data['created_by'],
                    $approvalStatus
                ]
            );
        }
        return $this->db->lastInsertId();
    }

    /**
     * Submit a DRAFT project for BAC review. Creates cycle and activities, sets PENDING_APPROVAL.
     * @param int $id Project ID
     * @param string $startDate Project start date (Y-m-d) for timeline generation
     * @return bool
     */
    public function submitForReview($id, $startDate) {
        $project = $this->findById($id);
        if (!$project || ($project['approval_status'] ?? '') !== 'DRAFT') {
            return false;
        }
        $procurementType = $project['procurement_type'] ?? 'PUBLIC_BIDDING';
        $this->db->query(
            "UPDATE projects SET approval_status = 'PENDING_APPROVAL', project_start_date = ? WHERE id = ?",
            [$startDate, $id]
        );
        require_once __DIR__ . '/BacCycle.php';
        require_once __DIR__ . '/ProjectActivity.php';
        $cycleModel = new BacCycle();
        $cycleId = $cycleModel->create($id, 1);
        $activityModel = new ProjectActivity();
        $activityModel->generateFromTemplate($cycleId, $procurementType, $startDate);
        return true;
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
        if (array_key_exists('project_start_date', $data)) {
            $fields[] = 'project_start_date = ?';
            $params[] = $data['project_start_date'] ?: null;
        }
        if (array_key_exists('project_owner_name', $data) && $this->projectsTableHasProjectOwnerNameColumn()) {
            $fields[] = 'project_owner_name = ?';
            $params[] = trim((string)$data['project_owner_name']);
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

        try {
            $stats['pending_approval'] = $this->db->fetch(
                "SELECT COUNT(*) as count FROM projects WHERE approval_status = 'PENDING_APPROVAL'"
            )['count'];
        } catch (Exception $e) {
            $stats['pending_approval'] = 0;
        }

        try {
            $stats['rejected'] = $this->db->fetch(
                "SELECT COUNT(*) as count FROM projects WHERE approval_status = 'REJECTED'"
            )['count'];
        } catch (Exception $e) {
            $stats['rejected'] = 0;
        }

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
