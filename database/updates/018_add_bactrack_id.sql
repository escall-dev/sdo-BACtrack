-- Add persistent BACTrack ID to projects
-- Format target: BT[3 alnum]-YYYYMM-[001..999], generated in application layer.

ALTER TABLE projects
    ADD COLUMN bactrack_id VARCHAR(32) NULL AFTER title;

CREATE UNIQUE INDEX uq_projects_bactrack_id ON projects (bactrack_id);
