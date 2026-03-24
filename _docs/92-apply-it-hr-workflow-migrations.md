## 92. Apply IT HR Workflow Migrations

1. `018_link_employees_to_users_and_add_privacy_fields.sql` local veritabanina uygulandi.
2. `employees` tablosu mevcut HR kayitlarini mevcut kullanicilarla iliskilendirebilecek sekilde `user_id` alani ile genisletildi.
3. `employees` tablosuna veri isleme hukuki dayanagi icin `data_processing_basis` ve saklama suresi icin `retention_until` alanlari eklendi.
4. `019_add_password_lifecycle_and_creator_to_users.sql` local veritabanina uygulandi.
5. `users` tablosu IT tarafindan acilan hesaplari izlemek ve ilk giriste parola degisimini zorunlu kilmak icin `created_by_user_id`, `password_change_required_at` ve `password_changed_at` alanlari ile genisletildi.
6. Boylece IT-first / HR-second akisini destekleyen veri tabani zemini aktif hale getirildi.
