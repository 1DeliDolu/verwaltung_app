## 83. Calendar Edit Visibility Verification

1. `php -l resources/views/pages/calendar.php` calistirildi.
2. `admin@verwaltung.local` ile login olduktan sonra `/calendar` ekraninda event kartlarinda `Termin aktualisieren` aksiyonu dogrulandi.
3. Linklerin `/calendar?edit={id}#calendarCreateForm` hedefiyle render edildigi kontrol edildi.
4. `/calendar?edit=5` cikisinda `createForm.scrollIntoView(...)` ve `Kalendereintrag aktualisieren` icerigi dogrulandi.
