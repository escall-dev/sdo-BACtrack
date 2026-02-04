<?php
/**
 * Notification Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';

class Notification {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT * FROM notifications WHERE id = ?",
            [$id]
        );
    }

    public function getByUser($userId, $limit = 20, $unreadOnly = false) {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$userId];

        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        return $this->db->fetchAll($sql, $params);
    }

    public function getUnreadCount($userId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        return $result['count'];
    }

    public function create($data) {
        $this->db->query(
            "INSERT INTO notifications (user_id, title, message, type, reference_type, reference_id) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'],
                $data['title'],
                $data['message'],
                $data['type'],
                $data['reference_type'] ?? null,
                $data['reference_id'] ?? null
            ]
        );
        return $this->db->lastInsertId();
    }

    public function markAsRead($id, $userId = null) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $params = [$id];
        
        if ($userId !== null) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        return $this->db->query($sql, $params);
    }

    public function markAllAsRead($userId) {
        return $this->db->query(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
    }

    public function delete($id) {
        return $this->db->query("DELETE FROM notifications WHERE id = ?", [$id]);
    }

    public function notifyDeadlineWarning($activityId, $activityName, $projectTitle, $daysRemaining) {
        // Notify all users about upcoming deadline
        require_once __DIR__ . '/User.php';
        $userModel = new User();
        $users = $userModel->getAll();

        foreach ($users as $user) {
            $this->create([
                'user_id' => $user['id'],
                'title' => 'Deadline Approaching',
                'message' => "Activity '{$activityName}' in project '{$projectTitle}' is due in {$daysRemaining} day(s).",
                'type' => 'DEADLINE_WARNING',
                'reference_type' => 'activity',
                'reference_id' => $activityId
            ]);
        }
    }

    public function notifyActivityDelayed($activityId, $activityName, $projectTitle) {
        require_once __DIR__ . '/User.php';
        $userModel = new User();
        $users = $userModel->getAll();

        foreach ($users as $user) {
            $this->create([
                'user_id' => $user['id'],
                'title' => 'Activity Delayed',
                'message' => "Activity '{$activityName}' in project '{$projectTitle}' is now marked as DELAYED.",
                'type' => 'ACTIVITY_DELAYED',
                'reference_type' => 'activity',
                'reference_id' => $activityId
            ]);
        }
    }

    public function notifyDocumentUploaded($activityId, $activityName, $projectTitle, $uploaderName) {
        require_once __DIR__ . '/User.php';
        $userModel = new User();
        $users = $userModel->getAll();

        foreach ($users as $user) {
            $this->create([
                'user_id' => $user['id'],
                'title' => 'New Document Uploaded',
                'message' => "{$uploaderName} uploaded a document for '{$activityName}' in project '{$projectTitle}'.",
                'type' => 'DOCUMENT_UPLOADED',
                'reference_type' => 'activity',
                'reference_id' => $activityId
            ]);
        }
    }

    public function notifyAdjustmentRequest($requestId, $activityName, $projectTitle, $requesterName) {
        require_once __DIR__ . '/User.php';
        $userModel = new User();
        $users = $userModel->getAll();

        foreach ($users as $user) {
            if ($user['role'] === 'PROCUREMENT') {
                $this->create([
                    'user_id' => $user['id'],
                    'title' => 'Timeline Adjustment Request',
                    'message' => "{$requesterName} requested a timeline adjustment for '{$activityName}' in project '{$projectTitle}'.",
                    'type' => 'ADJUSTMENT_REQUEST',
                    'reference_type' => 'adjustment_request',
                    'reference_id' => $requestId
                ]);
            }
        }
    }

    public function notifyAdjustmentResponse($requestId, $activityName, $projectTitle, $status, $requesterId) {
        $statusText = $status === 'APPROVED' ? 'approved' : 'rejected';
        $this->create([
            'user_id' => $requesterId,
            'title' => 'Adjustment Request ' . ucfirst($statusText),
            'message' => "Your timeline adjustment request for '{$activityName}' in project '{$projectTitle}' has been {$statusText}.",
            'type' => 'ADJUSTMENT_RESPONSE',
            'reference_type' => 'adjustment_request',
            'reference_id' => $requestId
        ]);
    }
}
