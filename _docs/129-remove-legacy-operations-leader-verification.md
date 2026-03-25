## 129. Remove Legacy Operations Leader Verification

1. `mysql -h 127.0.0.1 -P 3306 -u root -pD0cker! -D verwaltung_app -e "SELECT ... WHERE u.email = 'leiter.op@verwaltung.local'"` ile eski kaydin mevcut oldugu dogrulandi.
2. `database/seeds/008_remove_legacy_operations_leader.sql` dosyasi olusturuldu.
3. Cleanup seed veritabanina uygulanarak eski `leiter.op@verwaltung.local` kullanicisi ve membership kaydi silindi.
4. Sonrasinda `users` ve `department_user` sorgulari ile `leiter.op@verwaltung.local` kaydinin artik bulunmadigi dogrulandi.
5. `operations` departmaninda `leiter.operations@verwaltung.local` kaydinin korunmaya devam ettigi dogrulandi.
