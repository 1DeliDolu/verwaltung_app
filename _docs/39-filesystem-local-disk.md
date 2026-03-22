# 39 Filesystem Local Disk

## Yapilan Islem

Laravel filesystem mantigina benzer local disk ve departman dosya yukleme/listeme katmani eklendi.

## Eklenen veya Guncellenen Parcalar

- `config/filesystems.php`
- `bootstrap/app.php`
- `app/Core/Request.php`
- `app/Services/FilesystemService.php`
- `app/Controllers/DepartmentController.php`
- `routes/web.php`
- `resources/views/departments/show.php`

## Sonuc

- Departman paylasim klasorleri local disk olarak okunabiliyor.
- Teamleiter departman klasorune dosya yukleyebiliyor.
- Calisanlar ayni klasorde bulunan dosyalari liste halinde gorebiliyor.
