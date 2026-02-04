<?php
/**
 * Activity Document Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

class ActivityDocument {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT ad.*, u.name as uploader_name
             FROM activity_documents ad
             LEFT JOIN users u ON ad.uploaded_by = u.id
             WHERE ad.id = ?",
            [$id]
        );
    }

    public function getByActivity($activityId) {
        return $this->db->fetchAll(
            "SELECT ad.*, u.name as uploader_name
             FROM activity_documents ad
             LEFT JOIN users u ON ad.uploaded_by = u.id
             WHERE ad.activity_id = ?
             ORDER BY ad.uploaded_at DESC",
            [$activityId]
        );
    }

    public function create($data) {
        $this->db->query(
            "INSERT INTO activity_documents (activity_id, file_path, original_name, file_size, uploaded_by) 
             VALUES (?, ?, ?, ?, ?)",
            [
                $data['activity_id'],
                $data['file_path'],
                $data['original_name'],
                $data['file_size'] ?? 0,
                $data['uploaded_by']
            ]
        );
        return $this->db->lastInsertId();
    }

    public function delete($id) {
        $doc = $this->findById($id);
        if ($doc) {
            // Delete file from disk
            $fullPath = UPLOAD_DIR . $doc['file_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        return $this->db->query("DELETE FROM activity_documents WHERE id = ?", [$id]);
    }

    public function upload($file, $activityId, $uploadedBy) {
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File size exceeds maximum allowed size');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            throw new Exception('File type not allowed');
        }

        // Create upload directory if not exists
        $uploadDir = UPLOAD_DIR . 'activities/' . $activityId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filePath = 'activities/' . $activityId . '/' . $filename;
        $fullPath = UPLOAD_DIR . $filePath;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception('Failed to save uploaded file');
        }

        // Save to database
        return $this->create([
            'activity_id' => $activityId,
            'file_path' => $filePath,
            'original_name' => $file['name'],
            'file_size' => $file['size'],
            'uploaded_by' => $uploadedBy
        ]);
    }

    public function getCountByActivity($activityId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM activity_documents WHERE activity_id = ?",
            [$activityId]
        );
        return $result['count'];
    }
}
