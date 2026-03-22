# 24 Demo Verification

## Yapilan Islem

Demo mode altyapisi ve UI etiketi dogrulandi.

## Uygulanan Adimlar

1. Self-signed demo sertifikasi uretildi.
2. Demo env dosyasi `verwaltung.demo` degerleriyle uretildi.
3. Demo compose parse edilerek demo domain degerlerinin geldigı kontrol edildi.
4. Uygulama `/news` sayfasinda `Demo Umgebung` etiketi goruldu.

## Sonuc

- Demo altyapi stacki icin ayrik env ve cert akisi hazir.
- Uygulama arayuzunde ortam acikca demo olarak isaretleniyor.
