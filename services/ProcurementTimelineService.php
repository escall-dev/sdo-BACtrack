<?php
/**
 * Backward procurement timeline engine anchored on implementation date.
 */

require_once __DIR__ . '/../config/procurement.php';

class ProcurementTimelineService {
    private $config;

    public function __construct(?array $config = null) {
        $this->config = $config ?? procurementConfig();
    }

    public function generateTimeline($implementationDate, $procurementType = 'PUBLIC_BIDDING') {
        $implementation = $this->parseDate($implementationDate, 'Implementation date is required.');
        $workflow = $this->getValidatedWorkflow($procurementType);
        $backwardStages = $workflow['backward_timeline_stages'];
        $forwardStages = $workflow['forward_execution_stages'];

        $timeline = [];

        // Build backward phase so it ends the day before implementation.
        $cursor = $implementation->sub(new DateInterval('P1D'));
        $backwardRows = [];
        for ($i = count($backwardStages) - 1; $i >= 0; $i--) {
            $stage = $backwardStages[$i];
            $durationDays = max(0, (int)$stage['days']);
            if ($durationDays === 0) {
                // Zero-day milestones are placed on the next stage date without consuming a day.
                $plannedStart = $cursor->add(new DateInterval('P1D'));
                $plannedEnd = $plannedStart;
            } else {
                $plannedEnd = $cursor;
                $plannedStart = $plannedEnd->sub(new DateInterval('P' . ($durationDays - 1) . 'D'));
            }

            $backwardRows[] = [
                'stage_key' => $stage['key'],
                'stage' => $stage['name'],
                'phase' => 'backward_timeline',
                'planned_start_date' => $plannedStart->format('Y-m-d'),
                'planned_end_date' => $plannedEnd->format('Y-m-d'),
                'duration_days' => $durationDays,
            ];

            if ($durationDays > 0) {
                $cursor = $plannedStart->sub(new DateInterval('P1D'));
            }
        }

        $timeline = array_merge($timeline, array_reverse($backwardRows));

        // Build forward phase so implementation is the transition point.
        $cursor = $implementation;
        foreach ($forwardStages as $stage) {
            $durationDays = max(1, (int)$stage['days']);
            $plannedStart = $cursor;
            $plannedEnd = $plannedStart->add(new DateInterval('P' . ($durationDays - 1) . 'D'));

            $timeline[] = [
                'stage_key' => $stage['key'],
                'stage' => $stage['name'],
                'phase' => 'forward_execution',
                'planned_start_date' => $plannedStart->format('Y-m-d'),
                'planned_end_date' => $plannedEnd->format('Y-m-d'),
                'duration_days' => $durationDays,
            ];

            $cursor = $plannedEnd->add(new DateInterval('P1D'));
        }

        return $timeline;
    }

    public function resolveCurrentStage(array $timeline, ?DateTimeInterface $today = null, $isCompleted = false) {
        if (empty($timeline)) {
            return [
                'status' => 'NOT_STARTED',
                'current_stage' => null,
                'current_stage_key' => null,
            ];
        }

        $todayDate = $today ? DateTimeImmutable::createFromInterface($today) : new DateTimeImmutable('today');

        $firstStart = $this->parseDate($timeline[0]['planned_start_date'] ?? null, 'Invalid first stage date.');
        $lastEnd = $this->parseDate($timeline[count($timeline) - 1]['planned_end_date'] ?? null, 'Invalid last stage date.');

        if ($todayDate < $firstStart) {
            return [
                'status' => 'NOT_STARTED',
                'current_stage' => null,
                'current_stage_key' => null,
            ];
        }

        if ($todayDate > $lastEnd) {
            return [
                'status' => $isCompleted ? 'COMPLETED' : 'DELAYED',
                'current_stage' => $timeline[count($timeline) - 1]['stage'] ?? null,
                'current_stage_key' => $timeline[count($timeline) - 1]['stage_key'] ?? null,
            ];
        }

        foreach ($timeline as $item) {
            $start = $this->parseDate($item['planned_start_date'] ?? null, 'Invalid stage start date.');
            $end = $this->parseDate($item['planned_end_date'] ?? null, 'Invalid stage end date.');

            if ($todayDate >= $start && $todayDate <= $end) {
                return [
                    'status' => 'ACTIVE',
                    'current_stage' => $item['stage'],
                    'current_stage_key' => $item['stage_key'],
                ];
            }
        }

        return [
            'status' => 'ACTIVE',
            'current_stage' => $timeline[count($timeline) - 1]['stage'] ?? null,
            'current_stage_key' => $timeline[count($timeline) - 1]['stage_key'] ?? null,
        ];
    }

    public function validateActionForCurrentStage(array $timeline, $activityStageKey, $action, array $options = []) {
        $activityStageKey = (string)$activityStageKey;
        $isCompleted = !empty($options['is_completed']);
        $hasJustification = !empty($options['has_justification']);
        $today = $options['today'] ?? null;

        $resolved = $this->resolveCurrentStage($timeline, $today instanceof DateTimeInterface ? $today : null, $isCompleted);

        $result = [
            'allowed' => true,
            'status' => $resolved['status'],
            'current_stage' => $resolved['current_stage'],
            'current_stage_key' => $resolved['current_stage_key'],
            'message' => '',
        ];

        $enforcedActions = $this->config['enforced_actions'] ?? [];
        if (!in_array($action, $enforcedActions, true)) {
            return $result;
        }

        if ($resolved['status'] === 'NOT_STARTED') {
            $result['allowed'] = false;
            $result['message'] = 'Workflow has not started yet. Wait for the first procurement stage date.';
            return $result;
        }

        if ($resolved['status'] === 'COMPLETED') {
            $result['allowed'] = false;
            $result['message'] = 'Workflow is already completed.';
            return $result;
        }

        if ($resolved['status'] === 'DELAYED' && !$hasJustification && $action !== 'request_adjustment') {
            $result['allowed'] = false;
            $result['message'] = 'Workflow is delayed. Submit a justification before continuing.';
            return $result;
        }

        if (empty($activityStageKey)) {
            $result['allowed'] = false;
            $result['message'] = 'Unable to map this activity to a procurement stage.';
            return $result;
        }

        if (!empty($resolved['current_stage_key']) && $activityStageKey !== $resolved['current_stage_key']) {
            $result['allowed'] = false;
            $result['message'] = 'Action rejected. Current active stage is ' . $resolved['current_stage'] . '.';
            return $result;
        }

        return $result;
    }

    public function mapStepNameToStageKey($stepName) {
        $normalized = preg_replace('/[^a-z0-9]+/', '', strtolower((string)$stepName));

        $map = [
            'preparationofbiddingdocuments' => 'preparation_bidding_documents',
            'preparationbiddingdocuments' => 'preparation_bidding_documents',
            'preprocurementconference' => 'pre_procurement',
            'preprocurement' => 'pre_procurement',
            'advertisementandpostingofinvitationtobid' => 'posting_advertisement',
            'advertisementpostingofinvitationtobid' => 'posting_advertisement',
            'postingadvertisement' => 'posting_advertisement',
            'posting' => 'posting_advertisement',
            'advertisement' => 'posting_advertisement',
            'issuanceandavailabilityofbiddingdocuments' => 'issuance_bidding_documents',
            'prebidconference' => 'pre_bid_conference',
            'submissionandopeningofbids' => 'bid_submission_opening',
            'eligibilitycheckdeadlineofsubmissionandreceiptofbidsbidopening' => 'eligibility_submission_opening',
            'eligibilitycheck' => 'eligibility_submission_opening',
            'deadlineofsubmissionandreceiptofbids' => 'eligibility_submission_opening',
            'bidsubmissionandopening' => 'eligibility_submission_opening',
            'bidsubmissionopening' => 'bid_submission_opening',
            'bidopening' => 'bid_submission_opening',
            'bidevaluation' => 'bid_evaluation',
            'evaluation' => 'bid_evaluation',
            'postqualification' => 'post_qualification',
            'bacresolutionrecommendingaward' => 'bac_resolution_award',
            'bacresolution' => 'bac_resolution_award',
            'preparationandapprovalofresolutiontoaward' => 'resolution_to_award',
            'resolutiontoaward' => 'resolution_to_award',
            'noticeofawardpreparationandapproval' => 'noa_preparation_approval',
            'issuanceandsigningofnoticeofaward' => 'noa_issuance_signing',
            'noticeofawardissuance' => 'noa_issuance',
            'noticeofaward' => 'noa_issuance',
            'noticeofawardsigning' => 'noa_issuance_signing',
            'contractpreparationandsigning' => 'contract_preparation_signing',
            'contractpreparationandsigningofcontract' => 'contract_preparation_signing',
            'issuanceandsigningofnoticetoproceed' => 'notice_to_proceed',
            'noticetoproceed' => 'notice_to_proceed',
            'ntp' => 'notice_to_proceed',
            'implementation' => 'implementation',
            'deliveryandinspection' => 'delivery_inspection',
            'delivery' => 'delivery_inspection',
            'inspection' => 'delivery_inspection',
            'payment' => 'payment_processing',
            'paymentprocessing' => 'payment_processing',
            'preparationofpurchaserequest' => 'preparation_purchase_request',
            'purchaserequest' => 'preparation_purchase_request',
            'submissionandreceiptofapprovedpurchaserequest' => 'submission_receipt_approved_pr',
            'submissionandreceiptofapprovedpr' => 'submission_receipt_approved_pr',
            'approvedpurchaserequestsubmission' => 'submission_receipt_approved_pr',
            'preparationofrequestforquotationrfq' => 'preparation_rfq',
            'preparationofrequestforquotation' => 'preparation_rfq',
            'preparationrfq' => 'preparation_rfq',
            'rfqpreparation' => 'preparation_rfq',
            'postingofrfqorconductofcanvass' => 'posting_rfq_canvass',
            'postingofrfq' => 'posting_rfq_canvass',
            'conductofcanvass' => 'posting_rfq_canvass',
            'rfqposting' => 'posting_rfq_canvass',
            'openingofbidsdocumentspreparationofabstractofquotation' => 'opening_bids_abstract_quotation',
            'openingofbidsdocuments' => 'opening_bids_abstract_quotation',
            'preparationofabstractofquotation' => 'opening_bids_abstract_quotation',
            'abstractofquotation' => 'opening_bids_abstract_quotation',
            'preparationofabstractofquotationresolutiontoaward' => 'resolution_to_award',
            'abstractofquotationresolutiontoaward' => 'resolution_to_award',
            'preparationandapprovalofpurchaseorderpo' => 'preparation_approval_po',
            'preparationandapprovalofpurchaseorder' => 'preparation_approval_po',
            'purchaseorderpreparationapproval' => 'preparation_approval_po',
            'preparationandsigningofnoticetoproceed' => 'notice_to_proceed',
            'allowanceperiodofthesupplier' => 'allowance_period_supplier',
            'allowanceperiodsupplier' => 'allowance_period_supplier',
            'supplierallowanceperiod' => 'allowance_period_supplier',
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        foreach ($this->getFlattenedStages() as $stage) {
            if ($normalized === preg_replace('/[^a-z0-9]+/', '', strtolower($stage['name']))) {
                return $stage['key'];
            }
        }

        return null;
    }

    public function getStagePhase($stageKey, $procurementType = 'PUBLIC_BIDDING') {
        $workflow = $this->getValidatedWorkflow($procurementType);
        foreach ($workflow['backward_timeline_stages'] as $stage) {
            if ($stage['key'] === $stageKey) {
                return 'backward_timeline';
            }
        }
        foreach ($workflow['forward_execution_stages'] as $stage) {
            if ($stage['key'] === $stageKey) {
                return 'forward_execution';
            }
        }
        return null;
    }

    public function getExpectedStageCount($procurementType = 'PUBLIC_BIDDING') {
        $workflow = $this->getValidatedWorkflow($procurementType);
        return count($workflow['backward_timeline_stages']) + count($workflow['forward_execution_stages']);
    }

    private function getFlattenedStages($procurementType = 'PUBLIC_BIDDING') {
        $workflow = $this->getValidatedWorkflow($procurementType);
        return array_merge($workflow['backward_timeline_stages'], $workflow['forward_execution_stages']);
    }

    private function getValidatedWorkflow($procurementType = 'PUBLIC_BIDDING') {
        $workflows = $this->config['workflows'] ?? [];
        $workflow = $workflows[$procurementType] ?? ($workflows['PUBLIC_BIDDING'] ?? null);

        if (!$workflow || !is_array($workflow)) {
            throw new RuntimeException('Procurement workflow configuration is missing.');
        }

        $backward = $workflow['backward_timeline_stages'] ?? [];
        $forward = $workflow['forward_execution_stages'] ?? [];

        if (empty($backward) || empty($forward)) {
            throw new RuntimeException('Both backward and forward stage groups are required.');
        }

        $all = array_merge($backward, $forward);
        $seenKeys = [];

        foreach ($all as $index => $stage) {
            if (empty($stage['key']) || empty($stage['name'])) {
                throw new RuntimeException('Stage definition at index ' . $index . ' is invalid.');
            }
            if (isset($seenKeys[$stage['key']])) {
                throw new RuntimeException('Duplicate stage key found: ' . $stage['key']);
            }
            $seenKeys[$stage['key']] = true;
            if (!isset($stage['days']) || !is_numeric($stage['days']) || (int)$stage['days'] < 0) {
                throw new RuntimeException('Stage days for ' . $stage['name'] . ' must be a non-negative number.');
            }
        }

        return [
            'backward_timeline_stages' => array_values($backward),
            'forward_execution_stages' => array_values($forward),
        ];
    }

    private function parseDate($date, $errorMessage) {
        if (empty($date)) {
            throw new RuntimeException($errorMessage);
        }

        try {
            return new DateTimeImmutable((string)$date);
        } catch (Exception $e) {
            throw new RuntimeException($errorMessage);
        }
    }
}
