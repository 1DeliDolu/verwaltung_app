## 116. Add Automated Tests For Auth And HR Rules

1. Repo icine hafif bir custom PHP test runner eklendi.
2. `tests/bootstrap.php`, `tests/TestCase.php` ve `tests/run.php` ile framework bagimsiz otomatik test calistirma akisi kuruldu.
3. `AuthService` icin parola gucu kurallari otomatik test altina alindi.
4. `DepartmentService` icin yonetilen kisi olusturma kurallari ve HR personel profili validation kurallari testlenebilir saf metotlara ayrildi.
5. Auth ve HR provisioning tarafinda negatif validation senaryolari da otomatik olarak kapsandi.
6. Boylece README roadmap'indeki ikinci madde icin ilk calisan otomatik test tabani olusturulmus oldu.
