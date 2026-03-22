INSERT INTO users (name, email, password_hash)
VALUES (
    'Admin User',
    'admin@verwaltung.local',
    '$2y$12$0sp4wmBXC9GuF5lHhJyu1OR41AroedUHNW.5DuTnXjmYxyLdTIE/6'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password_hash = VALUES(password_hash);
