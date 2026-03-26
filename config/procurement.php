<?php
/**
 * Procurement timeline configuration (RA 12009 backward scheduling)
 */

if (!function_exists('procurementConfig')) {
    function procurementConfig() {
        static $config = null;

        if ($config !== null) {
            return $config;
        }

        $config = [
            'workflows' => [
                'PUBLIC_BIDDING' => [
                    // Computed backward from implementation date.
                    'backward_timeline_stages' => [
                        ['key' => 'pre_procurement', 'name' => 'Pre-Procurement Conference', 'days' => 3],
                        ['key' => 'posting_advertisement', 'name' => 'Advertisement and Posting of Invitation to Bid', 'days' => 10],
                        ['key' => 'issuance_bidding_documents', 'name' => 'Issuance and Availability of Bidding Documents', 'days' => 7],
                        ['key' => 'pre_bid_conference', 'name' => 'Pre-Bid Conference', 'days' => 1],
                        ['key' => 'bid_submission_opening', 'name' => 'Submission and Opening of Bids', 'days' => 1],
                        ['key' => 'bid_evaluation', 'name' => 'Bid Evaluation', 'days' => 7],
                        ['key' => 'post_qualification', 'name' => 'Post-Qualification', 'days' => 7],
                        ['key' => 'bac_resolution_award', 'name' => 'BAC Resolution Recommending Award', 'days' => 1],
                        ['key' => 'noa_preparation_approval', 'name' => 'Notice of Award Preparation and Approval', 'days' => 2],
                        ['key' => 'noa_issuance', 'name' => 'Notice of Award Issuance', 'days' => 1],
                        ['key' => 'contract_preparation_signing', 'name' => 'Contract Preparation and Signing', 'days' => 3],
                        ['key' => 'notice_to_proceed', 'name' => 'Notice to Proceed', 'days' => 2],
                    ],

                    // Starts at implementation date and runs forward.
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
            ],

            'enforced_actions' => [
                'update_status',
                'set_compliance',
                'upload_document',
                'request_adjustment',
            ],
        ];

        return $config;
    }
}
