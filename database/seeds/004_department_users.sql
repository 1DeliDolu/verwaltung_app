INSERT INTO users (name, email, password_hash, role_id)
VALUES
    (
        'Ines Leiter',
        'leiter.it@verwaltung.local',
        '$2y$12$0sp4wmBXC9GuF5lHhJyu1OR41AroedUHNW.5DuTnXjmYxyLdTIE/6',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Emre Mitarbeiter',
        'mitarbeiter.it@verwaltung.local',
        '$2y$12$0sp4wmBXC9GuF5lHhJyu1OR41AroedUHNW.5DuTnXjmYxyLdTIE/6',
        (SELECT id FROM roles WHERE name = 'employee')
    )
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password_hash = VALUES(password_hash),
    role_id = VALUES(role_id);
