-- Repair migration for Implementation step positioning before Payment.

START TRANSACTION;

-- If both steps exist but Implementation is not before Payment,
-- move Implementation to one step before Payment.
UPDATE timeline_templates impl
INNER JOIN timeline_templates pay
    ON pay.procurement_type = impl.procurement_type
   AND pay.step_name = 'Payment'
SET impl.step_order = pay.step_order - 1
WHERE impl.step_name = 'Implementation'
  AND impl.step_order >= pay.step_order;

-- If Delivery and Inspection + Payment exist but Implementation is missing,
-- insert Implementation at one step before Payment.
INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days)
SELECT pay.procurement_type, 'Implementation', pay.step_order - 1, 1
FROM timeline_templates pay
WHERE pay.step_name = 'Payment'
  AND EXISTS (
      SELECT 1
      FROM timeline_templates di
      WHERE di.procurement_type = pay.procurement_type
        AND di.step_name = 'Delivery and Inspection'
  )
  AND NOT EXISTS (
      SELECT 1
      FROM timeline_templates impl
      WHERE impl.procurement_type = pay.procurement_type
        AND impl.step_name = 'Implementation'
  );

COMMIT;
