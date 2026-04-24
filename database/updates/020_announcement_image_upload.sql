-- Add image support for announcements (idempotent)
SET @table_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'announcements'
);

SET @column_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'announcements'
      AND COLUMN_NAME = 'image_url'
);

SET @sql := IF(
    @table_exists = 1 AND @column_exists = 0,
    'ALTER TABLE announcements ADD COLUMN image_url VARCHAR(500) NULL AFTER link_url',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
