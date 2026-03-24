## 117. Add Automated Tests For Auth And HR Rules Verification

1. `php -l app/Services/DepartmentService.php` calistirildi.
2. `php -l tests/bootstrap.php` calistirildi.
3. `php -l tests/TestCase.php` calistirildi.
4. `php -l tests/run.php` calistirildi.
5. `php -l tests/Unit/AuthServiceTest.php` calistirildi.
6. `php -l tests/Unit/DepartmentServiceTest.php` calistirildi.
7. `php tests/run.php` calistirildi.
8. Test sonucu olarak 10 testin gectigi ve 0 hata oldugu dogrulandi.
9. Auth testlerinin guclu parola, kisa parola, ozel karakter eksigi ve kisisel tanimlayici iceren parola senaryolarini kapsadigi dogrulandi.
10. DepartmentService testlerinin managed person ve employee profile validation kurallarini kapsadigi dogrulandi.
