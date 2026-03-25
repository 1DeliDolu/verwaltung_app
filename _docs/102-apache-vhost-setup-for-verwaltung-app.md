## 102. Apache VHost Setup For Verwaltung App

1. `infra/apache/verwaltung_app.test.conf` dosyasi olusturuldu.
2. Virtual host dosyasi `verwaltung_app.test` host adini projenin `public` dizinine yonlendirecek sekilde hazirlandi.
3. `DocumentRoot` olarak `/mnt/d/Code/PHP/verwaltung_app/public` tanimlandi.
4. `AllowOverride All` etkinlestirilerek `.htaccess` tabanli yonlendirme ve rewrite akisina izin verildi.
5. Uygulamanin Apache varsayilan sayfasi yerine bu proje ile eslenmesi icin `/etc/apache2/sites-available` altina kopyalanip `a2ensite` ile etkinlestirilmesi gerektigi not edildi.
6. `verwaltung_app.test` host kaydinin `/etc/hosts` icine eklenmesi gerektigi not edildi.
7. WSL ortami kullanildigi icin `/etc/hosts` kaydinin yeniden olusabilecek sistem davranisindan etkilenebilecegi belirtildi.
