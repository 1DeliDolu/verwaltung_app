# 09 Infrastructure Access Layer

## Yapilan Islem

Departman dokuman klasorleri ve servis envanteri icin uygulama katmani eklendi.

## Eklenen veya Guncellenen Parcalar

- `app/Core/Router.php`
- `app/Models/User.php`
- `app/Models/Department.php`
- `app/Models/InfrastructureService.php`
- `app/Models/DepartmentDocument.php`
- `app/Services/DepartmentService.php`
- `app/Services/InfrastructureService.php`
- `app/Controllers/DepartmentController.php`
- `app/Controllers/InfrastructureController.php`
- `resources/views/departments/index.php`
- `resources/views/departments/show.php`
- `resources/views/services/index.php`
- `resources/views/dashboard/index.php`
- `routes/web.php`

## Sonuc

- Kullanici rolune gore gorulebilen departmanlar listeleniyor.
- Teamleiter departman sayfasinda yeni dokuman ekleyebiliyor.
- Calisanlar ayni klasor icerigini okuyabiliyor ancak yonetemiyor.
- Mail server ve file server kayitlari uygulama icinde listelenebiliyor.
