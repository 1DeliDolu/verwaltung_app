## 131. Admin Department Leader Directory Verification

1. `php -l app/Controllers/UserController.php` calistirildi.
2. `php -l app/Services/UserService.php` calistirildi.
3. `php -l routes/web.php` calistirildi.
4. `php -l resources/views/partials/header.php` calistirildi.
5. `php -l resources/views/users/index.php` calistirildi.
6. `php -l resources/views/errors/403.php` calistirildi.
7. `/users` route'unun tanimlandigi dogrulandi.
8. Header icinde `Users` baglantisinin sadece `admin` rolu icin eklendigi dogrulandi.
9. Kullanici servisinin sadece `leiter.*@verwaltung.local` ve `team_leader` kayitlarini listeleyecek sekilde filtreleme yaptigi dogrulandi.
10. Admin disi kullanicilar icin 403 yaniti render edilecek akisin controller icinde tanimlandigi dogrulandi.
