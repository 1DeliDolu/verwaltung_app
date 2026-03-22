# 08 Infrastructure Schema

## Yapilan Islem

Sirket ici servis ve departman dokuman yonetimi icin temel veritabani semasi ve seed verileri eklendi.

## Eklenen veya Guncellenen Parcalar

- `database/migrations/002_create_roles_table.sql`
- `database/migrations/003_create_departments_table.sql`
- `database/migrations/004_create_department_user_table.sql`
- `database/migrations/007_create_infrastructure_services_table.sql`
- `database/migrations/008_create_department_documents_table.sql`
- `database/seeds/001_departments.sql`
- `database/seeds/002_roles.sql`
- `database/seeds/003_admin_user.sql`
- `database/seeds/004_department_users.sql`
- `database/seeds/005_department_memberships.sql`
- `database/seeds/006_infrastructure_services.sql`
- `database/seeds/007_department_documents.sql`

## Sonuc

- Roller: `admin`, `team_leader`, `employee`
- Departman ve uyelik iliskileri
- Servis envanteri: `mail server`, `file server`
- Departman klasor/dokuman veri modeli
