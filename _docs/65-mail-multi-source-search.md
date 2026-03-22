# 65. Mail Multi Source Search

1. `app/Controllers/InternalMailController.php` arama filtresini tek `scope` yerine birden fazla kaynak kabul edecek sekilde guncelledi.
2. `app/Models/InternalMail.php` sender, recipient ve content kosullarini ayni sorguda birlikte kullanabilecek sekilde degistirildi.
3. `resources/views/mail/index.php` ust arama alanina coklu secilebilir `Sender`, `Empfaenger` ve `Inhalt` checkbox filtreleri eklendi.
