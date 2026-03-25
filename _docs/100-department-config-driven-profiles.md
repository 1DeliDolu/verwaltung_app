## 100. Department Config Driven Profiles

1. `config/departments.php` olusturularak departmanlara ait profil verileri merkezi bir config yapisina tasindi.
2. Config icine varsayilan alanlar ile `it`, `hr` ve `operations` icin detayli; diger departmanlar icin temel profil metadatasi eklendi.
3. `bootstrap/app.php` ve `tests/bootstrap.php` guncellenerek `departments` config'inin uygulama ve test ortamina yuklenmesi saglandi.
4. `DepartmentService` icinde gorunur departmanlar config verisi ile zenginlestirilecek sekilde guncellendi.
5. Departman detay sayfasi icin `tagline`, `focus`, `hero`, `responsibilities`, `workflows` ve `kpis` alanlari servis uzerinden tek noktadan uretilir hale getirildi.
6. Departman liste ekraninda kartlar artik sadece isim ve aciklama degil, config'ten gelen odak ve tanim bilgisini de gosteriyor.
7. Departman detay ekranina profil, cekirdek sorumluluklar ve tipik workflow bloklari eklendi.
8. KPI gosterimleri config uzerinden filtrelenebilir hale getirilerek departman bazli farkli metrik setleri tanimlanabilir yapida birakildi.
