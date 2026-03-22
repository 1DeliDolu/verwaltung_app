# 73. Calendar Event Storage

1. `database/migrations/013_create_calendar_events_table.sql` ve `014_create_calendar_event_departments_table.sql` ile takvim event ve departman etiketleme tablolari eklendi.
2. `app/Models/CalendarEvent.php` ve `app/Services/CalendarService.php` ile event kaydetme, siralama ve departman bildirim akisi olusturuldu.
3. Etiketlenen departman uyeleri icin internal mail ve SMTP bildirim gonderimi eklendi.
