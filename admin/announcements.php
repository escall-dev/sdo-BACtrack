<?php
/**
 * Announcements Management
 * SDO-BACtrack - BAC Secretary only
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/Announcement.php';

$auth = auth();
$auth->requireBacSecretary();

$announcementModel = new Announcement();

function announcementImageSrc($imageUrl) {
    $raw = trim((string)$imageUrl);
    if ($raw === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $raw) || strpos($raw, '//') === 0) {
        return $raw;
    }

    return rtrim(APP_URL, '/') . '/' . ltrim($raw, '/');
}

// Handle form submissions — must run BEFORE header.php outputs any HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'body' => trim($_POST['body'] ?? ''),
            'link_url' => trim($_POST['link_url'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'starts_at' => trim($_POST['starts_at'] ?? ''),
            'ends_at' => trim($_POST['ends_at'] ?? ''),
        ];

        if ($data['title'] === '') {
            setFlashMessage('error', 'Title is required.');
        } else {
            try {
                $announcementModel->create($data, $auth->getUserId(), $_FILES['image'] ?? null);
                setFlashMessage('success', 'Announcement created successfully.');
            } catch (Throwable $e) {
                setFlashMessage('error', $e->getMessage());
            }
        }
        $auth->redirect(APP_URL . '/admin/announcements.php');
    }

    if ($action === 'update') {
        $id = (int)($_POST['announcement_id'] ?? 0);
        $removeImage = isset($_POST['remove_image']) ? 1 : 0;
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'body' => trim($_POST['body'] ?? ''),
            'link_url' => trim($_POST['link_url'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'starts_at' => trim($_POST['starts_at'] ?? ''),
            'ends_at' => trim($_POST['ends_at'] ?? ''),
        ];

        if ($id <= 0) {
            setFlashMessage('error', 'Announcement not found.');
        } elseif ($data['title'] === '') {
            setFlashMessage('error', 'Title is required.');
        } else {
            try {
                $updated = $announcementModel->update($id, $data, $_FILES['image'] ?? null, $removeImage === 1);
                if ($updated) {
                    setFlashMessage('success', 'Announcement updated successfully.');
                } else {
                    setFlashMessage('error', 'Announcement not found.');
                }
            } catch (Throwable $e) {
                setFlashMessage('error', $e->getMessage());
            }
        }
        $auth->redirect(APP_URL . '/admin/announcements.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['announcement_id'] ?? 0);
        if ($id <= 0) {
            setFlashMessage('error', 'Announcement not found.');
        } else {
            try {
                $announcementModel->delete($id);
                setFlashMessage('success', 'Announcement deleted successfully.');
            } catch (Throwable $e) {
                setFlashMessage('error', 'Failed to delete announcement.');
            }
        }
        $auth->redirect(APP_URL . '/admin/announcements.php');
    }
}

$announcements = $announcementModel->listAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <p style="color: var(--text-muted); margin: 4px 0 0;"><?php echo count($announcements); ?> announcement(s)</p>
    </div>
    <button class="btn btn-primary" type="button" onclick="openAnnouncementModal()">
        <i class="fas fa-plus"></i> New Announcement
    </button>
</div>

<div class="data-card">
    <?php if (empty($announcements)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-bullhorn"></i></div>
            <h3>No announcements found</h3>
            <p>Create an announcement so it appears on the landing page carousel.</p>
            <button class="btn btn-primary" type="button" style="margin-top: 16px;" onclick="openAnnouncementModal()">
                <i class="fas fa-plus"></i> Create Announcement
            </button>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table" data-paginate="10">
                <thead>
                    <tr>
                        <th style="width: 22%;">Title</th>
                        <th>Body</th>
                        <th style="width: 14%;">Active</th>
                        <th style="width: 18%;">Schedule</th>
                        <th style="width: 16%;">Created</th>
                        <th style="width: 14%; text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($announcements as $a): ?>
                        <?php
                            $starts = !empty($a['starts_at']) ? date('M j, Y g:i A', strtotime($a['starts_at'])) : '—';
                            $ends = !empty($a['ends_at']) ? date('M j, Y g:i A', strtotime($a['ends_at'])) : '—';
                            $created = !empty($a['created_at']) ? date('M j, Y', strtotime($a['created_at'])) : '—';
                            $isActive = !empty($a['is_active']) ? 1 : 0;
                            $creator = $a['creator_name'] ?? '';
                            $linkUrl = $a['link_url'] ?? '';
                            $imageUrl = $a['image_url'] ?? '';
                        ?>
                        <tr>
                            <td style="font-weight:700;">
                                <?php echo htmlspecialchars($a['title'] ?? ''); ?>
                                <?php if (!empty($linkUrl)): ?>
                                    <div style="margin-top:4px;">
                                        <a href="<?php echo htmlspecialchars($linkUrl); ?>" target="_blank" rel="noopener noreferrer" style="font-size:0.8rem;color:var(--primary);text-decoration:none;">
                                            <i class="fas fa-link"></i> Link
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($imageUrl)): ?>
                                    <div style="margin-top:4px;">
                                        <a href="<?php echo htmlspecialchars(announcementImageSrc($imageUrl)); ?>" target="_blank" rel="noopener noreferrer" style="font-size:0.8rem;color:var(--primary);text-decoration:none;">
                                            <i class="fas fa-image"></i> Image
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--text-secondary);">
                                <?php
                                    $body = (string)($a['body'] ?? '');
                                    $preview = mb_strlen($body) > 140 ? (mb_substr($body, 0, 140) . '…') : $body;
                                    echo nl2br(htmlspecialchars($preview));
                                ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $isActive ? 'approved' : 'rejected'; ?>">
                                    <?php echo $isActive ? 'ACTIVE' : 'INACTIVE'; ?>
                                </span>
                            </td>
                            <td style="font-size:0.85rem;color:var(--text-secondary);">
                                <div><strong>Start:</strong> <?php echo htmlspecialchars($starts); ?></div>
                                <div><strong>End:</strong> <?php echo htmlspecialchars($ends); ?></div>
                            </td>
                            <td style="font-size:0.85rem;color:var(--text-secondary);">
                                <div><?php echo htmlspecialchars($created); ?></div>
                                <?php if (!empty($creator)): ?>
                                    <div style="color:var(--text-muted);font-size:0.8rem;"><?php echo htmlspecialchars($creator); ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <div class="action-buttons" style="justify-content:center;">
                                    <button type="button" class="btn btn-icon" title="Edit"
                                        onclick='editAnnouncement(<?php echo json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-icon btn-danger" title="Delete"
                                        onclick='openDeleteAnnouncementModal(<?php echo (int)$a["id"]; ?>, <?php echo json_encode($a["title"] ?? ""); ?>)'>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Add / Edit Announcement Modal -->
<div id="announcementModal" class="modal-overlay">
    <div class="modal-container">
        <form method="POST" id="announcementForm" enctype="multipart/form-data">
            <div class="modal-header">
                <h2 id="announcementModalTitle">New Announcement</h2>
                <button class="modal-close" type="button" onclick="closeAnnouncementModal()">&times;</button>
            </div>

            <input type="hidden" name="action" id="announcementFormAction" value="create">
            <input type="hidden" name="announcement_id" id="announcementId" value="">

            <div class="modal-body">
                <div class="form-section-title">Announcement Details</div>
                <div class="form-row">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Title <span style="color: var(--danger);">*</span></label>
                        <div class="input-group-custom">
                            <i class="fas fa-heading"></i>
                            <input type="text" name="title" id="announcementTitle" class="form-control" placeholder="Enter title" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Body</label>
                        <div class="input-group-custom" style="align-items: flex-start;">
                            <i class="fas fa-align-left" style="margin-top: 12px;"></i>
                            <textarea name="body" id="announcementBody" class="form-control" rows="6" placeholder="Write the announcement..."></textarea>
                        </div>
                        <small class="form-hint">This text will appear in the landing page carousel.</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Announcement Image (optional)</label>
                        <input type="file" name="image" id="announcementImage" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="form-hint">Allowed formats: JPG, PNG, GIF, WEBP. Maximum file size: 10 MB.</small>

                        <div id="announcementCurrentImageWrap" style="display:none; margin-top: 10px;">
                            <div style="font-size:0.8rem; font-weight:600; color:var(--text-muted); margin-bottom:6px;">Current image</div>
                            <img id="announcementCurrentImage" src="" alt="Announcement image" style="max-width:100%; width:320px; max-height:220px; object-fit:contain; background:#f8fafc; border-radius:8px; border:1px solid var(--border-color); display:block;">
                        </div>

                        <label id="announcementRemoveImageWrap" style="display:none; margin-top:10px; font-size:0.9rem; color:var(--text-secondary);">
                            <input type="checkbox" name="remove_image" id="announcementRemoveImage" value="1" style="margin-right:8px;">
                            Remove current image
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Link URL (optional)</label>
                        <div class="input-group-custom">
                            <i class="fas fa-link"></i>
                            <input type="url" name="link_url" id="announcementLinkUrl" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                </div>

                <div class="form-section-title">Visibility</div>
                <div class="form-row" style="align-items: flex-end;">
                    <div class="form-group">
                        <label class="form-label">Starts at</label>
                        <input type="datetime-local" name="starts_at" id="announcementStartsAt" class="form-control">
                        <small class="form-hint">Leave empty to publish immediately.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ends at</label>
                        <input type="datetime-local" name="ends_at" id="announcementEndsAt" class="form-control">
                        <small class="form-hint">Leave empty for no expiration.</small>
                    </div>
                    <div class="form-group">
                        <label class="toggle-wrapper" for="announcementIsActive">
                            <span class="switch">
                                <input type="checkbox" name="is_active" id="announcementIsActive" value="1" checked>
                                <span class="slider"></span>
                            </span>
                            <span class="toggle-label-text">Announcement is active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAnnouncementModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="announcementSubmitBtn">
                    <i class="fas fa-save"></i> <span id="announcementSubmitLabel">Create Announcement</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteAnnouncementModal" class="modal-overlay">
    <div class="modal-container modal-confirm">
        <form method="POST" id="deleteAnnouncementForm">
            <div class="modal-header">
                <h2><i class="fas fa-trash" style="margin-right: 8px;"></i>Delete Announcement</h2>
                <button class="modal-close" type="button" onclick="closeDeleteAnnouncementModal()">&times;</button>
            </div>

            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="announcement_id" id="deleteAnnouncementId" value="">

            <div class="modal-body">
                <p>Are you sure you want to delete this announcement?</p>
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px; margin: 12px 0;">
                    <div style="font-weight: 700; color: var(--text-primary);" id="deleteAnnouncementTitle">-</div>
                </div>
                <p class="form-hint" style="color: var(--danger);">This action cannot be undone.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteAnnouncementModal()">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
const ANNOUNCEMENT_APP_URL = <?php echo json_encode(rtrim(APP_URL, '/')); ?>;

function normalizeAnnouncementImageUrl(path) {
    const raw = String(path || '').trim();
    if (!raw) return '';
    if (/^https?:\/\//i.test(raw) || raw.startsWith('//')) return raw;
    return ANNOUNCEMENT_APP_URL + '/' + raw.replace(/^\/+/, '');
}

function resetAnnouncementImageFields() {
    const imageInput = document.getElementById('announcementImage');
    const imageWrap = document.getElementById('announcementCurrentImageWrap');
    const imageEl = document.getElementById('announcementCurrentImage');
    const removeWrap = document.getElementById('announcementRemoveImageWrap');
    const removeBox = document.getElementById('announcementRemoveImage');

    imageInput.value = '';
    imageEl.src = '';
    imageWrap.style.display = 'none';
    removeWrap.style.display = 'none';
    removeBox.checked = false;
}

function setAnnouncementCurrentImage(imagePath) {
    const imageWrap = document.getElementById('announcementCurrentImageWrap');
    const imageEl = document.getElementById('announcementCurrentImage');
    const removeWrap = document.getElementById('announcementRemoveImageWrap');
    const removeBox = document.getElementById('announcementRemoveImage');
    const imageSrc = normalizeAnnouncementImageUrl(imagePath);

    if (!imageSrc) {
        imageEl.src = '';
        imageWrap.style.display = 'none';
        removeWrap.style.display = 'none';
        removeBox.checked = false;
        return;
    }

    imageEl.src = imageSrc;
    imageWrap.style.display = 'block';
    removeWrap.style.display = 'inline-flex';
    removeBox.checked = false;
}

function openAnnouncementModal() {
    document.getElementById('announcementModalTitle').textContent = 'New Announcement';
    document.getElementById('announcementFormAction').value = 'create';
    document.getElementById('announcementId').value = '';
    document.getElementById('announcementForm').reset();
    resetAnnouncementImageFields();
    document.getElementById('announcementIsActive').checked = true;
    document.getElementById('announcementSubmitLabel').textContent = 'Create Announcement';
    document.getElementById('announcementModal').classList.add('show');
}

function toDateTimeLocalValue(sqlDateTime) {
    if (!sqlDateTime) return '';
    // Expected: YYYY-MM-DD HH:MM:SS
    const s = String(sqlDateTime).trim();
    if (!s) return '';
    const parts = s.split(' ');
    if (parts.length < 2) return '';
    return parts[0] + 'T' + parts[1].slice(0, 5);
}

function editAnnouncement(a) {
    document.getElementById('announcementModalTitle').textContent = 'Edit Announcement';
    document.getElementById('announcementFormAction').value = 'update';
    document.getElementById('announcementId').value = a.id || '';
    document.getElementById('announcementTitle').value = a.title || '';
    document.getElementById('announcementBody').value = a.body || '';
    document.getElementById('announcementLinkUrl').value = a.link_url || '';
    document.getElementById('announcementIsActive').checked = String(a.is_active || '0') === '1';
    document.getElementById('announcementStartsAt').value = toDateTimeLocalValue(a.starts_at);
    document.getElementById('announcementEndsAt').value = toDateTimeLocalValue(a.ends_at);
    resetAnnouncementImageFields();
    setAnnouncementCurrentImage(a.image_url || '');
    document.getElementById('announcementSubmitLabel').textContent = 'Update Announcement';
    document.getElementById('announcementModal').classList.add('show');
}

function closeAnnouncementModal() {
    document.getElementById('announcementModal').classList.remove('show');
}

function openDeleteAnnouncementModal(id, title) {
    document.getElementById('deleteAnnouncementId').value = id;
    document.getElementById('deleteAnnouncementTitle').textContent = title || '-';
    document.getElementById('deleteAnnouncementModal').classList.add('show');
}

function closeDeleteAnnouncementModal() {
    document.getElementById('deleteAnnouncementModal').classList.remove('show');
}

document.getElementById('announcementImage').addEventListener('change', function () {
    if (this.files && this.files.length > 0) {
        document.getElementById('announcementRemoveImage').checked = false;
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

