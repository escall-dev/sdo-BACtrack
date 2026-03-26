-- Align SMALL_VALUE_PROCUREMENT and SMALL_VALUE_PROCUREMENT_200K timeline templates
-- with the process flow charts shown in project view.
-- Run this once on existing databases.

START TRANSACTION;

DELETE FROM timeline_templates
WHERE procurement_type IN ('SMALL_VALUE_PROCUREMENT', 'SMALL_VALUE_PROCUREMENT_200K');

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
VALUES
('SMALL_VALUE_PROCUREMENT', 'Preparation of Purchase Request', 1, 1),
('SMALL_VALUE_PROCUREMENT', 'Submission and Receipt of Approved Purchase Request', 2, 1),
('SMALL_VALUE_PROCUREMENT', 'Preparation of Request for Quotation (RFQ)', 3, 3),
('SMALL_VALUE_PROCUREMENT', 'Posting of RFQ or Conduct of Canvass', 4, 3),
('SMALL_VALUE_PROCUREMENT', 'Opening of bids documents / Preparation of Abstract of Quotation', 5, 1),
('SMALL_VALUE_PROCUREMENT', 'Preparation and Approval of Purchase Order (PO)', 6, 4),
('SMALL_VALUE_PROCUREMENT', 'Allowance period of the supplier', 7, 10),
('SMALL_VALUE_PROCUREMENT_200K', 'Preparation of Purchase Request', 1, 1),
('SMALL_VALUE_PROCUREMENT_200K', 'Submission and Receipt of Approved PR', 2, 1),
('SMALL_VALUE_PROCUREMENT_200K', 'Preparation of Request for Quotation (RFQ)', 3, 4),
('SMALL_VALUE_PROCUREMENT_200K', 'Posting of RFQ or Conduct of Canvass', 4, 3),
('SMALL_VALUE_PROCUREMENT_200K', 'Preparation of Abstract of Quotation / Resolution to Award', 5, 3),
('SMALL_VALUE_PROCUREMENT_200K', 'Notice of Award', 6, 2),
('SMALL_VALUE_PROCUREMENT_200K', 'Preparation and Approval of Purchase Order (PO)', 7, 4),
('SMALL_VALUE_PROCUREMENT_200K', 'Preparation and Signing of Notice to Proceed', 8, 2),
('SMALL_VALUE_PROCUREMENT_200K', 'Allowance period of the supplier', 9, 10);

COMMIT;
