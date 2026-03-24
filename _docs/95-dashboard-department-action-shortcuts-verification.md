## 95. Dashboard Department Action Shortcuts Verification

1. `php -l app/Services/DepartmentService.php` calistirildi.
2. `php -l app/Controllers/DashboardController.php` calistirildi.
3. `php -l resources/views/dashboard/index.php` calistirildi.
4. `php -l resources/views/departments/show.php` calistirildi.
5. `php -l resources/views/layouts/app.php` calistirildi.
6. `DepartmentService` icinde dashboard icin bolum bazli hizli aksiyon listesi uretildigi dogrulandi.
7. Dashboard gorunumunde ortak kart yapisi ve departman bazli aksiyon butonlari eklendigi dogrulandi.
8. Departman sayfalarinda `department-document-create`, `department-file-upload`, `department-managed-person-create`, `department-employee-create`, `department-employee-document-upload`, `department-documents` ve `department-filesystem` anchor hedefleri eklendigi dogrulandi.
