<?php
/**
 * Announcement Model
 * SDO-BACtrack
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

class Announcement {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function findById($id) {
        return $this->db->fetch(
            "SELECT a.*, u.name AS creator_name
             FROM announcements a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.id = ?",
            [(int)$id]
        );
    }

    public function getActive($limit = 8) {
        $limit = max(1, (int)$limit);
        return $this->db->fetchAll(
            "SELECT a.*, u.name AS creator_name
             FROM announcements a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.is_active = 1
               AND (a.starts_at IS NULL OR a.starts_at = '' OR a.starts_at <= NOW())
               AND (a.ends_at   IS NULL OR a.ends_at   = '' OR a.ends_at   >= NOW())
             ORDER BY COALESCE(a.starts_at, a.created_at) DESC, a.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function listAll() {
        return $this->db->fetchAll(
            "SELECT a.*, u.name AS creator_name
             FROM announcements a
             LEFT JOIN users u ON u.id = a.created_by
             ORDER BY a.id DESC"
        );
    }

    public function create($data, $createdBy, $imageFile = null) {
        $title = trim((string)($data['title'] ?? ''));
        $body = trim((string)($data['body'] ?? ''));
        $linkUrl = trim((string)($data['link_url'] ?? ''));
        $isActive = !empty($data['is_active']) ? 1 : 0;
        $startsAt = $this->normalizeDateTime($data['starts_at'] ?? null);
        $endsAt = $this->normalizeDateTime($data['ends_at'] ?? null);
        $imageUrl = null;
        $uploadedImageUrl = null;

        if ($this->hasUploadedFile($imageFile)) {
            $uploadedImageUrl = $this->uploadAnnouncementImage($imageFile);
            $imageUrl = $uploadedImageUrl;
        } elseif (!empty($data['image_url'])) {
            $imageUrl = $this->normalizeImageInput($data['image_url']);
        }

        try {
            $this->db->query(
                "INSERT INTO announcements (title, body, link_url, image_url, is_active, starts_at, ends_at, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$title, $body, ($linkUrl !== '' ? $linkUrl : null), $imageUrl, $isActive, $startsAt, $endsAt, (int)$createdBy]
            );
        } catch (Throwable $e) {
            if ($uploadedImageUrl !== null) {
                $this->deleteAnnouncementImageFile($uploadedImageUrl);
            }
            throw $e;
        }

        return (int)$this->db->lastInsertId();
    }

    public function update($id, $data, $imageFile = null, $removeImage = false) {
        $existing = $this->findById($id);
        if (!$existing) {
            return false;
        }

        $title = trim((string)($data['title'] ?? ''));
        $body = trim((string)($data['body'] ?? ''));
        $linkUrl = trim((string)($data['link_url'] ?? ''));
        $isActive = !empty($data['is_active']) ? 1 : 0;
        $startsAt = $this->normalizeDateTime($data['starts_at'] ?? null);
        $endsAt = $this->normalizeDateTime($data['ends_at'] ?? null);
        $currentImageUrl = $this->normalizeImageInput($existing['image_url'] ?? null);
        $newImageUrl = $currentImageUrl;
        $uploadedImageUrl = null;

        if ($this->hasUploadedFile($imageFile)) {
            $uploadedImageUrl = $this->uploadAnnouncementImage($imageFile);
            $newImageUrl = $uploadedImageUrl;
        } elseif ($removeImage) {
            $newImageUrl = null;
        } elseif (array_key_exists('image_url', $data)) {
            $newImageUrl = $this->normalizeImageInput($data['image_url']);
        }

        try {
            $this->db->query(
                "UPDATE announcements
                 SET title = ?, body = ?, link_url = ?, image_url = ?, is_active = ?, starts_at = ?, ends_at = ?
                 WHERE id = ?",
                [$title, $body, ($linkUrl !== '' ? $linkUrl : null), $newImageUrl, $isActive, $startsAt, $endsAt, (int)$id]
            );
        } catch (Throwable $e) {
            if ($uploadedImageUrl !== null) {
                $this->deleteAnnouncementImageFile($uploadedImageUrl);
            }
            throw $e;
        }

        if ($uploadedImageUrl !== null && !empty($currentImageUrl) && $currentImageUrl !== $uploadedImageUrl) {
            $this->deleteAnnouncementImageFile($currentImageUrl);
        }

        if ($removeImage && $uploadedImageUrl === null && !empty($currentImageUrl)) {
            $this->deleteAnnouncementImageFile($currentImageUrl);
        }

        return true;
    }

    public function delete($id) {
        $existing = $this->findById($id);
        $this->db->query("DELETE FROM announcements WHERE id = ?", [(int)$id]);

        if (!empty($existing['image_url'])) {
            $this->deleteAnnouncementImageFile($existing['image_url']);
        }

        return true;
    }

    private function hasUploadedFile($file) {
        if (!is_array($file)) {
            return false;
        }

        if (!isset($file['error'])) {
            return false;
        }

        return (int)$file['error'] !== UPLOAD_ERR_NO_FILE;
    }

    private function uploadAnnouncementImage($file) {
        if (!is_array($file) || !isset($file['error'])) {
            throw new RuntimeException('Invalid image upload payload.');
        }

        $errorCode = (int)$file['error'];
        if ($errorCode !== UPLOAD_ERR_OK) {
            throw new RuntimeException($this->mapUploadError($errorCode));
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Invalid uploaded image.');
        }

        $maxSize = 10 * 1024 * 1024;
        if ((int)$file['size'] > $maxSize) {
            throw new RuntimeException('Announcement image must be under 10 MB.');
        }

        $mimeType = '';
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = (string)$finfo->file($file['tmp_name']);
        }

        $extension = strtolower((string)pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowedMimeToExt = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'image/webp' => ['webp'],
        ];

        if (!isset($allowedMimeToExt[$mimeType])) {
            throw new RuntimeException('Only JPG, PNG, GIF, and WEBP images are allowed.');
        }

        if (!in_array($extension, $allowedMimeToExt[$mimeType], true)) {
            throw new RuntimeException('Image extension does not match the uploaded file type.');
        }

        $uploadDir = rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . 'announcements' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new RuntimeException('Failed to prepare the announcement uploads directory.');
        }

        try {
            $random = bin2hex(random_bytes(6));
        } catch (Throwable $e) {
            $random = uniqid('', true);
        }

        $filename = 'announcement_' . date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9]/', '', (string)$random) . '.' . $extension;
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to upload announcement image.');
        }

        return 'uploads/announcements/' . $filename;
    }

    private function deleteAnnouncementImageFile($imageUrl) {
        $absolutePath = $this->resolveAnnouncementImagePath($imageUrl);
        if ($absolutePath === null || !is_file($absolutePath)) {
            return;
        }

        $uploadsDir = realpath(rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . 'announcements');
        $fileDir = realpath(dirname($absolutePath));

        if ($uploadsDir && $fileDir && strpos($fileDir, $uploadsDir) === 0) {
            @unlink($absolutePath);
        }
    }

    private function resolveAnnouncementImagePath($imageUrl) {
        $normalized = $this->normalizeImageInput($imageUrl);
        if ($normalized === null) {
            return null;
        }

        $path = parse_url($normalized, PHP_URL_PATH);
        if (!is_string($path) || trim($path) === '') {
            $path = $normalized;
        }

        $filename = basename($path);
        if ($filename === '' || $filename === '.' || $filename === '..') {
            return null;
        }

        return rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . 'announcements' . DIRECTORY_SEPARATOR . $filename;
    }

    private function normalizeImageInput($imageUrl) {
        if ($imageUrl === null) {
            return null;
        }

        $value = trim((string)$imageUrl);
        if ($value === '') {
            return null;
        }

        return $value;
    }

    private function mapUploadError($code) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Uploaded image exceeds the server upload_max_filesize limit.',
            UPLOAD_ERR_FORM_SIZE => 'Uploaded image exceeds the allowed form size.',
            UPLOAD_ERR_PARTIAL => 'Uploaded image was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder for file uploads.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write uploaded image to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the image upload.',
        ];

        return $messages[$code] ?? 'Failed to upload announcement image.';
    }

    private function normalizeDateTime($val) {
        if ($val === null) return null;
        $s = trim((string)$val);
        if ($s === '') return null;

        // Accept HTML datetime-local (YYYY-MM-DDTHH:MM) and date (YYYY-MM-DD)
        $s = str_replace('T', ' ', $s);

        $ts = strtotime($s);
        if ($ts === false) {
            return null;
        }
        return date('Y-m-d H:i:s', $ts);
    }
}

