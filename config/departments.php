<?php

return [
    'defaults' => [
        'tagline' => 'Abteilungsbereich fuer operative Zusammenarbeit, Dokumente und geteilte Dateien.',
        'focus' => 'Teamkoordination und nachvollziehbare Facharbeit.',
        'hero' => 'Jede Abteilung arbeitet mit einem gemeinsamen Bereich fuer Dokumente, Wissen und dateibasierte Zusammenarbeit.',
        'responsibilities' => [
            'Fachliche Dokumentation aktuell halten',
            'Dateien nachvollziehbar im Abteilungsbereich ablegen',
            'Interne Zusammenarbeit ueber klare Verantwortlichkeiten organisieren',
        ],
        'workflows' => [
            'Fachinformationen im Team veroeffentlichen',
            'Freigegebene Dateien fuer den Tagesbetrieb bereitstellen',
            'Aenderungen ueber zustaendige Rollen koordinieren',
        ],
        'kpis' => [
            'Dokumente',
            'Dateien',
        ],
    ],
    'profiles' => [
        'it' => [
            'tagline' => 'Systeme, Konten und technische Betriebsfaehigkeit.',
            'focus' => 'Provisionierung, Infrastrukturpflege und technische Standards.',
            'hero' => 'IT steuert Konten, Zugriffe und die technische Bereitstellung interner Services. Der Bereich trennt technische Stammdaten klar von sensiblen HR-Daten.',
            'responsibilities' => [
                'Benutzerkonten und Zugriffsrollen provisionieren',
                'Abteilungsdateien und technische Dokumente verwalten',
                'Interne Systeme und Infrastruktur betriebsbereit halten',
            ],
            'workflows' => [
                'Neue Person technisch anlegen und Zielabteilung zuweisen',
                'Technische Dokumente, Runbooks und Uebergaben pflegen',
                'Dateien fuer Teams zentral im Share bereitstellen',
            ],
            'kpis' => [
                'Dokumente',
                'Dateien',
                'Verwaltete Konten',
            ],
        ],
        'hr' => [
            'tagline' => 'Personalprozesse, Akten und Datenschutz.',
            'focus' => 'Mitarbeiterprofile, Aufbewahrungspflichten und sensible Personaldaten.',
            'hero' => 'HR fuehrt Personalprofile getrennt von technischen Kontodaten und dokumentiert datenschutzrelevante Informationen inklusive Aufbewahrungsfristen.',
            'responsibilities' => [
                'Personalprofile auf Basis von IT-Stammdaten fuehren',
                'Personalakten datenschutzkonform verwalten',
                'Beschaeftigungsstatus und Aufbewahrungsfristen dokumentieren',
            ],
            'workflows' => [
                'Von IT bereitgestellte Personen in Personalprofile uebernehmen',
                'Personalakten pro Mitarbeiter ablegen und aktualisieren',
                'Aenderungen an Status, Notizen und Rechtsgrundlagen nachvollziehen',
            ],
            'kpis' => [
                'Dokumente',
                'Dateien',
                'Mitarbeiter',
                'Personalakten',
            ],
        ],
        'operations' => [
            'tagline' => 'Tagesbetrieb, Abstimmung und stabile Ausfuehrung.',
            'focus' => 'Laufende Abwicklung, Uebergaben und operative Transparenz.',
            'hero' => 'Operations nutzt den Abteilungsbereich fuer Arbeitsanweisungen, Lagebilder und abgestimmte Dateien, damit der taegliche Betrieb nachvollziehbar bleibt.',
            'responsibilities' => [
                'Betriebsablaeufe und Uebergaben dokumentieren',
                'Aktuelle Arbeitsdateien fuer das Team bereitstellen',
                'Stoerungen, Prioritaeten und Tageskoordination transparent halten',
            ],
            'workflows' => [
                'Arbeitsanweisungen und operative Updates veroeffentlichen',
                'Gemeinsame Dateien fuer Tagesgeschaeft und Schichtuebergaben pflegen',
                'Abteilungswissen fuer wiederkehrende Aufgaben konsistent halten',
            ],
            'kpis' => [
                'Dokumente',
                'Dateien',
            ],
        ],
        'marketing' => [
            'tagline' => 'Kampagnen, Inhalte und Marktkommunikation.',
            'focus' => 'Planung, Freigaben und konsistente Markenkommunikation.',
        ],
        'sales' => [
            'tagline' => 'Pipeline, Angebote und Kundenabschluesse.',
            'focus' => 'Vertriebsunterlagen, Abschlussreife und Angebotssteuerung.',
        ],
        'research-development' => [
            'tagline' => 'Produktideen, Validierung und technische Weiterentwicklung.',
            'focus' => 'Experimentieren, dokumentieren und belastbare Entscheidungen treffen.',
        ],
        'finance-accounting' => [
            'tagline' => 'Finanzsteuerung, Abschluesse und kaufmaennische Nachweise.',
            'focus' => 'Zahlenqualitaet, Nachvollziehbarkeit und termingerechte Reports.',
        ],
        'legal-compliance' => [
            'tagline' => 'Regelwerk, Freigaben und Risikosteuerung.',
            'focus' => 'Vertraege, Richtlinien und regulatorische Anforderungen absichern.',
        ],
        'customer-service-support' => [
            'tagline' => 'Anfragen, Loesungen und Servicequalitaet.',
            'focus' => 'Reaktionsfaehigkeit, Dokumentation und saubere Falluebergaben.',
        ],
        'quality-management' => [
            'tagline' => 'Standards, Audits und kontinuierliche Verbesserung.',
            'focus' => 'Qualitaetsnachweise und belastbare Prozesskontrolle.',
        ],
        'production-service-delivery' => [
            'tagline' => 'Leistungserbringung, Lieferfaehigkeit und Stabilitaet.',
            'focus' => 'Durchsatz, Terminzuverlaessigkeit und saubere Auslieferung.',
        ],
        'supply-chain-logistics-procurement' => [
            'tagline' => 'Beschaffung, Materialfluss und Verfuegbarkeit.',
            'focus' => 'Lieferketten, Bestellungen und terminierte Versorgung.',
        ],
        'general-management' => [
            'tagline' => 'Steuerung, Priorisierung und bereichsuebergreifende Entscheidungen.',
            'focus' => 'Ziele, Eskalationen und abgestimmte Unternehmenssteuerung.',
        ],
    ],
];
