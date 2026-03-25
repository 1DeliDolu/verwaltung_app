INSERT INTO users (name, email, password_hash, role_id)
VALUES
    (
        'Ines Leiter',
        'leiter.it@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Hanna Personal',
        'leiter.hr@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Olaf Operations',
        'leiter.operations@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Mara Marketing',
        'leiter.marketing@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Selim Sales',
        'leiter.sales@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Rana Forschung',
        'leiter.research-development@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Fiona Finanzen',
        'leiter.finance-accounting@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Lara Recht',
        'leiter.legal-compliance@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Cem Support',
        'leiter.customer-service-support@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Quentin Qualitaet',
        'leiter.quality-management@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Pia Delivery',
        'leiter.production-service-delivery@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Samuel Supply',
        'leiter.supply-chain-logistics-procurement@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Greta Management',
        'leiter.general-management@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'team_leader')
    ),
    (
        'Emre Mitarbeiter',
        'mitarbeiter.it@verwaltung.local',
        '$2y$12$Sb/qL.Uj4SLpPEfpitmnr.je.kAjtrob3YZeB0FG2CaFt0aWWu8hK',
        (SELECT id FROM roles WHERE name = 'employee')
    )
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password_hash = VALUES(password_hash),
    role_id = VALUES(role_id);

UPDATE users
SET email_verified_at = NULL,
    email_verification_token = NULL,
    email_verification_sent_at = NULL
WHERE email IN (
    'leiter.it@verwaltung.local',
    'leiter.hr@verwaltung.local',
    'leiter.operations@verwaltung.local',
    'leiter.marketing@verwaltung.local',
    'leiter.sales@verwaltung.local',
    'leiter.research-development@verwaltung.local',
    'leiter.finance-accounting@verwaltung.local',
    'leiter.legal-compliance@verwaltung.local',
    'leiter.customer-service-support@verwaltung.local',
    'leiter.quality-management@verwaltung.local',
    'leiter.production-service-delivery@verwaltung.local',
    'leiter.supply-chain-logistics-procurement@verwaltung.local',
    'leiter.general-management@verwaltung.local',
    'mitarbeiter.it@verwaltung.local'
);
