# 07 Header Verification

## Yapilan Islem

Header navigasyonu misafir ve giris yapmis kullanici durumlari icin dogrulandi.

## Uygulanan Adimlar

1. `/login` sayfasi acildi ve header icinde `News`, `Calendar`, `Login` linkleri goruldu.
2. Login formunun Almanca oldugu ve `E-Mail`, `Passwort` alanlarini gosterdigi dogrulandi.
3. `admin@verwaltung.local` kullanicisi ile giris yapildi.
4. Basarili login sonrasi `/dashboard` yonlendirmesi dogrulandi.
5. Dashboard sayfasinda header icinde `News`, `Calendar`, `Dashboard` linkleri ve `Abmelden` butonu goruldu.

## Sonuc

- Misafir kullanici icin header beklendigi gibi `Login` gosteriyor.
- Giris sonrasi ayni header `Dashboard` gosteriyor.
- UI metinleri Almanca olarak calisiyor.
