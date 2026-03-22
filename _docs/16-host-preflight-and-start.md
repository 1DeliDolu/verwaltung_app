# 16 Host Preflight And Start

## Yapilan Islem

Host on kontrolu ve stack baslatma scriptleri eklendi.

## Eklenen veya Guncellenen Parcalar

- `infra/scripts/preflight-internal-services.sh`
- `infra/scripts/start-internal-services.sh`
- `infra/scripts/check-internal-services.sh`

## Sonuc

- Gerekli dosyalar ve portlar kontrol ediliyor.
- TLS sertifika varligi kontrol ediliyor.
- Stack tek script ile baslatilabiliyor.
