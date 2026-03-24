INSERT INTO department_user (department_id, user_id, membership_role)
VALUES
    (
        (SELECT id FROM departments WHERE slug = 'it'),
        (SELECT id FROM users WHERE email = 'leiter.it@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'hr'),
        (SELECT id FROM users WHERE email = 'leiter.hr@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'it'),
        (SELECT id FROM users WHERE email = 'mitarbeiter.it@verwaltung.local'),
        'employee'
    )
ON DUPLICATE KEY UPDATE
    membership_role = VALUES(membership_role);
