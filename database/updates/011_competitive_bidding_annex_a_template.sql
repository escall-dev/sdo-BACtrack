-- Align COMPETITIVE_BIDDING timeline template with Annex A process flow.
-- Run this once on existing databases.

START TRANSACTION;

DELETE FROM timeline_templates
WHERE procurement_type = 'COMPETITIVE_BIDDING';

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
VALUES
('COMPETITIVE_BIDDING', 'Preparation of Bidding Documents', 1, 1),
('COMPETITIVE_BIDDING', 'Pre-Procurement Conference', 2, 1),
('COMPETITIVE_BIDDING', 'Advertisement / Posting of Invitation to Bid', 3, 7),
('COMPETITIVE_BIDDING', 'Pre-Bid Conference', 4, 12),
('COMPETITIVE_BIDDING', 'Eligibility Check / Deadline of Submission and Receipt of Bids / Bid Opening', 5, 1),
('COMPETITIVE_BIDDING', 'Bid Evaluation', 6, 1),
('COMPETITIVE_BIDDING', 'Post-Qualification', 7, 12),
('COMPETITIVE_BIDDING', 'Preparation and Approval of Resolution to Award', 8, 11),
('COMPETITIVE_BIDDING', 'Issuance and Signing of Notice of Award', 9, 1),
('COMPETITIVE_BIDDING', 'Contract Preparation and Signing of Contract', 10, 11),
('COMPETITIVE_BIDDING', 'Issuance and Signing of Notice to Proceed', 11, 1);

COMMIT;
 