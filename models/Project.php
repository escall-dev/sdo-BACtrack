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
            "SELECT p.*, u.name as creator_name,
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
             WHERE u.role = 'PROJECT_OWNER'
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

        if (!empty($filters['approval_status'])) {
            $sql .= " AND p.approval_status = ?";
            $params[] = $filters['approval_status'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function create($data) {
        $approvalStatus = $data['approval_status'] ?? 'APPROVED';
        $startDate = !empty($data['project_start_date']) ? $data['project_start_date'] : null;
        $this->db->query(
            "INSERT INTO projects (title, description, procurement_type, project_start_date, created_by, approval_status) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['title'],
                $data['description'] ?? '',
                $data['procurement_type'] ?? 'PUBLIC_BIDDING',
                $startDate,
                $data['created_by'],
                $approvalStatus
            ]
        );
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
