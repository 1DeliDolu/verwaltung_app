INSERT INTO departments (name, slug, description)
VALUES
    ('IT', 'it', 'IT betreut interne Systeme, Infrastruktur und technische Freigaben.'),
    ('HR', 'hr', 'HR verwaltet Personalprozesse und interne Richtlinien.'),
    ('Operations', 'operations', 'Operations koordiniert den taeglichen Betriebsablauf.')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description);
