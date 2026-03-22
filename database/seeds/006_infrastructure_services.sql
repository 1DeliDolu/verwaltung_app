INSERT INTO infrastructure_services (
    name,
    service_type,
    host_name,
    status,
    access_level,
    description,
    managed_by_department_id
)
VALUES
    (
        'Interner Mail Server',
        'mail',
        'mail.verwaltung.local',
        'planned',
        'Nur interne Benutzer mit Firmenkonto',
        'Zentraler Maildienst fuer Team-Postfaecher und interne Kommunikation.',
        (SELECT id FROM departments WHERE slug = 'it')
    ),
    (
        'Abteilungs-Dateiserver',
        'file',
        'files.verwaltung.local',
        'active',
        'Abteilungsbezogener Zugriff nach Rolle',
        'Dateifreigaben fuer Richtlinien, Arbeitsanweisungen und Vorlagen.',
        (SELECT id FROM departments WHERE slug = 'it')
    )
ON DUPLICATE KEY UPDATE
    host_name = VALUES(host_name),
    status = VALUES(status),
    access_level = VALUES(access_level),
    description = VALUES(description),
    managed_by_department_id = VALUES(managed_by_department_id);
