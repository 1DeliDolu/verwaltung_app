INSERT INTO roles (name)
VALUES
    ('admin'),
    ('team_leader'),
    ('employee')
ON DUPLICATE KEY UPDATE
    name = VALUES(name);
