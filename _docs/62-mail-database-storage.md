# 62. Mail Database Storage

1. `database/migrations/010_create_internal_mails_table.sql`, `011_create_internal_mail_recipients_table.sql` ve `012_create_internal_mail_attachments_table.sql` ile internal mail, recipient ve attachment tablolari eklendi.
2. `app/Models/InternalMail.php` ile mail kaydetme, mailbox okuma ve attachment erisimi icin DB modeli olusturuldu.
3. `app/Services/InternalMailService.php` gonderilen mailleri artik veritabanina yazacak sekilde guncellendi.
