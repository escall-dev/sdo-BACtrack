<?php

function timelineParseDate($date) {
    if (empty($date)) {
        return null;
    }

    try {
        return new DateTimeImmutable($date);
    } catch (Exception $e) {
        return null;
    }
}

function timelineFormatDayCount($days) {
    $days = (int) $days;
    return $days . ' day' . ($days === 1 ? '' : 's');
}

function timelineNormalizeStepName($stepName) {
    $stepName = (string) $stepName;

    $replacements = [
        '(<= P200K)' => '(≤ ₱200K)',
        '(<=P200K)' => '(≤ ₱200K)',
        '(>= P200K)' => '(≥ ₱200K)',
        '(>=P200K)' => '(≥ ₱200K)',
        '(> P200K)' => '(> ₱200K)',
        'P200K' => '₱200K',
    ];

    return strtr($stepName, $replacements);
}

function timelineDurationDays($startDate, $endDate) {
    $start = timelineParseDate($startDate);
    $end = timelineParseDate($endDate);

    if (!$start || !$end || $end < $start) {
        return null;
    }

    return $start->diff($end)->days + 1;
}

function timelineDurationLabel($startDate, $endDate, $emptyLabel = '-') {
    $days = timelineDurationDays($startDate, $endDate);
    return $days === null ? $emptyLabel : timelineFormatDayCount($days);
}

function timelineIsActivityComplete($activity) {
    return !empty($activity['actual_completion_date']) || (($activity['status'] ?? '') === 'COMPLETED');
}

function timelineActivityMeta($activity, $today = null) {
    $today = $today instanceof DateTimeInterface ? DateTimeImmutable::createFromInterface($today) : new DateTimeImmutable('today');
    $start = timelineParseDate($activity['planned_start_date'] ?? null);
    $end = timelineParseDate($activity['planned_end_date'] ?? null);
    $isComplete = timelineIsActivityComplete($activity);
    $durationDays = timelineDurationDays($activity['planned_start_date'] ?? null, $activity['planned_end_date'] ?? null);

    $meta = [
        'duration_days' => $durationDays,
        'duration_label' => $durationDays === null ? '-' : timelineFormatDayCount($durationDays),
        'timing_label' => 'Schedule unavailable',
        'timing_tone' => 'muted',
        'days_remaining' => null,
        'days_overdue' => null,
        'days_until_start' => null,
        'is_complete' => $isComplete,
        'is_overdue' => false,
        'is_due_today' => false,
    ];

    if (!$start || !$end) {
        return $meta;
    }

    if ($isComplete) {
        $completionDate = timelineParseDate($activity['actual_completion_date'] ?? null);
        $formattedCompletion = $completionDate ? $completionDate->format('M j, Y') : $end->format('M j, Y');
        $meta['timing_label'] = (($activity['status'] ?? '') === 'DELAYED')
            ? 'Completed late on ' . $formattedCompletion
            : 'Completed on ' . $formattedCompletion;
        $meta['timing_tone'] = (($activity['status'] ?? '') === 'DELAYED') ? 'danger' : 'success';
        return $meta;
    }

    if ($end < $today) {
        $daysOverdue = max(1, $end->diff($today)->days);
        $meta['days_overdue'] = $daysOverdue;
        $meta['is_overdue'] = true;
        $meta['timing_label'] = 'Overdue by ' . timelineFormatDayCount($daysOverdue);
        $meta['timing_tone'] = 'danger';
        return $meta;
    }

    if ($start > $today) {
        $daysUntilStart = max(1, $today->diff($start)->days);
        $meta['days_until_start'] = $daysUntilStart;
        $meta['timing_label'] = 'Starts in ' . timelineFormatDayCount($daysUntilStart);
        $meta['timing_tone'] = 'info';
        return $meta;
    }

    if ($end->format('Y-m-d') === $today->format('Y-m-d')) {
        $meta['days_remaining'] = 1;
        $meta['is_due_today'] = true;
        $meta['timing_label'] = 'Due today';
        $meta['timing_tone'] = 'warning';
        return $meta;
    }

    $daysRemaining = $today->diff($end)->days + 1;
    $meta['days_remaining'] = $daysRemaining;
    $meta['timing_label'] = timelineFormatDayCount($daysRemaining) . ' remaining';
    $meta['timing_tone'] = 'warning';
    return $meta;
}

function timelineProjectSummary($activities, $today = null) {
    $today = $today instanceof DateTimeInterface ? DateTimeImmutable::createFromInterface($today) : new DateTimeImmutable('today');
    $summary = [
        'meta_by_id' => [],
        'current_activity' => null,
        'next_activity' => null,
        'remaining_steps' => 0,
        'overdue_steps' => 0,
        'due_today_steps' => 0,
        'total_planned_days' => 0,
    ];
    $pendingQueue = [];

    foreach ($activities as $activity) {
        $meta = timelineActivityMeta($activity, $today);
        $summary['meta_by_id'][$activity['id']] = $meta;

        if ($meta['duration_days'] !== null) {
            $summary['total_planned_days'] += $meta['duration_days'];
        }
        if ($meta['is_overdue']) {
            $summary['overdue_steps']++;
        }
        if ($meta['is_due_today']) {
            $summary['due_today_steps']++;
        }
        if (!$meta['is_complete']) {
            $summary['remaining_steps']++;
            $pendingQueue[] = $activity;
        }
    }

    $summary['current_activity'] = $pendingQueue[0] ?? null;
    $summary['next_activity'] = $pendingQueue[1] ?? null;

    return $summary;
}