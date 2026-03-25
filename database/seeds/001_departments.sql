INSERT INTO departments (name, slug, description)
VALUES
    ('IT', 'it', 'IT betreut interne Systeme, Infrastruktur und technische Freigaben.'),
    ('HR', 'hr', 'HR verwaltet Personalprozesse und interne Richtlinien.'),
    ('Operations', 'operations', 'Operations koordiniert den taeglichen Betriebsablauf.'),
    ('Marketing', 'marketing', 'Marketing steuert Kampagnen, Inhalte und interne Freigaben.'),
    ('Sales', 'sales', 'Sales koordiniert Angebote, Abschluesse und vertriebsnahe Unterlagen.'),
    ('Research & Development', 'research-development', 'R&D dokumentiert Ideen, Validierung und technische Weiterentwicklung.'),
    ('Finance & Accounting', 'finance-accounting', 'Finance & Accounting verantwortet Reports, Abschluesse und kaufmaennische Nachweise.'),
    ('Legal & Compliance', 'legal-compliance', 'Legal & Compliance sichert Richtlinien, Vertrage und regulatorische Anforderungen ab.'),
    ('Customer Service & Support', 'customer-service-support', 'Customer Service & Support pflegt Servicequalitaet, Loesungen und Uebergaben.'),
    ('Quality Management', 'quality-management', 'Quality Management steuert Standards, Audits und kontinuierliche Verbesserungen.'),
    ('Production & Service Delivery', 'production-service-delivery', 'Production & Service Delivery verantwortet stabile Leistungserbringung und Auslieferung.'),
    ('Supply Chain, Logistics & Procurement', 'supply-chain-logistics-procurement', 'Supply Chain, Logistics & Procurement koordiniert Beschaffung, Materialfluss und Verfuegbarkeit.'),
    ('General Management', 'general-management', 'General Management priorisiert bereichsuebergreifende Entscheidungen und Unternehmenssteuerung.')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description);
