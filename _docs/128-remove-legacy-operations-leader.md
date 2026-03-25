## 128. Remove Legacy Operations Leader

1. Veritabaninda eski standart disi `leiter.op@verwaltung.local` kullanicisının halen `operations` departmanina `team_leader` olarak bagli oldugu tespit edildi.
2. Bu kaydin yeni standart `leiter.operations@verwaltung.local` adresi ile cakismamasi icin `database/seeds/008_remove_legacy_operations_leader.sql` eklendi.
3. Cleanup seed once `department_user` kaydini, sonra `users` kaydini silerek yabanci anahtar ve tekrar calistirma guvenligini koruyacak sekilde yazildi.
4. Boylece `operations` departmaninda yalnizca yeni standart lider hesabi kalacak sekilde kalici bir seed temizligi saglandi.
