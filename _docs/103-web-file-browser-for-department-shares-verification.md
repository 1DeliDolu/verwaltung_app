## 103. Web File Browser For Department Shares Verification

1. `php -l app/Controllers/InfrastructureController.php` calistirildi.
2. `php -l app/Services/InfrastructureService.php` calistirildi.
3. `php -l app/Services/DepartmentService.php` calistirildi.
4. `php -l resources/views/services/index.php` calistirildi.
5. `php -l resources/views/services/fileserver.php` calistirildi.
6. `php -l routes/web.php` calistirildi.
7. `InfrastructureController` icinde yeni `fileBrowser` aksiyonunun eklendigi dogrulandi.
8. `Infrastruktur` gorunumunde file server kartindan `/services/fileserver` sayfasina gecis eklendigi dogrulandi.
9. Yeni file browser gorunumunde kullanicinin gorunur departmanlarina ait dosya tablolarinin render edildigi dogrulandi.
