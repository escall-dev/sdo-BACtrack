<?php
/**
 * Calendar View
 * SDO-BACtrack - BAC Members only
 */

require_once __DIR__ . '/../includes/auth.php';
$auth = auth();
$auth->requireProcurement();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Project.php';

$projectModel = new Project();
$projects = $projectModel->getAll(['approval_status' => 'APPROVED']);
?>

<div class="calendar-page-container">
    <div class="page-header">
        <div>
            <p class="calendar-subtitle">View BAC procedural activities</p>
        </div>
    </div>

    <div class="calendar-widget-card">
        <div class="calendar-widget-header">
            <i class="fas fa-calendar-alt"></i>
            BAC Activity Calendar
        </div>

        <div class="calendar-widget-body">
            <div class="calendar-widget-filter">
                <div class="calendar-widget-field project">
                    <label for="adminCalendarProjectFilter">Project</label>
                    <select id="adminCalendarProjectFilter" <?php echo empty($projects) ? 'disabled' : ''; ?>>
                        <option value="">Select a project first</option>
                        <?php foreach ($projects as $project): ?>
                            <?php
                                $trackingId = trim((string)($project['bactrack_id'] ?? ''));
                                if ($trackingId === '') {
                                    $trackingId = 'PR-' . str_pad((string)$project['id'], 4, '0', STR_PAD_LEFT);
                                }
                                $projectTitle = (string)($project['title'] ?? '');
                                $optionLabel = $trackingId . ' - ' . $projectTitle;
                            ?>
                            <option
                                value="<?php echo (int)$project['id']; ?>"
                                data-bactrack-id="<?php echo htmlspecialchars($trackingId, ENT_QUOTES, 'UTF-8'); ?>"
                                data-project-title="<?php echo htmlspecialchars($projectTitle, ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <?php echo htmlspecialchars($optionLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="calendar-widget-legend" aria-label="Calendar status color legend">
                <span class="calendar-widget-legend-title">Legend:</span>
                <span class="calendar-widget-legend-item"><span class="calendar-widget-legend-swatch pending"></span>Pending</span>
                <span class="calendar-widget-legend-item"><span class="calendar-widget-legend-swatch in-progress"></span>In Progress</span>
                <span class="calendar-widget-legend-item"><span class="calendar-widget-legend-swatch completed"></span>Completed</span>
                <span class="calendar-widget-legend-item"><span class="calendar-widget-legend-swatch delayed"></span>Delayed</span>
            </div>

            <?php if (empty($projects)): ?>
                <div class="calendar-widget-empty">
                    No approved projects are available yet.
                </div>
            <?php else: ?>
                <div id="adminCalendarPrompt" class="calendar-widget-prompt">
                    <i class="fas fa-info-circle"></i>
                    Select a project to load its timeline and calendar events.
                </div>
                <div id="adminCalendarShell" class="calendar-widget-shell" style="display:none;">
                    <div class="calendar-layout">
                        <section class="calendar-main-panel" aria-label="Project activity calendar">
                            <div class="calendar-main-scroll">
                                <div id="adminCalendar"></div>
                            </div>
                        </section>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="year-activity-modal" id="yearActivityModal" style="display:none;">
    <div class="year-activity-modal-card" role="dialog" aria-modal="true" aria-labelledby="yearActivityModalTitle">
        <div class="year-activity-modal-header">
            <h3 id="yearActivityModalTitle">Activity Details</h3>
            <button type="button" class="year-activity-modal-close" id="yearActivityModalClose" aria-label="Close">×</button>
        </div>
        <div class="year-activity-modal-body" id="yearActivityModalBody"></div>
    </div>
</div>

<style>

.calendar-page-container {
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
}

.calendar-subtitle {
    color: var(--text-muted);
    margin: 2px 0 0;
    font-size: 0.9rem;
}

.calendar-widget-card {
    border: 5px solid var(--border-color);
    border-radius: 20px;
    background: var(--card-bg);
    box-shadow: var(--shadow-sm);
    overflow: visible;
    max-width: 100%;
}

.calendar-widget-header {
    padding: 10px 14px;
    background: var(--primary-gradient);
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-radius: 14px 14px 0 0;
}

.calendar-widget-body {
    padding: 12px;
}

.calendar-widget-filter {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    flex-wrap: nowrap;
    margin-bottom: 10px;
}

.calendar-widget-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.calendar-widget-field.project {
    flex: 0 1 420px;
    min-width: 220px;
}

.calendar-widget-filter label {
    font-weight: 700;
    color: var(--text-secondary);
    font-size: 0.84rem;
}

.calendar-widget-filter select {
    min-width: 0;
    width: 100%;
    max-width: 100%;
    padding: 8px 10px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    background: #fff;
    color: var(--text-primary);
    font-family: inherit;
}

.calendar-widget-filter select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
}

.calendar-widget-legend {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px 12px;
    margin: 4px 0 10px;
    padding: 8px 10px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    background: #f8fafc;
    font-size: 0.76rem;
    color: var(--text-secondary);
}

.calendar-widget-legend-title {
    font-weight: 700;
    color: var(--text-primary);
}

.calendar-widget-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.calendar-widget-legend-swatch {
    width: 11px;
    height: 11px;
    border-radius: 3px;
    border: 1px solid rgba(15, 23, 42, 0.12);
}

.calendar-widget-legend-swatch.pending {
    background: #f59e0b;
}

.calendar-widget-legend-swatch.in-progress {
    background: #1b4a9a;
}

.calendar-widget-legend-swatch.completed {
    background: #10b981;
}

.calendar-widget-legend-swatch.delayed {
    background: #ef4444;
}

.calendar-widget-prompt {
    padding: 10px 12px;
    border: 1px solid #bfdbfe;
    border-radius: var(--radius-md);
    background: #eff6ff;
    color: #0f2d5c;
    font-size: 0.82rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.calendar-widget-shell {
    margin-top: 10px;
}

.calendar-widget-empty {
    padding: 10px 12px;
    border: 1px solid #fcd34d;
    border-radius: var(--radius-md);
    background: #fffbeb;
    color: #92400e;
    font-size: 0.82rem;
}

#adminCalendar {
    min-height: 0;
}

.calendar-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    align-items: start;
}

.calendar-layout.year-mode {
    grid-template-columns: 1fr;
}

.calendar-layout.year-mode .calendar-main-panel {
    grid-column: 1 / -1;
    overflow: visible;
    position: relative;
}

.calendar-main-scroll {
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-gutter: stable both-edges;
    padding-bottom: 12px;
}

.calendar-main-scroll.popover-open {
    overflow: visible;
    position: relative;
    z-index: 5;
}

.calendar-layout.year-mode #adminCalendar {
    min-width: 1120px;
}

.calendar-layout.year-mode #adminCalendar .fc .fc-multimonth {
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    gap: 12px;
}

.calendar-layout.year-mode #adminCalendar .fc .fc-multimonth-daygrid-table {
    table-layout: fixed;
}

.calendar-layout.year-mode #adminCalendar .fc .fc-daygrid-day-events {
    display: none !important;
}

.calendar-layout.year-mode #adminCalendar .fc .fc-daygrid-event {
    display: none !important;
}

.calendar-layout.year-mode #adminCalendar .fc .fc-daygrid-event .fc-event-main {
    display: none !important;
}

.calendar-layout.year-mode #adminCalendar .fc .fc-daygrid-day-frame {
    position: relative;
}

.calendar-layout.year-mode #adminCalendar .year-day-marker {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 1px;
    display: block;
    height: 8px;
    border-radius: 2px;
    opacity: 0.96;
    pointer-events: auto;
    cursor: pointer;
}

.calendar-layout.year-mode #adminCalendar .year-day-marker.span-start {
    left: 1px;
    right: -1px;
    border-top-left-radius: 999px;
    border-bottom-left-radius: 999px;
    border-top-right-radius: 2px;
    border-bottom-right-radius: 2px;
}

.calendar-layout.year-mode #adminCalendar .year-day-marker.span-middle {
    left: -1px;
    right: -1px;
    border-radius: 0;
}

.calendar-layout.year-mode #adminCalendar .year-day-marker.span-end {
    left: -1px;
    right: 1px;
    border-top-right-radius: 999px;
    border-bottom-right-radius: 999px;
    border-top-left-radius: 2px;
    border-bottom-left-radius: 2px;
}

.calendar-layout.year-mode #adminCalendar .year-day-marker.span-single {
    left: 1px;
    right: 1px;
    border-radius: 999px;
}

.year-marker-popover {
    position: fixed;
    z-index: 1200;
    min-width: 250px;
    max-width: min(340px, calc(100vw - 24px));
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: #f3f4f6;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.16);
}

.year-marker-popover-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 10px;
    font-weight: 600;
    color: #111827;
    border-bottom: 1px solid #d1d5db;
}

.year-marker-popover-close {
    border: 0;
    background: transparent;
    color: #6b7280;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
}

.year-marker-popover-body {
    padding: 10px;
}

.year-marker-popover-item {
    display: block;
    width: 100%;
    border: 0;
    border-radius: 4px;
    padding: 8px 10px;
    color: #fff;
    text-align: left;
    font-size: 0.95rem;
    line-height: 1.25;
    cursor: pointer;
}

.year-activity-modal {
    position: fixed;
    inset: 0;
    z-index: 1300;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.48);
    padding: 12px;
}

.year-activity-modal-card {
    width: min(560px, calc(100vw - 24px));
    max-height: calc(100vh - 24px);
    overflow: auto;
    border-radius: 10px;
    border: 1px solid var(--border-color);
    background: #fff;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.3);
}

.year-activity-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 14px;
    border-bottom: 1px solid var(--border-color);
}

.year-activity-modal-header h3 {
    margin: 0;
    font-size: 1rem;
    color: var(--text-primary);
}

.year-activity-modal-close {
    border: 0;
    background: transparent;
    color: #64748b;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    width: 28px;
    height: 28px;
}

.year-activity-modal-body {
    padding: 14px;
}

.year-activity-modal-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.year-activity-cell {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 10px;
    background: #f8fafc;
}

.year-activity-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 6px;
}

.year-activity-value {
    color: var(--text-primary);
    font-size: 0.9rem;
}

.year-activity-heading {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 10px;
}

@media (max-width: 640px) {
    .year-activity-modal-grid {
        grid-template-columns: 1fr;
    }
}

.calendar-layout.year-mode #adminCalendar .fc .fc-daygrid-more-link,
.calendar-layout.year-mode #adminCalendar .fc-daygrid-more-link {
    display: none !important;
    visibility: hidden !important;
    pointer-events: none !important;
    height: 0 !important;
    min-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
}

.calendar-layout.year-mode .calendar-main-panel .fc-popover {
    z-index: 1000;
    max-width: min(360px, calc(100% - 12px));
}

.calendar-layout.year-mode .calendar-main-panel .fc-popover-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.calendar-layout.year-mode .calendar-main-panel .fc-popover .fc-popover-title {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.calendar-layout.year-mode .calendar-main-panel .fc-popover .fc-popover-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border: 1px solid #d1d5db;
    border-radius: 999px;
    color: #334155;
    background: #ffffff;
    line-height: 1;
    text-decoration: none;
    cursor: pointer;
    padding: 0;
}

.calendar-layout.year-mode .calendar-main-panel .fc-popover .fc-popover-close .fc-icon {
    display: none;
}

.calendar-layout.year-mode .calendar-main-panel .fc-popover .fc-popover-close::before {
    content: 'x';
    font-size: 12px;
    font-weight: 700;
}

.calendar-layout.year-mode .calendar-main-panel .fc-popover .fc-daygrid-event-harness {
    max-width: 100%;
}

.calendar-layout.year-mode .calendar-main-panel .fc-popover .fc-daygrid-event {
    white-space: normal;
}

.calendar-layout.year-mode .calendar-main-panel .fc-popover .fc-event-main {
    font-size: 0.78rem;
    line-height: 1.2;
    overflow-wrap: anywhere;
}

.calendar-main-panel {
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    background: #fff;
    padding: 8px;
    overflow: visible;
}

.fc {
    font-size: 0.78rem;
}

.fc .fc-toolbar {
    margin-bottom: 4px;
    gap: 6px;
}

.fc .fc-toolbar-title {
    font-size: 0.92rem;
}

.fc .fc-button {
    font-size: 0.72rem;
    padding: 0.2em 0.48em;
    box-shadow: none;
}

.fc .fc-button-primary {
    background: #fff;
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    text-transform: lowercase;
    box-shadow: none;
}

.fc .fc-button-primary:hover {
    background: var(--bg-secondary);
    border-color: var(--border-color);
    color: var(--text-primary);
}

.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}

@media (max-width: 1100px) {
    .calendar-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .calendar-widget-filter {
        flex-direction: column;
        align-items: stretch;
        flex-wrap: wrap;
    }

    .calendar-widget-field.project {
        min-width: 100%;
    }

    .calendar-widget-filter select {
        min-width: 100%;
    }

    .calendar-layout {
        grid-template-columns: 1fr;
    }

    .calendar-layout.year-mode #adminCalendar {
        min-width: 1120px;
    }
}


</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var projectSelect = document.getElementById('adminCalendarProjectFilter');
    var prompt = document.getElementById('adminCalendarPrompt');
    var shell = document.getElementById('adminCalendarShell');
    var calendarEl = document.getElementById('adminCalendar');
    var yearActivityModalEl = document.getElementById('yearActivityModal');
    var yearActivityModalBodyEl = document.getElementById('yearActivityModalBody');
    var yearActivityModalCloseEl = document.getElementById('yearActivityModalClose');
    var yearActivityModalTitleEl = document.getElementById('yearActivityModalTitle');
    var layoutEl = shell.querySelector('.calendar-layout');
    var mainScrollEl = shell.querySelector('.calendar-main-scroll');
    var selectedProject = '';
    var focusedProject = '';
    var focusRequestId = 0;
    var calendarInstance = null;
    var popoverObserver = null;
    var yearDayMarkerMap = new Map();
    var yearMarkerPopoverEl = null;
    var selectedDate = null;
    var viewStorageKey = 'sdo_admin_calendar_view';

    if (!projectSelect || !prompt || !shell || !calendarEl) {
        return;
    }

    function normalizeDateInput(value) {
        if (!value) {
            return null;
        }
        if (value instanceof Date) {
            return value.toISOString().slice(0, 10);
        }
        var raw = String(value).trim();
        return /^\d{4}-\d{2}-\d{2}$/.test(raw) ? raw : null;
    }

    function setSelectedDate(value) {
        var normalized = normalizeDateInput(value);
        if (!normalized) {
            return;
        }
        selectedDate = normalized;
    }

    function isYearViewType(viewType) {
        return viewType === 'yearGrid' || viewType === 'multiMonthYear' || viewType === 'dayGridYear';
    }

    function syncLayoutForView(viewType) {
        if (!layoutEl) {
            return;
        }

        if (isYearViewType(viewType)) {
            layoutEl.classList.add('year-mode');
            return;
        }

        layoutEl.classList.remove('year-mode');
    }

    function switchToYearView() {
        if (!calendarInstance) {
            return;
        }
        try {
            calendarInstance.changeView('yearGrid');
        } catch (error) {
            try {
                calendarInstance.changeView('multiMonthYear');
            } catch (fallbackError) {
                try {
                    calendarInstance.changeView('dayGridYear');
                } catch (finalFallbackError) {
                    calendarInstance.changeView('dayGridMonth');
                }
            }
        }
    }

    function setPopoverOverflowMode(isOpen) {
        if (!mainScrollEl) {
            return;
        }
        mainScrollEl.classList.toggle('popover-open', !!isOpen);
    }

    function toLocalDateOnly(value) {
        if (!(value instanceof Date)) {
            return null;
        }
        return new Date(value.getFullYear(), value.getMonth(), value.getDate());
    }

    function toDateKey(value) {
        if (!(value instanceof Date)) {
            return '';
        }
        var y = value.getFullYear();
        var m = String(value.getMonth() + 1).padStart(2, '0');
        var d = String(value.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function parseDateKey(key) {
        var raw = String(key || '').trim();
        var match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(raw);
        if (!match) {
            return null;
        }

        var year = Number(match[1]);
        var month = Number(match[2]);
        var day = Number(match[3]);
        var date = new Date(year, month - 1, day);

        if (date.getFullYear() !== year || date.getMonth() !== (month - 1) || date.getDate() !== day) {
            return null;
        }

        return date;
    }

    function shiftDateKey(key, days) {
        var base = parseDateKey(key);
        if (!base || !Number.isFinite(days)) {
            return '';
        }

        base.setDate(base.getDate() + Number(days));
        return toDateKey(base);
    }

    function markerPriorityForStatus(status) {
        var key = String(status || '').toUpperCase();
        if (key === 'DELAYED') return 4;
        if (key === 'IN_PROGRESS') return 3;
        if (key === 'PENDING') return 2;
        if (key === 'COMPLETED') return 1;
        return 0;
    }

    function formatDateText(rawValue) {
        var raw = String(rawValue || '').trim();
        if (!raw || raw === '0000-00-00') {
            return 'N/A';
        }
        var parsed = new Date(raw + 'T00:00:00');
        if (Number.isNaN(parsed.getTime())) {
            return raw;
        }
        return parsed.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function closeYearActivityModal() {
        if (!yearActivityModalEl) {
            return;
        }
        yearActivityModalEl.style.display = 'none';
    }

    function renderYearActivityModal(payload, fallbackTitle) {
        if (!yearActivityModalBodyEl || !yearActivityModalTitleEl) {
            return;
        }

        var title = String((payload && payload.step_name) || fallbackTitle || 'Activity Details');
        var project = String((payload && payload.project_title) || 'Unknown project');
        var status = String((payload && payload.status_label) || (payload && payload.status) || 'PENDING').replace(/_/g, ' ');
        var start = formatDateText(payload && payload.planned_start_date);
        var end = formatDateText(payload && payload.planned_end_date);
        var timing = String((payload && payload.timing_label) || 'N/A');
        var compliance = String((payload && payload.compliance_label) || 'Not set');

        yearActivityModalTitleEl.textContent = title;
        yearActivityModalBodyEl.innerHTML = [
            '<p class="year-activity-heading">' + project + '</p>',
            '<div class="year-activity-modal-grid">',
            '<div class="year-activity-cell"><div class="year-activity-label">Status</div><div class="year-activity-value">' + status + '</div></div>',
            '<div class="year-activity-cell"><div class="year-activity-label">Timeline</div><div class="year-activity-value">' + timing + '</div></div>',
            '<div class="year-activity-cell"><div class="year-activity-label">Planned Start</div><div class="year-activity-value">' + start + '</div></div>',
            '<div class="year-activity-cell"><div class="year-activity-label">Planned End</div><div class="year-activity-value">' + end + '</div></div>',
            '<div class="year-activity-cell"><div class="year-activity-label">Compliance</div><div class="year-activity-value">' + compliance + '</div></div>',
            '</div>'
        ].join('');
    }

    function openYearActivityModal(activityId, fallbackTitle) {
        if (!yearActivityModalEl) {
            return;
        }

        yearActivityModalEl.style.display = 'flex';
        if (yearActivityModalTitleEl) {
            yearActivityModalTitleEl.textContent = fallbackTitle || 'Activity Details';
        }
        if (yearActivityModalBodyEl) {
            yearActivityModalBodyEl.innerHTML = '<p class="year-activity-value">Loading activity details...</p>';
        }

        fetch('api/activity-detail.php?id=' + encodeURIComponent(String(activityId)))
            .then(function(response) { return response.json(); })
            .then(function(payload) {
                renderYearActivityModal(payload, fallbackTitle);
            })
            .catch(function() {
                renderYearActivityModal(null, fallbackTitle);
            });
    }

    function formatDisplayDate(dateObj) {
        if (!(dateObj instanceof Date)) {
            return '';
        }
        return dateObj.toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function closeYearMarkerPopover() {
        if (yearMarkerPopoverEl && yearMarkerPopoverEl.parentNode) {
            yearMarkerPopoverEl.parentNode.removeChild(yearMarkerPopoverEl);
        }
        yearMarkerPopoverEl = null;
    }

    function openActivityLabelPopover(anchorEl, entry, dateKey) {
        closeYearMarkerPopover();

        if (!anchorEl || !entry) {
            return;
        }

        var dateObj = parseDateKey(dateKey);
        var popover = document.createElement('div');
        popover.className = 'year-marker-popover';

        var header = document.createElement('div');
        header.className = 'year-marker-popover-header';

        var title = document.createElement('span');
        title.textContent = formatDisplayDate(dateObj);

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'year-marker-popover-close';
        closeBtn.setAttribute('aria-label', 'Close');
        closeBtn.textContent = '×';
        closeBtn.addEventListener('click', function(evt) {
            evt.stopPropagation();
            closeYearMarkerPopover();
        });

        header.appendChild(title);
        header.appendChild(closeBtn);

        var body = document.createElement('div');
        body.className = 'year-marker-popover-body';

        var item = document.createElement('button');
        item.type = 'button';
        item.className = 'year-marker-popover-item';
        item.textContent = entry.title || 'Project activity';
        item.style.backgroundColor = entry.color || '#f59e0b';
        item.addEventListener('click', function(evt) {
            evt.stopPropagation();
            if (!entry.eventId) {
                return;
            }
            closeYearMarkerPopover();
            openYearActivityModal(entry.eventId, entry.title || 'Activity Details');
        });

        body.appendChild(item);
        popover.appendChild(header);
        popover.appendChild(body);
        document.body.appendChild(popover);

        var anchorRect = anchorEl.getBoundingClientRect();
        var popRect = popover.getBoundingClientRect();
        var left = anchorRect.left;
        var top = anchorRect.bottom + 6;

        var maxLeft = Math.max(8, window.innerWidth - popRect.width - 8);
        if (left > maxLeft) {
            left = maxLeft;
        }
        if (left < 8) {
            left = 8;
        }

        if (top + popRect.height > window.innerHeight - 8) {
            top = Math.max(8, anchorRect.top - popRect.height - 6);
        }

        popover.style.left = left + 'px';
        popover.style.top = top + 'px';
        yearMarkerPopoverEl = popover;
    }

    function buildYearDayMarkerMap(events) {
        yearDayMarkerMap.clear();

        if (!Array.isArray(events) || events.length === 0) {
            return;
        }

        events.forEach(function(eventItem) {
            if (!eventItem || !eventItem.start) {
                return;
            }

            var startDate = toLocalDateOnly(eventItem.start);
            if (!startDate) {
                return;
            }

            var endDateExclusive = eventItem.end ? toLocalDateOnly(eventItem.end) : null;
            if (!endDateExclusive) {
                endDateExclusive = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate() + 1);
            }

            var color = eventItem.backgroundColor || eventItem.borderColor || '#f59e0b';
            var status = eventItem.extendedProps && eventItem.extendedProps.status ? eventItem.extendedProps.status : '';
            var priority = markerPriorityForStatus(status);
            var eventId = eventItem.id != null ? String(eventItem.id) : '';
            var title = eventItem.title || 'Project activity';

            var cursor = new Date(startDate.getTime());
            while (cursor < endDateExclusive) {
                var dateKey = toDateKey(cursor);
                var existing = yearDayMarkerMap.get(dateKey);
                if (!existing || priority >= existing.priority) {
                    yearDayMarkerMap.set(dateKey, {
                        color: color,
                        priority: priority,
                        eventId: eventId,
                        title: title
                    });
                }
                cursor.setDate(cursor.getDate() + 1);
            }
        });
    }

    function renderYearDayMarkers() {
        if (!calendarEl) {
            return;
        }

        calendarEl.querySelectorAll('.year-day-marker').forEach(function(node) {
            node.remove();
        });

        if (!isYearViewType(calendarInstance && calendarInstance.view ? calendarInstance.view.type : '')) {
            closeYearMarkerPopover();
        }

        if (!calendarInstance || !isYearViewType(calendarInstance.view.type)) {
            return;
        }

        calendarEl.querySelectorAll('.fc-daygrid-day[data-date]').forEach(function(dayCell) {
            var dateKey = dayCell.getAttribute('data-date') || '';
            if (dateKey === '' || !yearDayMarkerMap.has(dateKey)) {
                return;
            }

            var frame = dayCell.querySelector('.fc-daygrid-day-frame');
            if (!frame) {
                return;
            }

            var entry = yearDayMarkerMap.get(dateKey);
            var dateObj = parseDateKey(dateKey);
            var dayOfWeek = dateObj ? dateObj.getDay() : -1;
            var prevKey = shiftDateKey(dateKey, -1);
            var nextKey = shiftDateKey(dateKey, 1);
            var prevEntry = prevKey ? yearDayMarkerMap.get(prevKey) : null;
            var nextEntry = nextKey ? yearDayMarkerMap.get(nextKey) : null;
            var hasPrevSame = dayOfWeek !== 0 && !!(prevEntry && prevEntry.eventId && prevEntry.eventId === entry.eventId);
            var hasNextSame = dayOfWeek !== 6 && !!(nextEntry && nextEntry.eventId && nextEntry.eventId === entry.eventId);

            var spanClass = 'span-single';
            if (hasPrevSame && hasNextSame) {
                spanClass = 'span-middle';
            } else if (hasPrevSame) {
                spanClass = 'span-end';
            } else if (hasNextSame) {
                spanClass = 'span-start';
            }

            var marker = document.createElement('span');
            marker.className = 'year-day-marker ' + spanClass;
            marker.style.backgroundColor = entry.color;
            marker.title = entry.title || 'Project activity';
            marker.addEventListener('click', function(evt) {
                evt.stopPropagation();
                openActivityLabelPopover(marker, entry, dateKey);
            });
            frame.appendChild(marker);
        });
    }

    function syncPopoverOverflowModeFromDom() {
        if (!calendarEl) {
            return;
        }
        var hasOpenPopover = !!calendarEl.querySelector('.fc-popover');
        setPopoverOverflowMode(hasOpenPopover);
    }

    function ensureCalendarInstance() {
        if (calendarInstance || typeof FullCalendar === 'undefined') {
            return;
        }

        var savedView = localStorage.getItem(viewStorageKey) || 'dayGridMonth';
        calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: savedView,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridDay,timeGridWeek,dayGridMonth,adminYear'
            },
            buttonText: {
                today: 'today',
                dayGridDay: 'day',
                timeGridWeek: 'week',
                dayGridMonth: 'month'
            },
            customButtons: {
                adminYear: {
                    text: 'year',
                    click: function() {
                        switchToYearView();
                    }
                }
            },
            events: function(info, successCallback, failureCallback) {
                if (!selectedProject) {
                    successCallback([]);
                    return;
                }

                var url = APP_URL + '/admin/api/calendar-events.php?start=' + encodeURIComponent(info.startStr)
                    + '&end=' + encodeURIComponent(info.endStr)
                    + '&project=' + encodeURIComponent(selectedProject);

                if (window.SDO_BACTRACK_buildApiUrl) {
                    url = window.SDO_BACTRACK_buildApiUrl(url);
                }

                fetch(url)
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (Array.isArray(data)) {
                            successCallback(data.map(function(eventItem) {
                                if (!eventItem || typeof eventItem !== 'object') {
                                    return eventItem;
                                }
                                var normalizedEvent = Object.assign({}, eventItem);
                                delete normalizedEvent.url;
                                return normalizedEvent;
                            }));
                            return;
                        }
                        if (data && data.success && Array.isArray(data.events)) {
                            successCallback(data.events.map(function(eventItem) {
                                if (!eventItem || typeof eventItem !== 'object') {
                                    return eventItem;
                                }
                                var normalizedEvent = Object.assign({}, eventItem);
                                delete normalizedEvent.url;
                                return normalizedEvent;
                            }));
                            return;
                        }
                        failureCallback(data && data.error ? data.error : 'Unable to load events');
                    })
                    .catch(function(error) {
                        failureCallback(error);
                    });
            },
            eventDidMount: function(info) {
                var status = String(info.event.extendedProps.status || '').toLowerCase();
                if (status !== '') {
                    info.el.classList.add('status-' + status);
                }
                if (info.el && info.el.hasAttribute('href')) {
                    info.el.removeAttribute('href');
                }
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (!info.event || !info.event.id) {
                    return;
                }

                var eventDate = info.event.start ? toDateKey(toLocalDateOnly(info.event.start)) : '';
                var entry = {
                    eventId: String(info.event.id),
                    title: info.event.title || 'Project activity',
                    color: info.event.backgroundColor || info.event.borderColor || '#f59e0b'
                };

                openActivityLabelPopover(info.el, entry, eventDate);
            },
            dateClick: function(info) {
                if (!isYearViewType(info.view.type)) {
                    return;
                }

                var clickedDate = new Date(info.date.getFullYear(), info.date.getMonth(), info.date.getDate());
                var matchingEvent = calendarInstance.getEvents().find(function(eventItem) {
                    if (!eventItem || !eventItem.id || !eventItem.start) {
                        return false;
                    }

                    var eventStart = new Date(eventItem.start.getFullYear(), eventItem.start.getMonth(), eventItem.start.getDate());
                    var eventEndSource = eventItem.end ? eventItem.end : eventItem.start;
                    var eventEnd = new Date(eventEndSource.getFullYear(), eventEndSource.getMonth(), eventEndSource.getDate());

                    return clickedDate >= eventStart && clickedDate < eventEnd;
                });

                if (matchingEvent) {
                    openYearActivityModal(String(matchingEvent.id), matchingEvent.title || 'Activity Details');
                }
            },
            moreLinkClick: function(info) {
                if (!isYearViewType(info.view.type)) {
                    return 'popover';
                }

                if (info && Array.isArray(info.allSegs) && info.allSegs.length > 0) {
                    var firstSegEvent = info.allSegs[0] && info.allSegs[0].event ? info.allSegs[0].event : null;
                    if (firstSegEvent && firstSegEvent.id) {
                        openYearActivityModal(String(firstSegEvent.id), firstSegEvent.title || 'Activity Details');
                    }
                }

                return false;
            },
            moreLinkDidMount: function(info) {
                if (!isYearViewType(info.view.type)) {
                    return;
                }
                if (info && info.el) {
                    info.el.style.display = 'none';
                    info.el.setAttribute('aria-hidden', 'true');
                }
            },
            datesSet: function(info) {
                localStorage.setItem(viewStorageKey, info.view.type);
                syncLayoutForView(info.view.type);
                setSelectedDate(calendarInstance.getDate());
                setPopoverOverflowMode(false);
                window.requestAnimationFrame(renderYearDayMarkers);
            },
            eventsSet: function(events) {
                buildYearDayMarkerMap(events);
                window.requestAnimationFrame(renderYearDayMarkers);
            },
            height: 'auto',
            contentHeight: 'auto',
            expandRows: false,
            fixedWeekCount: false,
            showNonCurrentDates: true,
            dayMaxEvents: 1,
            dayMaxEventRows: 1,
            views: {
                yearGrid: {
                    type: 'multiMonth',
                    duration: { years: 1 },
                    multiMonthMaxColumns: 4,
                    multiMonthMinWidth: 1,
                    dayMaxEvents: false,
                    dayMaxEventRows: false
                },
                dayGridYear: {
                    type: 'dayGrid',
                    duration: { years: 1 }
                }
            }
        });

        calendarInstance.render();
        syncLayoutForView(calendarInstance.view.type);
        syncPopoverOverflowModeFromDom();

        if (popoverObserver) {
            popoverObserver.disconnect();
            popoverObserver = null;
        }

        popoverObserver = new MutationObserver(function() {
            syncPopoverOverflowModeFromDom();
        });
        popoverObserver.observe(calendarEl, {
            childList: true,
            subtree: true
        });

        if (savedView === 'yearGrid' || savedView === 'multiMonthYear' || savedView === 'dayGridYear') {
            switchToYearView();
        }

        setSelectedDate(calendarInstance.getDate());
    }

    function applyProjectSelection() {
        selectedProject = String(projectSelect.value || '').trim();

        if (!selectedProject) {
            prompt.style.display = '';
            shell.style.display = 'none';
            setPopoverOverflowMode(false);
            if (layoutEl) {
                layoutEl.classList.remove('year-mode');
            }
            focusedProject = '';
            focusRequestId += 1;
            return;
        }

        prompt.style.display = 'none';
        shell.style.display = '';
        ensureCalendarInstance();

        if (!calendarInstance) {
            return;
        }

        if (focusedProject === selectedProject) {
            calendarInstance.refetchEvents();
            return;
        }

        focusedProject = selectedProject;
        var requestId = ++focusRequestId;

        var firstLoadUrl = APP_URL + '/admin/api/calendar-events.php?project=' + encodeURIComponent(selectedProject);
        if (window.SDO_BACTRACK_buildApiUrl) {
            firstLoadUrl = window.SDO_BACTRACK_buildApiUrl(firstLoadUrl);
        }

        fetch(firstLoadUrl)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (!calendarInstance || requestId !== focusRequestId || focusedProject !== selectedProject) {
                    return;
                }

                var events = Array.isArray(data)
                    ? data
                    : (data && data.success && Array.isArray(data.events) ? data.events : []);

                if (events.length > 0) {
                    var firstValidEvent = events.find(function(eventItem) {
                        var rawDate = String((eventItem && eventItem.start) || '').trim();
                        return /^\d{4}-\d{2}-\d{2}$/.test(rawDate) && rawDate !== '0000-00-00';
                    });

                    if (firstValidEvent) {
                        var focusDate = String(firstValidEvent.start || '').trim();
                        calendarInstance.gotoDate(focusDate);
                        setSelectedDate(focusDate);
                    }
                }

                calendarInstance.refetchEvents();
            })
            .catch(function() {
                if (!calendarInstance || requestId !== focusRequestId || focusedProject !== selectedProject) {
                    return;
                }
                calendarInstance.refetchEvents();
            });
    }

    projectSelect.addEventListener('change', applyProjectSelection);

    if (yearActivityModalCloseEl) {
        yearActivityModalCloseEl.addEventListener('click', function() {
            closeYearActivityModal();
        });
    }

    if (yearActivityModalEl) {
        yearActivityModalEl.addEventListener('click', function(evt) {
            if (evt.target === yearActivityModalEl) {
                closeYearActivityModal();
            }
        });
    }

    document.addEventListener('click', function(event) {
        var target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        if (!target.closest('.year-marker-popover') && !target.closest('.year-day-marker')) {
            closeYearMarkerPopover();
        }

        if (target.closest('.fc-daygrid-more-link')) {
            window.requestAnimationFrame(function() {
                syncPopoverOverflowModeFromDom();
            });
            return;
        }

        if (target.closest('.fc-popover-close')) {
            window.requestAnimationFrame(function() {
                syncPopoverOverflowModeFromDom();
            });
            return;
        }

        setTimeout(function() {
            syncPopoverOverflowModeFromDom();
        }, 0);
    }, true);

    window.addEventListener('resize', function() {
        closeYearMarkerPopover();
    });

    applyProjectSelection();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

