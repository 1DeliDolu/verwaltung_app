## 119. Add Service Health Indicators Verification

1. `php -l app/Services/InfrastructureService.php` calistirildi.
2. `php -l app/Controllers/InfrastructureController.php` calistirildi.
3. `php -l resources/views/services/index.php` calistirildi.
4. `php -l resources/views/layouts/app.php` calistirildi.
5. `mysql -h 127.0.0.1 ... SELECT name, service_type, status FROM infrastructure_services` ile servis kayitlarinin mevcut oldugu dogrulandi.
6. `php -r` ile `127.0.0.1:1025`, `127.0.0.1:8025` ve `127.0.0.1:1445` socket kontrolleri yapildi.
7. Bu kontrol aninda `mail-smtp:down`, `mailhog-ui:down` ve `samba-demo:down` goruldu; health indicator mantiginin canli port durumuna gore sonuc uretecegi dogrulandi.
