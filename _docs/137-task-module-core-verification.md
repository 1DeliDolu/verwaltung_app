## 137. Task Module Core Verification

1. `php -l app/Models/Task.php` calistirildi.
2. `php -l app/Models/TaskComment.php` calistirildi.
3. `php -l app/Services/TaskService.php` calistirildi.
4. `php -l app/Controllers/TaskController.php` calistirildi.
5. `php -l routes/web.php` calistirildi.
6. `php -l resources/views/tasks/index.php` calistirildi.
7. `php -l resources/views/tasks/create.php` calistirildi.
8. `php -l resources/views/tasks/edit.php` calistirildi.
9. `php -l resources/views/tasks/show.php` calistirildi.
10. `mysql -h 127.0.0.1 -P 3306 -u root -pD0cker! verwaltung_app < database/migrations/005_create_tasks_table.sql` calistirildi.
11. `mysql -h 127.0.0.1 -P 3306 -u root -pD0cker! verwaltung_app < database/migrations/006_create_task_comments_table.sql` calistirildi.
12. `information_schema.COLUMNS` sorgusu ile `tasks` ve `task_comments` tablolarinin olustugu ve beklenen alanlari icerdigi dogrulandi.
13. Header icinde `Tasks` baglantisinin gorundugu dogrulandi.
14. Task servisinde departman gorunurlugu ve `team_leader` tabanli yetki kontrolunun backend tarafinda tanimlandigi dogrulandi.
