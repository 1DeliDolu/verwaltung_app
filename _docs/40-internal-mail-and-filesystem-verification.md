# 40 Internal Mail And Filesystem Verification

## Yapilan Islem

Ic mail ve local filesystem akislari uçtan uca dogrulandi.

## Uygulanan Adimlar

1. Admin kullanicisi ile `/mail` sayfasi acildi.
2. Header icinde `Mail` linki ve alici dizini goruldu.
3. `admin@verwaltung.local` kullanicisindan `mitarbeiter.it@verwaltung.local` hesabina `Probe Nachricht 1145` konulu mail gonderildi.
4. MailHog API uzerinde ayni mesajin olustugu dogrulandi.
5. `mitarbeiter.it@verwaltung.local` hesabi ile `/mail` sayfasinda gelen kutusunda mesajin goruldugu dogrulandi.
6. `leiter.it@verwaltung.local` hesabi ile `IT` departmanina `it-probe.txt` dosyasi yuklendi.
7. Dosyanin `infra/file/shares/it/uploads/` altina yazildigi dogrulandi.
8. `mitarbeiter.it@verwaltung.local` hesabi ile ayni dosyanin departman sayfasinda listelendigi ve upload formunun gorunmedigi dogrulandi.

## Sonuc

- Calisanlar ve birimler kendi aralarinda internal mail gonderebiliyor.
- Gelen ve giden mesajlar uygulama icinde MailHog uzerinden gorulebiliyor.
- Teamleiter departman dosya alanina yukleme yapabiliyor.
- Calisanlar dosyalari okuyabiliyor ancak upload yapamiyor.
