# 03 Login Verification

## Yapilan Islem

Bu asamada login altyapisi gercek veritabani ve HTTP akisi uzerinde dogrulandi.

## Uygulanan Adimlar

1. `database/migrations/001_create_users_table.sql` dosyasi `verwaltung_app` veritabaninda calistirildi.
2. `database/seeds/003_admin_user.sql` ile varsayilan admin kullanicisi eklendi veya guncellendi.
3. PHP built-in server `127.0.0.1:8080` uzerinde ayaga kaldirildi.
4. `/login` sayfasinin acildigi ve CSRF token urettigi dogrulandi.
5. `admin@verwaltung.local` / `D0cker!123` ile login POST istegi atildi.
6. Login sonrasi `/dashboard` sayfasinin `Admin User` bilgisiyle acildigi dogrulandi.
7. `/logout` sonrasinda korumali dashboard isteginin tekrar `/login` sayfasina yonlendigi dogrulandi.

## Sonuc

- Login ekrani calisiyor.
- Veritabani tabanli kimlik dogrulamasi calisiyor.
- Session tabanli korumali sayfa erisimi calisiyor.
- Logout sonrasi yetkisiz erisim tekrar login sayfasina dusuyor.
