<?php
/**
 * Landing Calendar Widget (AJAX fragment)
 * Loaded into landing.php calendar tab.
 */

require_once __DIR__ . '/../models/Project.php';

$projectModel = new Project();
$projects = $projectModel->getAll(['approval_status' => 'APPROVED']);
?>

<style>
#landing-calendar-container .calendar-widget-card {
    border: 5px solid var(--border-color);
    border-radius: 20px;
    background: var(--card-bg);
    box-shadow: var(--shadow-sm);
    overflow: visible;
    max-width: 740px;
    margin: 0 auto;
}

#landing-calendar-container .calendar-widget-header {
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
    border-radius: 8px;
}

#landing-calendar-container .calendar-widget-body {
    padding: 12px;
}

#landing-calendar-container .calendar-widget-filter {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    flex-wrap: nowrap;
    margin-bottom: 10px;
}

#landing-calendar-container .calendar-widget-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

#landing-calendar-container .calendar-widget-field.search {
    flex: 1 1 210px;
    min-width: 170px;
}

#landing-calendar-container .calendar-widget-field.project {
    flex: 0 1 340px;
    min-width: 220px;
}

#landing-calendar-container .calendar-widget-filter label {
    font-weight: 700;
    color: var(--text-secondary);
    font-size: 0.84rem;
}

#landing-calendar-container .calendar-widget-filter input,
#landing-calendar-container .calendar-widget-filter select {
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

#landing-calendar-container .calendar-widget-filter input:focus,
#landing-calendar-container .calendar-widget-filter select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
}

#landing-calendar-container .calendar-widget-search-empty {
    margin: -2px 0 8px;
    font-size: 0.78rem;
    color: var(--text-muted);
}

#landing-calendar-container .calendar-widget-legend {
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

#landing-calendar-container .calendar-widget-legend-title {
    font-weight: 700;
    color: var(--text-primary);
}

#landing-calendar-container .calendar-widget-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

#landing-calendar-container .calendar-widget-legend-swatch {
    width: 11px;
    height: 11px;
    border-radius: 3px;
    border: 1px solid rgba(15, 23, 42, 0.12);
}

#landing-calendar-container .calendar-widget-legend-swatch.pending {
    background: #f59e0b;
}

#landing-calendar-container .calendar-widget-legend-swatch.in-progress {
    background: #3b82f6;
}

#landing-calendar-container .calendar-widget-legend-swatch.completed {
    background: #10b981;
}

#landing-calendar-container .calendar-widget-legend-swatch.delayed {
    background: #ef4444;
}

#landing-calendar-container .calendar-widget-prompt {
    padding: 10px 12px;
    border: 1px solid #bfdbfe;
    border-radius: var(--radius-md);
    background: #eff6ff;
    color: #1e3a8a;
    font-size: 0.82rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

#landing-calendar-container .calendar-widget-shell {
    margin-top: 10px;
}

#landing-calendar-container .calendar-widget-empty {
    padding: 10px 12px;
    border: 1px solid #fcd34d;
    border-radius: var(--radius-md);
    background: #fffbeb;
    color: #92400e;
    font-size: 0.82rem;
}

#landing-calendar-container #landingCalendar {
    min-height: 0;
}

#landing-calendar-container .fc {
    font-size: 0.78rem;
}

#landing-calendar-container .fc .fc-toolbar {
    margin-bottom: 4px;
    gap: 6px;
}

#landing-calendar-container .fc .fc-toolbar-title {
    font-size: 0.92rem;
}

#landing-calendar-container .fc .fc-button {
    font-size: 0.72rem;
    padding: 0.2em 0.48em;
}

#landing-calendar-container .fc .fc-daygrid-day-frame {
    min-height: 44px;
}

#landing-calendar-container .fc .fc-daygrid-day-number {
    font-size: 0.78rem;
    padding: 3px 4px;
}

#landing-calendar-container .fc .fc-daygrid-day-top {
    padding-top: 1px;
}

#landing-calendar-container .fc .fc-daygrid-event {
    margin-top: 1px;
    padding: 1px 3px;
    font-size: 0.7rem;
}

#landing-calendar-container .fc .fc-daygrid-body-balanced .fc-daygrid-day-events {
    min-height: 0;
}

#landing-calendar-container .fc .fc-scroller,
#landing-calendar-container .fc .fc-scroller-harness,
#landing-calendar-container .fc .fc-scroller-harness-liquid,
#landing-calendar-container .fc .fc-scroller-liquid-absolute {
    overflow: visible !important;
}

#landing-calendar-container .fc .fc-button-primary {
    background: #fff;
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    text-transform: lowercase;
    box-shadow: none;
}

#landing-calendar-container .fc .fc-button-primary:hover {
    background: var(--bg-secondary);
    border-color: var(--border-color);
    color: var(--text-primary);
}

#landing-calendar-container .fc .fc-button-primary:not(:disabled).fc-button-active,
#landing-calendar-container .fc .fc-button-primary:not(:disabled):active {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}

#landing-calendar-container .fc-day-today {
    background: rgba(59, 130, 246, 0.08) !important;
}

@media (max-width: 768px) {
    #landing-calendar-container .calendar-widget-filter {
        flex-direction: column;
        align-items: stretch;
        flex-wrap: wrap;
    }

    #landing-calendar-container .calendar-widget-field.project,
    #landing-calendar-container .calendar-widget-field.search {
        min-width: 100%;
    }

    #landing-calendar-container .calendar-widget-filter select {
        min-width: 100%;
    }
}
</style>

<div class="calendar-widget-card">
    <div class="calendar-widget-header">
        <i class="fas fa-calendar-alt"></i>
        BAC Activity Calendar
    </div>

    <div class="calendar-widget-body">
        <div class="calendar-widget-filter">
            <div class="calendar-widget-field search">
                <label for="landingCalendarTrackingSearch">Search BACTrack No.</label>
                <input
                    id="landingCalendarTrackingSearch"
                    type="search"
                    placeholder="Type tracking no. (e.g., BTQ2M-202504-001)"
                    <?php echo empty($projects) ? 'disabled' : ''; ?>
                >
            </div>
            <div class="calendar-widget-field project">
                <label for="landingCalendarProjectFilter">Project</label>
                <select id="landingCalendarProjectFilter" <?php echo empty($projects) ? 'disabled' : ''; ?>>
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
        <div id="landingCalendarSearchEmpty" class="calendar-widget-search-empty" style="display:none;">
            No project matched that BACTrack number.
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
            <div id="landingCalendarPrompt" class="calendar-widget-prompt">
                <i class="fas fa-info-circle"></i>
                Select a project to load its timeline and calendar events.
            </div>
            <div id="landingCalendarShell" class="calendar-widget-shell" style="display:none;">
                <div id="landingCalendar"></div>
            </div>
        <?php endif; ?>
    </div>
</div>
