## 101. Open Uploaded Department Files Verification

1. `php -l app/Controllers/DepartmentController.php` calistirildi.
2. `php -l app/Services/FilesystemService.php` calistirildi.
3. `php -l routes/web.php` calistirildi.
4. `php -l resources/views/departments/show.php` calistirildi.
5. `DepartmentController` icinde `openDepartmentFile` aksiyonunun eklendigi ve departman gorunurlugu kontrolu yaptigi dogrulandi.
6. `FilesystemService` icinde dosya metadata ve guvenli path resolve yardimcilarinin eklendigi dogrulandi.
7. Departman `Filesystem` tablosunda her satir icin `Oeffnen` linkinin render edildigi dogrulandi.
