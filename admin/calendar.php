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

$selectedProject = isset($_GET['project']) ? (int)$_GET['project'] : null;
// If a specific project is selected but it's not approved, ignore it (won't appear in calendar)
if ($selectedProject) {
    $approvedIds = array_column($projects, 'id');
    if (!in_array($selectedProject, $approvedIds)) {
        $selectedProject = null;
    }
}
?>

<div class="page-header">
    <div>
        <p class="calendar-subtitle">View and manage BAC procedural activities</p>
    </div>
</div>

<div class="filter-bar calendar-filter-bar">
    <div class="calendar-filter-header">
        <span class="calendar-filter-title"><i class="fas fa-calendar-alt"></i> BAC Activity Calendar</span>
        <div class="calendar-filter-right">
    <form class="filter-form calendar-filter-form" method="GET">
        <div class="filter-group calendar-project-filter">
            <label for="project-filter">Project</label>
            <select id="project-filter" name="project" class="filter-select" onchange="this.form.submit()">
                <option value="">All Projects</option>
                <?php foreach ($projects as $project): ?>
                <option value="<?php echo $project['id']; ?>" <?php echo $selectedProject == $project['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($project['title']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
        </div>
        <div class="calendar-legend" aria-label="Activity status legend">
            <span class="calendar-legend-item">
                <span class="calendar-legend-dot legend-pending"></span>
                <small>Pending</small>
            </span>
            <span class="calendar-legend-item">
                <span class="calendar-legend-dot legend-in-progress"></span>
                <small>In Progress</small>
            </span>
            <span class="calendar-legend-item">
                <span class="calendar-legend-dot legend-completed"></span>
                <small>Completed</small>
            </span>
            <span class="calendar-legend-item">
                <span class="calendar-legend-dot legend-delayed"></span>
                <small>Delayed</small>
            </span>
        </div>
    </div>
</div>

<style>
.calendar-subtitle {
    color: var(--text-muted);
    margin: 2px 0 0;
    font-size: 0.9rem;
}

/* ── Filter bar upgraded to profile-quality card ── */
.calendar-filter-bar {
    padding: 0;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    margin-bottom: 20px;
}

.calendar-filter-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
    border-bottom: 1px solid var(--border-color);
    gap: 16px;
    flex-wrap: wrap;
}

.calendar-filter-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: nowrap;
}

.calendar-filter-title i {
    font-size: 1.05rem;
    color: var(--primary);
}

.calendar-filter-right {
    display: flex;
    align-items: center;
    flex: 1;
    justify-content: center;
}

.calendar-filter-form {
    justify-content: center;
    align-items: center;
    gap: 20px;
    width: 100%;
}

.calendar-project-filter {
    gap: 4px;
}

.calendar-project-filter label {
    font-size: 0.74rem;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.calendar-project-filter .filter-select {
    min-width: 230px;
    height: 36px;
}

.calendar-legend {
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}

.calendar-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: var(--text-muted);
    font-size: 1rem;
}

.calendar-legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.legend-pending { background: #d97706; }
.legend-in-progress { background: var(--info); }
.legend-completed { background: var(--success); }
.legend-delayed { background: var(--danger); }

/* ── Calendar card (profile-quality container) ── */
.calendar-card {
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    margin-bottom: 0;
}

.calendar-card-header {
    padding: 18px 24px;
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
    border-bottom: 1px solid var(--border-color);
}

.calendar-card-header h2 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.calendar-card-header h2 i {
    font-size: 1rem;
    color: var(--primary);
}

.calendar-container {
    padding: 14px;
    width: 100%;
    overflow-x: auto;
    background: var(--card-bg);
}

.fc .fc-toolbar {
    margin-bottom: 10px;
    gap: 10px;
}

.fc .fc-toolbar-title {
    font-size: 1.15rem;
    font-weight: 600;
}

.fc .fc-button {
    box-shadow: none !important;
}

.fc .fc-button-primary {
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    font-weight: 500;
    text-transform: lowercase;
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

.fc .fc-today-button {
    text-transform: lowercase;
}

.fc-list .fc-list-event-time {
    display: none !important;
    width: 0 !important;
    padding: 0 !important;
}
.fc-footer-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 4px 4px;
    border-top: 1px solid var(--border, #e5e7eb);
    margin-top: 0 !important;
}
.fc-footer-toolbar .fc-button {
    background: none !important;
    border: 1px solid var(--border, #d1d5db) !important;
    box-shadow: none !important;
    color: var(--text, #374151) !important;
    font-size: 0.82rem !important;
    padding: 5px 18px !important;
    border-radius: 6px !important;
    transition: background 0.15s, color 0.15s;
}
.fc-footer-toolbar .fc-button:hover {
    background: var(--primary, #2563eb) !important;
    border-color: var(--primary, #2563eb) !important;
    color: #fff !important;
}
.fc-footer-toolbar .fc-toolbar-chunk {
    display: flex;
    gap: 10px;
}
.fc-header-toolbar .fc-toolbar-chunk:last-child .fc-button {
    margin-left: 6px !important;
}

.fc .fc-day-today {
    background: rgba(37, 99, 235, 0.05) !important;
}

.fc-timeGridWeek-view .fc-timegrid-col,
.fc-timeGridWeek-view .fc-col-header-cell {
    background: var(--card-bg);
}

.fc-event {
    border-radius: 3px;
    font-weight: 500;
    box-shadow: none;
    padding: 6px 4px !important;
    margin-bottom: 4px !important;
    overflow: visible !important;
    white-space: normal !important;
    word-wrap: break-word !important;
    text-overflow: clip !important;
}

.fc-event-title {
    overflow: visible !important;
    white-space: normal !important;
    word-wrap: break-word !important;
    text-overflow: clip !important;
    max-width: none !important;
}

.fc-event-title-container {
    overflow: visible !important;
    white-space: normal !important;
    word-wrap: break-word !important;
    text-overflow: clip !important;
    max-width: none !important;
}

.fc-daygrid-day-content {
    padding: 6px 2px !important;
    overflow: visible !important;
    width: 100% !important;
    max-width: none !important;
}

.fc-daygrid-day {
    min-height: auto !important;
    overflow: visible !important;
    max-height: none !important;
}

.fc-daygrid-day-frame {
    position: relative;
    overflow: visible !important;
}

.fc {
    overflow: visible !important;
}

.fc-daygrid {
    overflow: visible !important;
}

.fc-daygrid-body {
    overflow: visible !important;
}

.fc * {
    text-overflow: clip !important;
}

.fc-event-main {
    overflow: visible !important;
    white-space: normal !important;
    word-break: break-word !important;
    text-overflow: clip !important;
}

.fc-daygrid-day-number {
    font-size: 1.2rem !important;
    font-weight: 700 !important;
    color: var(--primary, #2563eb) !important;
    padding: 8px 6px !important;
    background: rgba(37, 99, 235, 0.08) !important;
    border-radius: 4px !important;
    display: inline-block !important;
}

.calendar-tooltip {
    background: var(--card-bg);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-md);
}

.calendar-tooltip-date {
    color: #60a5fa;
}

.fc-list-event-project {
    font-size: 0.92rem;
}

.fc-list-event-project::before {
    display: none;
}


.fc-timegrid-event {
    margin-bottom: 3px !important;
}

.fc-timegrid-slot {
    height: 2.5em !important;
}

.fc-list-event-graphic {
    margin-right: 8px !important;
}

.fc-list-event {
    border-bottom: 1px solid var(--border-color) !important;
    padding-bottom: 8px !important;
    margin-bottom: 8px !important;
}

@media (max-width: 768px) {
    .calendar-filter-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
    }

    .calendar-filter-right {
        width: 100%;
        justify-content: flex-start;
    }

    .calendar-filter-form {
        flex-direction: column;
        align-items: stretch;
        width: 100%;
    }

    .calendar-project-filter .filter-select {
        min-width: 100%;
    }

    .calendar-legend {
        gap: 10px;
    }

    .calendar-container {
        padding: 10px;
    }

    .fc .fc-toolbar {
        flex-wrap: wrap;
    }

    .fc .fc-toolbar-chunk {
        width: 100%;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
    }

    .fc .fc-toolbar-title {
        text-align: center;
    }

    .fc-daygrid-day {
        min-height: auto !important;
        overflow: visible !important;
        max-height: none !important;
    }
}


</style>

<div class="data-card calendar-card">
    <div class="card-header calendar-card-header">
        <h2><i class="fas fa-calendar-check"></i> Schedule</h2>
    </div>
    <div class="calendar-container">
        <div id="calendar"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var selectedProject = <?php echo $selectedProject ? $selectedProject : 'null'; ?>;
    var activeTooltip = null;
    var STORAGE_KEY = 'sdo_calendar_view';
    var savedView = localStorage.getItem(STORAGE_KEY) || 'dayGridMonth';

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: savedView,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listFiveDays'
        },
        events: function(info, successCallback, failureCallback) {
            var url = APP_URL + '/admin/api/calendar-events.php?start=' + info.startStr + '&end=' + info.endStr;
            if (selectedProject) {
                url += '&project=' + selectedProject;
            }
            if (window.SDO_BACTRACK_buildApiUrl) {
                url = window.SDO_BACTRACK_buildApiUrl(url);
            }
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    // API currently returns a plain array of events
                    if (Array.isArray(data)) {
                        successCallback(data);
                    } else if (data && data.success && Array.isArray(data.events)) {
                        successCallback(data.events);
                    } else {
                        failureCallback(data && data.message ? data.message : 'Unable to load events');
                    }
                })
                .catch(error => {
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            if (activeTooltip) {
                activeTooltip.remove();
                activeTooltip = null;
            }
            openActivityModal(info.event.id);
        },
        eventDidMount: function(info) {
            // Add status class for grid views (month / week)
            info.el.classList.add('status-' + info.event.extendedProps.status.toLowerCase());
        },
        eventMouseEnter: function(info) {

            var props = info.event.extendedProps || {};
            var tooltip = document.createElement('div');
            tooltip.className = 'calendar-tooltip';

            var titleEl = document.createElement('div');
            titleEl.className = 'calendar-tooltip-title';
            titleEl.textContent = info.event.title || '';
            tooltip.appendChild(titleEl);

            var dateStr = (props.planned_start_date || info.event.startStr);
            if (props.planned_end_date && props.planned_end_date !== props.planned_start_date) {
                dateStr += ' \u2013 ' + props.planned_end_date;
            }
            var dateEl = document.createElement('div');
            dateEl.className = 'calendar-tooltip-date';
            dateEl.textContent = dateStr;
            tooltip.appendChild(dateEl);

            var metaEl = document.createElement('div');
            metaEl.className = 'calendar-tooltip-meta';
            if (props.project_title) {
                var projSpan = document.createElement('span');
                projSpan.textContent = props.project_title;
                metaEl.appendChild(projSpan);
            }
            if (props.status) {
                var statusSpan = document.createElement('span');
                statusSpan.className = 'tooltip-status-badge list-status-' + props.status.toLowerCase();
                statusSpan.textContent = props.status.replace(/_/g, ' ');
                metaEl.appendChild(statusSpan);
            }
            tooltip.appendChild(metaEl);

            document.body.appendChild(tooltip);

            var rect = info.el.getBoundingClientRect();
            tooltip.style.left = (rect.left + window.pageXOffset + 4) + 'px';
            tooltip.style.top = (rect.bottom + window.pageYOffset + 8) + 'px';

            activeTooltip = tooltip;
        },
        eventMouseLeave: function() {
            if (activeTooltip) {
                activeTooltip.remove();
                activeTooltip = null;
            }
        },
        footerToolbar: {
            right: 'prev next'
        },
        datesSet: function(info) {
            localStorage.setItem(STORAGE_KEY, info.view.type);
            var footer = calendarEl.querySelector('.fc-footer-toolbar');
            if (footer) {
                footer.style.display = info.view.type === 'listFiveDays' ? '' : 'none';
            }
        },
        height: 'auto',
        dayMaxEvents: false,
        eventDisplay: 'block',
        eventMargin: 3,
        views: {
            listFiveDays: {
                type: 'list',
                duration: { days: 5 },
                buttonText: 'list',
                eventDisplay: 'list-item',
                // Override eventDidMount: don't add status class to list rows
                // (backgroundColor already colours the dot via the API)
                eventDidMount: function() {},
                // Override eventMouseEnter: list rows already show full info
                eventMouseEnter: function() {},
                eventContent: function(arg) {
                    var props = arg.event.extendedProps || {};

                    var wrapper = document.createElement('div');
                    wrapper.className = 'fc-list-event-custom';

                    var projEl = document.createElement('span');
                    projEl.className = 'fc-list-event-project';
                    projEl.textContent = props.project_title || arg.event.title || '';
                    wrapper.appendChild(projEl);

                    return { domNodes: [wrapper] };
                }
            }
        }
    });
    
    calendar.render();

    // Label footer-only prev/next buttons without touching the header toolbar
    var footer = calendarEl.querySelector('.fc-footer-toolbar');
    if (footer) {
        var btns = footer.querySelectorAll('.fc-prev-button, .fc-next-button');
        btns.forEach(function(btn) {
            btn.textContent = btn.classList.contains('fc-prev-button') ? 'Prev' : 'Next';
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

