## 135. Mail Archive Instead Of Delete Verification

1. `php -l app/Controllers/InternalMailController.php` calistirildi.
2. `php -l app/Services/InternalMailService.php` calistirildi.
3. `php -l app/Models/InternalMail.php` calistirildi.
4. `php -l routes/web.php` calistirildi.
5. `php -l resources/views/mail/index.php` calistirildi.
6. `mysql -h 127.0.0.1 -P 3306 -u root -pD0cker! verwaltung_app < database/migrations/021_add_archive_fields_to_internal_mail.sql` calistirildi.
7. `information_schema.COLUMNS` sorgulari ile `internal_mail_recipients.archived_at` ve `internal_mails.sender_archived_at` alanlarinin olustugu dogrulandi.
8. Model tarafinda inbox ve sent listelerinin arsivlenmis kayitlari filtreledigi dogrulandi.
9. Mail ekraninda `Archiv` sekmesinin ve `Archivieren` aksiyonunun render edildigi dogrulandi.
10. Arsiv endpoint'inin JSON yanit ile unread badge degerini de geri dondurdugu dogrulandi.
