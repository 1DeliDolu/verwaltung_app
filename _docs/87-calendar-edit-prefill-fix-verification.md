## 87. Calendar Edit Prefill Fix Verification

1. `php -l resources/views/pages/calendar.php` calistirildi.
2. `admin@verwaltung.local` ile login olunduktan sonra `/calendar?edit=7#calendarCreateForm` acildi.
3. Formun `/calendar/events/7/update` action'i ile geldigi dogrulandi.
4. Form icinde `rfq` basligi, `er` aciklamasi, `er` lokasyonu ve `2026-03-22T23:56` tarih alanlari render edildi.
