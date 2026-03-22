## 81. Calendar Event Editing Verification

1. `php -l app/Models/CalendarEvent.php`, `php -l app/Services/CalendarService.php`, `php -l app/Controllers/CalendarController.php` ve `php -l resources/views/pages/calendar.php` calistirildi.
2. `php -S 127.0.0.1:8080 -t public` ile uygulama acildi.
3. `admin@verwaltung.local` hesabi ile login olunup `/calendar?edit=5` ekrani acildi.
4. Event `Demo Bearbeitet` basligi, `Bearbeitete Terminbeschreibung` aciklamasi, `Raum 2` konumu ve yeni tarih bilgisi ile guncellendi.
5. `calendar_events` tablosunda `id=5` satirinin yeni alan degerleri dogrulandi.
6. `/calendar` ekraninda `Termin wurde aktualisiert.` mesaji ve guncel event karti dogrulandi.
7. Test event'inde departman etiketi olmadigi icin bu guncellemede yeni bir bilgilendirme maili uretilmedi.
