-- Update SVP Step 2 wording (<=200K and >200K) to the approved label.
-- Applies to:
-- - timeline_templates (template rows)
-- - project_activities (existing project timelines)

START TRANSACTION;

-- Update templates for both SVP types (Step 2 only).
UPDATE timeline_templates
SET step_name = 'submission of complete and approved procurement requirements.'
WHERE procurement_type IN ('SMALL_VALUE_PROCUREMENT', 'SMALL_VALUE_PROCUREMENT_200K')
  AND step_order = 2;

-- Update existing project activities for both SVP types (Step 2 only).
UPDATE project_activities pa
JOIN bac_cycles bc ON bc.id = pa.bac_cycle_id
JOIN projects p ON p.id = bc.project_id
SET pa.step_name = 'submission of complete and approved procurement requirements.'
WHERE p.procurement_type IN ('SMALL_VALUE_PROCUREMENT', 'SMALL_VALUE_PROCUREMENT_200K')
  AND pa.step_order = 2
  AND pa.step_name IN (
    'Submission and Receipt of Approved Purchase Request',
    'Submission and Receipt of Approved PR'
  );

COMMIT;
