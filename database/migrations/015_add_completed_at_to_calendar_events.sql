ALTER TABLE calendar_events
    ADD COLUMN completed_at DATETIME NULL AFTER ends_at;
