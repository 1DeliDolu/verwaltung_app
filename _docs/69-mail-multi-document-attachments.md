# 69. Mail Multi Document Attachments

1. `app/Services/InternalMailService.php` attachment upload akisi tek dosyadan coklu dosya yapisina genisletildi.
2. `resources/views/mail/index.php` compose alanindaki dosya secici `multiple` destekleyecek sekilde `attachment[]` formatina cevrildi.
3. Ayni mail ile birden fazla dokumanin gonderilebilmesi saglandi.
