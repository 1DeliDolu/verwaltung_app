<?php

return [
    'defaults' => [
        'tagline' => 'Abteilungsbereich fuer operative Zusammenarbeit, Dokumente und geteilte Dateien.',
        'focus' => 'Teamkoordination und nachvollziehbare Facharbeit.',
        'hero' => 'Jede Abteilung arbeitet mit einem gemeinsamen Bereich fuer Dokumente, Wissen und dateibasierte Zusammenarbeit.',
        'leader_title' => 'Leiterarbeitsplatz',
        'leader_intro' => 'Teamleiter steuern hier die wichtigsten Abteilungsaufgaben direkt ueber Dokumente, Dateien und abgestimmte Arbeitsablaeufe.',
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
        'leader_tasks' => [
            [
                'title' => 'Abteilungsdokumentation pflegen',
                'description' => 'Aktuelle Standards, Vorlagen und Teamabsprachen als Dokument veroeffentlichen.',
                'action_label' => 'Dokumentbereich oeffnen',
                'action_target' => '#department-documents',
            ],
            [
                'title' => 'Arbeitsdateien bereitstellen',
                'description' => 'Freigegebene Dateien in der Abteilungsablage ablegen und veraltete Stande bereinigen.',
                'action_label' => 'Dateibereich oeffnen',
                'action_target' => '#department-filesystem',
            ],
        ],
    ],
    'profiles' => [
        'it' => [
            'tagline' => 'Systeme, Konten und technische Betriebsfaehigkeit.',
            'focus' => 'Provisionierung, Infrastrukturpflege und technische Standards.',
            'hero' => 'IT steuert Konten, Zugriffe und die technische Bereitstellung interner Services. Der Bereich trennt technische Stammdaten klar von sensiblen HR-Daten.',
            'leader_intro' => 'IT-Leiter koordinieren technische Bereitstellung, Zugriffe und Betriebsdokumentation direkt aus dem Abteilungsbereich.',
            'detail_partial' => 'information-technology',
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
            'leader_tasks' => [
                [
                    'title' => 'Neue Person provisionieren',
                    'description' => 'Konto, Rolle und Zielabteilung fuer neue interne Nutzer anlegen.',
                    'action_label' => 'Provisionierung starten',
                    'action_target' => '#department-managed-person-create',
                ],
                [
                    'title' => 'Runbooks aktualisieren',
                    'description' => 'Technische Anweisungen und Uebergaben fuer den Betrieb sauber dokumentieren.',
                    'action_label' => 'Dokumente pflegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Abteilungsdateien bereitstellen',
                    'description' => 'Installationspakete, Exporte und abgestimmte Dateien im Share hochladen.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
        'hr' => [
            'tagline' => 'Personalprozesse, Akten und Datenschutz.',
            'focus' => 'Mitarbeiterprofile, Aufbewahrungspflichten und sensible Personaldaten.',
            'hero' => 'HR fuehrt Personalprofile getrennt von technischen Kontodaten und dokumentiert datenschutzrelevante Informationen inklusive Aufbewahrungsfristen.',
            'leader_intro' => 'HR-Leiter steuern Personalprofile, Akten und Datenschutzschritte direkt im geschuetzten Bereich der Abteilung.',
            'detail_partial' => 'human-resources',
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
            'leader_tasks' => [
                [
                    'title' => 'Personalprofil anlegen',
                    'description' => 'Von IT angelegte Personen in den HR-Prozess uebernehmen und beschaeftigungsrelevante Daten pflegen.',
                    'action_label' => 'Profil erfassen',
                    'action_target' => '#department-employee-create',
                ],
                [
                    'title' => 'Personalakte hochladen',
                    'description' => 'Dokumente datenschutzkonform direkt der richtigen Mitarbeiterakte zuordnen.',
                    'action_label' => 'Akte hochladen',
                    'action_target' => '#department-employee-document-upload',
                ],
                [
                    'title' => 'Richtlinien veroeffentlichen',
                    'description' => 'Personalrichtlinien, Formulare und Hinweise als freigegebene Dokumente pflegen.',
                    'action_label' => 'Dokumente pflegen',
                    'action_target' => '#department-document-create',
                ],
            ],
        ],
        'operations' => [
            'tagline' => 'Tagesbetrieb, Abstimmung und stabile Ausfuehrung.',
            'focus' => 'Laufende Abwicklung, Uebergaben und operative Transparenz.',
            'hero' => 'Operations nutzt den Abteilungsbereich fuer Arbeitsanweisungen, Lagebilder und abgestimmte Dateien, damit der taegliche Betrieb nachvollziehbar bleibt.',
            'leader_intro' => 'Operations-Leiter koordinieren Uebergaben, Tagessteuerung und abgestimmte Arbeitsdateien aus einem gemeinsamen Bereich.',
            'detail_partial' => 'operations',
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
            'leader_tasks' => [
                [
                    'title' => 'Schichtuebergabe dokumentieren',
                    'description' => 'Aktuelle Lage, Risiken und naechste Schritte als Teamdokument festhalten.',
                    'action_label' => 'Dokument anlegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Arbeitsdateien verteilen',
                    'description' => 'Checklisten, Statuslisten und Uebergabeunterlagen fuer das Team hochladen.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
        'marketing' => [
            'tagline' => 'Kampagnen, Inhalte und Marktkommunikation.',
            'focus' => 'Planung, Freigaben und konsistente Markenkommunikation.',
            'leader_intro' => 'Marketing-Leiter koordinieren Kampagnenunterlagen, Freigaben und operative Assets direkt im Bereich.',
            'playbook' => [
                'eyebrow' => 'Marketing',
                'title' => 'Kampagnensteuerung',
                'intro' => 'Dieser Bereich dient fuer Briefings, Freigaben, Redaktionsplaene und abgestimmte Kampagnenassets.',
                'items' => [
                    'Briefings und Zielgruppenannahmen je Kampagne dokumentieren',
                    'Freigabefaehige Dateien mit klarer Benennung ablegen',
                    'Kanalspezifische Inhalte in wiederverwendbaren Paketen strukturieren',
                ],
            ],
            'leader_tasks' => [
                [
                    'title' => 'Kampagnenbriefing veroeffentlichen',
                    'description' => 'Ziele, Botschaften und Freigabestatus als Teamdokument pflegen.',
                    'action_label' => 'Dokument anlegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Kreativdateien bereitstellen',
                    'description' => 'Freigegebene Motive, Texte und Mediaplaene im Share aktualisieren.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
        'sales' => [
            'tagline' => 'Pipeline, Angebote und Kundenabschluesse.',
            'focus' => 'Vertriebsunterlagen, Abschlussreife und Angebotssteuerung.',
            'leader_intro' => 'Sales-Leiter koordinieren Angebotsunterlagen, Teamsteuerung und vertriebsrelevante Dateien an einer Stelle.',
            'playbook' => [
                'eyebrow' => 'Sales',
                'title' => 'Vertriebsunterlagen',
                'intro' => 'Hier werden Angebotsvorlagen, Preisargumentationen und Abschlussunterstuetzung zentral gepflegt.',
                'items' => [
                    'Angebotsbausteine mit gueltigem Stand ablegen',
                    'Einwaende und Gegenargumente dokumentierbar halten',
                    'Kundenseitige Anforderungen sauber an nachgelagerte Teams uebergeben',
                ],
            ],
            'leader_tasks' => [
                [
                    'title' => 'Angebotsvorlagen pflegen',
                    'description' => 'Vertriebsvorlagen und Abschlussleitfaeden fuer das Team aktuell halten.',
                    'action_label' => 'Dokument anlegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Abschlussdateien teilen',
                    'description' => 'Preislisten, Kundenunterlagen und Freigaben im Share bereitstellen.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
        'research-development' => [
            'tagline' => 'Produktideen, Validierung und technische Weiterentwicklung.',
            'focus' => 'Experimentieren, dokumentieren und belastbare Entscheidungen treffen.',
            'leader_intro' => 'R&D-Leiter steuern Hypothesen, Versuchsstaende und abgestimmte Entwicklungsunterlagen direkt aus der Abteilungsseite.',
            'playbook' => [
                'eyebrow' => 'Research & Development',
                'title' => 'Validierung und Wissensaufbau',
                'intro' => 'Der Bereich sammelt Hypothesen, Experimente, technische Bewertungen und belastbare Produktentscheidungen.',
                'items' => [
                    'Hypothesen und Annahmen explizit dokumentieren',
                    'Versuchsergebnisse mit Entscheidungskriterien festhalten',
                    'Technische Erkenntnisse in anschlussfaehige Dokumente ueberfuehren',
                ],
            ],
            'leader_tasks' => [
                [
                    'title' => 'Versuchsplan dokumentieren',
                    'description' => 'Hypothesen, Testziele und Ergebnisse als nachvollziehbares Dokument ablegen.',
                    'action_label' => 'Dokument anlegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Artefakte bereitstellen',
                    'description' => 'Modelle, Exporte und technische Unterlagen fuer Team und Freigabe hochladen.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
        'finance-accounting' => [
            'tagline' => 'Finanzsteuerung, Abschluesse und kaufmaennische Nachweise.',
            'focus' => 'Zahlenqualitaet, Nachvollziehbarkeit und termingerechte Reports.',
            'leader_intro' => 'Finance-Leiter pflegen Reports, Abschlussunterlagen und kaufmaennische Nachweise zentral im Fachbereich.',
            'playbook' => [
                'eyebrow' => 'Finance & Accounting',
                'title' => 'Nachweise und Abschlussfaehigkeit',
                'intro' => 'Finanzrelevante Dokumente muessen nachvollziehbar, termingerecht und mit klarer Pruefbasis abgelegt werden.',
                'items' => [
                    'Abstimmungen und Nachweise je Berichtszeitraum strukturieren',
                    'Freigegebene Dateien fuer Pruefung und Abschluss zentral halten',
                    'Versionen von Reports und Freigabestufen kenntlich machen',
                ],
            ],
            'leader_tasks' => [
                [
                    'title' => 'Reporting dokumentieren',
                    'description' => 'Abschlussstaende, Fristen und Prufschritte als Abteilungsdokument bereitstellen.',
                    'action_label' => 'Dokument anlegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Nachweise hochladen',
                    'description' => 'Freigegebene Tabellen, Belegsammlungen und Reports im Share ablegen.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
        'legal-compliance' => [
            'tagline' => 'Regelwerk, Freigaben und Risikosteuerung.',
            'focus' => 'Vertraege, Richtlinien und regulatorische Anforderungen absichern.',
            'leader_intro' => 'Legal- und Compliance-Leiter koordinieren Richtlinien, Freigaben und belastbare Nachweise in einem gemeinsamen Bereich.',
            'playbook' => [
                'eyebrow' => 'Legal & Compliance',
                'title' => 'Regelwerk und Risikokontrolle',
                'intro' => 'Vertraege, Richtlinien und regulatorische Pruefschritte werden hier konsistent und revisionsnah verwaltet.',
                'items' => [
                    'Richtlinien mit gueltigem Stand und Freigabedatum dokumentieren',
                    'Vertragsmuster und Pruefhinweise zentral verfuegbar halten',
                    'Compliance-Risiken mit Handlungsempfehlungen erfassen',
                ],
            ],
            'leader_tasks' => [
                [
                    'title' => 'Richtlinie veroeffentlichen',
                    'description' => 'Regelwerke, Freigaben und Eskalationsvorgaben als Dokument pflegen.',
                    'action_label' => 'Dokument anlegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Pruefunterlagen ablegen',
                    'description' => 'Vertragsfassungen, Nachweise und regulatorische Artefakte hochladen.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
        'customer-service-support' => [
            'tagline' => 'Anfragen, Loesungen und Servicequalitaet.',
            'focus' => 'Reaktionsfaehigkeit, Dokumentation und saubere Falluebergaben.',
            'leader_intro' => 'Support-Leiter pflegen Antwortleitfaeden, Eskalationen und freigegebene Servicedateien direkt im Teamraum.',
            'playbook' => [
                'eyebrow' => 'Customer Service',
                'title' => 'Servicequalitaet und Falluebergaben',
                'intro' => 'Support-relevante Anleitungen, Antwortvorlagen und Eskalationspfade werden hier fuer das Team gepflegt.',
                'items' => [
                    'Standardantworten mit aktuellem Freigabestand hinterlegen',
                    'Eskalationen mit klaren Schwellenwerten dokumentieren',
                    'Wissensartikel aus wiederkehrenden Faellen ableiten',
                ],
            ],
            'leader_tasks' => [
                [
                    'title' => 'Serviceleitfaden pflegen',
                    'description' => 'Antwortbausteine, Eskalationswege und Teamhinweise als Dokument aktuell halten.',
                    'action_label' => 'Dokument anlegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Supportdateien bereitstellen',
                    'description' => 'Exports, Checklisten und Kundenunterlagen im Share aktualisieren.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
        'quality-management' => [
            'tagline' => 'Standards, Audits und kontinuierliche Verbesserung.',
            'focus' => 'Qualitaetsnachweise und belastbare Prozesskontrolle.',
            'leader_intro' => 'Qualitaetsleiter steuern Audits, Standards und Verbesserungsmassnahmen zentral im Abteilungsbereich.',
            'playbook' => [
                'eyebrow' => 'Quality Management',
                'title' => 'Standards und Abweichungen',
                'intro' => 'Dieser Bereich sammelt Qualitaetsstandards, Audit-Hinweise und dokumentierte Verbesserungsmassnahmen.',
                'items' => [
                    'Pruefkriterien und Auditlisten versioniert bereitstellen',
                    'Abweichungen mit Ursache und Massnahme erfassen',
                    'Verbesserungen bis zur Wirksamkeitspruefung nachhalten',
                ],
            ],
            'leader_tasks' => [
                [
                    'title' => 'Auditplan dokumentieren',
                    'description' => 'Pruefungen, Abweichungen und Folgemassnahmen als nachvollziehbares Dokument fuehren.',
                    'action_label' => 'Dokument anlegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Nachweise sammeln',
                    'description' => 'Auditdateien, Messwerte und freigegebene Nachweise hochladen.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
        'production-service-delivery' => [
            'tagline' => 'Leistungserbringung, Lieferfaehigkeit und Stabilitaet.',
            'focus' => 'Durchsatz, Terminzuverlaessigkeit und saubere Auslieferung.',
            'leader_intro' => 'Leiter fuer Delivery und Produktion koordinieren Leistungserbringung, Uebergaben und operative Dateien aus dem Bereich heraus.',
            'playbook' => [
                'eyebrow' => 'Production / Service Delivery',
                'title' => 'Auslieferung und Stabilitaet',
                'intro' => 'Leistungserbringung braucht klare Arbeitsunterlagen, saubere Uebergaben und verifizierbare Lieferfaehigkeit.',
                'items' => [
                    'Betriebsanweisungen fuer die Ausfuehrung eindeutig dokumentieren',
                    'Lieferrelevante Dateien mit gueltigem Stand zentral halten',
                    'Fehlerbilder und Gegenmassnahmen praxisnah beschreiben',
                ],
            ],
            'leader_tasks' => [
                [
                    'title' => 'Lieferplan abstimmen',
                    'description' => 'Auslieferungsstatus, Prioritaeten und Abweichungen als Dokument teilen.',
                    'action_label' => 'Dokument anlegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Auftragsdateien bereitstellen',
                    'description' => 'Arbeitslisten, Begleitunterlagen und Freigabedateien im Share verwalten.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
        'supply-chain-logistics-procurement' => [
            'tagline' => 'Beschaffung, Materialfluss und Verfuegbarkeit.',
            'focus' => 'Lieferketten, Bestellungen und terminierte Versorgung.',
            'leader_intro' => 'Supply-Chain-Leiter steuern Bedarfe, Lieferstatus und beschaffungsrelevante Unterlagen in einem Fachbereich.',
            'playbook' => [
                'eyebrow' => 'Supply Chain',
                'title' => 'Beschaffung und Verfuegbarkeit',
                'intro' => 'Lieferketteninformationen, Beschaffungsunterlagen und operative Abstimmungen werden hier zentral sichtbar gehalten.',
                'items' => [
                    'Lieferanteninformationen und Bestellvorlagen geordnet ablegen',
                    'Materialengpaesse fruehzeitig dokumentieren und eskalieren',
                    'Logistikrelevante Dateien fuer alle Beteiligten nachvollziehbar halten',
                ],
            ],
            'leader_tasks' => [
                [
                    'title' => 'Beschaffungsstatus dokumentieren',
                    'description' => 'Liefertermine, Risiken und Freigaben als Abteilungsdokument pflegen.',
                    'action_label' => 'Dokument anlegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Lieferdokumente hochladen',
                    'description' => 'Bestellunterlagen, Lieferlisten und Freigabedateien im Share bereitstellen.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
        'general-management' => [
            'tagline' => 'Steuerung, Priorisierung und bereichsuebergreifende Entscheidungen.',
            'focus' => 'Ziele, Eskalationen und abgestimmte Unternehmenssteuerung.',
            'leader_intro' => 'Bereichsleiter der Geschaeftsfuehrung koordinieren Entscheidungen, Prioritaeten und freigegebene Unterlagen zentral ueber die Abteilungsseite.',
            'playbook' => [
                'eyebrow' => 'General Management',
                'title' => 'Steuerung und Entscheidungen',
                'intro' => 'Hier liegen bereichsuebergreifende Leitlinien, Priorisierungen und Entscheidungsgrundlagen fuer die Unternehmenssteuerung.',
                'items' => [
                    'Entscheidungsvorlagen mit Kontext und Auswirkungen dokumentieren',
                    'Bereichsuebergreifende Prioritaeten regelmaessig aktualisieren',
                    'Freigegebene Management-Dokumente zentral verfuegbar halten',
                ],
            ],
            'leader_tasks' => [
                [
                    'title' => 'Entscheidungsvorlage pflegen',
                    'description' => 'Strategische Beschluesse, Prioritaeten und Eskalationen als Dokument bereitstellen.',
                    'action_label' => 'Dokument anlegen',
                    'action_target' => '#department-document-create',
                ],
                [
                    'title' => 'Freigegebene Unterlagen teilen',
                    'description' => 'Management-Reports, Beschlussdateien und abgestimmte Materialien hochladen.',
                    'action_label' => 'Datei hochladen',
                    'action_target' => '#department-file-upload',
                ],
            ],
        ],
    ],
];
