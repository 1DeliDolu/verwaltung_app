## 101. Department Config Driven Profiles Verification

1. `php -l config/departments.php` calistirildi.
2. `php -l bootstrap/app.php` calistirildi.
3. `php -l tests/bootstrap.php` calistirildi.
4. `php -l app/Services/DepartmentService.php` calistirildi.
5. `php -l resources/views/departments/index.php` calistirildi.
6. `php -l resources/views/departments/show.php` calistirildi.
7. `bootstrap/app.php` ve `tests/bootstrap.php` icinde `departments` config'inin yuklendigi dogrulandi.
8. `DepartmentService` icinde veritabani departman kayitlarinin config tabanli profil verileri ile birlestirildigi dogrulandi.
9. Departman liste ekraninin config'ten gelen `tagline` ve `focus` alanlarini render ettigi dogrulandi.
10. Departman detay ekranina profil, sorumluluk ve workflow bloklarinin eklendigi dogrulandi.
