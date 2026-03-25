## 136. Task Module Core

1. Bos olan `database/migrations/005_create_tasks_table.sql` ve `database/migrations/006_create_task_comments_table.sql` dosyalari gercek task veritabani semasiyla dolduruldu.
2. Gorevler icin `department_id`, `title`, `description`, `status`, `priority`, `due_date`, `created_by_user_id` ve `assigned_to_user_id` alanlari tanimlandi.
3. `task_comments` tablosu ile gorev basina yorum akisı eklendi.
4. Bos olan `app/Models/Task.php` ve `app/Models/TaskComment.php` dosyalari veri erisim katmani olarak dolduruldu.
5. `app/Services/TaskService.php` icinde gorunur departmanlar, gorev listeleme, atama kontrolu, yetki kurallari, durum guncelleme ve yorum ekleme akisi tanimlandi.
6. `app/Controllers/TaskController.php` icine listeleme, olusturma, gosterme, duzenleme, durum degistirme ve yorum ekleme aksiyonlari eklendi.
7. `routes/web.php` icinde `/tasks`, `/tasks/create`, `/tasks/{id}`, `/tasks/{id}/edit`, `/tasks/{id}/update`, `/tasks/{id}/status` ve `/tasks/{id}/comments` route'lari tanimlandi.
8. `resources/views/tasks/index.php`, `create.php`, `edit.php` ve `show.php` ile task modulu icin ilk tam ekran seti olusturuldu.
9. Header navigasyonuna `Tasks` baglantisi eklendi.
10. Task atama alani department secimine gore filtrelenen kullanici listesi ile calisacak sekilde JavaScript ile baglandi.
11. Workflow durumlari acik olarak `open`, `in_progress`, `blocked`, `done` seklinde modellendi.
12. Duzenleme yetkisi backend tarafinda yaratici, admin veya ayni departmanin `team_leader` rolune baglandi; durum guncelleme buna ek olarak assignee tarafindan da yapilabilir hale getirildi.
