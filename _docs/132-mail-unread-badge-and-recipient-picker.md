## 132. Mail Unread Badge And Recipient Picker

1. Mail sistemi alici bazinda okunma durumunu izlemek icin `database/migrations/020_add_read_at_to_internal_mail_recipients.sql` eklendi.
2. `internal_mail_recipients` tablosuna `read_at` alani ve okunmamis sorgulari hizlandiran index tanimlandi.
3. `app/Models/InternalMail.php` guncellenerek unread inbox sayaci artik sadece `read_at IS NULL` kayitlarini sayar hale getirildi.
4. Mail acildiginda recipient kaydini `read_at` ile isaretleyen model ve servis metotlari eklendi.
5. `app/Controllers/InternalMailController.php` icine yeni `/mail/{mailId}/read` endpoint'i eklendi.
6. `resources/views/mail/index.php` icinde mail satirlari `is_read` durumuna gore isaretlenir hale getirildi.
7. Mail detay paneline `Antworten` ve `Weiterleiten` aksiyonlari eklenerek secili mesajdan compose prefill akisi tamamlandi.
8. Mail acildiginda JavaScript ile yeni read endpoint'ine istek atilip okunmamis badge degeri otomatik dusurulur hale getirildi.
9. `Neue Nachricht > An` alani duz multiple select yerine Bootstrap dropdown tabanli, arama yapilabilen ve secilen kisileri badge olarak gosteren bir picker'a donusturuldu.
10. Mail compose ve toolbar yerlesimleri kucuk ekranlarda daha kontrollu davranacak sekilde responsive olarak guclendirildi.
