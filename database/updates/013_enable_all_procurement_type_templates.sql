-- Ensure every procurement type has timeline template rows.
-- Missing types will inherit the PUBLIC_BIDDING step structure.

START TRANSACTION;

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
SELECT 'LIMITED_SOURCE_BIDDING', step_name, step_order, default_duration_days
FROM timeline_templates
WHERE procurement_type = 'PUBLIC_BIDDING'
  AND NOT EXISTS (
    SELECT 1 FROM timeline_templates WHERE procurement_type = 'LIMITED_SOURCE_BIDDING'
  );

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
SELECT 'COMPETITIVE_DIALOGUE', step_name, step_order, default_duration_days
FROM timeline_templates
WHERE procurement_type = 'PUBLIC_BIDDING'
  AND NOT EXISTS (
    SELECT 1 FROM timeline_templates WHERE procurement_type = 'COMPETITIVE_DIALOGUE'
  );

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
SELECT 'UNSOLICITED_OFFER', step_name, step_order, default_duration_days
FROM timeline_templates
WHERE procurement_type = 'PUBLIC_BIDDING'
  AND NOT EXISTS (
    SELECT 1 FROM timeline_templates WHERE procurement_type = 'UNSOLICITED_OFFER'
  );

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
SELECT 'DIRECT_CONTRACTING', step_name, step_order, default_duration_days
FROM timeline_templates
WHERE procurement_type = 'PUBLIC_BIDDING'
  AND NOT EXISTS (
    SELECT 1 FROM timeline_templates WHERE procurement_type = 'DIRECT_CONTRACTING'
  );

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
SELECT 'DIRECT_ACQUISITION', step_name, step_order, default_duration_days
FROM timeline_templates
WHERE procurement_type = 'PUBLIC_BIDDING'
  AND NOT EXISTS (
    SELECT 1 FROM timeline_templates WHERE procurement_type = 'DIRECT_ACQUISITION'
  );

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
SELECT 'REPEAT_ORDER', step_name, step_order, default_duration_days
FROM timeline_templates
WHERE procurement_type = 'PUBLIC_BIDDING'
  AND NOT EXISTS (
    SELECT 1 FROM timeline_templates WHERE procurement_type = 'REPEAT_ORDER'
  );

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
SELECT 'NEGOTIATED_PROCUREMENT', step_name, step_order, default_duration_days
FROM timeline_templates
WHERE procurement_type = 'PUBLIC_BIDDING'
  AND NOT EXISTS (
    SELECT 1 FROM timeline_templates WHERE procurement_type = 'NEGOTIATED_PROCUREMENT'
  );

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
SELECT 'DIRECT_SALES', step_name, step_order, default_duration_days
FROM timeline_templates
WHERE procurement_type = 'PUBLIC_BIDDING'
  AND NOT EXISTS (
    SELECT 1 FROM timeline_templates WHERE procurement_type = 'DIRECT_SALES'
  );

INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
SELECT 'DIRECT_PROCUREMENT_STI', step_name, step_order, default_duration_days
FROM timeline_templates
WHERE procurement_type = 'PUBLIC_BIDDING'
  AND NOT EXISTS (
    SELECT 1 FROM timeline_templates WHERE procurement_type = 'DIRECT_PROCUREMENT_STI'
  );

COMMIT;
