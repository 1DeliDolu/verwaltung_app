# 45 Remove Verification And Add Mail Badge

## Yapilan Islem

Login sonrasi verification zorunlulugu kaldirildi ve header icindeki `Mail` linkine mesaj sayisi badge'i eklendi.

## Eklenen veya Guncellenen Parcalar

- `app/Controllers/AuthController.php`
- `app/Controllers/DashboardController.php`
- `app/Controllers/DepartmentController.php`
- `app/Controllers/InfrastructureController.php`
- `app/Controllers/InternalMailController.php`
- `app/Controllers/MailController.php`
- `app/Services/InternalMailService.php`
- `resources/views/partials/header.php`

## Sonuc

- Login olan kullanici dogrudan uygulamayi kullanabilir.
- Header icinde `Mail` her zaman gorunur.
- Gelen mesaj sayisi `Mail` linki uzerinde badge olarak gorunur.
