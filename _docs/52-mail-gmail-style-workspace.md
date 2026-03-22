# 52. Mail Gmail Style Workspace

1. `resources/views/layouts/app.php` mail sayfasi icin ozel `page-mail` layout davranisi aldi; global header ve footer bu ekranda kapatildi.
2. `resources/views/mail/index.php` koyu tema, ust arama cubugu, sol klasor menusu, orta liste alani ve sag compose paneli ile Gmail benzeri workspace olarak yeniden kuruldu.
3. Yeni mail paneli ikinci referans gorsele uygun sekilde `An`, `Betreff`, mesaj govdesi, toolbar ve `Senden` aksiyonlari ile pencere gorunumu aldi.
