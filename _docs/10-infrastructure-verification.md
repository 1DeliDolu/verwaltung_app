# 10 Infrastructure Verification

## Yapilan Islem

Servis envanteri ve departman dokuman erisim modeli gercek veritabani ve HTTP akisi uzerinde dogrulandi.

## Uygulanan Adimlar

1. Roller, departmanlar, uyelikler, servisler ve dokumanlar icin migration dosyalari `verwaltung_app` veritabanina uygulandi.
2. Admin, `Teamleiter` ve `Mitarbeiter` kullanicilari seed ile hazirlandi.
3. `Teamleiter` hesabi ile giris yapilip `/services` sayfasinda `Mail Server` ve `File Server` kayitlari dogrulandi.
4. `Teamleiter` hesabi ile `/departments/it` sayfasinda dokuman olusturma formu goruldu.
5. `Teamleiter` hesabi ile yeni bir `Zugriffsfreigabe` dokumani olusturuldu.
6. Yeni dokumanin veritabanina yazildigi kontrol edildi.
7. `Mitarbeiter` hesabi ile ayni departman sayfasinda dokumanlarin gorunur oldugu ancak olusturma formunun gizli kaldigi dogrulandi.

## Sonuc

- `Teamleiter` departman dokumanlarini yonetebiliyor.
- `Mitarbeiter` departman dokumanlarini okuyabiliyor.
- Mail ve file server kayitlari uygulama icinde listelenebiliyor.
- Gercek OS duzeyinde mail/file server kurulumlari bu repoda yapilmadi; burada uygulama ici yonetim ve erisim katmani kuruldu.
