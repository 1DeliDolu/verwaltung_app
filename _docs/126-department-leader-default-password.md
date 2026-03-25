## 126. Department Leader Default Password

1. `database/seeds/004_department_users.sql` icindeki tum `leiter.*@verwaltung.local` hesaplarinin parola hash'i tek bir standart parola ile guncellendi.
2. Yeni standart parola `DockerDocker!123` olarak sabitlendi.
3. `ON DUPLICATE KEY UPDATE` yapisi korundugu icin seed tekrar calistirildiginda mevcut lider kullanicilarin parola hash'i de bu degerle yenilenir.
4. Boylece her departman lideri ayni demo giris bilgisiyle test edilebilir hale getirildi.
