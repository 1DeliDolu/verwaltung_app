## 103. Apache VHost Setup For Verwaltung App Verification

1. Sistemde sadece Apache varsayilan sitesi (`000-default.conf`) etkin oldugu kontrol edildi.
2. `/etc/hosts` icinde `verwaltung_app.test` kaydi olmadigi dogrulandi.
3. Projenin HTTP giris noktasi olarak `public/index.php` bulundugu dogrulandi.
4. `infra/apache/verwaltung_app.test.conf` dosyasinin `DocumentRoot` degeri olarak `/mnt/d/Code/PHP/verwaltung_app/public` kullandigi dogrulandi.
5. `AllowOverride All` ve `Require all granted` ayarlarinin virtual host dosyasinda yer aldigi dogrulandi.
6. `sudo` parola gereksinimi nedeniyle sistem genelinde site etkinlestirme ve Apache reload adimlarinin ortam icinde otomatik tamamlanamadigi not edildi.
7. Manuel olarak `cp`, `a2ensite`, `a2enmod rewrite`, `apache2ctl configtest` ve `systemctl reload apache2` adimlarinin calistirilmasi gerektigi not edildi.
