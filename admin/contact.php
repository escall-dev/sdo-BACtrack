<?php
/**
 * Contact Page
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Project.php';

$projectModel = new Project();
$projectFilters = [];
if (isset($auth) && $auth->isProjectOwner()) {
    $projectFilters['created_by'] = $auth->getUserId();
}
$projects = $projectModel->getAll($projectFilters);
?>

<style>
.contact-page-wrapper {
    animation: contactFadeIn 0.35s ease-out;
    max-width: 720px;
    margin: 0 auto;
}

@keyframes contactFadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.bac-contact-card {
    background: #ffffff;
    border: 1px solid #d9dee8;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
    padding: 26px;
}

.bac-contact-title {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
}

.bac-contact-subtitle {
    margin: 12px 0 20px;
    color: #475569;
    font-size: 1.2rem;
    line-height: 1.55;
    max-width: 580px;
}

.bac-contact-label {
    display: block;
    margin-bottom: 8px;
    font-size: 1.05rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #334155;
}

.bac-contact-select {
    width: 100%;
    border: 1px solid #bfc8d7;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 1.08rem;
    background: #ffffff;
    color: #0f172a;
}

.bac-contact-select:focus {
    outline: none;
    border-color: #1d4f80;
    box-shadow: 0 0 0 3px rgba(29, 79, 128, 0.14);
}

.bac-contact-recipient {
    margin: 10px 0 16px;
    color: #475569;
    font-size: 1.05rem;
}

.bac-contact-actions {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.bac-contact-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    border: 1px solid #cfd7e5;
    border-radius: 12px;
    padding: 11px 14px;
    background: #ffffff;
    color: #334155;
    font-weight: 700;
    font-size: 1.24rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.bac-contact-action:hover {
    border-color: #1d4f80;
    box-shadow: 0 3px 10px rgba(29, 79, 128, 0.12);
    background: #f8fbff;
}

.bac-contact-action.is-disabled {
    pointer-events: none;
    color: #8f99aa;
    background: #f8fafc;
    border-color: #d7deea;
}

@media (max-width: 768px) {
    .contact-page-wrapper {
        max-width: 100%;
    }

    .bac-contact-card {
        padding: 18px;
    }

    .bac-contact-title {
        font-size: 1.55rem;
    }

    .bac-contact-subtitle {
        font-size: 1rem;
        margin-bottom: 16px;
    }

    .bac-contact-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="contact-page-wrapper">
    <section class="bac-contact-card" aria-labelledby="bacContactHeading">
        <h2 class="bac-contact-title" id="bacContactHeading">
            <i class="fas fa-envelope-open-text"></i>
            Contact BAC Secretariat
        </h2>
        <p class="bac-contact-subtitle">Select a project first, then choose Gmail or Outlook to compose your message.</p>

        <label class="bac-contact-label" for="projectSelect">Select Project</label>
        <select id="projectSelect" class="bac-contact-select" onchange="updateEmailLinks()">
            <option value="">-- Choose a project --</option>
            <?php foreach ($projects as $p): ?>
                <?php $projectTitle = (string)($p['title'] ?? ''); ?>
                <?php if ($projectTitle === ''): ?>
                    <?php continue; ?>
                <?php endif; ?>
                <option value="<?php echo htmlspecialchars($projectTitle, ENT_QUOTES); ?>">
                    <?php echo htmlspecialchars($projectTitle); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <p class="bac-contact-recipient">Recipient: bac.sanpedro@deped.gov.ph</p>

        <div class="bac-contact-actions">
            <a id="outlookLink" class="bac-contact-action is-disabled" href="mailto:bac.sanpedro@deped.gov.ph" aria-disabled="true" tabindex="-1">
                <i class="fas fa-paper-plane"></i> Outlook
            </a>
            <a id="gmailLink" class="bac-contact-action is-disabled" href="https://mail.google.com/mail/?view=cm&amp;to=bac.sanpedro@deped.gov.ph" target="_blank" rel="noopener noreferrer" aria-disabled="true" tabindex="-1">
                <i class="fab fa-google"></i> Gmail
            </a>
        </div>
    </section>
</div>

<script>
const BAC_RECIPIENT = 'bac.sanpedro@deped.gov.ph';

function setBacActionsEnabled(isEnabled) {
    const linkIds = ['outlookLink', 'gmailLink'];
    linkIds.forEach(function(linkId) {
        const link = document.getElementById(linkId);
        if (!link) {
            return;
        }

        link.classList.toggle('is-disabled', !isEnabled);
        link.setAttribute('aria-disabled', isEnabled ? 'false' : 'true');
        if (isEnabled) {
            link.removeAttribute('tabindex');
        } else {
            link.setAttribute('tabindex', '-1');
        }
    });
}

function updateEmailLinks() {
    const title = String((document.getElementById('projectSelect') || {}).value || '').trim();
    const outlookLink = document.getElementById('outlookLink');
    const gmailLink = document.getElementById('gmailLink');

    if (!outlookLink || !gmailLink) {
        return;
    }

    const baseGmailUrl = 'https://mail.google.com/mail/?view=cm&to=' + encodeURIComponent(BAC_RECIPIENT);

    if (title === '') {
        outlookLink.href = 'mailto:' + BAC_RECIPIENT;
        gmailLink.href = baseGmailUrl;
        setBacActionsEnabled(false);
        return;
    }

    const subject = encodeURIComponent(title + ' - BAC');
    outlookLink.href = 'mailto:' + BAC_RECIPIENT + '?subject=' + subject;
    gmailLink.href = baseGmailUrl + '&su=' + subject;
    setBacActionsEnabled(true);
}

document.addEventListener('DOMContentLoaded', function() {
    setBacActionsEnabled(false);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>