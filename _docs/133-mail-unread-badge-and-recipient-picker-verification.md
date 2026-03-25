## 133. Mail Unread Badge And Recipient Picker Verification

1. `php -l app/Controllers/InternalMailController.php` calistirildi.
2. `php -l app/Services/InternalMailService.php` calistirildi.
3. `php -l app/Models/InternalMail.php` calistirildi.
4. `php -l routes/web.php` calistirildi.
5. `php -l resources/views/mail/index.php` calistirildi.
6. `mysql -h 127.0.0.1 -P 3306 -u root -pD0cker! verwaltung_app < database/migrations/020_add_read_at_to_internal_mail_recipients.sql` calistirildi.
7. `information_schema.COLUMNS` sorgusu ile `internal_mail_recipients.read_at` alaninin olustugu dogrulandi.
8. `information_schema.STATISTICS` sorgusu ile `idx_internal_mail_recipients_read_at` index'inin olustugu dogrulandi.
9. Model tarafinda unread badge sayacinin `read_at IS NULL` filtresi ile hesaplandigi dogrulandi.
10. Mail ekraninda secili satirdan `Antworten` ve `Weiterleiten` akislari ile compose prefill davranisinin tanimlandigi dogrulandi.
11. Recipient picker'in Bootstrap dropdown, arama input'u ve secili badge listesi ile render edildigi dogrulandi.
