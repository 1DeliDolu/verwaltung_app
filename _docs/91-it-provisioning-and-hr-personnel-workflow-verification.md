## 91. IT Provisioning And HR Personnel Workflow Verification

1. `php -l app/Models/User.php` calistirildi.
2. `php -l app/Models/Department.php` calistirildi.
3. `php -l app/Models/Employee.php` calistirildi.
4. `php -l app/Services/AuthService.php` calistirildi.
5. `php -l app/Services/DepartmentService.php` calistirildi.
6. `php -l app/Controllers/AuthController.php` calistirildi.
7. `php -l app/Controllers/DepartmentController.php` calistirildi.
8. `php -l app/Core/App.php` calistirildi.
9. `php -l resources/views/auth/change-password.php` calistirildi.
10. `php -l resources/views/departments/show.php` calistirildi.
11. `php -l routes/web.php` calistirildi.
12. Non-ASCII kontrolu icin `rg -n "[^\\x00-\\x7F]" ...` taramasi yapildi ve yeni degisikliklerde kalan karakterler temizlendi.
13. Not: `018` ve `019` migration dosyalari bu kayit sirasinda henuz veritabanina uygulanmadi; DB seviyesi dogrulama ayrica yapilmalidir.
