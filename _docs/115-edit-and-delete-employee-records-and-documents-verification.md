## 115. Edit And Delete Employee Records And Documents Verification

1. `php -l app/Models/Employee.php` calistirildi.
2. `php -l app/Models/EmployeeDocument.php` calistirildi.
3. `php -l app/Services/FilesystemService.php` calistirildi.
4. `php -l app/Services/DepartmentService.php` calistirildi.
5. `php -l app/Controllers/DepartmentController.php` calistirildi.
6. `php -l routes/web.php` calistirildi.
7. `php -l resources/views/departments/show.php` calistirildi.
8. `DepartmentService` icinde `updateEmployee`, `deleteEmployee` ve `deleteEmployeeDocument` akislarinin eklendigi dogrulandi.
9. `DepartmentController` icinde HR update/delete route aksiyonlarinin eklendigi dogrulandi.
10. HR gorunumunde calisan kartlarina duzenleme ve silme butonlari, dokuman satirlarina silme butonlari eklendigi dogrulandi.
