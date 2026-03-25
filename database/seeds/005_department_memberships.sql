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
        (SELECT id FROM departments WHERE slug = 'operations'),
        (SELECT id FROM users WHERE email = 'leiter.operations@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'marketing'),
        (SELECT id FROM users WHERE email = 'leiter.marketing@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'sales'),
        (SELECT id FROM users WHERE email = 'leiter.sales@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'research-development'),
        (SELECT id FROM users WHERE email = 'leiter.research-development@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'finance-accounting'),
        (SELECT id FROM users WHERE email = 'leiter.finance-accounting@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'legal-compliance'),
        (SELECT id FROM users WHERE email = 'leiter.legal-compliance@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'customer-service-support'),
        (SELECT id FROM users WHERE email = 'leiter.customer-service-support@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'quality-management'),
        (SELECT id FROM users WHERE email = 'leiter.quality-management@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'production-service-delivery'),
        (SELECT id FROM users WHERE email = 'leiter.production-service-delivery@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'supply-chain-logistics-procurement'),
        (SELECT id FROM users WHERE email = 'leiter.supply-chain-logistics-procurement@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'general-management'),
        (SELECT id FROM users WHERE email = 'leiter.general-management@verwaltung.local'),
        'team_leader'
    ),
    (
        (SELECT id FROM departments WHERE slug = 'it'),
        (SELECT id FROM users WHERE email = 'mitarbeiter.it@verwaltung.local'),
        'employee'
    )
ON DUPLICATE KEY UPDATE
    membership_role = VALUES(membership_role);
