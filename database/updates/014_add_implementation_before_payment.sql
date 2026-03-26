-- Add "Implementation" step before "Payment" for procurement templates
-- where "Delivery and Inspection" exists.

START TRANSACTION;

-- Shift Payment one slot forward for templates that need
-- the new Implementation step and do not have it yet.
UPDATE timeline_templates tt
INNER JOIN (
    SELECT procurement_type, MIN(step_order) AS payment_order
    FROM timeline_templates
    WHERE step_name = 'Payment'
    GROUP BY procurement_type
) p ON p.procurement_type = tt.procurement_type
LEFT JOIN timeline_templates impl
    ON impl.procurement_type = tt.procurement_type
   AND impl.step_name = 'Implementation'
SET tt.step_order = tt.step_order + 1
WHERE impl.id IS NULL
  AND tt.step_name = 'Payment'
  AND tt.step_order = p.payment_order
  AND EXISTS (
      SELECT 1
      FROM timeline_templates di
      WHERE di.procurement_type = tt.procurement_type
        AND di.step_name = 'Delivery and Inspection'
  );

-- Insert the new Implementation step at the old Payment order.
INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
SELECT p.procurement_type, 'Implementation', p.payment_order, 1
FROM (
    SELECT procurement_type, MIN(step_order) AS payment_order
    FROM timeline_templates
    WHERE step_name = 'Payment'
    GROUP BY procurement_type
) p
WHERE EXISTS (
    SELECT 1
    FROM timeline_templates di
    WHERE di.procurement_type = p.procurement_type
      AND di.step_name = 'Delivery and Inspection'
)
AND NOT EXISTS (
    SELECT 1
    FROM timeline_templates impl
    WHERE impl.procurement_type = p.procurement_type
      AND impl.step_name = 'Implementation'
);

COMMIT;
