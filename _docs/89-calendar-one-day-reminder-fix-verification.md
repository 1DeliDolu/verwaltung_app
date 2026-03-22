## 89. Calendar One Day Reminder Fix Verification

1. `php -l resources/views/pages/calendar.php` calistirildi.
2. `admin@verwaltung.local` ile login olduktan sonra `/calendar` ekrani acildi.
3. `rfq` baslikli ve `22.03.2026 23:56` zamanli event icin `calendarReminderHost` alani dogrulandi.
4. Sayfa cikisinda `Termin-Alarm` ve `1-Tages-Fensters` icerigi goruldu.
5. JavaScript tarafinda `reminderWindowInSeconds = 86400` ve `beginnt in weniger als 1 Tag` mesajlari dogrulandi.
