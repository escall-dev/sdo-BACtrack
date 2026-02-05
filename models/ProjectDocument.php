<?php
/**
 * Project Document Model
 * SDO-BACtrack - Project owner document uploads, categorized by procurement procedure
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

class ProjectDocument {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT pd.*, u.name as uploader_name
             FROM project_documents pd
             LEFT JOIN users u ON pd.uploaded_by = u.id
             WHERE pd.id = ?",
            [$id]
        );
    }

    public function getByProject($projectId) {
        return $this->db->fetchAll(
            "SELECT pd.*, u.name as uploader_name
             FROM project_documents pd
             LEFT JOIN users u ON pd.uploaded_by = u.id
             WHERE pd.project_id = ?
             ORDER BY pd.category ASC, pd.uploaded_at DESC",
            [$projectId]
        );
    }

    /**
     * Get project documents by project and category (e.g. for displaying on activity view
     * where category matches the activity step_name).
     */
    public function getByProjectAndCategory($projectId, $category) {
        return $this->db->fetchAll(
            "SELECT pd.*, u.name as uploader_name
             FROM project_documents pd
             LEFT JOIN users u ON pd.uploaded_by = u.id
             WHERE pd.project_id = ? AND pd.category = ?
             ORDER BY pd.uploaded_at DESC",
            [$projectId, $category]
        );
    }

    /**
     * Get documents grouped by category for display
     */
    public function getByProjectGrouped($projectId) {
        $docs = $this->getByProject($projectId);
        $grouped = [];
        foreach ($docs as $doc) {
            $cat = $doc['category'];
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            $grouped[$cat][] = $doc;
        }
        return $grouped;
    }

    public function create($data) {
        $this->db->query(
            "INSERT INTO project_documents (project_id, category, file_path, original_name, file_size, description, uploaded_by) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $data['project_id'],
                $data['category'],
                $data['file_path'],
                $data['original_name'],
                $data['file_size'] ?? 0,
                $data['description'] ?? null,
                $data['uploaded_by']
            ]
        );
        return $this->db->lastInsertId();
    }

    public function delete($id) {
        $doc = $this->findById($id);
        if ($doc) {
            $fullPath = UPLOAD_DIR . $doc['file_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        return $this->db->query("DELETE FROM project_documents WHERE id = ?", [$id]);
    }

    /**
     * Get category slug from display name
     */
    public static function categoryToSlug($category) {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $category);
        $slug = trim(strtolower($slug), '-');
        return $slug ?: 'other';
    }

    public function upload($file, $projectId, $category, $uploadedBy, $description = null) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File size exceeds maximum allowed (' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB)');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = defined('PROJECT_OWNER_ALLOWED_EXTENSIONS') ? PROJECT_OWNER_ALLOWED_EXTENSIONS : ALLOWED_EXTENSIONS;
        if (!in_array($extension, $allowed)) {
            throw new Exception('File type not allowed. Allowed: ' . implode(', ', $allowed));
        }

        $categorySlug = self::categoryToSlug($category);
        $uploadDir = UPLOAD_DIR . 'projects/' . $projectId . '/' . $categorySlug . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filePath = 'projects/' . $projectId . '/' . $categorySlug . '/' . $filename;
        $fullPath = UPLOAD_DIR . $filePath;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception('Failed to save uploaded file');
        }

        return $this->create([
            'project_id' => $projectId,
            'category' => $category,
            'file_path' => $filePath,
            'original_name' => $file['name'],
            'file_size' => $file['size'],
            'description' => $description,
            'uploaded_by' => $uploadedBy
        ]);
    }

    /**
     * Get procurement procedure categories for the project (from timeline templates)
     */
    public function getCategories($procurementType = 'PUBLIC_BIDDING') {
        $rows = $this->db->fetchAll(
            "SELECT DISTINCT step_name FROM timeline_templates WHERE procurement_type = ? ORDER BY step_order ASC",
            [$procurementType]
        );
        $cats = array_column($rows, 'step_name');
        $cats[] = 'Other';
        return $cats;
    }
}
