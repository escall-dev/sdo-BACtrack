<?php
/**
 * Contact Page
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Project.php';

$projectModel = new Project();
$projectFilters = [];
if ($auth->isProjectOwner()) {
    $projectFilters['created_by'] = $auth->getUserId();
}
$projects = $projectModel->getAll($projectFilters);
?>

<style>
/* ── Contact page: premium styles ── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.contact-page-wrapper {
    font-family: 'Inter', sans-serif;
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.contact-page {
    display: flex;
    flex-direction: column;
    gap: 0;
    height: calc(100vh - var(--topbar-height) - 48px); /* fill remaining viewport */
    min-height: 520px;
}
.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    flex: 1;
    min-height: 0;
}
/* shared card shell */
.contact-panel {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    font-family: 'Inter', sans-serif;
}
.contact-panel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(to right, #154c79, #2b7bc0);
    z-index: 10;
}
/* panel header strip */
.contact-panel-header {
    padding: 32px 40px 24px;
    position: relative;
    border-bottom: 2px solid #f3f4f6;
}
.contact-panel-header::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 40px;
    width: 60px;
    height: 2px;
    background: #154c79;
    border-radius: 2px;
}
.contact-panel-header h2 {
    font-size: 1.4rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.contact-panel-header h2 i {
    color: #154c79;
}
.contact-panel-header p {
    margin: 0;
    font-size: 0.9rem;
    color: #6b7280;
}
/* scrollable body that fills leftover height */
.contact-panel-body {
    flex: 1;
    padding: 32px 40px 40px;
    display: flex;
    flex-direction: column;
    gap: 28px;
    overflow-y: auto;
}
/* project select */
.contact-project-wrap .form-label {
    font-size: 0.95rem;
    font-weight: 600;
    color: #374151;
    text-transform: none;
    letter-spacing: normal;
    margin-bottom: 8px;
    display: block;
}
.contact-project-wrap .form-control {
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 1rem;
    background: #f9fafb;
    transition: all 0.2s ease;
    color: #111827;
    width: 100%;
    box-sizing: border-box;
}
.contact-project-wrap .form-control:focus {
    border-color: #154c79;
    background: #ffffff;
    outline: none;
    box-shadow: 0 0 0 4px rgba(21, 76, 121, 0.1);
}
/* contact rows */
.contact-rows {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.contact-row {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 20px 24px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}
.contact-row:hover { 
    background: #f9fafb; 
    border-color: #d1d5db;
}
.contact-row-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #154c79;
    font-size: 1.1rem;
    flex-shrink: 0;
    transition: all 0.3s ease;
}
.contact-row:hover .contact-row-icon {
    background: #e1effe;
    color: #1a56db;
}
.contact-row-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}
.contact-row-label {
    font-size: 0.8rem;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.contact-row-value a {
    font-size: 1.05rem;
    font-weight: 600;
    color: #111827;
    text-decoration: none;
    transition: color 0.2s ease;
}
.contact-row-value a:hover { 
    color: #154c79; 
}
/* helpdesk panel body */
.helpdesk-body {
    flex: 1;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.helpdesk-top {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.helpdesk-icon-wrap {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #154c79;
    font-size: 1.8rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.03);
}
.helpdesk-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}
.helpdesk-desc {
    font-size: 1rem;
    color: #4b5563;
    line-height: 1.6;
    margin: 0;
}
.helpdesk-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 28px;
    background: #154c79;
    color: #ffffff;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    align-self: flex-start;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px -1px rgba(21, 76, 121, 0.2), 0 2px 4px -1px rgba(21, 76, 121, 0.1);
}
.helpdesk-cta:hover { 
    background: #0f3a5e; 
    color: #ffffff; 
    text-decoration: none; 
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(21, 76, 121, 0.3), 0 4px 6px -1px rgba(21, 76, 121, 0.2);
}
.helpdesk-cta:active {
    transform: translateY(0);
    box-shadow: none;
}
@media (max-width: 900px) {
    .contact-grid { grid-template-columns: 1fr; }
    .contact-page { height: auto; }
}
</style>

<div class="contact-page-wrapper">

    <div class="contact-page">
    <div class="contact-grid">

        <!-- Left: Connect with -->
        <div class="contact-panel">
            <div class="contact-panel-header">
                <h2><i class="fas fa-address-book"></i> Connect with</h2>
                <p>Select a project, then click an address to compose a message.</p>
            </div>
            <div class="contact-panel-body">
                <div class="contact-project-wrap">
                    <label class="form-label">Select Project <span style="color: var(--danger);">*</span></label>
                    <select id="projectSelect" class="form-control" onchange="updateEmailLinks()">
                        <option value="">— Choose a project —</option>
                        <?php foreach ($projects as $p): ?>
                        <option value="<?php echo htmlspecialchars($p['title'], ENT_QUOTES); ?>">
                            <?php echo htmlspecialchars($p['title']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="contact-rows">
                    <div class="contact-row">
                        <div class="contact-row-icon"><i class="fas fa-envelope"></i></div>
                        <div class="contact-row-info">
                            <span class="contact-row-label">Outlook</span>
                            <div class="contact-row-value">
                                <a href="mailto:seijunqt@outlook.com">seijunqt@outlook.com</a>
                            </div>
                        </div>
                    </div>
                    <div class="contact-row">
                        <div class="contact-row-icon"><i class="fas fa-envelope"></i></div>
                        <div class="contact-row-info">
                            <span class="contact-row-label">Gmail</span>
                            <div class="contact-row-value">
                                <a id="gmailLink" href="https://mail.google.com/mail/?view=cm&to=redginepinedes09%40gmail.com" target="_blank" rel="noopener noreferrer">
                                    redginepinedes09@gmail.com
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Help Desk -->
        <div class="contact-panel">
            <div class="helpdesk-body">
                <div class="helpdesk-top">
                    <div class="helpdesk-icon-wrap"><i class="fas fa-headset"></i></div>
                    <h2 class="helpdesk-title">Need help?</h2>
                    <p class="helpdesk-desc">
                        For technical support, system issues, or assistance navigating the BACtrack platform,
                        reach out to the ICT Help Desk. Our team is ready to assist you.
                    </p>
                </div>
                <a href="http://192.168.11.1/icthelpdesk/login.php" target="_blank" rel="noopener noreferrer" class="helpdesk-cta">
                    <i class="fas fa-arrow-right"></i> Go to ICT Help Desk
                </a>
            </div>
        </div>

    </div>
</div>
</div>

<script>
function updateEmailLinks() {
    const title = document.getElementById('projectSelect').value;
    const recipient = 'redginepinedes09@gmail.com';
    const gmailLink = document.getElementById('gmailLink');
    if (title) {
        const subject = encodeURIComponent(title + ' - BAC');
        gmailLink.href = 'https://mail.google.com/mail/?view=cm&to=' + encodeURIComponent(recipient) + '&su=' + subject;
    } else {
        gmailLink.href = 'https://mail.google.com/mail/?view=cm&to=' + encodeURIComponent(recipient);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
