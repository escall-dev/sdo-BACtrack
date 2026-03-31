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
    align-items: center;
    gap: 8px;
    flex-wrap: nowrap;
    margin-bottom: 10px;
}

#landing-calendar-container .calendar-widget-filter label {
    font-weight: 700;
    color: var(--text-secondary);
    font-size: 0.84rem;
}

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
            <label for="landingCalendarProjectFilter">Project</label>
            <select id="landingCalendarProjectFilter" <?php echo empty($projects) ? 'disabled' : ''; ?>>
                <option value="">Select a project first</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?php echo (int) $project['id']; ?>"><?php echo htmlspecialchars($project['title']); ?></option>
                <?php endforeach; ?>
            </select>
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
