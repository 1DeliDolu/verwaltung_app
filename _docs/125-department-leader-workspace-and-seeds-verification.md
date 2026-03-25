## 125. Department Leader Workspace And Seeds Verification

1. `php -l config/departments.php` calistirildi.
2. `php -l app/Services/DepartmentService.php` calistirildi.
3. `php -l resources/views/departments/index.php` calistirildi.
4. `php -l resources/views/departments/show.php` calistirildi.
5. `config/departments.php` icinde tum profiller icin `leader_intro` ve `leader_tasks` tanimlandigi dogrulandi.
6. `DepartmentService` icinde `leader_title`, `leader_intro` ve `leader_tasks` alanlarinin departman verisine eklendigi dogrulandi.
7. Departman detay ekraninda yeni `Leiterarbeitsplatz` kartinin sadece `canManage` durumunda render edildigi dogrulandi.
8. Gorev kartlarinin ilgili departman form ve bolum anchor'larina link verdigi dogrulandi.
9. Departman liste ekraninda leiter gorev sayisinin gorundugu dogrulandi.
10. Seed dosyalarinda tum departmanlarin ve her departman icin bir team leader hesabinin tanimlandigi dogrulandi.
