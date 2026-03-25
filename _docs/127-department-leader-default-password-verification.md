## 127. Department Leader Default Password Verification

1. Eski hash icin `password_verify('DockerDocker!123', '<old-hash>')` kontrolu calistirildi ve eslesmedigi goruldu.
2. PHP `password_hash('DockerDocker!123', PASSWORD_DEFAULT)` ile yeni hash uretildi.
3. `database/seeds/004_department_users.sql` icindeki tum `leiter.*@verwaltung.local` kullanicilarinin bu yeni hash'i kullandigi dogrulandi.
4. `ON DUPLICATE KEY UPDATE` nedeniyle seed tekrar uygulandiginda mevcut kullanicilarin sifresinin de guncellenecegi dogrulandi.
