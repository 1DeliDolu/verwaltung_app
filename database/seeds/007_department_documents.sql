INSERT INTO department_documents (
    department_id,
    folder_name,
    title,
    body,
    created_by,
    updated_by
)
VALUES
    (
        (SELECT id FROM departments WHERE slug = 'it'),
        'mail-server',
        'Mailserver-Konzept',
        'Dieser Ordner enthaelt das Konzept fuer interne E-Mail-Konten, Team-Postfaecher und Zugriffsregeln.',
        (SELECT id FROM users WHERE email = 'leiter.it@verwaltung.local'),
        (SELECT id FROM users WHERE email = 'leiter.it@verwaltung.local')
    ),
    (
        (SELECT id FROM departments WHERE slug = 'it'),
        'file-server',
        'Dateiserver-Richtlinie',
        'Mitarbeiter duerfen freigegebene Dokumente lesen. Teamleiter pflegen Ordnerstruktur und Inhalte.',
        (SELECT id FROM users WHERE email = 'leiter.it@verwaltung.local'),
        (SELECT id FROM users WHERE email = 'leiter.it@verwaltung.local')
    )
ON DUPLICATE KEY UPDATE
    body = VALUES(body),
    updated_by = VALUES(updated_by);
