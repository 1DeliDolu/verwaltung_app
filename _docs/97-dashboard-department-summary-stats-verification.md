## 97. Dashboard Department Summary Stats Verification

1. `php -l app/Models/DepartmentDocument.php` calistirildi.
2. `php -l app/Models/EmployeeDocument.php` calistirildi.
3. `php -l app/Models/Employee.php` calistirildi.
4. `php -l app/Models/User.php` calistirildi.
5. `php -l app/Services/FilesystemService.php` calistirildi.
6. `php -l app/Services/DepartmentService.php` calistirildi.
7. `php -l resources/views/dashboard/index.php` calistirildi.
8. `php -l resources/views/layouts/app.php` calistirildi.
9. `DepartmentDocument`, `Employee`, `EmployeeDocument` ve `User` modellerinde dashboard metrikleri icin sayim metodlari eklendigi dogrulandi.
10. `DepartmentService` icinde dashboard kartlarina `summary_stats` verisinin baglandigi dogrulandi.
