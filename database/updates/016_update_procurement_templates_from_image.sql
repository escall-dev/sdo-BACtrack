-- Update procurement-type timeline templates to match the phasing/durations
-- shown in the provided image.
--
-- Note: This aligns the project-create template preview with the timeline
-- generator in `config/procurement.php` (which is anchored on implementation date).

START TRANSACTION;

DELETE FROM timeline_templates
WHERE procurement_type IN (
  'COMPETITIVE_DIALOGUE',
  'UNSOLICITED_OFFER',
  'DIRECT_SALES',
  'LIMITED_SOURCE_BIDDING',
  'DIRECT_CONTRACTING',
  'DIRECT_ACQUISITION',
  'REPEAT_ORDER',
  'NEGOTIATED_PROCUREMENT',
  'CONSULTING_SERVICES'
);

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
VALUES
  -- Competitive Dialogue
  ('COMPETITIVE_DIALOGUE', 'Invitation and Pre-qualification', 1, 75),
  ('COMPETITIVE_DIALOGUE', 'Dialogue Stage', 2, 15),
  ('COMPETITIVE_DIALOGUE', 'Submission of Final Proposals', 3, 20),
  ('COMPETITIVE_DIALOGUE', 'Implementation', 4, 1),
  ('COMPETITIVE_DIALOGUE', 'Delivery and Inspection', 5, 1),
  ('COMPETITIVE_DIALOGUE', 'Payment Processing', 6, 1),

  -- Unsolicited Offer with Bid Matching
  ('UNSOLICITED_OFFER', 'Pre-assessment of Proposal', 1, 20),
  ('UNSOLICITED_OFFER', 'Submission of Initial Offer', 2, 30),
  ('UNSOLICITED_OFFER', 'Detailed Offer Evaluation', 3, 60),
  ('UNSOLICITED_OFFER', 'Negotiation of Terms', 4, 1),
  ('UNSOLICITED_OFFER', 'Comparative Bid Matching', 5, 15),
  ('UNSOLICITED_OFFER', 'Implementation', 6, 1),
  ('UNSOLICITED_OFFER', 'Delivery and Inspection', 7, 1),
  ('UNSOLICITED_OFFER', 'Payment Processing', 8, 1),

  -- Direct Sales
  ('DIRECT_SALES', 'Issuance of Request (DSR)', 1, 180),
  ('DIRECT_SALES', 'Supplier Written Acceptance', 2, 5),
  ('DIRECT_SALES', 'Implementation', 3, 1),
  ('DIRECT_SALES', 'Delivery and Inspection', 4, 1),
  ('DIRECT_SALES', 'Payment Processing', 5, 1),

  -- Limited Source Bidding (Selective Bidding)
  ('LIMITED_SOURCE_BIDDING', 'Direct Invitation to List', 1, 7),
  ('LIMITED_SOURCE_BIDDING', 'Bid Evaluation and Award', 2, 23),
  ('LIMITED_SOURCE_BIDDING', 'Implementation', 3, 1),
  ('LIMITED_SOURCE_BIDDING', 'Delivery and Inspection', 4, 1),
  ('LIMITED_SOURCE_BIDDING', 'Payment Processing', 5, 1),

  -- Direct Contracting (Single Source Procurement)
  ('DIRECT_CONTRACTING', 'Request for Quotation', 1, 1),
  ('DIRECT_CONTRACTING', 'Evaluation and Negotiation', 2, 1),
  ('DIRECT_CONTRACTING', 'Implementation', 3, 1),
  ('DIRECT_CONTRACTING', 'Delivery and Inspection', 4, 1),
  ('DIRECT_CONTRACTING', 'Payment Processing', 5, 1),

  -- Direct Acquisition
  ('DIRECT_ACQUISITION', 'Market Identification (≤ ₱200K)', 1, 1),
  ('DIRECT_ACQUISITION', 'Direct Purchase and Recording', 2, 1),
  ('DIRECT_ACQUISITION', 'Implementation', 3, 1),
  ('DIRECT_ACQUISITION', 'Delivery and Inspection', 4, 1),
  ('DIRECT_ACQUISITION', 'Payment Processing', 5, 1),

  -- Repeat Order
  ('REPEAT_ORDER', 'Determination of Need', 1, 180),
  ('REPEAT_ORDER', 'BAC Recommendation', 2, 1),
  ('REPEAT_ORDER', 'Implementation', 3, 1),
  ('REPEAT_ORDER', 'Delivery and Inspection', 4, 1),
  ('REPEAT_ORDER', 'Payment Processing', 5, 1),

  -- Negotiated Procurement
  ('NEGOTIATED_PROCUREMENT', 'Two Failed Biddings / Review', 1, 1),
  ('NEGOTIATED_PROCUREMENT', 'Submission of Best Offer', 2, 1),
  ('NEGOTIATED_PROCUREMENT', 'Implementation', 3, 1),
  ('NEGOTIATED_PROCUREMENT', 'Delivery and Inspection', 4, 1),
  ('NEGOTIATED_PROCUREMENT', 'Payment Processing', 5, 1),

  -- Consulting Services
  ('CONSULTING_SERVICES', 'Shortlisting Phase', 1, 20),
  ('CONSULTING_SERVICES', 'Implementation', 2, 1),
  ('CONSULTING_SERVICES', 'Delivery and Inspection', 3, 1),
  ('CONSULTING_SERVICES', 'Payment Processing', 4, 1);

COMMIT;

