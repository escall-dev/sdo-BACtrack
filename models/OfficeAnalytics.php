<?php
/**
 * Office Analytics Model
 * SDO-BACtrack - Analytics for OSDS, SGOD, and CID
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

class OfficeAnalytics {
    private $db;

    private $officeDefinitions = [
        'OSDS' => [
            'name' => 'Office of the Schools Division Superintendent (OSDS)',
            'short_name' => 'OSDS',
            'units' => [
                'OSDS-Admin' => 'Administrative Unit',
                'OSDS-Budget' => 'Budget Section',
                'OSDS-HR' => 'Human Resource',
                'OSDS-Procurement' => 'Procurement',
                'OSDS-Other' => 'Other',
            ],
        ],
        'SGOD' => [
            'name' => 'Schools Governance and Operations Division (SGOD)',
            'short_name' => 'SGOD',
            'units' => [
                'SGOD-SSM' => 'School Management',
                'SGOD-EF' => 'Education Facilities',
                'SGOD-SDS' => 'School District Supervision',
                'SGOD-Other' => 'Other',
            ],
        ],
        'CID' => [
            'name' => 'Curriculum and Instruction Division (CID)',
            'short_name' => 'CID',
            'units' => [
                'CID-LRM' => 'Learning Resource Management',
                'CID-EduTech' => 'Education Technology',
                'CID-Programs' => 'Programs Section',
                'CID-Other' => 'Other',
            ],
        ],
    ];

    public function __construct() {
        $this->db = db();
    }

    public function getDefinitions() {
        return $this->officeDefinitions;
    }

    public function getAnalytics($selectedOffice = null) {
        $selectedOffice = $selectedOffice && isset($this->officeDefinitions[$selectedOffice]) ? $selectedOffice : null;
        $officeCodes = $selectedOffice ? [$selectedOffice] : array_keys($this->officeDefinitions);

        $offices = [];
        foreach ($officeCodes as $officeCode) {
            $offices[$officeCode] = $this->buildOfficeAnalytics($officeCode);
        }

        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'selected_office' => $selectedOffice,
            'definitions' => $this->officeDefinitions,
            'locations' => $this->getLocationBreakdown(),
            'overall' => $this->buildOverallSummary($offices),
            'offices' => $offices,
        ];
    }

    private function buildOfficeAnalytics($officeCode) {
        $definition = $this->officeDefinitions[$officeCode];

        $usersTotal = $this->fetchCount(
            "SELECT COUNT(*) AS total FROM users WHERE office = ?",
            [$officeCode]
        );

        $projectsTotal = $this->fetchCount(
            "SELECT COUNT(*) AS total
             FROM projects p
             INNER JOIN users u ON u.id = p.created_by
             WHERE u.office = ?",
            [$officeCode]
        );

        $activitiesTotal = $this->fetchCount(
            "SELECT COUNT(*) AS total
             FROM project_activities pa
             INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
             INNER JOIN projects p ON p.id = bc.project_id
             INNER JOIN users u ON u.id = p.created_by
             WHERE u.office = ?",
            [$officeCode]
        );

        $projectDocuments = $this->fetchCount(
            "SELECT COUNT(*) AS total
             FROM project_documents pd
             INNER JOIN projects p ON p.id = pd.project_id
             INNER JOIN users u ON u.id = p.created_by
             WHERE u.office = ?",
            [$officeCode]
        );

        $activityDocuments = $this->fetchCount(
            "SELECT COUNT(*) AS total
             FROM activity_documents ad
             INNER JOIN project_activities pa ON pa.id = ad.activity_id
             INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
             INNER JOIN projects p ON p.id = bc.project_id
             INNER JOIN users u ON u.id = p.created_by
             WHERE u.office = ?",
            [$officeCode]
        );

        $projectStatuses = $this->normalizeCounts(
            [
                'APPROVED' => 0,
                'PENDING_APPROVAL' => 0,
                'REJECTED' => 0,
            ],
            $this->db->fetchAll(
                "SELECT p.approval_status AS label, COUNT(*) AS total
                 FROM projects p
                 INNER JOIN users u ON u.id = p.created_by
                 WHERE u.office = ?
                 GROUP BY p.approval_status",
                [$officeCode]
            )
        );

        $activityStatuses = $this->normalizeCounts(
            [
                'PENDING' => 0,
                'IN_PROGRESS' => 0,
                'COMPLETED' => 0,
                'DELAYED' => 0,
            ],
            $this->db->fetchAll(
                "SELECT pa.status AS label, COUNT(*) AS total
                 FROM project_activities pa
                 INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
                 INNER JOIN projects p ON p.id = bc.project_id
                 INNER JOIN users u ON u.id = p.created_by
                 WHERE u.office = ?
                 GROUP BY pa.status",
                [$officeCode]
            )
        );

        $responseRow = $this->db->fetch(
            "SELECT
                COUNT(*) AS completed_total,
                AVG(DATEDIFF(pa.actual_completion_date, pa.planned_start_date)) AS avg_completion_days,
                SUM(CASE WHEN pa.actual_completion_date <= pa.planned_end_date THEN 1 ELSE 0 END) AS on_time_total
             FROM project_activities pa
             INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
             INNER JOIN projects p ON p.id = bc.project_id
             INNER JOIN users u ON u.id = p.created_by
             WHERE u.office = ?
             AND pa.actual_completion_date IS NOT NULL",
            [$officeCode]
        ) ?: [];

        $complianceRow = $this->db->fetch(
            "SELECT
                COUNT(*) AS tagged_total,
                SUM(CASE WHEN pa.compliance_status = 'COMPLIANT' THEN 1 ELSE 0 END) AS compliant_total
             FROM project_activities pa
             INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
             INNER JOIN projects p ON p.id = bc.project_id
             INNER JOIN users u ON u.id = p.created_by
             WHERE u.office = ?
             AND pa.compliance_status IS NOT NULL",
            [$officeCode]
        ) ?: [];

        $categories = $this->db->fetchAll(
            "SELECT p.procurement_type AS label, COUNT(*) AS total
             FROM projects p
             INNER JOIN users u ON u.id = p.created_by
             WHERE u.office = ?
             GROUP BY p.procurement_type
             ORDER BY total DESC, p.procurement_type ASC",
            [$officeCode]
        );

        foreach ($categories as &$category) {
            $category['display_label'] = PROCUREMENT_TYPES[$category['label']] ?? $category['label'];
        }
        unset($category);

        $units = $this->db->fetchAll(
            "SELECT u.unit_section AS label, COUNT(p.id) AS total
             FROM users u
             LEFT JOIN projects p ON p.created_by = u.id
             WHERE u.office = ?
             AND u.unit_section IS NOT NULL
             AND u.unit_section <> ''
             GROUP BY u.unit_section
             ORDER BY total DESC, u.unit_section ASC",
            [$officeCode]
        );

        foreach ($units as &$unit) {
            $unit['display_label'] = $this->officeDefinitions[$officeCode]['units'][$unit['label']] ?? $unit['label'];
        }
        unset($unit);

        $trends = $this->buildTrendSeries($officeCode);

        return [
            'code' => $officeCode,
            'name' => $definition['name'],
            'short_name' => $definition['short_name'],
            'volume' => [
                'users' => $usersTotal,
                'projects' => $projectsTotal,
                'activities' => $activitiesTotal,
                'documents' => $projectDocuments + $activityDocuments,
            ],
            'status' => [
                'projects' => $projectStatuses,
                'activities' => $activityStatuses,
            ],
            'response' => [
                'completed_total' => (int)($responseRow['completed_total'] ?? 0),
                'avg_completion_days' => ($responseRow['avg_completion_days'] ?? null) !== null ? round((float)$responseRow['avg_completion_days'], 1) : null,
                'on_time_rate' => !empty($responseRow['completed_total'])
                    ? round((((int)($responseRow['on_time_total'] ?? 0)) / (int)$responseRow['completed_total']) * 100, 1)
                    : 0,
                'delayed_open' => (int)($activityStatuses['DELAYED'] ?? 0),
                'compliance_rate' => !empty($complianceRow['tagged_total'])
                    ? round((((int)($complianceRow['compliant_total'] ?? 0)) / (int)$complianceRow['tagged_total']) * 100, 1)
                    : 0,
            ],
            'categories' => $categories,
            'units' => $units,
            'trends' => $trends,
        ];
    }

    private function buildOverallSummary($offices) {
        $summary = [
            'users' => 0,
            'projects' => 0,
            'activities' => 0,
            'documents' => 0,
            'completed_activities' => 0,
        ];

        foreach ($offices as $office) {
            $summary['users'] += (int)$office['volume']['users'];
            $summary['projects'] += (int)$office['volume']['projects'];
            $summary['activities'] += (int)$office['volume']['activities'];
            $summary['documents'] += (int)$office['volume']['documents'];
            $summary['completed_activities'] += (int)$office['response']['completed_total'];
        }

        return $summary;
    }

    private function getLocationBreakdown() {
        $rows = $this->db->fetchAll(
            "SELECT u.office AS label, COUNT(p.id) AS total
             FROM users u
             LEFT JOIN projects p ON p.created_by = u.id
             WHERE u.office IN ('OSDS', 'SGOD', 'CID')
             GROUP BY u.office
             ORDER BY FIELD(u.office, 'OSDS', 'SGOD', 'CID')"
        );

        $normalized = [];
        foreach (array_keys($this->officeDefinitions) as $officeCode) {
            $normalized[$officeCode] = [
                'label' => $officeCode,
                'display_label' => $this->officeDefinitions[$officeCode]['short_name'],
                'total' => 0,
            ];
        }

        foreach ($rows as $row) {
            if (!isset($normalized[$row['label']])) {
                continue;
            }
            $normalized[$row['label']]['total'] = (int)$row['total'];
        }

        return array_values($normalized);
    }

    private function buildTrendSeries($officeCode) {
        $months = $this->getRecentMonths(6);

        $projectRows = $this->db->fetchAll(
            "SELECT DATE_FORMAT(p.created_at, '%Y-%m') AS month_key, COUNT(*) AS total
             FROM projects p
             INNER JOIN users u ON u.id = p.created_by
             WHERE u.office = ?
             AND p.created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
             GROUP BY month_key",
            [$officeCode]
        );

        $completedRows = $this->db->fetchAll(
            "SELECT DATE_FORMAT(pa.actual_completion_date, '%Y-%m') AS month_key, COUNT(*) AS total
             FROM project_activities pa
             INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
             INNER JOIN projects p ON p.id = bc.project_id
             INNER JOIN users u ON u.id = p.created_by
             WHERE u.office = ?
             AND pa.actual_completion_date IS NOT NULL
             AND pa.actual_completion_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
             GROUP BY month_key",
            [$officeCode]
        );

        $documentRows = $this->db->fetchAll(
            "SELECT month_key, COUNT(*) AS total
             FROM (
                 SELECT DATE_FORMAT(pd.uploaded_at, '%Y-%m') AS month_key
                 FROM project_documents pd
                 INNER JOIN projects p ON p.id = pd.project_id
                 INNER JOIN users u ON u.id = p.created_by
                 WHERE u.office = ?
                 AND pd.uploaded_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')

                 UNION ALL

                 SELECT DATE_FORMAT(ad.uploaded_at, '%Y-%m') AS month_key
                 FROM activity_documents ad
                 INNER JOIN project_activities pa ON pa.id = ad.activity_id
                 INNER JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
                 INNER JOIN projects p ON p.id = bc.project_id
                 INNER JOIN users u ON u.id = p.created_by
                 WHERE u.office = ?
                 AND ad.uploaded_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
             ) AS docs
             GROUP BY month_key",
            [$officeCode, $officeCode]
        );

        $projectMap = $this->rowsToMap($projectRows);
        $completedMap = $this->rowsToMap($completedRows);
        $documentMap = $this->rowsToMap($documentRows);

        foreach ($months as &$month) {
            $monthKey = $month['month_key'];
            $month['projects'] = $projectMap[$monthKey] ?? 0;
            $month['completed'] = $completedMap[$monthKey] ?? 0;
            $month['documents'] = $documentMap[$monthKey] ?? 0;
        }
        unset($month);

        return $months;
    }

    private function getRecentMonths($count) {
        $months = [];
        $cursor = new DateTime('first day of this month');
        $cursor->modify('-' . ($count - 1) . ' months');

        for ($i = 0; $i < $count; $i++) {
            $months[] = [
                'month_key' => $cursor->format('Y-m'),
                'label' => $cursor->format('M Y'),
            ];
            $cursor->modify('+1 month');
        }

        return $months;
    }

    private function rowsToMap($rows) {
        $map = [];
        foreach ($rows as $row) {
            $map[$row['month_key']] = (int)$row['total'];
        }
        return $map;
    }

    private function normalizeCounts($defaults, $rows) {
        $counts = $defaults;
        foreach ($rows as $row) {
            $counts[$row['label']] = (int)$row['total'];
        }
        return $counts;
    }

    private function fetchCount($sql, $params = []) {
        $row = $this->db->fetch($sql, $params);
        return (int)($row['total'] ?? 0);
    }
}
