## 130. Admin Department Leader Directory

1. Admin kullanicilar icin tum `leiter.*@verwaltung.local` hesaplarini tek ekranda gosteren yeni bir kullanici dizini eklendi.
2. `app/Controllers/UserController.php` ve `app/Services/UserService.php` doldurularak mevcut uygulama yapisi icinde admin-ozel kullanici listeleme akisi olusturuldu.
3. `UserService` icinde `User::internalDirectory()` verisi filtrelenerek sadece `team_leader` uyelikli ve `leiter.` ile baslayan hesaplar listelenir hale getirildi.
4. `routes/web.php` icine yeni `/users` route'u eklendi.
5. `resources/views/partials/header.php` guncellenerek `Users` baglantisi yalnizca admin oturumlarinda gorunur yapildi.
6. `resources/views/users/index.php` icinde departman, ad, e-posta ve rol alanlarini gosteren lider tablosu olusturuldu.
7. Ekrana standart demo sifresinin `DockerDocker!123` oldugunu belirten bir bilgi karti eklendi.
8. Admin olmayan erisimler icin `resources/views/errors/403.php` olusturularak yetkisiz istekler acik bir 403 sayfasina yonlendirildi.
