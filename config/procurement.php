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
                'COMPETITIVE_BIDDING' => [
                    // Annex A (Competitive Bidding) stages before implementation date.
                    'backward_timeline_stages' => [
                        ['key' => 'preparation_bidding_documents', 'name' => 'Preparation of Bidding Documents', 'days' => 1],
                        ['key' => 'pre_procurement', 'name' => 'Pre-Procurement Conference', 'days' => 1],
                        ['key' => 'posting_advertisement', 'name' => 'Advertisement / Posting of Invitation to Bid', 'days' => 7],
                        ['key' => 'pre_bid_conference', 'name' => 'Pre-Bid Conference', 'days' => 12],
                        ['key' => 'eligibility_submission_opening', 'name' => 'Eligibility Check / Deadline of Submission and Receipt of Bids / Bid Opening', 'days' => 1],
                        ['key' => 'bid_evaluation', 'name' => 'Bid Evaluation', 'days' => 1],
                        ['key' => 'post_qualification', 'name' => 'Post-Qualification', 'days' => 12],
                        ['key' => 'resolution_to_award', 'name' => 'Preparation and Approval of Resolution to Award', 'days' => 11],
                        ['key' => 'noa_issuance_signing', 'name' => 'Issuance and Signing of Notice of Award', 'days' => 1],
                        ['key' => 'contract_preparation_signing', 'name' => 'Contract Preparation and Signing of Contract', 'days' => 11],
                        ['key' => 'notice_to_proceed', 'name' => 'Issuance and Signing of Notice to Proceed', 'days' => 1],
                    ],

                    // Kept consistent with existing project execution behavior.
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
                'SMALL_VALUE_PROCUREMENT' => [
                    // Annex B (SVP 200k & below) stages before implementation date.
                    'backward_timeline_stages' => [
                        ['key' => 'preparation_purchase_request', 'name' => 'Preparation of Purchase Request', 'days' => 0],
                        ['key' => 'submission_receipt_approved_pr', 'name' => 'submission of complete and approved procurement requirements.', 'days' => 1],
                        ['key' => 'preparation_rfq', 'name' => 'Preparation of Request for Quotation (RFQ)', 'days' => 4],
                        ['key' => 'posting_rfq_canvass', 'name' => 'Posting of RFQ or Conduct of Canvass', 'days' => 3],
                        ['key' => 'opening_bids_abstract_quotation', 'name' => 'Opening of bids documents / Preparation of Abstract of Quotation', 'days' => 1],
                        ['key' => 'preparation_approval_po', 'name' => 'Preparation and Approval of Purchase Order (PO)', 'days' => 4],
                        ['key' => 'allowance_period_supplier', 'name' => 'Allowance period of the supplier', 'days' => 10],
                    ],

                    // Kept consistent with existing project execution behavior.
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
                'SMALL_VALUE_PROCUREMENT_200K' => [
                    // Annex B (SVP 200k & above) stages before implementation date.
                    'backward_timeline_stages' => [
                        ['key' => 'preparation_purchase_request', 'name' => 'Preparation of Purchase Request', 'days' => 0],
                        ['key' => 'submission_receipt_approved_pr', 'name' => 'submission of complete and approved procurement requirements.', 'days' => 1],
                        ['key' => 'preparation_rfq', 'name' => 'Preparation of Request for Quotation (RFQ)', 'days' => 4],
                        ['key' => 'posting_rfq_canvass', 'name' => 'Posting of RFQ or Conduct of Canvass', 'days' => 3],
                        ['key' => 'resolution_to_award', 'name' => 'Preparation of Abstract of Quotation / Resolution to Award', 'days' => 4],
                        ['key' => 'noa_issuance', 'name' => 'Notice of Award', 'days' => 2],
                        ['key' => 'preparation_approval_po', 'name' => 'Preparation and Approval of Purchase Order (PO)', 'days' => 4],
                        ['key' => 'notice_to_proceed', 'name' => 'Preparation and Signing of Notice to Proceed', 'days' => 2],
                        ['key' => 'allowance_period_supplier', 'name' => 'Allowance period of the supplier', 'days' => 10],
                    ],

                    // Kept consistent with existing project execution behavior.
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
                'COMPETITIVE_DIALOGUE' => [
                    'backward_timeline_stages' => [
                        ['key' => 'invitation_pre_qualification', 'name' => 'Invitation and Pre-qualification', 'days' => 75],
                        ['key' => 'dialogue_stage', 'name' => 'Dialogue Stage', 'days' => 15],
                        ['key' => 'submission_final_proposals', 'name' => 'Submission of Final Proposals', 'days' => 20],
                    ],
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
                'UNSOLICITED_OFFER' => [
                    'backward_timeline_stages' => [
                        ['key' => 'preassessment_of_proposal', 'name' => 'Pre-assessment of Proposal', 'days' => 20],
                        ['key' => 'submission_initial_offer', 'name' => 'Submission of Initial Offer', 'days' => 30],
                        ['key' => 'detailed_offer_evaluation', 'name' => 'Detailed Offer Evaluation', 'days' => 60],
                        ['key' => 'negotiation_of_terms', 'name' => 'Negotiation of Terms', 'days' => 1],
                        ['key' => 'comparative_bid_matching', 'name' => 'Comparative Bid Matching', 'days' => 15],
                    ],
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
                'DIRECT_SALES' => [
                    'backward_timeline_stages' => [
                        ['key' => 'issuance_of_request_dsr', 'name' => 'Issuance of Request (DSR)', 'days' => 180],
                        ['key' => 'supplier_written_acceptance', 'name' => 'Supplier Written Acceptance', 'days' => 5],
                    ],
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
                'LIMITED_SOURCE_BIDDING' => [
                    'backward_timeline_stages' => [
                        ['key' => 'direct_invitation_to_list', 'name' => 'Direct Invitation to List', 'days' => 7],
                        ['key' => 'bid_evaluation_and_award', 'name' => 'Bid Evaluation and Award', 'days' => 23],
                    ],
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
                'DIRECT_CONTRACTING' => [
                    'backward_timeline_stages' => [
                        ['key' => 'request_for_quotation', 'name' => 'Request for Quotation', 'days' => 1],
                        ['key' => 'evaluation_and_negotiation', 'name' => 'Evaluation and Negotiation', 'days' => 1],
                    ],
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
                'DIRECT_ACQUISITION' => [
                    'backward_timeline_stages' => [
                        ['key' => 'market_identification_p200k', 'name' => 'Market Identification (≤ ₱200K)', 'days' => 1],
                        ['key' => 'direct_purchase_and_recording', 'name' => 'Direct Purchase and Recording', 'days' => 1],
                    ],
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
                'REPEAT_ORDER' => [
                    'backward_timeline_stages' => [
                        ['key' => 'determination_of_need', 'name' => 'Determination of Need', 'days' => 180],
                        ['key' => 'bac_recommendation', 'name' => 'BAC Recommendation', 'days' => 1],
                    ],
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
                'NEGOTIATED_PROCUREMENT' => [
                    'backward_timeline_stages' => [
                        ['key' => 'two_failed_biddings_review', 'name' => 'Two Failed Biddings / Review', 'days' => 1],
                        ['key' => 'submission_best_offer', 'name' => 'Submission of Best Offer', 'days' => 1],
                    ],
                    'forward_execution_stages' => [
                        ['key' => 'implementation', 'name' => 'Implementation', 'days' => 1],
                        ['key' => 'delivery_inspection', 'name' => 'Delivery and Inspection', 'days' => 1],
                        ['key' => 'payment_processing', 'name' => 'Payment Processing', 'days' => 1],
                    ],
                ],
                'CONSULTING_SERVICES' => [
                    'backward_timeline_stages' => [
                        ['key' => 'shortlisting_phase', 'name' => 'Shortlisting Phase', 'days' => 20],
                    ],
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
