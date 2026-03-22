## 85. Calendar Delete Action Verification

1. `php -l app/Models/CalendarEvent.php`, `php -l app/Services/CalendarService.php`, `php -l app/Controllers/CalendarController.php` ve `php -l resources/views/pages/calendar.php` calistirildi.
2. Test icin `Delete Probe Event` isimli gecici bir calendar kaydi veritabanina eklendi.
3. `admin@verwaltung.local` ile login olduktan sonra `/calendar` ekraninda test event'i icin `Edit`, `Delete`, `Erledigt` butonlari goruldu.
4. `POST /calendar/events/6/delete` sonrasi `Termin wurde geloescht.` flash mesaji dogrulandi.
5. `calendar_events` tablosunda `id=6` kaydinin silindigi ve `calendar_event_departments` icinde ilgili pivot kaydi kalmadigi kontrol edildi.
