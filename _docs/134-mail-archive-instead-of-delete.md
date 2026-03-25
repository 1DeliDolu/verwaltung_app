## 134. Mail Archive Instead Of Delete

1. Mail silme istegi fiziksel silme yerine kullanici bazli arsivleme olarak modellendi.
2. `database/migrations/021_add_archive_fields_to_internal_mail.sql` ile `internal_mails.sender_archived_at` ve `internal_mail_recipients.archived_at` alanlari eklendi.
3. `app/Models/InternalMail.php` guncellenerek inbox ve sent listeleri arsivlenmis kayitlari dislayacak, ayri `archived` listesi ise kullaniciya ait arsivlenen mailleri gosterecek sekilde revize edildi.
4. Arama, okunma ve attachment akislari korunurken sender ve recipient icin ayrik arsivleme mantigi eklendi.
5. `app/Services/InternalMailService.php` ve `app/Controllers/InternalMailController.php` icine yeni arsivleme endpoint ve servis akisi eklendi.
6. `routes/web.php` icinde `/mail/{mailId}/archive` route'u tanimlandi.
7. `resources/views/mail/index.php` icinde `Archiv` sekmesi eklendi.
8. Mail detay alanina `Archivieren` aksiyonu eklenerek secili mesaj kullanici acisindan arsive tasinabilir hale getirildi.
9. Arsivleme sonrasi mesaj aktif listeden duser ve `Archiv` sekmesinden gorulebilir.
