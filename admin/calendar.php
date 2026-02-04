<?php
/**
 * Calendar View
 * SDO-BACtrack
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/Project.php';

$projectModel = new Project();
$projects = $projectModel->getAll();

$selectedProject = isset($_GET['project']) ? (int)$_GET['project'] : null;
?>

<div class="page-header">
    <div>
        <h2 style="margin: 0;">Project Timeline Calendar</h2>
        <p style="color: var(--text-muted); margin: 4px 0 0;">View and manage BAC procedural activities</p>
    </div>
</div>

<div class="filter-bar">
    <form class="filter-form" method="GET">
        <div class="filter-group">
            <label>Filter by Project</label>
            <select name="project" class="filter-select" onchange="this.form.submit()">
                <option value="">All Projects</option>
                <?php foreach ($projects as $project): ?>
                <option value="<?php echo $project['id']; ?>" <?php echo $selectedProject == $project['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($project['title']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Legend</label>
            <div style="display: flex; gap: 16px; align-items: center; padding: 8px 0;">
                <span style="display: flex; align-items: center; gap: 4px;">
                    <span style="width: 12px; height: 12px; background: #9ca3af; border-radius: 2px;"></span>
                    <small>Pending</small>
                </span>
                <span style="display: flex; align-items: center; gap: 4px;">
                    <span style="width: 12px; height: 12px; background: var(--info); border-radius: 2px;"></span>
                    <small>In Progress</small>
                </span>
                <span style="display: flex; align-items: center; gap: 4px;">
                    <span style="width: 12px; height: 12px; background: var(--success); border-radius: 2px;"></span>
                    <small>Completed</small>
                </span>
                <span style="display: flex; align-items: center; gap: 4px;">
                    <span style="width: 12px; height: 12px; background: var(--danger); border-radius: 2px;"></span>
                    <small>Delayed</small>
                </span>
            </div>
        </div>
    </form>
</div>

<div class="calendar-container">
    <div id="calendar"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var selectedProject = <?php echo $selectedProject ? $selectedProject : 'null'; ?>;
    var activeTooltip = null;
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        events: function(info, successCallback, failureCallback) {
            var url = APP_URL + '/admin/api/calendar-events.php?start=' + info.startStr + '&end=' + info.endStr;
            if (selectedProject) {
                url += '&project=' + selectedProject;
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
            if (activeTooltip) {
                activeTooltip.remove();
                activeTooltip = null;
            }
            openActivityModal(info.event.id);
        },
        eventDidMount: function(info) {
            // Add status class for styling
            info.el.classList.add('status-' + info.event.extendedProps.status.toLowerCase());
        },
        eventMouseEnter: function(info) {
            var props = info.event.extendedProps || {};
            var tooltip = document.createElement('div');
            tooltip.className = 'calendar-tooltip';
            tooltip.innerHTML = ''
                + '<div class="calendar-tooltip-title">' + (info.event.title || '') + '</div>'
                + '<div class="calendar-tooltip-date">'
                + (props.planned_start_date || info.event.startStr)
                + (props.planned_end_date && props.planned_end_date !== props.planned_start_date
                    ? ' – ' + props.planned_end_date
                    : '')
                + '</div>'
                + '<div class="calendar-tooltip-meta">'
                + (props.project_title ? '<span>' + props.project_title + '</span>' : '')
                + (props.status ? '<span class="status-badge status-' + props.status.toLowerCase() + '">' + props.status.replace('_', ' ') + '</span>' : '')
                + '</div>';

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
        height: 'auto',
        dayMaxEvents: 3,
        eventDisplay: 'block'
    });
    
    calendar.render();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
